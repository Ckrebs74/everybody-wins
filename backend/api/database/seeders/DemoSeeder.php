<?php
// =====================================================
// FILE: database/seeders/DemoSeeder.php
// =====================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Product;
use App\Models\Raffle;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Erstelle Kategorien
        $categories = [
            ['name' => 'Elektronik', 'slug' => 'elektronik'],
            ['name' => 'Mode', 'slug' => 'mode'],
            ['name' => 'Gaming', 'slug' => 'gaming'],
            ['name' => 'Haushalt', 'slug' => 'haushalt'],
            ['name' => 'Sport', 'slug' => 'sport'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 2. Erstelle Test-Verkäufer
        $seller = User::firstOrCreate(
            ['email' => 'seller@demo.de'],
            [
                'email' => 'seller@demo.de',
                'password' => Hash::make('password'),
                'first_name' => 'Max',
                'last_name' => 'Verkäufer',
                'role' => 'seller',
                'age_verified' => true,
                'birth_date' => '1990-01-01'
            ]
        );

        // Wallet für Verkäufer
        Wallet::firstOrCreate(
            ['user_id' => $seller->id],
            ['balance' => 0]
        );

        // 3. Erstelle Test-Käufer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer@demo.de'],
            [
                'email' => 'buyer@demo.de',
                'password' => Hash::make('password'),
                'first_name' => 'Lisa',
                'last_name' => 'Käufer',
                'role' => 'buyer',
                'age_verified' => true,
                'birth_date' => '1995-05-15'
            ]
        );

        // Wallet für Käufer mit Startguthaben
        Wallet::firstOrCreate(
            ['user_id' => $buyer->id],
            [
                'balance' => 50.00,
                'bonus_balance' => 5.00
            ]
        );

        // 4. Erstelle Demo-Produkte mit aktiven Raffles
        $products = [
            [
                'title' => 'iPhone 16 Pro Max',
                'description' => 'Brandneues iPhone 16 Pro Max mit 256GB Speicher. Originalverpackt und ungeöffnet.',
                'category' => 'elektronik',
                'target_price' => 800.00,
                'retail_price' => 1299.00,
                'brand' => 'Apple',
                'condition' => 'new',
                'decision_type' => 'give'
            ],
            [
                'title' => 'PlayStation 5 Pro',
                'description' => 'Sony PlayStation 5 Pro mit 2TB SSD. Die neueste Gaming-Konsole für ultimatives Gaming-Erlebnis.',
                'category' => 'gaming',
                'target_price' => 400.00,
                'retail_price' => 699.00,
                'brand' => 'Sony',
                'condition' => 'new',
                'decision_type' => 'give'
            ],
            [
                'title' => 'MacBook Air M3',
                'description' => 'Apple MacBook Air mit M3 Chip, 8GB RAM und 256GB SSD. Perfekt für Arbeit und Studium.',
                'category' => 'elektronik',
                'target_price' => 700.00,
                'retail_price' => 1199.00,
                'brand' => 'Apple',
                'condition' => 'new',
                'decision_type' => 'keep'
            ],
            [
                'title' => 'Nike Air Jordan 1 Retro',
                'description' => 'Original Nike Air Jordan 1 Retro High OG. Größe 42. Limitierte Edition.',
                'category' => 'mode',
                'target_price' => 150.00,
                'retail_price' => 250.00,
                'brand' => 'Nike',
                'condition' => 'new',
                'decision_type' => 'give'
            ],
            [
                'title' => 'Dyson V15 Detect',
                'description' => 'Kabelloser Premium-Staubsauger mit Laser-Technologie. Top Saugleistung.',
                'category' => 'haushalt',
                'target_price' => 300.00,
                'retail_price' => 599.00,
                'brand' => 'Dyson',
                'condition' => 'new',
                'decision_type' => 'give'
            ],
            [
                'title' => 'Samsung 65" OLED Smart TV',
                'description' => 'Samsung S95C OLED Smart TV, 65 Zoll, 4K, HDR10+. Kinoerlebnis für Zuhause.',
                'category' => 'elektronik',
                'target_price' => 1200.00,
                'retail_price' => 2199.00,
                'brand' => 'Samsung',
                'condition' => 'new',
                'decision_type' => 'keep'
            ]
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])->first();
            
            $product = Product::create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'title' => $productData['title'],
                'description' => $productData['description'],
                'brand' => $productData['brand'],
                'condition' => $productData['condition'],
                'retail_price' => $productData['retail_price'],
                'target_price' => $productData['target_price'],
                'decision_type' => $productData['decision_type'],
                'status' => 'active'
            ]);

            // Erstelle aktive Raffle für jedes Produkt
            $raffle = Raffle::create([
                'product_id' => $product->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays(7),
                'target_price' => $product->target_price,
                'platform_fee' => $product->target_price * 0.30,
                'total_target' => $product->target_price * 1.30,
                'status' => 'active',
                'tickets_sold' => rand(10, 100),
                'total_revenue' => rand(10, 100),
                'unique_participants' => rand(5, 20)
            ]);
        }

        echo "✅ Demo-Daten wurden erstellt!\n";
        echo "📧 Test-Accounts:\n";
        echo "   Käufer: buyer@demo.de / password (50€ Guthaben)\n";
        echo "   Verkäufer: seller@demo.de / password\n";
        echo "🎯 6 aktive Verlosungen wurden erstellt!\n";
    }
}