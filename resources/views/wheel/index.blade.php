@extends('layouts.app')
@section('title', 'Wheel of Fortune')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Wheel of Fortune</h2>
        <div class="flex gap-2">
            <a href="{{ route('wheel.play') }}" target="_blank"
                class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-external-link-alt mr-1"></i> Open Play View
            </a>
            <button onclick="openPrizeModal()"
                class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-plus mr-1"></i> Add Prize
            </button>
        </div>
    </div>

    <!-- Settings Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Minimum Purchase Setting -->
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
            <div class="shrink-0">
                <i class="fas fa-shopping-cart text-gray-400 text-lg"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-700">Minimum Purchase to Spin</p>
                <p class="text-xs text-gray-400">Set to 0 to allow free spins without a purchase requirement.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-gray-500 text-sm">₱</span>
                <input type="number" id="min-purchase-input" value="{{ $minPurchase }}" min="0" step="0.01"
                    class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-28 focus:outline-none focus:ring-2 focus:ring-brand">
                <button onclick="saveMinPurchase()"
                    class="bg-brand hover:bg-brand-dark text-white px-4 py-1.5 rounded-lg text-sm font-medium transition">
                    Save
                </button>
            </div>
        </div>

        <!-- Spin Mode Setting -->
        <div class="bg-white rounded-xl shadow p-4 flex items-start gap-4">
            <div class="shrink-0 mt-0.5">
                <i class="fas fa-dharmachakra text-gray-400 text-lg"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-700 mb-1">Spin Mode</p>
                <p class="text-xs text-gray-400 mb-3">Choose when the customer spins the wheel.</p>
                <div class="flex flex-col gap-2">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="radio" name="spin-mode" value="immediate" {{ $spinMode === 'immediate' ? 'checked' : '' }}
                            onchange="saveSpinMode('immediate')"
                            class="mt-0.5 accent-indigo-600 shrink-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800 group-hover:text-indigo-700">Immediate</p>
                            <p class="text-xs text-gray-400">Spin <strong>before</strong> payment — discount applies to the current cart.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="radio" name="spin-mode" value="next_purchase" {{ $spinMode === 'next_purchase' ? 'checked' : '' }}
                            onchange="saveSpinMode('next_purchase')"
                            class="mt-0.5 accent-indigo-600 shrink-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800 group-hover:text-indigo-700">Next Purchase</p>
                            <p class="text-xs text-gray-400">Spin <strong>after</strong> payment — a one-time link is given to use next time.</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Wheel preview -->
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center gap-4">
            <p class="text-sm text-gray-400">Preview (spin on the Play View)</p>
            <div class="relative">
                <canvas id="wheel-canvas" width="340" height="340" class="rounded-full"></canvas>
            </div>
        </div>

        <!-- Prizes table -->
        <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <span class="font-semibold text-gray-700">Prizes</span>
                    <span class="ml-2 text-xs text-gray-400">Total: <span class="{{ $total > 100 ? 'text-red-500 font-bold' : ($total < 100 ? 'text-yellow-600 font-medium' : 'text-green-600 font-medium') }}">{{ number_format($total, 2) }}%</span></span>
                </div>
                @if(round($total, 2) != 100)
                <span class="text-xs {{ $total > 100 ? 'text-red-500' : 'text-yellow-600' }} font-medium">
                    {{ $total > 100 ? '⚠ Exceeds 100%' : '⚠ Should sum to 100%' }}
                </span>
                @endif
            </div>
            <div class="overflow-y-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                        <tr>
                            <th class="text-left px-4 py-2 font-semibold text-gray-600">Prize</th>
                            <th class="text-right px-4 py-2 font-semibold text-gray-600">Chance</th>
                            <th class="text-center px-4 py-2 font-semibold text-gray-600">Active</th>
                            <th class="text-center px-4 py-2 font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($prizes as $prize)
                        <tr id="prize-row-{{ $prize->id }}" class="{{ $prize->is_active ? '' : 'opacity-50' }}">
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background:{{ $prize->color }}"></span>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $prize->name }}</p>
                                        @if($prize->description)
                                        <p class="text-xs text-gray-400">{{ $prize->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-700">{{ number_format($prize->percentage, 2) }}%</td>
                            <td class="px-4 py-2.5 text-center">
                                @if($prize->is_active)
                                    <span class="text-green-600 text-xs font-medium">Yes</span>
                                @else
                                    <span class="text-gray-400 text-xs">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="flex justify-center gap-1">
                                    <button onclick="editPrize({{ $prize->id }}, '{{ addslashes($prize->name) }}', '{{ addslashes($prize->description ?? '') }}', '{{ $prize->color }}', {{ $prize->percentage }}, {{ $prize->is_active ? 1 : 0 }}, '{{ $prize->discount_type }}', {{ $prize->discount_value }})"
                                        class="text-gray-500 hover:text-indigo-600 px-2 py-1 rounded text-xs transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deletePrize({{ $prize->id }})"
                                        class="text-gray-400 hover:text-red-500 px-2 py-1 rounded text-xs transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-10 text-gray-400">No prizes yet. Add one to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Prize Modal -->
<div id="prize-modal" class="fixed inset-0 bg-black/50 z-60 hidden">
    <div class="flex items-center justify-center h-full">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-4" id="prize-modal-title">Add Prize</h3>
        <div class="space-y-3">
            <input type="hidden" id="pm-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prize Name <span class="text-red-500">*</span></label>
                <input type="text" id="pm-name" placeholder="e.g. 10% Discount"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" id="pm-desc" placeholder="Optional details"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="color" id="pm-color" value="#6366f1"
                        class="w-full h-10 border border-gray-300 rounded-lg px-1 py-1 cursor-pointer">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chance % <span class="text-red-500">*</span></label>
                    <input type="number" id="pm-pct" min="0.01" max="100" step="0.01" placeholder="e.g. 25"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" id="pm-active" checked class="rounded border-gray-300">
                Active (included in wheel)
            </label>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                <select id="pm-discount-type" onchange="toggleDiscountValue()"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="none">None (no discount)</option>
                    <option value="fixed">Fixed Amount (₱)</option>
                    <option value="percentage">Percentage (%)</option>
                </select>
            </div>
            <div id="pm-discount-value-wrap" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1" id="pm-discount-value-label">Discount Value</label>
                <input type="number" id="pm-discount-value" min="0" step="0.01" placeholder="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div id="pm-error" class="hidden text-red-500 text-xs"></div>
            <div class="flex gap-3 pt-2">
                <button onclick="savePrize()" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="closePrizeModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const prizes = @json($prizes->where('is_active', true)->values());

function drawWheel(canvas, rotationRad, size) {
    const ctx = canvas.getContext('2d');
    const cx  = canvas.width / 2, cy = canvas.height / 2;
    const r   = cx - 8;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (!prizes.length) {
        ctx.fillStyle = '#e5e7eb';
        ctx.beginPath(); ctx.arc(cx, cy, r, 0, 2 * Math.PI); ctx.fill();
        ctx.fillStyle = '#9ca3af'; ctx.font = 'bold 13px sans-serif';
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText('No active prizes', cx, cy);
        return;
    }

    const total = prizes.reduce((s, p) => s + parseFloat(p.percentage), 0);
    let angle = rotationRad;

    prizes.forEach(prize => {
        const slice    = (parseFloat(prize.percentage) / total) * 2 * Math.PI;
        const endAngle = angle + slice;

        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, angle, endAngle);
        ctx.closePath();
        ctx.fillStyle = prize.color;
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();

        const midAngle = angle + slice / 2;
        const labelR   = r * 0.68;
        ctx.save();
        ctx.translate(cx + labelR * Math.cos(midAngle), cy + labelR * Math.sin(midAngle));
        ctx.rotate(midAngle + Math.PI / 2);
        ctx.fillStyle = '#fff';
        ctx.font = `bold ${size === 'sm' ? 10 : 12}px sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const label = prize.name.length > 14 ? prize.name.slice(0, 13) + '…' : prize.name;
        ctx.fillText(label, 0, 0);
        ctx.restore();

        angle = endAngle;
    });

    ctx.beginPath(); ctx.arc(cx, cy, 18, 0, 2 * Math.PI);
    ctx.fillStyle = '#fff'; ctx.fill();
    ctx.strokeStyle = '#d1d5db'; ctx.lineWidth = 2; ctx.stroke();
}

const previewCanvas = document.getElementById('wheel-canvas');
drawWheel(previewCanvas, 0, 'sm');

// Prize CRUD
function toggleDiscountValue() {
    const type = document.getElementById('pm-discount-type').value;
    const wrap  = document.getElementById('pm-discount-value-wrap');
    const label = document.getElementById('pm-discount-value-label');
    if (type === 'none') {
        wrap.classList.add('hidden');
    } else {
        wrap.classList.remove('hidden');
        label.textContent = type === 'fixed' ? 'Discount Amount (₱)' : 'Discount Percentage (%)';
    }
}

function openPrizeModal() {
    document.getElementById('prize-modal-title').textContent = 'Add Prize';
    document.getElementById('pm-id').value    = '';
    document.getElementById('pm-name').value  = '';
    document.getElementById('pm-desc').value  = '';
    document.getElementById('pm-color').value = '#6366f1';
    document.getElementById('pm-pct').value   = '';
    document.getElementById('pm-active').checked = true;
    document.getElementById('pm-discount-type').value  = 'none';
    document.getElementById('pm-discount-value').value = '';
    toggleDiscountValue();
    document.getElementById('pm-error').classList.add('hidden');
    document.getElementById('prize-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('pm-name').focus(), 100);
}

function editPrize(id, name, desc, color, pct, active, discountType, discountValue) {
    document.getElementById('prize-modal-title').textContent = 'Edit Prize';
    document.getElementById('pm-id').value    = id;
    document.getElementById('pm-name').value  = name;
    document.getElementById('pm-desc').value  = desc;
    document.getElementById('pm-color').value = color;
    document.getElementById('pm-pct').value   = pct;
    document.getElementById('pm-active').checked = !!active;
    document.getElementById('pm-discount-type').value  = discountType || 'none';
    document.getElementById('pm-discount-value').value = discountValue || '';
    toggleDiscountValue();
    document.getElementById('pm-error').classList.add('hidden');
    document.getElementById('prize-modal').classList.remove('hidden');
}

function closePrizeModal() {
    document.getElementById('prize-modal').classList.add('hidden');
}

function savePrize() {
    const id            = document.getElementById('pm-id').value;
    const name          = document.getElementById('pm-name').value.trim();
    const desc          = document.getElementById('pm-desc').value.trim();
    const color         = document.getElementById('pm-color').value;
    const pct           = document.getElementById('pm-pct').value;
    const active        = document.getElementById('pm-active').checked ? 1 : 0;
    const discountType  = document.getElementById('pm-discount-type').value;
    const discountValue = document.getElementById('pm-discount-value').value || 0;
    const errEl         = document.getElementById('pm-error');

    if (!name || !pct) { errEl.textContent = 'Name and percentage are required.'; errEl.classList.remove('hidden'); return; }
    if (pct <= 0 || pct > 100) { errEl.textContent = 'Percentage must be between 0.01 and 100.'; errEl.classList.remove('hidden'); return; }
    errEl.classList.add('hidden');

    $.ajax({ url: id ? `/wheel/prizes/${id}` : '/wheel/prizes', method: id ? 'PUT' : 'POST',
        data: { name, description: desc, color, percentage: pct, is_active: active, discount_type: discountType, discount_value: discountValue },
        success: () => location.reload(),
        error: (xhr) => {
            const msgs = xhr.responseJSON?.errors;
            errEl.textContent = msgs ? Object.values(msgs).flat().join(' ') : 'Error saving.';
            errEl.classList.remove('hidden');
        }
    });
}

function deletePrize(id) {
    swalConfirm({ title: 'Delete Prize?', text: 'This will remove it from the wheel.', confirmText: 'Delete' }, () => {
        $.ajax({ url: `/wheel/prizes/${id}`, method: 'DELETE',
            success: () => location.reload(),
            error: () => showToast('Error deleting prize.', 'error')
        });
    });
}

function saveMinPurchase() {
    const val = document.getElementById('min-purchase-input').value;
    $.ajax({ url: '/wheel/settings', method: 'POST',
        data: { min_purchase: val },
        success: () => showToast('Minimum purchase saved.'),
        error: () => showToast('Failed to save.', 'error')
    });
}

function saveSpinMode(mode) {
    $.ajax({ url: '/wheel/settings', method: 'POST',
        data: { spin_mode: mode },
        success: () => showToast('Spin mode updated.'),
        error: () => showToast('Failed to save.', 'error')
    });
}
</script>
@endpush
