<?php

namespace App\Console\Commands;

use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;

class ResetDemoRaffles extends Command
{
    protected $signature = 'raffles:reset-demo 
                            {--ids=11,12,13 : Comma-separated raffle IDs to reset}';
    
    protected $description = 'Reset demo raffles to pending_draw state';

    public function handle()
    {
        $ids = explode(',', $this->option('ids'));
        
        $this->info('🔄 Resetting demo raffles...');
        
        // 1. Raffles zurücksetzen
        $raffles = Raffle::whereIn('id', $ids)->get();
        
        if ($raffles->isEmpty()) {
            $this->error('No raffles found with IDs: ' . implode(', ', $ids));
            return 1;
        }
        
        foreach($raffles as $raffle) {
            $raffle->update([
                'status' => 'pending_draw',
                'winner_ticket_id' => null,
                'winner_notified_at' => null,
                'drawn_at' => null,
                'final_decision' => null,
                'payout_amount' => null,
                'random_seed' => null,
                'target_reached' => $raffle->total_revenue >= $raffle->total_target ? 1 : 0
            ]);
            
            $this->line("✓ Reset Raffle #{$raffle->id}");
        }
        
        // 2. Tickets zurücksetzen
        $ticketCount = Ticket::whereIn('raffle_id', $ids)->update(['status' => 'valid']);
        $this->line("✓ Reset {$ticketCount} tickets to 'valid'");
        
        // 3. Alte Transactions löschen
        $transactionCount = Transaction::whereIn('type', ['winning'])->delete();
        $this->line("✓ Deleted {$transactionCount} winning transactions");
        
        // 4. Verkäufer Wallet zurücksetzen
        $seller = User::find(1);
        if ($seller) {
            $seller->update(['wallet_balance' => 0]);
            $this->line("✓ Reset seller wallet to €0");
        }
        
        // 5. Demo-Gewinner Wallets zurücksetzen (User 3-12)
        $resetCount = User::whereIn('id', range(3, 12))->update(['wallet_balance' => 50]);
        $this->line("✓ Reset {$resetCount} demo buyer wallets to €50");
        
        // 6. Notifications löschen
        $notificationCount = Notification::whereIn('type', [
            'raffle_won', 
            'product_won', 
            'money_won', 
            'raffle_completed'
        ])->delete();
        $this->line("✓ Deleted {$notificationCount} notifications");
        
        $this->newLine();
        $this->info('✅ Demo raffles reset successfully!');
        $this->newLine();
        $this->comment('You can now draw them again:');
        foreach($ids as $id) {
            $this->comment("  php artisan raffles:draw --id={$id}");
        }
        
        return 0;
    }
}