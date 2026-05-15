<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Wheel of Fortune</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { background: #0f172a; font-family: sans-serif; }

        #win-overlay { animation: none; }
        #win-overlay.visible { display: flex !important; }
        #win-card { animation: popIn .3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes popIn {
            from { opacity: 0; transform: scale(.7); }
            to   { opacity: 1; transform: scale(1); }
        }

        #spin-btn {
            background: linear-gradient(135deg, #1DB87A, #0F8A58);
            letter-spacing: 2px;
            transition: transform .1s, box-shadow .1s;
        }
        #spin-btn:not(:disabled):hover { transform: scale(1.04); box-shadow: 0 8px 30px rgba(29,184,122,.4); }
        #spin-btn:not(:disabled):active { transform: scale(.97); }
        #spin-btn:disabled { opacity: .5; cursor: not-allowed; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center gap-4 p-6 overflow-hidden">

    <h1 class="text-white font-black text-3xl sm:text-4xl tracking-tight">
        🎡 Wheel of Fortune
    </h1>

    @if($tokenError)
    <div class="bg-red-900/50 border border-red-500/40 text-red-300 rounded-xl px-6 py-4 text-center max-w-sm">
        <i class="fas fa-lock mr-2"></i> {{ $tokenError }}
        @if($minPurchase > 0)
        <p class="text-xs text-red-400 mt-1">Minimum purchase: ₱{{ number_format($minPurchase, 2) }}</p>
        @endif
    </div>
    @endif

    <!-- Wheel + arrow stacked in a column, all centered -->
    <div class="flex flex-col items-center gap-0">
        <!-- Arrow pointing down, sits directly above the wheel center -->
        <div class="text-white text-4xl leading-none z-10"
             style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.7)); margin-bottom:-6px;">▼</div>
        <canvas id="wheel-canvas" width="420" height="420"
            class="rounded-full shadow-2xl block"
            style="max-width: min(420px, 88vw); height: auto;"></canvas>
    </div>

    <!-- SPIN button -->
    <button id="spin-btn" onclick="spinWheel()"
        class="text-white font-black text-2xl rounded-full shadow-xl px-16 py-4 mt-2">
        SPIN
    </button>
    <p id="spin-status" class="text-white/40 text-sm min-h-5 text-center"></p>

    <!-- Win overlay -->
    <div id="win-overlay" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
        <div id="win-card" class="rounded-3xl shadow-2xl p-8 w-full max-w-sm text-center" id="win-card"
             style="background: #1e293b; border: 2px solid rgba(255,255,255,.08);">
            <div class="text-6xl mb-4">🎉</div>
            <p class="text-white/50 text-xs uppercase tracking-widest font-bold mb-2">You Won!</p>
            <p class="text-5xl font-black mb-2" id="win-name"></p>
            <p class="text-white/40 text-sm mb-8" id="win-desc"></p>
            <button onclick="closeWin()"
                class="w-full text-white font-bold text-lg py-3.5 rounded-2xl transition"
                style="background: linear-gradient(135deg,#1DB87A,#0F8A58);">
                Awesome! 🎊
            </button>
        </div>
    </div>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

const prizes      = @json($activePrizes);
const spinToken   = @json($token);
const tokenValid  = @json($tokenValid);
const TAU    = 2 * Math.PI;
let currentRotation = 0;
let spinning = false;

// ── Draw — equal slices ───────────────────────────────────────────────────────
function drawWheel(rot) {
    const canvas = document.getElementById('wheel-canvas');
    const ctx    = canvas.getContext('2d');
    const cx = canvas.width / 2, cy = canvas.height / 2, r = cx - 8;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (!prizes.length) {
        ctx.fillStyle = '#1e293b';
        ctx.beginPath(); ctx.arc(cx, cy, r, 0, TAU); ctx.fill();
        ctx.fillStyle = '#64748b'; ctx.font = 'bold 16px sans-serif';
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText('No active prizes', cx, cy);
        return;
    }

    const slice = TAU / prizes.length;
    let angle = rot;

    prizes.forEach(p => {
        const end = angle + slice;

        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, angle, end);
        ctx.closePath();
        ctx.fillStyle = p.color;
        ctx.fill();
        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 3;
        ctx.stroke();

        // Label
        const mid = angle + slice / 2;
        const lr  = r * 0.65;
        ctx.save();
        ctx.translate(cx + lr * Math.cos(mid), cy + lr * Math.sin(mid));
        ctx.rotate(mid + Math.PI / 2);
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 14px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const label = p.name.length > 13 ? p.name.slice(0, 12) + '…' : p.name;
        ctx.fillText(label, 0, 0);
        ctx.restore();

        angle = end;
    });

    // Centre hub
    ctx.beginPath(); ctx.arc(cx, cy, 20, 0, TAU);
    ctx.fillStyle = '#fff'; ctx.fill();
    ctx.strokeStyle = '#cbd5e1'; ctx.lineWidth = 2; ctx.stroke();
}

drawWheel(0);

if (!tokenValid) {
    document.getElementById('spin-btn').disabled = true;
}

// ── Spin ─────────────────────────────────────────────────────────────────────
function spinWheel() {
    if (spinning || !prizes.length || !tokenValid) return;
    spinning = true;

    const btn = document.getElementById('spin-btn');
    btn.disabled = true;
    document.getElementById('spin-status').textContent = '';

    $.ajax({
        url: '{{ route("wheel.spin") }}',
        method: 'POST',
        data: spinToken ? { token: spinToken } : {},
        success(res) {
            const prize = res.prize;

            // Equal slices: prize at index i has midpoint at (i + 0.5) * slice
            const slice  = TAU / prizes.length;
            const idx    = prizes.findIndex(p => p.id === prize.id);
            const segMid = (idx + 0.5) * slice;

            // Pointer is at the TOP of the canvas = angle 3π/2
            // We need: currentRotation + delta + segMid ≡ 3π/2  (mod TAU)
            // → delta residual = (3π/2 - segMid - currentRotation) mod TAU
            const targetAngle = ((3 * Math.PI / 2 - segMid - currentRotation) % TAU + TAU) % TAU;

            // Add 5–8 full spins for drama
            const extraSpins = (5 + Math.floor(Math.random() * 4)) * TAU;
            const delta = extraSpins + targetAngle;

            animateSpin(currentRotation, currentRotation + delta, 5000, prize);
        },
        error(xhr) {
            document.getElementById('spin-status').textContent = xhr.responseJSON?.error || 'Error. Try again.';
            spinning = false;
            btn.disabled = false;
        }
    });
}

// ── Animation ─────────────────────────────────────────────────────────────────
function animateSpin(from, to, duration, prize) {
    const t0 = performance.now();

    (function frame(now) {
        const progress = Math.min((now - t0) / duration, 1);
        const eased    = easeOutQuart(progress);
        currentRotation = from + (to - from) * eased;
        drawWheel(currentRotation);

        if (progress < 1) {
            requestAnimationFrame(frame);
        } else {
            currentRotation = to;
            drawWheel(to);
            spinning = false;
            document.getElementById('spin-btn').disabled = false;
            showWin(prize);
        }
    })(t0);
}

function easeOutQuart(t) { return 1 - Math.pow(1 - t, 4); }

// ── Win ───────────────────────────────────────────────────────────────────────
function showWin(prize) {
    document.getElementById('win-name').textContent = prize.name;
    document.getElementById('win-name').style.color = prize.color;
    document.getElementById('win-desc').textContent = prize.description || '';
    const overlay = document.getElementById('win-overlay');
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
    // Re-trigger animation
    const card = document.getElementById('win-card');
    card.style.animation = 'none';
    card.offsetHeight; // reflow
    card.style.animation = '';
}

function closeWin() {
    const overlay = document.getElementById('win-overlay');
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
}
</script>
</body>
</html>
