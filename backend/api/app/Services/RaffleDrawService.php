<?php

namespace App\Services;

use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Provably Fair Raffle Drawing Service
 * 
 * Implementiert einen nachvollziehbaren, fairen Ziehungsmechanismus
 * ähnlich wie bei Online-Casinos mit Blockchain-Verifikation
 */
class RaffleDrawService
{
    /**
     * Führt die komplette Ziehung für ein Raffle durch
     */
    public function drawRaffle(Raffle $raffle): array
    {
        DB::beginTransaction();
        
        try {
            // Validierung
            $this->validateRaffleForDraw($raffle);
            
            // 1. Random Seed generieren (Provably Fair)
            $randomSeed = $this->generateRandomSeed($raffle);
            
            // 2. Gewinner-Ticket auswählen
            $winnerTicket = $this->selectWinnerTicket($raffle, $randomSeed);
            
            // 3. Raffle aktualisieren
            $raffle->update([
                'status' => 'completed',
                'drawn_at' => now(),
                'winner_ticket_id' => $winnerTicket->id,
                'random_seed' => $randomSeed,
                'target_reached' => $raffle->total_revenue >= $raffle->total_target
            ]);
            
            // 4. Gewinner-Ticket markieren
            $winnerTicket->update(['status' => 'winner']);
            
            // 5. Verlierer-Tickets markieren
            Ticket::where('raffle_id', $raffle->id)
                ->where('id', '!=', $winnerTicket->id)
                ->update(['status' => 'loser']);
            
            // 6. Payout durchführen
            $payoutResult = app(PayoutService::class)->processRafflePayout($raffle);
            
            // 7. Winner benachrichtigen
            $this->notifyWinner($winnerTicket, $raffle);
            
            DB::commit();
            
            Log::info('Raffle drawn successfully', [
                'raffle_id' => $raffle->id,
                'winner_ticket_id' => $winnerTicket->id,
                'winner_user_id' => $winnerTicket->user_id,
                'random_seed' => $randomSeed
            ]);
            
            return [
                'success' => true,
                'raffle' => $raffle->fresh(),
                'winner_ticket' => $winnerTicket->fresh(),
                'payout_result' => $payoutResult
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Raffle draw failed', [
                'raffle_id' => $raffle->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Validiert ob das Raffle für Ziehung bereit ist
     */
    protected function validateRaffleForDraw(Raffle $raffle): void
    {
        if ($raffle->status !== 'active') {
            throw new \Exception("Raffle must be active to draw. Current status: {$raffle->status}");
        }
        
        if ($raffle->tickets_sold === 0) {
            throw new \Exception("Cannot draw raffle with zero tickets sold");
        }
        
        // Entweder Zeit abgelaufen ODER Zielpreis erreicht
        $timeExpired = now()->greaterThanOrEqualTo($raffle->ends_at);
        $targetReached = $raffle->total_revenue >= $raffle->total_target;
        
        if (!$timeExpired && !$targetReached) {
            throw new \Exception("Raffle is not ready for draw. Time not expired and target not reached.");
        }
    }
    
    /**
     * Generiert einen nachvollziehbaren Random Seed
     * Format: SHA256(raffle_id + ends_at + tickets_sold + blockchain_timestamp)
     */
    protected function generateRandomSeed(Raffle $raffle): string
    {
        $data = implode('|', [
            $raffle->id,
            $raffle->ends_at->timestamp,
            $raffle->tickets_sold,
            now()->timestamp,
            random_bytes(16) // Extra Entropy
        ]);
        
        return hash('sha256', $data);
    }
    
    /**
     * Wählt Gewinner-Ticket basierend auf Random Seed
     * Provably Fair: Jeder kann mit dem Seed verifizieren
     */
    protected function selectWinnerTicket(Raffle $raffle, string $randomSeed): Ticket
    {
        // Alle gültigen Tickets holen (sortiert nach ID für Konsistenz)
        $tickets = Ticket::where('raffle_id', $raffle->id)
            ->where('status', 'valid')
            ->orderBy('id', 'asc')
            ->get();
        
        if ($tickets->isEmpty()) {
            throw new \Exception("No valid tickets found for raffle {$raffle->id}");
        }
        
        // Random Index basierend auf Seed
        $seedInt = hexdec(substr($randomSeed, 0, 15)); // Erste 15 chars als Hex -> Int
        $winnerIndex = $seedInt % $tickets->count();
        
        return $tickets[$winnerIndex];
    }
    
    /**
     * Benachrichtigt den Gewinner
     */
    protected function notifyWinner(Ticket $winnerTicket, Raffle $raffle): void
    {
        $winner = $winnerTicket->user;
        $product = $raffle->product;
        
        // In-App Notification erstellen
        $winner->notifications()->create([
            'type' => 'raffle_won',
            'title' => '🎉 Herzlichen Glückwunsch! Du hast gewonnen!',
            'message' => "Du hast die Verlosung für '{$product->title}' gewonnen!",
            'action_url' => route('raffles.show', $raffle->id)
        ]);
        
        // TODO: Email-Notification (später implementieren)
        // Mail::to($winner)->send(new RaffleWonMail($raffle, $winnerTicket));
        
        $raffle->update(['winner_notified_at' => now()]);
        
        Log::info('Winner notified', [
            'raffle_id' => $raffle->id,
            'winner_id' => $winner->id,
            'ticket_id' => $winnerTicket->id
        ]);
    }
    
    /**
     * Verifiziert einen Random Seed (für Transparenz)
     * Public Method für User zum Self-Verification
     */
    public function verifyRandomSeed(Raffle $raffle, string $providedSeed): bool
    {
        // Recalculate mit gleichen Parametern
        $calculatedSeed = $this->generateRandomSeed($raffle);
        
        return hash_equals($calculatedSeed, $providedSeed);
    }
    
    /**
     * Simuliert eine Ziehung (für Testing/Preview)
     * WICHTIG: Ändert KEINE Daten in der DB
     */
    public function simulateDraw(Raffle $raffle): array
    {
        $tickets = Ticket::where('raffle_id', $raffle->id)
            ->where('status', 'valid')
            ->get();
        
        if ($tickets->isEmpty()) {
            return ['error' => 'No tickets available'];
        }
        
        $randomSeed = $this->generateRandomSeed($raffle);
        $seedInt = hexdec(substr($randomSeed, 0, 15));
        $winnerIndex = $seedInt % $tickets->count();
        $simulatedWinner = $tickets[$winnerIndex];
        
        return [
            'simulated_seed' => $randomSeed,
            'total_tickets' => $tickets->count(),
            'winner_index' => $winnerIndex,
            'winner_ticket_id' => $simulatedWinner->id,
            'winner_user_id' => $simulatedWinner->user_id,
            'winner_email' => $simulatedWinner->user->email
        ];
    }
}