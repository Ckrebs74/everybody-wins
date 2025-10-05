<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Services\WalletService;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        // Middleware wird in routes/web.php definiert
        $this->walletService = $walletService;
    }

    /**
     * Display wallet overview
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get transaction history
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Wallet stats
        $stats = [
            'balance' => $user->wallet_balance,
            'total_deposited' => $user->total_deposited,
            'total_spent' => $user->total_spent,
            'total_withdrawn' => $user->total_withdrawn,
        ];

        return view('wallet.index', compact('user', 'transactions', 'stats'));
    }

    /**
     * Show deposit form
     */
    public function showDeposit()
    {
        return view('wallet.deposit');
    }

    /**
     * Process deposit via Stripe
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5|max:500',
        ], [
            'amount.required' => 'Bitte geben Sie einen Betrag ein.',
            'amount.numeric' => 'Der Betrag muss eine Zahl sein.',
            'amount.min' => 'Der Mindestbetrag beträgt 5€.',
            'amount.max' => 'Der Höchstbetrag beträgt 500€.',
        ]);

        // Demo Mode: Directly add balance
        if (config('app.demo_mode', true)) {
            try {
                $this->walletService->addBalance(
                    Auth::id(),
                    $request->amount,
                    null,
                    'Demo-Einzahlung'
                );

                return redirect()->route('wallet.index')
                    ->with('success', "Erfolgreich {$request->amount}€ eingezahlt (Demo-Modus)!");
            } catch (\Exception $e) {
                return back()->with('error', 'Fehler bei der Einzahlung: ' . $e->getMessage());
            }
        }

        // Stripe Checkout Session erstellen
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Guthaben aufladen',
                            'description' => 'Jeder gewinnt! Wallet Guthaben',
                        ],
                        'unit_amount' => $request->amount * 100, // Cent
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('wallet.deposit.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('wallet.index'),
                'client_reference_id' => Auth::id(),
                'metadata' => [
                    'user_id' => Auth::id(),
                    'amount' => $request->amount,
                    'type' => 'wallet_deposit',
                ],
            ]);

            return redirect($session->url);

        } catch (\Exception $e) {
            return back()->with('error', 'Stripe Fehler: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful Stripe payment
     */
    public function depositSuccess(Request $request)
    {
        if (!$request->has('session_id')) {
            return redirect()->route('wallet.index')
                ->with('error', 'Ungültige Session.');
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($request->session_id);

            if ($session->payment_status === 'paid') {
                $amount = $session->metadata->amount;
                $userId = $session->metadata->user_id;

                // Prüfe ob bereits verarbeitet
                $existingTransaction = \App\Models\Transaction::where('reference_type', 'stripe_session')
                    ->where('reference_id', $session->id)
                    ->first();

                if (!$existingTransaction) {
                    $this->walletService->addBalance(
                        $userId,
                        $amount,
                        $session->id,
                        'Stripe Einzahlung - Session: ' . $session->id
                    );
                }

                return redirect()->route('wallet.index')
                    ->with('success', "Erfolgreich {$amount}€ eingezahlt!");
            }

            return redirect()->route('wallet.index')
                ->with('error', 'Zahlung wurde nicht bestätigt.');

        } catch (\Exception $e) {
            return redirect()->route('wallet.index')
                ->with('error', 'Fehler bei der Verarbeitung: ' . $e->getMessage());
        }
    }

    /**
     * Show withdrawal form
     */
    public function showWithdraw()
    {
        $user = Auth::user();
        return view('wallet.withdraw', compact('user'));
    }

    /**
     * Process withdrawal
     */
    public function withdraw(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'amount' => 'required|numeric|min:10|max:' . $user->wallet_balance,
        ], [
            'amount.required' => 'Bitte geben Sie einen Betrag ein.',
            'amount.numeric' => 'Der Betrag muss eine Zahl sein.',
            'amount.min' => 'Der Mindestbetrag für Auszahlungen beträgt 10€.',
            'amount.max' => 'Sie können maximal Ihr verfügbares Guthaben auszahlen.',
        ]);

        try {
            $this->walletService->withdraw(
                $user->id,
                $request->amount,
                'Auszahlung an PayPal/Banküberweisung'
            );

            return redirect()->route('wallet.index')
                ->with('success', "Auszahlung von {$request->amount}€ wurde beantragt. Sie erhalten das Geld innerhalb von 2-5 Werktagen.");

        } catch (\Exception $e) {
            return back()->with('error', 'Fehler bei der Auszahlung: ' . $e->getMessage());
        }
    }
}