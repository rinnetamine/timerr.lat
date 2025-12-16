<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Transaction History - {{ $user->first_name }} {{ $user->last_name }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
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
        <h2>Transaction History</h2>
        <div>{{ $user->first_name }} {{ $user->last_name }} — {{ $user->email }}</div>
        <div>Exported: {{ now()->toDayDateTimeString() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Date</th>
                <th style="width: 60%;">Description</th>
                <th style="width: 20%;">Credits</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t->created_at?->format('M d, Y H:i') }}</td>
                    <td>{{ $t->description }}</td>
                    <td style="text-align: right;">
                        <span class="{{ $t->amount > 0 ? 'amount-positive' : 'amount-negative' }}">
                            {{ $t->amount > 0 ? '+' : '' }}{{ $t->amount }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center;">No transactions found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
