<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glückwunsch! Du hast gewonnen!</title>
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
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
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
            background-color: #fff9e6;
            border-left: 4px solid #FFD700;
            padding: 20px;
            margin: 20px 0;
        }
        .product-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #FFD700;
            color: #000;
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
            <div class="emoji">🎉</div>
            <h1>GLÜCKWUNSCH!</h1>
            <p style="color: #fff; font-size: 18px;">Du hast gewonnen!</p>
        </div>

        {{-- Content --}}
        <div class="content">
            <p>Hallo {{ $winner->first_name ?? 'Gewinner' }},</p>
            
            <p><strong>Fantastische Nachrichten!</strong></p>
            
            <p>Du hast bei der Verlosung <strong>"{{ $product->title }}"</strong> gewonnen!</p>

            {{-- Prize Information --}}
            <div class="highlight-box">
                @if($prizeType === 'product')
                    <h3 style="margin-top: 0;">🎁 Dein Gewinn:</h3>
                    <p><strong>{{ $product->title }}</strong></p>
                    <p>{{ $product->description }}</p>
                    
                    @if($product->retail_price)
                        <p>Einzelhandelspreis: <strong>{{ number_format($product->retail_price, 2, ',', '.') }}€</strong></p>
                    @endif
                @else
                    <h3 style="margin-top: 0;">💰 Dein Gewinn:</h3>
                    <p style="font-size: 24px; font-weight: bold; color: #FFD700;">
                        {{ number_format($prizeAmount, 2, ',', '.') }}€
                    </p>
                    <p>Der Verkäufer hat den Zielpreis nicht erreicht, aber das Geld gehört jetzt dir!</p>
                @endif
            </div>

            {{-- Ticket Info --}}
            <div class="product-info">
                <h3 style="margin-top: 0;">📋 Deine Gewinn-Details:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li><strong>Ticket-Nummer:</strong> {{ $ticketNumber }}</li>
                    <li><strong>Verlosung:</strong> {{ $product->title }}</li>
                    <li><strong>Datum:</strong> {{ $raffle->drawn_at->format('d.m.Y H:i') }} Uhr</li>
                </ul>
            </div>

            {{-- Next Steps --}}
            <h3>🎯 Nächste Schritte:</h3>
            @if($prizeType === 'product')
                <ol>
                    <li>Wir kontaktieren dich in den nächsten 24 Stunden bezüglich der Abholung/Lieferung</li>
                    <li>Halte deine Ticket-Nummer bereit</li>
                    <li>Du wirst per Email über den Versandstatus informiert</li>
                </ol>
            @else
                <ol>
                    <li>Der Gewinn wurde bereits deinem Wallet gutgeschrieben</li>
                    <li>Du kannst das Geld jederzeit auszahlen lassen</li>
                    <li>Oder für weitere spannende Verlosungen nutzen</li>
                </ol>
            @endif

            {{-- CTA Button --}}
            <div style="text-align: center;">
                <a href="{{ route('dashboard') }}" class="button">
                    Zum Dashboard
                </a>
            </div>

            <p>Viel Freude mit deinem Gewinn!</p>
            
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