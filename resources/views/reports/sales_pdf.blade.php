<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
h1 { color: #4f46e5; font-size: 20px; margin-bottom: 4px; }
.meta { color: #666; font-size: 11px; margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; margin-top: 12px; }
th { background: #4f46e5; color: white; padding: 8px; text-align: left; font-size: 11px; }
td { padding: 7px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
tr:nth-child(even) td { background: #f9f9f9; }
.summary { background: #f0f0ff; padding: 12px; border-radius: 6px; margin-bottom: 16px; }
.summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.summary-item { text-align: center; }
.summary-item .label { font-size: 10px; color: #666; }
.summary-item .value { font-size: 16px; font-weight: bold; color: #4f46e5; }
</style>
</head>
<body>
<h1>👗 Clothing Store POS - Sales Report</h1>
<p class="meta">Period: {{ $startDate }} to {{ $endDate }} | Generated: {{ now()->format('Y-m-d H:i') }}</p>

<div class="summary">
    <div class="summary-grid">
        <div class="summary-item"><div class="label">Transactions</div><div class="value">{{ $summary['total_transactions'] }}</div></div>
        <div class="summary-item"><div class="label">Total Revenue</div><div class="value">₱{{ number_format($summary['total_revenue'], 2) }}</div></div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Transaction #</th><th>Date</th><th>Cashier</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $t)
        <tr>
            <td>{{ $t->transaction_number }}</td>
            <td>{{ $t->created_at->format('m/d/Y H:i') }}</td>
            <td>{{ $t->user->name }}</td>
            <td>{{ $t->items->sum('quantity') }}</td>
            <td>₱{{ number_format($t->subtotal, 2) }}</td>
            <td>-₱{{ number_format($t->discount, 2) }}</td>
            <td><strong>₱{{ number_format($t->total, 2) }}</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
