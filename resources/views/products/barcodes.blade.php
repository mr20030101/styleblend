<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Labels — {{ $product->name }}</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; }

        /* Label styles */
        .label {
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3mm;
            box-sizing: border-box;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .label .lbl-store   { font-size: 7pt; font-weight: 700; text-align: center; letter-spacing: 0.5px; }
        .label .lbl-product { font-size: 6pt; text-align: center; color: #555; margin-top: 1px; }
        .label .lbl-variant { font-size: 6pt; text-align: center; color: #888; }
        .label svg          { max-width: 100%; height: 14mm; margin: 1mm 0; }
        .label .lbl-price   { font-size: 10pt; font-weight: 800; margin-top: 1mm; letter-spacing: -0.5px; }

        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            #labels-print {
                display: grid !important;
                padding: 5mm;
                gap: 2mm;
            }
            #labels-preview { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen">

{{-- Top bar --}}
<div class="no-print bg-white border-b border-gray-200 px-6 py-3 flex items-center gap-4 sticky top-0 z-20 shadow-sm">
    <a href="{{ route('products.edit', $product) }}" class="text-gray-400 hover:text-gray-700 transition">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-1 min-w-0">
        <h1 class="font-bold text-gray-900 text-base truncate">{{ $product->name }}</h1>
        <p class="text-xs text-gray-400">{{ $product->category->name }} &nbsp;·&nbsp; SKU: {{ $product->sku }}</p>
    </div>
    <button onclick="window.print()"
        class="flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition">
        <i class="fas fa-print"></i> Print Labels
    </button>
</div>

{{-- Main layout --}}
<div class="no-print flex h-[calc(100vh-57px)]">

    {{-- Left sidebar: controls --}}
    <div class="w-72 flex-shrink-0 bg-white border-r border-gray-200 flex flex-col overflow-y-auto">
        <div class="p-5 space-y-5">

            {{-- Copies --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Copies per Variant</label>
                <div class="flex items-center gap-2">
                    <button onclick="changeCopies(-1)" class="w-8 h-8 rounded-lg border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold transition flex items-center justify-center">−</button>
                    <input type="number" id="copies" value="1" min="1" max="100"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm text-center font-semibold focus:outline-none focus:ring-2 focus:ring-brand"
                        onchange="renderLabels()">
                    <button onclick="changeCopies(1)" class="w-8 h-8 rounded-lg border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold transition flex items-center justify-center">+</button>
                </div>
            </div>

            {{-- Label size --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Label Size</label>
                <div class="space-y-1.5">
                    @foreach(['50x30' => '50 × 30 mm', '60x40' => '60 × 40 mm', '40x25' => '40 × 25 mm (small)'] as $val => $label)
                    <label class="flex items-center gap-2.5 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition">
                        <input type="radio" name="label-size" value="{{ $val }}" {{ $val === '50x30' ? 'checked' : '' }}
                            onchange="renderLabels()" class="text-gray-900">
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Options --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Options</label>
                <label class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <span class="text-sm text-gray-700">Show Price</span>
                    <input type="checkbox" id="show-price" checked onchange="renderLabels()"
                        class="rounded border-gray-300 text-gray-900">
                </label>
                <label class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <span class="text-sm text-gray-700">Show Store Name</span>
                    <input type="checkbox" id="show-store" checked onchange="renderLabels()"
                        class="rounded border-gray-300 text-gray-900">
                </label>
                <label class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <span class="text-sm text-gray-700">Show Variant</span>
                    <input type="checkbox" id="show-variant" checked onchange="renderLabels()"
                        class="rounded border-gray-300 text-gray-900">
                </label>
            </div>

            {{-- Variants --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Variants</label>
                    <div class="flex gap-2 text-xs">
                        <button onclick="toggleAll(true)" class="text-gray-500 hover:text-gray-800 underline">All</button>
                        <button onclick="toggleAll(false)" class="text-gray-500 hover:text-gray-800 underline">None</button>
                    </div>
                </div>
                <div class="space-y-1">
                    @foreach($product->variants as $v)
                    <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" class="variant-check rounded border-gray-300 text-gray-900"
                            value="{{ $v->id }}" checked onchange="renderLabels()">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $v->size }} / {{ $v->color }}</p>
                            <p class="text-xs text-gray-400">₱{{ number_format($v->price, 2) }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Label count footer --}}
        <div class="mt-auto p-4 border-t border-gray-100 bg-gray-50">
            <p class="text-xs text-gray-500 text-center" id="label-count">0 labels to print</p>
        </div>
    </div>

    {{-- Right: preview --}}
    <div class="flex-1 overflow-y-auto p-6 bg-gray-100">
        <div id="labels-preview" class="flex flex-wrap gap-4 justify-start"></div>
        <p id="empty-msg" class="hidden text-center text-gray-400 mt-20 text-sm">No variants selected.</p>
    </div>
</div>

{{-- Print-only grid --}}
<div id="labels-print" class="hidden"></div>

{{-- Data --}}
<script>
const storeName  = '{{ \App\Models\Setting::get("store_name", "StyleBlend") }}';
const variants   = @json($variantsData);
const productName = '{{ addslashes($product->name) }}';

const SIZES = { '50x30': [50,30], '60x40': [60,40], '40x25': [40,25] };

function changeCopies(delta) {
    const el = document.getElementById('copies');
    el.value = Math.max(1, Math.min(100, parseInt(el.value || 1) + delta));
    renderLabels();
}

function getSize() {
    return document.querySelector('input[name="label-size"]:checked')?.value || '50x30';
}

function getSelectedIds() {
    return [...document.querySelectorAll('.variant-check:checked')].map(el => parseInt(el.value));
}

function toggleAll(state) {
    document.querySelectorAll('.variant-check').forEach(el => el.checked = state);
    renderLabels();
}

function makeLabel(variant, wMm, hMm) {
    const showPrice   = document.getElementById('show-price').checked;
    const showStore   = document.getElementById('show-store').checked;
    const showVariant = document.getElementById('show-variant').checked;

    const div = document.createElement('div');
    div.className = 'label';
    div.style.width  = wMm + 'mm';
    div.style.height = hMm + 'mm';

    let html = '';
    if (showStore)   html += `<div class="lbl-store">${storeName}</div>`;
    html += `<div class="lbl-product">${productName}</div>`;
    if (showVariant) html += `<div class="lbl-variant">${variant.size} / ${variant.color}</div>`;
    html += `<svg class="barcode-svg" data-value="${variant.barcode}"></svg>`;
    if (showPrice)   html += `<div class="lbl-price">₱${parseFloat(variant.price).toFixed(2)}</div>`;

    div.innerHTML = html;
    return div;
}

function renderLabels() {
    const copies     = parseInt(document.getElementById('copies').value) || 1;
    const selectedIds = getSelectedIds();
    const selected   = variants.filter(v => selectedIds.includes(v.id));
    const sizeKey    = getSize();
    const [w, h]     = SIZES[sizeKey];

    const preview = document.getElementById('labels-preview');
    const print   = document.getElementById('labels-print');
    const empty   = document.getElementById('empty-msg');
    const counter = document.getElementById('label-count');

    preview.innerHTML = '';
    print.innerHTML   = '';

    if (!selected.length) {
        empty.classList.remove('hidden');
        counter.textContent = '0 labels to print';
        return;
    }
    empty.classList.add('hidden');

    const total = selected.length * copies;
    counter.textContent = `${total} label${total !== 1 ? 's' : ''} to print`;

    // Set print grid columns
    print.style.gridTemplateColumns = `repeat(auto-fill, ${w}mm)`;

    selected.forEach(variant => {
        for (let i = 0; i < copies; i++) {
            preview.appendChild(makeLabel(variant, w, h));
            print.appendChild(makeLabel(variant, w, h));
        }
    });

    // Render barcodes
    document.querySelectorAll('.barcode-svg').forEach(svg => {
        try {
            JsBarcode(svg, svg.dataset.value, {
                format:       'CODE128',
                width:        1.4,
                height:       36,
                displayValue: true,
                fontSize:     7,
                margin:       2,
                textMargin:   1,
            });
        } catch(e) {
            svg.outerHTML = `<div style="font-size:6pt;color:red;text-align:center;">Invalid</div>`;
        }
    });
}

window.addEventListener('beforeprint', () => document.getElementById('labels-print').classList.remove('hidden'));
window.addEventListener('afterprint',  () => document.getElementById('labels-print').classList.add('hidden'));

renderLabels();
</script>

</body>
</html>
