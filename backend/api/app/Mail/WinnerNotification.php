<?php

namespace App\Mail;

use App\Models\Raffle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WinnerNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Raffle $raffle;
    public User $winner;
    public string $prizeType; // 'product' oder 'money'
    public ?float $prizeAmount;

    /**
     * Create a new message instance.
     */
    public function __construct(Raffle $raffle, User $winner)
    {
        $this->raffle = $raffle;
        $this->winner = $winner;
        
        // Bestimme was der Gewinner erhält
        if ($raffle->target_reached || $raffle->product->decision_type === 'give') {
            $this->prizeType = 'product';
            $this->prizeAmount = null;
        } else {
            $this->prizeType = 'money';
            $this->prizeAmount = $raffle->total_revenue - $raffle->platform_fee;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Glückwunsch! Du hast gewonnen! - Jeder gewinnt!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.winner-notification',
            with: [
                'raffle' => $this->raffle,
                'product' => $this->raffle->product,
                'winner' => $this->winner,
                'prizeType' => $this->prizeType,
                'prizeAmount' => $this->prizeAmount,
                'ticketNumber' => $this->raffle->winnerTicket->ticket_number,
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