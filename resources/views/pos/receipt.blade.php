<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; color: #000; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; margin: 2px 0; }
        .item-name { flex: 1; }
        .item-qty { width: 30px; text-align: center; }
        .item-price { width: 60px; text-align: right; }
        .total-row { font-size: 14px; font-weight: bold; }
        @media print {
            body { margin: 0; padding: 8px; width: 80mm; color: #000; }
            * { color: #000 !important; }
            .no-print { display: none !important; }
        }
        @media screen {
            body { background: #f5f5f5; }
            .receipt-wrap {
                background: #fff;
                box-shadow: 0 2px 12px rgba(0,0,0,0.12);
                margin: 20px auto;
                padding: 10px;
                width: 80mm;
            }
        }
    </style>
</head>
<body>
<div class="receipt-wrap">
    @if(!empty($storeLogo))
    <div class="center" style="margin-bottom:6px;">
        <img src="{{ asset('storage/' . $storeLogo) }}" alt="{{ $storeName }}" style="max-height:60px; max-width:180px; margin:0 auto; display:block;">
    </div>
    @endif
    <div class="center bold" style="font-size:16px;">{{ $storeName }}</div>
    <div class="center" style="margin:2px 0; font-size:11px;">{{ $storeAddress }}</div>
    @if($storePhone)<div class="center" style="font-size:11px;">{{ $storePhone }}</div>@endif
    <div class="divider"></div>

    <div class="row"><span>Date:</span><span>{{ $transaction->created_at->format('m/d/Y H:i') }}</span></div>
    <div class="row"><span>TXN #:</span><span class="bold">{{ $transaction->transaction_number }}</span></div>
    <div class="row"><span>Cashier:</span><span>{{ $transaction->user->name }}</span></div>

    <div class="divider"></div>
    <div class="row bold">
        <span class="item-name">Item</span>
        <span class="item-qty">Qty</span>
        <span class="item-price">Price</span>
    </div>
    <div class="divider"></div>

    @foreach($transaction->items as $item)
    <div style="margin: 3px 0;">
        <div class="item-name bold">{{ $item->product_name }}</div>
        <div class="row">
            <span class="item-name" style="padding-left:8px;">{{ $item->variant_info }}</span>
            <span class="item-qty">{{ $item->quantity }}</span>
            <span class="item-price">₱{{ number_format($item->subtotal, 2) }}</span>
        </div>
        <div style="text-align:right;">@ ₱{{ number_format($item->unit_price, 2) }} each</div>
    </div>
    @endforeach

    <div class="divider"></div>
    <div class="row"><span>Subtotal:</span><span>₱{{ number_format($transaction->subtotal, 2) }}</span></div>
    @if($transaction->discount > 0)
    <div class="row"><span>Discount:</span><span>-₱{{ number_format($transaction->discount, 2) }}</span></div>
    @endif
    @if($transaction->tax > 0)
    <div class="row"><span>+{{ $taxLabel }}:</span><span>+₱{{ number_format($transaction->tax, 2) }}</span></div>
    @endif
    <div class="divider"></div>
    <div class="row total-row"><span>TOTAL:</span><span>₱{{ number_format($transaction->total, 2) }}</span></div>
    <div class="row"><span>Cash Paid:</span><span>₱{{ number_format($transaction->amount_paid, 2) }}</span></div>
    <div class="row bold"><span>Change:</span><span>₱{{ number_format($transaction->change_amount, 2) }}</span></div>

    <div class="divider"></div>
    <div class="center" style="margin-top:8px;">{{ $storeFooter }}</div>
    <div class="center" style="margin-top:4px; font-size:10px;">Please come again</div>

    <div class="no-print" style="margin-top:20px; text-align:center;">
        <button onclick="triggerPrint()" style="background:#1DB87A;color:white;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:14px;">
            🖨️ Print Receipt
        </button>
        <button onclick="window.close()" style="background:#6b7280;color:white;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:14px;margin-left:8px;">
            Close
        </button>
    </div>
</div>{{-- end .receipt-wrap --}}

    <script>
        function triggerPrint() {
            window.focus();
            window.print();
        }

        // Auto-print: wait for all assets (fonts, images) to fully load
        window.addEventListener('load', function () {
            setTimeout(triggerPrint, 600);
        });

        // Close popup automatically after printing on macOS
        window.addEventListener('afterprint', function () {
            setTimeout(function () { window.close(); }, 300);
        });
    </script>
</body>
</html>
