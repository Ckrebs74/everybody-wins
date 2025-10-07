<?php

// =====================================================
// FILE 2: app/Console/Commands/CheckRaffleProgress.php
// =====================================================

namespace App\Console\Commands;

use App\Models\Raffle;
use Illuminate\Console\Command;

class CheckRaffleProgress extends Command
{
    protected $signature = 'raffles:check-progress';
    protected $description = 'Check if any active raffle has reached target or end time';

    public function handle()
    {
        $now = now();
        
        $raffles = Raffle::where('status', 'active')
            ->where(function($query) use ($now) {
                // Entweder Zeit abgelaufen ODER Zielpreis erreicht
                $query->where('ends_at', '<=', $now)
                      ->orWhereRaw('total_revenue >= total_target');
            })
            ->get();
        
        if ($raffles->isEmpty()) {
            $this->info('No raffles ready for drawing.');
            return 0;
        }
        
        $count = 0;
        foreach ($raffles as $raffle) {
            try {
                $reason = $raffle->total_revenue >= $raffle->total_target 
                    ? 'Target reached' 
                    : 'Time expired';
                
                $raffle->update(['status' => 'pending_draw']);
                
                $this->info("✓ Raffle #{$raffle->id} ready for draw ({$reason})");
                $count++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to update raffle #{$raffle->id}: {$e->getMessage()}");
            }
        }
        
        $this->info("\n{$count} raffle(s) marked for drawing!");
        return 0;
    }
}