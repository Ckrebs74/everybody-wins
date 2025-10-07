<?php

namespace App\Mail;

use App\Models\Raffle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerPayoutNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Raffle $raffle;
    public User $seller;
    public string $scenario; // 'target_reached', 'give', 'keep'
    public ?float $payoutAmount;

    /**
     * Create a new message instance.
     */
    public function __construct(Raffle $raffle, User $seller)
    {
        $this->raffle = $raffle;
        $this->seller = $seller;
        
        // Bestimme Szenario und Auszahlung
        if ($raffle->target_reached) {
            $this->scenario = 'target_reached';
            $this->payoutAmount = $raffle->target_price;
        } elseif ($raffle->product->decision_type === 'give') {
            $this->scenario = 'give';
            $this->payoutAmount = $raffle->total_revenue - $raffle->platform_fee;
        } else {
            $this->scenario = 'keep';
            $this->payoutAmount = null;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->scenario) {
            'target_reached' => '💰 Zielpreis erreicht - Auszahlung erfolgt!',
            'give' => '📦 Verlosung abgeschlossen - Auszahlung erfolgt!',
            'keep' => '🔄 Verlosung abgeschlossen - Produkt zurück',
        };

        return new Envelope(
            subject: $subject . ' - Jeder gewinnt!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.seller-payout-notification',
            with: [
                'raffle' => $this->raffle,
                'product' => $this->raffle->product,
                'seller' => $this->seller,
                'scenario' => $this->scenario,
                'payoutAmount' => $this->payoutAmount,
                'winner' => $this->raffle->winnerTicket->user,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
