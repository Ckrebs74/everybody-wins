<?php

// =====================================================
// FILE 3: app/Console/Commands/DrawPendingRaffles.php
// =====================================================

namespace App\Console\Commands;

use App\Models\Raffle;
use App\Services\RaffleDrawService;
use Illuminate\Console\Command;

class DrawPendingRaffles extends Command
{
    protected $signature = 'raffles:draw 
                            {--id= : Draw specific raffle by ID}
                            {--auto : Automatically draw all pending raffles}';
    
    protected $description = 'Draw winners for pending raffles';

    protected RaffleDrawService $drawService;

    public function __construct(RaffleDrawService $drawService)
    {
        parent::__construct();
        $this->drawService = $drawService;
    }

    public function handle()
    {
        // Einzelnes Raffle ziehen
        if ($id = $this->option('id')) {
            return $this->drawSingleRaffle($id);
        }
        
        // Alle pending Raffles automatisch ziehen
        if ($this->option('auto')) {
            return $this->drawAllPending();
        }
        
        // Interaktiv: User wählt aus
        return $this->drawInteractive();
    }
    
    protected function drawSingleRaffle($id)
    {
        $raffle = Raffle::find($id);
        
        if (!$raffle) {
            $this->error("Raffle #{$id} not found!");
            return 1;
        }
        
        if ($raffle->status !== 'pending_draw') {
            $this->error("Raffle #{$id} is not ready for draw (Status: {$raffle->status})");
            return 1;
        }
        
        return $this->executeDraw($raffle);
    }
    
    protected function drawAllPending()
    {
        $raffles = Raffle::where('status', 'pending_draw')->get();
        
        if ($raffles->isEmpty()) {
            $this->info('No pending raffles to draw.');
            return 0;
        }
        
        $this->info("Found {$raffles->count()} pending raffle(s)...\n");
        
        $successCount = 0;
        foreach ($raffles as $raffle) {
            if ($this->executeDraw($raffle) === 0) {
                $successCount++;
            }
        }
        
        $this->info("\n✅ {$successCount}/{$raffles->count()} raffles drawn successfully!");
        return 0;
    }
    
    protected function drawInteractive()
    {
        $raffles = Raffle::where('status', 'pending_draw')->get();
        
        if ($raffles->isEmpty()) {
            $this->info('No pending raffles to draw.');
            return 0;
        }
        
        $this->table(
            ['ID', 'Product', 'Tickets Sold', 'Target', 'Revenue', 'Status'],
            $raffles->map(fn($r) => [
                $r->id,
                $r->product->title,
                $r->tickets_sold,
                '€' . number_format($r->total_target, 2),
                '€' . number_format($r->total_revenue, 2),
                $r->target_reached ? '✓ Target reached' : '✗ Time expired'
            ])
        );
        
        $id = $this->ask('Enter Raffle ID to draw (or "all" for all)');
        
        if ($id === 'all') {
            return $this->drawAllPending();
        }
        
        return $this->drawSingleRaffle($id);
    }
    
    protected function executeDraw(Raffle $raffle)
    {
        try {
            $this->info("\n🎲 Drawing raffle #{$raffle->id} - {$raffle->product->title}...");
            
            // Preview zeigen
            $simulation = $this->drawService->simulateDraw($raffle);
            $this->line("   Simulated winner: User #{$simulation['winner_user_id']} ({$simulation['winner_email']})");
            
            // Bestätigung holen (außer bei --auto)
            if (!$this->option('auto')) {
                if (!$this->confirm('Proceed with real draw?', true)) {
                    $this->warn('Draw cancelled.');
                    return 1;
                }
            }
            
            // Echte Ziehung
            $result = $this->drawService->drawRaffle($raffle);
            
            $this->newLine();
            $this->info('🎉 WINNER DRAWN! 🎉');
            $this->line("   Winner: User #{$result['winner_ticket']->user_id}");
            $this->line("   Ticket: {$result['winner_ticket']->ticket_number}");
            $this->line("   Scenario: {$result['payout_result']['scenario']}");
            $this->line("   Decision: {$result['payout_result']['final_decision']}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("✗ Draw failed: {$e->getMessage()}");
            return 1;
        }
    }
}