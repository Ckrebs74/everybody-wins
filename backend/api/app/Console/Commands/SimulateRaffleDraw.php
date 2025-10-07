<?php

// =====================================================
// FILE 4: app/Console/Commands/SimulateRaffleDraw.php
// =====================================================

namespace App\Console\Commands;

use App\Models\Raffle;
use App\Services\RaffleDrawService;
use Illuminate\Console\Command;

class SimulateRaffleDraw extends Command
{
    protected $signature = 'raffles:simulate {raffle_id}';
    protected $description = 'Simulate a raffle draw without changing database';

    protected RaffleDrawService $drawService;

    public function __construct(RaffleDrawService $drawService)
    {
        parent::__construct();
        $this->drawService = $drawService;
    }

    public function handle()
    {
        $raffleId = $this->argument('raffle_id');
        $raffle = Raffle::find($raffleId);
        
        if (!$raffle) {
            $this->error("Raffle #{$raffleId} not found!");
            return 1;
        }
        
        $this->info("Simulating draw for: {$raffle->product->title}");
        $this->line("Status: {$raffle->status}");
        $this->line("Tickets sold: {$raffle->tickets_sold}");
        $this->line("Revenue: €{$raffle->total_revenue} / €{$raffle->total_target}");
        $this->newLine();
        
        $result = $this->drawService->simulateDraw($raffle);
        
        if (isset($result['error'])) {
            $this->error($result['error']);
            return 1;
        }
        
        $this->table(
            ['Property', 'Value'],
            [
                ['Random Seed', substr($result['simulated_seed'], 0, 32) . '...'],
                ['Total Tickets', $result['total_tickets']],
                ['Winner Index', $result['winner_index']],
                ['Winner Ticket ID', $result['winner_ticket_id']],
                ['Winner User ID', $result['winner_user_id']],
                ['Winner Email', $result['winner_email']]
            ]
        );
        
        $this->warn('⚠️  This was a SIMULATION. No data was changed in the database.');
        
        return 0;
    }
}