<?php

// =====================================================
// FILE 1: app/Console/Commands/StartScheduledRaffles.php
// =====================================================

namespace App\Console\Commands;

use App\Models\Raffle;
use Illuminate\Console\Command;

class StartScheduledRaffles extends Command
{
    protected $signature = 'raffles:start';
    protected $description = 'Start all scheduled raffles that have reached their start time';

    public function handle()
    {
        $now = now();
        
        $raffles = Raffle::where('status', 'scheduled')
            ->where('starts_at', '<=', $now)
            ->get();
        
        if ($raffles->isEmpty()) {
            $this->info('No scheduled raffles to start.');
            return 0;
        }
        
        $count = 0;
        foreach ($raffles as $raffle) {
            try {
                $raffle->update(['status' => 'active']);
                $this->info("✓ Started raffle #{$raffle->id} - {$raffle->product->title}");
                $count++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to start raffle #{$raffle->id}: {$e->getMessage()}");
            }
        }
        
        $this->info("\n{$count} raffle(s) started successfully!");
        return 0;
    }
}