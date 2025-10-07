<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verlosung abgeschlossen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 32px;
        }
        .emoji {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .content {
            padding: 40px 20px;
        }
        .highlight-box {
            background-color: #e6f3ff;
            border-left: 4px solid #4A90E2;
            padding: 20px;
            margin: 20px 0;
        }
        .success-box {
            background-color: #e6ffe6;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff9e6;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .stat-item {
            display: table-cell;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            text-align: center;
            margin: 5px;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #4A90E2;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <div class="emoji">
                @if($scenario === 'target_reached')
                    💰
                @elseif($scenario === 'give')
                    📦
                @else
                    🔄
                @endif
            </div>
            <h1>Verlosung abgeschlossen!</h1>
        </div>

        {{-- Content --}}
        <div class="content">
            <p>Hallo {{ $seller->first_name ?? 'Verkäufer' }},</p>
            
            <p>Deine Verlosung für <strong>"{{ $product->title }}"</strong> ist abgeschlossen!</p>

            {{-- Scenario-specific Information --}}
            @if($scenario === 'target_reached')
                {{-- Zielpreis erreicht --}}
                <div class="success-box">
                    <h3 style="margin-top: 0;">✅ Zielpreis erreicht!</h3>
                    <p>Herzlichen Glückwunsch! Der Zielpreis wurde erreicht.</p>
                    <p style="font-size: 24px; font-weight: bold; color: #28a745;">
                        {{ number_format($payoutAmount, 2, ',', '.') }}€
                    </p>
                    <p>Die Auszahlung wurde bereits auf dein Wallet überwiesen.</p>
                </div>

                <h3>📊 Verlosungs-Statistiken:</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div style="font-size: 20px; font-weight: bold;">{{ $raffle->tickets_sold }}</div>
                        <div style="font-size: 12px; color: #666;">Verkaufte Lose</div>
                    </div>
                    <div class="stat-item">
                        <div style="font-size: 20px; font-weight: bold;">{{ $raffle->unique_participants }}</div>
                        <div style="font-size: 12px; color: #666;">Teilnehmer</div>
                    </div>
                    <div class="stat-item">
                        <div style="font-size: 20px; font-weight: bold;">{{ number_format($raffle->total_revenue, 2, ',', '.') }}€</div>
                        <div style="font-size: 12px; color: #666;">Einnahmen</div>
                    </div>
                </div>

            @elseif($scenario === 'give')
                {{-- Zielpreis nicht erreicht, aber abgegeben --}}
                <div class="warning-box">
                    <h3 style="margin-top: 0;">📦 Produkt abgegeben</h3>
                    <p>Der Zielpreis wurde nicht erreicht, aber du hast dich entschieden, das Produkt trotzdem abzugeben.</p>
                    <p style="font-size: 24px; font-weight: bold; color: #ffc107;">
                        {{ number_format($payoutAmount, 2, ',', '.') }}€
                    </p>
                    <p>Die Auszahlung wurde bereits auf dein Wallet überwiesen.</p>
                </div>

                <p><strong>Gewinner:</strong> {{ $winner->first_name ?? 'Gewinner' }} ({{ $winner->email }})</p>
                <p>Wir kontaktieren den Gewinner bezüglich der Abholung/Lieferung.</p>

            @else
                {{-- Zielpreis nicht erreicht, behalten --}}
                <div class="highlight-box">
                    <h3 style="margin-top: 0;">🔄 Produkt zurück</h3>
                    <p>Der Zielpreis wurde nicht erreicht und du hast dich entschieden, das Produkt zu behalten.</p>
                    <p>Der Gewinner erhält stattdessen den Erlös aus dem Losverkauf.</p>
                </div>

                <p><strong>Verkaufte Lose:</strong> {{ $raffle->tickets_sold }}</p>
                <p><strong>Einnahmen:</strong> {{ number_format($raffle->total_revenue, 2, ',', '.') }}€</p>
                <p><strong>Gewinner erhält:</strong> {{ number_format($raffle->total_revenue - $raffle->platform_fee, 2, ',', '.') }}€</p>
            @endif

            {{-- Winner Information --}}
            <div style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="margin-top: 0;">🎉 Gewinner der Verlosung:</h3>
                <p><strong>Name:</strong> {{ $winner->first_name }} {{ $winner->last_name }}</p>
                <p><strong>Email:</strong> {{ $winner->email }}</p>
                <p><strong>Ticket-Nummer:</strong> {{ $raffle->winnerTicket->ticket_number }}</p>
            </div>

            {{-- CTA Button --}}
            <div style="text-align: center;">
                <a href="{{ route('raffles.show', $product->slug) }}" class="button">
                    Verlosung ansehen
                </a>
            </div>

            <p>Vielen Dank für die Nutzung unserer Plattform!</p>
            
            <p>
                Dein Team von<br>
                <strong>JEDER GEWINNT!</strong>
            </p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>JEDER GEWINNT! - Der gamifizierte Marktplatz</p>
            <p>Einer verkauft. Viele spielen mit. Einer gewinnt.</p>
            <p style="font-size: 10px; margin-top: 10px;">
                Diese Email wurde automatisch generiert. Bitte antworte nicht direkt auf diese Nachricht.
            </p>
        </div>
    </div>
</body>
</html>