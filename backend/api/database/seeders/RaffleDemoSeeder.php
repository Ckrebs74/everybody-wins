<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\Category;
use Illuminate\Database\Seeder;

class RaffleDemoSeeder extends Seeder
{
    /**
     * Seeder für Demo-Szenarien:
     * - Raffle mit erreichtem Zielpreis
     * - Raffle ohne erreichtem Zielpreis (decision_type: give)
     * - Raffle ohne erreichtem Zielpreis (decision_type: keep)
     * - Raffle bereit zur Ziehung
     */
    public function run(): void
    {
        // Test-User erstellen (falls nicht vorhanden)
        $seller = User::firstOrCreate(
            ['email' => 'seller@demo.de'],
            [
                'password' => bcrypt('password'),
                'role' => 'seller',
                'age_verified' => true,
                'first_name' => 'Max',
                'last_name' => 'Verkäufer'
            ]
        );

        $buyers = [];
        for ($i = 1; $i <= 10; $i++) {
            $buyers[] = User::firstOrCreate(
                ['email' => "buyer{$i}@demo.de"],
                [
                    'password' => bcrypt('password'),
                    'role' => 'buyer',
                    'age_verified' => true,
                    'first_name' => "Käufer{$i}",
                    'wallet_balance' => 50.00
                ]
            );
        }

        $category = Category::where('slug', 'elektronik')->first();

        // =====================================================
        // SZENARIO 1: Target erreicht, bereit zur Ziehung
        // =====================================================
        $product1 = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => '[DEMO] iPhone 16 Pro - TARGET REACHED',
            'description' => 'Demo-Raffle für Szenario 1: Zielpreis wurde erreicht!',
            'brand' => 'Apple',
            'condition' => 'new',
            'retail_price' => 1299.00,
            'target_price' => 800.00,
            'decision_type' => 'give',
            'status' => 'active',
            'slug' => 'demo-iphone-target-reached-' . uniqid()
        ]);

        $raffle1 = Raffle::create([
            'product_id' => $product1->id,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subHours(1), // Zeit abgelaufen
            'target_price' => 800.00,
            'platform_fee' => 240.00,
            'total_target' => 1040.00,
            'status' => 'pending_draw',
            'target_reached' => true,
            'tickets_sold' => 1100,
            'total_revenue' => 1100.00, // Über Target!
            'unique_participants' => 50
        ]);

        // Tickets für Raffle 1
        foreach ($buyers as $index => $buyer) {
            for ($j = 0; $j < 10; $j++) {
                Ticket::create([
                    'raffle_id' => $raffle1->id,
                    'user_id' => $buyer->id,
                    'ticket_number' => 'TKT-' . strtoupper(uniqid()),
                    'price' => 1.00,
                    'status' => 'valid'
                ]);
            }
        }

        // =====================================================
        // SZENARIO 2: Target NICHT erreicht, decision_type = give
        // =====================================================
        $product2 = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => '[DEMO] PlayStation 5 Pro - TARGET MISSED (GIVE)',
            'description' => 'Demo-Raffle für Szenario 2: Zielpreis nicht erreicht, Verkäufer gibt Produkt trotzdem ab',
            'brand' => 'Sony',
            'condition' => 'new',
            'retail_price' => 699.00,
            'target_price' => 400.00,
            'decision_type' => 'give', // Verkäufer gibt Produkt ab
            'status' => 'active',
            'slug' => 'demo-ps5-target-missed-give-' . uniqid()
        ]);

        $raffle2 = Raffle::create([
            'product_id' => $product2->id,
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subMinutes(30), // Zeit gerade abgelaufen
            'target_price' => 400.00,
            'platform_fee' => 120.00,
            'total_target' => 520.00,
            'status' => 'pending_draw',
            'target_reached' => false,
            'tickets_sold' => 250,
            'total_revenue' => 250.00, // Nur 48% vom Target
            'unique_participants' => 30
        ]);

        // Tickets für Raffle 2
        foreach ($buyers as $buyer) {
            for ($j = 0; $j < 5; $j++) {
                Ticket::create([
                    'raffle_id' => $raffle2->id,
                    'user_id' => $buyer->id,
                    'ticket_number' => 'TKT-' . strtoupper(uniqid()),
                    'price' => 1.00,
                    'status' => 'valid'
                ]);
            }
        }

        // =====================================================
        // SZENARIO 3: Target NICHT erreicht, decision_type = keep
        // =====================================================
        $product3 = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => '[DEMO] MacBook Air M3 - TARGET MISSED (KEEP)',
            'description' => 'Demo-Raffle für Szenario 3: Zielpreis nicht erreicht, Verkäufer behält Produkt',
            'brand' => 'Apple',
            'condition' => 'new',
            'retail_price' => 1199.00,
            'target_price' => 700.00,
            'decision_type' => 'keep', // Verkäufer behält Produkt
            'status' => 'active',
            'slug' => 'demo-macbook-target-missed-keep-' . uniqid()
        ]);

        $raffle3 = Raffle::create([
            'product_id' => $product3->id,
            'starts_at' => now()->subDays(4),
            'ends_at' => now()->subMinutes(10),
            'target_price' => 700.00,
            'platform_fee' => 210.00,
            'total_target' => 910.00,
            'status' => 'pending_draw',
            'target_reached' => false,
            'tickets_sold' => 350,
            'total_revenue' => 350.00, // Nur 38% vom Target
            'unique_participants' => 25
        ]);

        // Tickets für Raffle 3
        foreach ($buyers as $buyer) {
            for ($j = 0; $j < 7; $j++) {
                Ticket::create([
                    'raffle_id' => $raffle3->id,
                    'user_id' => $buyer->id,
                    'ticket_number' => 'TKT-' . strtoupper(uniqid()),
                    'price' => 1.00,
                    'status' => 'valid'
                ]);
            }
        }

        // =====================================================
        // SZENARIO 4: Noch aktives Raffle (zum Testen)
        // =====================================================
        $product4 = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => '[DEMO] Samsung OLED TV - ACTIVE',
            'description' => 'Demo-Raffle: Noch aktiv, zum Testen von Ticket-Käufen',
            'brand' => 'Samsung',
            'condition' => 'new',
            'retail_price' => 2199.00,
            'target_price' => 1200.00,
            'decision_type' => 'give',
            'status' => 'active',
            'slug' => 'demo-samsung-tv-active-' . uniqid()
        ]);

        $raffle4 = Raffle::create([
            'product_id' => $product4->id,
            'starts_at' => now()->subHours(6),
            'ends_at' => now()->addDays(2), // Noch 2 Tage Zeit
            'target_price' => 1200.00,
            'platform_fee' => 360.00,
            'total_target' => 1560.00,
            'status' => 'active',
            'target_reached' => false,
            'tickets_sold' => 450,
            'total_revenue' => 450.00, // 29% vom Target
            'unique_participants' => 35
        ]);

        // Tickets für Raffle 4
        foreach ($buyers as $buyer) {
            for ($j = 0; $j < 9; $j++) {
                Ticket::create([
                    'raffle_id' => $raffle4->id,
                    'user_id' => $buyer->id,
                    'ticket_number' => 'TKT-' . strtoupper(uniqid()),
                    'price' => 1.00,
                    'status' => 'valid'
                ]);
            }
        }

        $this->command->info('✅ Demo-Daten erstellt!');
        $this->command->info('   - Raffle #' . $raffle1->id . ': Target erreicht (bereit zur Ziehung)');
        $this->command->info('   - Raffle #' . $raffle2->id . ': Target nicht erreicht, GIVE (bereit zur Ziehung)');
        $this->command->info('   - Raffle #' . $raffle3->id . ': Target nicht erreicht, KEEP (bereit zur Ziehung)');
        $this->command->info('   - Raffle #' . $raffle4->id . ': Noch aktiv (zum Testen)');
        $this->command->newLine();
        $this->command->info('🎲 Test Ziehungen mit:');
        $this->command->info('   php artisan raffles:draw --id=' . $raffle1->id);
        $this->command->info('   php artisan raffles:draw --id=' . $raffle2->id);
        $this->command->info('   php artisan raffles:draw --id=' . $raffle3->id);
    }
}