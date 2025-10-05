<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->middleware('auth');
        $this->walletService = $walletService;
    }

    /**
     * Wallet-Übersicht
     */
    public function index()
    {
        $user = Auth::user();
        
        // Balance
        $balance = $this->walletService->getBalance($user->id);

        // Transaktionen
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistiken
        $stats = [
            'total_deposited' => $user->total_deposited ?? 0,
            'total_spent' => $user->total_spent ?? 0,
            'total_withdrawn' => $user->total_withdrawn ?? 0,
            'current_balance' => $balance,
        ];

        return view('wallet.index', compact('user', 'balance', 'transactions', 'stats'));
    }

    /**
     * Guthaben aufladen (Demo-Mode oder Stripe)
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5|max:500',
        ]);

        $amount = $request->amount;
        $user = Auth::user();

        // DEMO-MODE: Sofort gutschreiben
        if (config('app.demo_mode', true)) {
            $success = $this->walletService->addFunds($user->id, $amount, [
                'payment_method' => 'demo',
                'note' => 'Demo-Aufladung (Test-Modus)',
            ]);

            if ($success) {
                return back()->with('success', "✅ Erfolgreich {$amount}€ aufgeladen! (Demo-Mode)");
            } else {
                return back()->with('error', 'Fehler beim Aufladen. Bitte versuchen Sie es erneut.');
            }
        }

        // STRIPE-MODE: PaymentIntent erstellen
        $paymentIntent = $this->walletService->createPaymentIntent($user->id, $amount);

        if ($paymentIntent) {
            // Weiterleitung zu Stripe Checkout
            return view('wallet.checkout', [
                'amount' => $amount,
                'clientSecret' => $paymentIntent->client_secret,
                'publishableKey' => config('services.stripe.key'),
            ]);
        }

        return back()->with('error', 'Fehler bei der Zahlungsabwicklung.');
    }

    /**
     * Auszahlung beantragen
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);

        $amount = $request->amount;
        $user = Auth::user();

        // Prüfe Balance
        if (!$this->walletService->hasBalance($user->id, $amount)) {
            return back()->with('error', 'Nicht genug Guthaben für diese Auszahlung.');
        }

        $success = $this->walletService->withdraw($user->id, $amount);

        if ($success) {
            return back()->with('success', "Auszahlung von {$amount}€ beantragt. Sie erhalten das Geld in 3-5 Werktagen.");
        }

        return back()->with('error', 'Fehler bei der Auszahlung. Bitte kontaktieren Sie den Support.');
    }
}