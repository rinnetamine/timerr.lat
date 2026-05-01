{{-- Šis skats veido darījumu vēstures PDF dokumenta HTML saturu. --}}
<!doctype html>
<html lang="lv">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Transakciju vēsture - {{ $user->first_name }} {{ $user->last_name }}</title>
    <style>
        @charset "UTF-8";
        body { 
            font-family: "DejaVu Sans", "Arial Unicode MS", Arial, Helvetica, sans-serif; 
            color: #222; 
        }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 6px; border: 1px solid #ddd; font-size: 12px; }
        th { background: #f5f5f5; text-align: left; }
        .amount-positive { color: #1f8a3d; }
        .amount-negative { color: #c0392b; }
        .note { font-size: 11px; color: #666; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Transakciju vēsture</h2>
        <div>{{ $user->first_name }} {{ $user->last_name }} — {{ $user->email }}</div>
        <div>Eksportēts: {{ now()->translatedFormat('j. F Y, H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Datums</th>
                <th style="width: 60%;">Apraksts</th>
                <th style="width: 20%;">Kredīti</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t->created_at?->translatedFormat('j. M Y, H:i') }}</td>
                    <td>{{ $t->description }}</td>
                    <td style="text-align: right;">
                        <span class="{{ $t->amount > 0 ? 'amount-positive' : 'amount-negative' }}">
                            {{ $t->amount > 0 ? '+' : '' }}{{ $t->amount }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center;">Transakciju nav atrastu</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
