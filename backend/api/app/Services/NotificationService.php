<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\Raffle;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Erstelle eine Benachrichtigung für einen User
     */
    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'read_at' => null
        ]);
    }

    /**
     * Benachrichtige den Gewinner einer Verlosung
     */
    public function notifyWinner(Raffle $raffle): void
    {
        $winner = $raffle->winnerTicket->user;
        $product = $raffle->product;

        // Bestimme was der Gewinner erhält
        if ($raffle->target_reached || $raffle->product->decision_type === 'give') {
            // Gewinner erhält das Produkt
            $title = "🎉 Glückwunsch! Du hast gewonnen!";
            $message = "Du hast die Verlosung für '{$product->title}' gewonnen! Das Produkt gehört jetzt dir.";
        } else {
            // Gewinner erhält Geldpreis
            $netRevenue = $raffle->total_revenue - $raffle->platform_fee;
            $title = "🎉 Glückwunsch! Du hast gewonnen!";
            $message = "Du hast die Verlosung für '{$product->title}' gewonnen! Du erhältst {$netRevenue}€.";
        }

        // Dashboard-Benachrichtigung
        $this->createNotification(
            user: $winner,
            type: 'winner_notification',
            title: $title,
            message: $message,
            actionUrl: route('raffles.show', $raffle->product->slug)
        );

        // Email senden (in Queue)
        try {
            \Illuminate\Support\Facades\Mail::to($winner->email)
                ->queue(new \App\Mail\WinnerNotification($raffle, $winner));
                
            Log::info('Winner email queued', [
                'raffle_id' => $raffle->id,
                'winner_id' => $winner->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue winner email', [
                'raffle_id' => $raffle->id,
                'winner_id' => $winner->id,
                'error' => $e->getMessage()
            ]);
        }

        Log::info('Winner notification created', [
            'raffle_id' => $raffle->id,
            'winner_id' => $winner->id
        ]);
    }

    /**
     * Benachrichtige den Verkäufer über den Abschluss
     */
    public function notifySeller(Raffle $raffle): void
    {
        $seller = $raffle->product->seller;
        $product = $raffle->product;

        // Bestimme Nachricht basierend auf Szenario
        if ($raffle->target_reached) {
            // Zielpreis erreicht - Verkäufer erhält Geld
            $title = "💰 Zielpreis erreicht!";
            $message = "Deine Verlosung für '{$product->title}' ist abgeschlossen. Du erhältst {$raffle->target_price}€.";
        } else {
            if ($product->decision_type === 'give') {
                // Zielpreis nicht erreicht, aber Produkt abgegeben
                $netRevenue = $raffle->total_revenue - $raffle->platform_fee;
                $title = "📦 Verlosung abgeschlossen";
                $message = "Deine Verlosung für '{$product->title}' ist abgeschlossen. Du erhältst {$netRevenue}€. Das Produkt geht an den Gewinner.";
            } else {
                // Zielpreis nicht erreicht, Produkt behalten
                $title = "🔄 Verlosung abgeschlossen - Produkt zurück";
                $message = "Deine Verlosung für '{$product->title}' ist abgeschlossen. Der Zielpreis wurde nicht erreicht, du behältst das Produkt.";
            }
        }

        // Dashboard-Benachrichtigung
        $this->createNotification(
            user: $seller,
            type: 'seller_payout',
            title: $title,
            message: $message,
            actionUrl: route('raffles.show', $product->slug)
        );

        // Email senden (in Queue)
        try {
            \Illuminate\Support\Facades\Mail::to($seller->email)
                ->queue(new \App\Mail\SellerPayoutNotification($raffle, $seller));
                
            Log::info('Seller email queued', [
                'raffle_id' => $raffle->id,
                'seller_id' => $seller->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue seller email', [
                'raffle_id' => $raffle->id,
                'seller_id' => $seller->id,
                'error' => $e->getMessage()
            ]);
        }

        Log::info('Seller notification created', [
            'raffle_id' => $raffle->id,
            'seller_id' => $seller->id
        ]);
    }

    /**
     * Benachrichtige alle Verlierer (optional)
     */
    public function notifyLosers(Raffle $raffle): void
    {
        $loserTickets = $raffle->tickets()
            ->where('status', 'loser')
            ->with('user')
            ->get();

        $product = $raffle->product;

        foreach ($loserTickets as $ticket) {
            $this->createNotification(
                user: $ticket->user,
                type: 'raffle_completed',
                title: "🎲 Verlosung beendet",
                message: "Die Verlosung für '{$product->title}' ist abgeschlossen. Leider hast du dieses Mal nicht gewonnen. Viel Glück beim nächsten Mal!",
                actionUrl: route('raffles.show', $product->slug)
            );
        }

        Log::info('Loser notifications created', [
            'raffle_id' => $raffle->id,
            'count' => $loserTickets->count()
        ]);
    }

    /**
     * Benachrichtige User über Wallet-Einzahlung
     */
    public function notifyWalletDeposit(User $user, float $amount): void
    {
        $this->createNotification(
            user: $user,
            type: 'wallet_deposit',
            title: "💳 Einzahlung erfolgreich",
            message: "Deine Einzahlung von {$amount}€ wurde erfolgreich verarbeitet.",
            actionUrl: route('dashboard')
        );
    }

    /**
     * Benachrichtige User über Spending Limit
     */
    public function notifySpendingLimit(User $user): void
    {
        $this->createNotification(
            user: $user,
            type: 'spending_limit',
            title: "⚠️ Ausgabenlimit erreicht",
            message: "Du hast dein Ausgabenlimit von 10€ pro Stunde erreicht. Bitte warte, bevor du weitere Lose kaufst.",
            actionUrl: route('dashboard')
        );
    }

    /**
     * Benachrichtige User über Auszahlung
     */
    public function notifyWithdrawal(User $user, float $amount): void
    {
        $this->createNotification(
            user: $user,
            type: 'withdrawal',
            title: "💸 Auszahlung wird bearbeitet",
            message: "Deine Auszahlung von {$amount}€ wird bearbeitet und wird in 2-3 Werktagen auf deinem Konto sein.",
            actionUrl: route('dashboard')
        );
    }

    /**
     * Markiere Benachrichtigung als gelesen
     */
    public function markAsRead(Notification $notification): void
    {
        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }
    }

    /**
     * Markiere alle Benachrichtigungen eines Users als gelesen
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Hole ungelesene Benachrichtigungen
     */
    public function getUnreadNotifications(User $user)
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Zähle ungelesene Benachrichtigungen
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->count();
    }
}