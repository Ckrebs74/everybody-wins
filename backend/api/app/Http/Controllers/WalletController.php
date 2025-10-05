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

        // Stripe Integration (für Production)
        // TODO: Stripe Checkout Session erstellen
        return back()->with('error', 'Stripe-Integration folgt in Kürze.');
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