<?php
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/LogModel.php';
require_once __DIR__ . '/../../models/InfraModel.php';

require_role('admin');

$products = products_with_meta(null, 4);
$produkA = $products[0]['nama'] ?? 'Produk A';
$produkB = $products[1]['nama'] ?? 'Produk B';

zapiere_page_start('Simulasi Deadlock', 'admin', 'deadlock', 'Visualisasi transaksi paralel saat dua checkout berebut lock stok produk.');
?>

<style>
@keyframes terminalType {
    from { opacity: 0; transform: translateX(-6px); }
    to   { opacity: 1; transform: translateX(0); }
}
.terminal-line {
    opacity: 0;
    animation: terminalType 0.3s ease-out forwards;
}
.modal-backdrop {
    background: rgba(1, 28, 39, 0.6);
    backdrop-filter: blur(4px);
}
</style>

<!-- Modal Terminal -->
<div id="sim-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop p-4">
    <div class="relative w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden" style="background:#0f1117; border:1px solid #2a2f3d;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-5 py-4" style="background:#011C27; border-bottom:1px solid #1e2535;">
            <div class="flex items-center gap-3">
                <span class="text-lg">⚡</span>
                <p class="font-black text-white">Skenario Deadlock — Checkout Bersamaan</p>
            </div>
            <button id="sim-close" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-white/10 hover:text-white">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <!-- Terminal Body -->
        <div id="sim-output" class="h-80 overflow-y-auto px-5 py-4 font-mono text-xs leading-6 scroll-smooth" style="background:#0f1117;">
            <p class="text-slate-500 italic">// Tekan "Jalankan Simulasi" untuk memulai...</p>
        </div>

        <!-- Modal Footer -->
        <div id="sim-footer" class="hidden items-center justify-between gap-3 px-5 py-4" style="background:#011C27; border-top:1px solid #1e2535;">
            <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold">
                <i data-lucide="check-circle-2" class="h-4 w-4"></i>
                <span>Simulasi selesai — database tetap konsisten berkat InnoDB ACID</span>
            </div>
            <button id="sim-done" class="inline-flex items-center gap-2 rounded-lg bg-[#03254E] px-4 py-2 text-xs font-black text-white transition hover:bg-[#011C27]">
                Tutup
            </button>
        </div>
    </div>
</div>

<section class="grid gap-6 xl:grid-cols-[1fr_0.85fr]">
    <!-- Left: Diagram & Penjelasan -->
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Wait-for Graph</p>
                <h2 class="mt-2 text-2xl font-black">TX-A dan TX-B saling menunggu</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#545677]">Skenario checkout ketika transaksi pertama mengunci produk A lalu meminta produk B, sementara transaksi kedua melakukan urutan sebaliknya — memicu deadlock.</p>
            </div>
            <button id="sim-run" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
                <i data-lucide="play" class="h-5 w-5"></i>
                Jalankan Simulasi
            </button>
        </div>

        <!-- Wait-for diagram -->
        <div class="mt-8 grid gap-4 lg:grid-cols-[1fr_100px_1fr]">
            <article class="rounded-lg border-2 border-[#EB9FEF] bg-[#FECEE9]/40 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-[#545677]">Transaksi Pembeli Abdul</p>
                        <h3 class="mt-1 text-2xl font-black">TX-A</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-white text-[#011C27]">
                        <i data-lucide="shopping-cart" class="h-6 w-6"></i>
                    </div>
                </div>
                <div class="mt-5 space-y-3">
                    <div class="rounded-lg bg-white p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#545677]">Lock pertama ✅</p>
                        <p class="mt-2 font-black"><?= e($produkA) ?></p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Menunggu ⏳</p>
                        <p class="mt-2 font-black text-amber-800"><?= e($produkB) ?></p>
                    </div>
                </div>
            </article>

            <div class="flex items-center justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full border border-slate-200 bg-white text-[#03254E] shadow-soft">
                    <i data-lucide="repeat-2" class="h-9 w-9"></i>
                </div>
            </div>

            <article class="rounded-lg border-2 border-[#03254E] bg-slate-50 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-[#545677]">Transaksi Pembeli Budi</p>
                        <h3 class="mt-1 text-2xl font-black">TX-B</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#011C27] text-white">
                        <i data-lucide="credit-card" class="h-6 w-6"></i>
                    </div>
                </div>
                <div class="mt-5 space-y-3">
                    <div class="rounded-lg bg-white p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#545677]">Lock pertama ✅</p>
                        <p class="mt-2 font-black"><?= e($produkB) ?></p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Menunggu ⏳</p>
                        <p class="mt-2 font-black text-amber-800"><?= e($produkA) ?></p>
                    </div>
                </div>
            </article>
        </div>

        <!-- Fase-fase -->
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FECEE9]">
                    <i data-lucide="lock-keyhole" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-4 font-black">Lock stok</h3>
                <p class="mt-2 text-sm leading-6 text-[#545677]">Baris produk dikunci saat checkout menghitung stok dan total pembayaran.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FECEE9]">
                    <i data-lucide="circle-alert" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-4 font-black">Cycle detected</h3>
                <p class="mt-2 text-sm leading-6 text-[#545677]">Database mendeteksi TX-A menunggu TX-B dan TX-B menunggu TX-A.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FECEE9]">
                    <i data-lucide="rotate-ccw" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-4 font-black">Rollback korban</h3>
                <p class="mt-2 text-sm leading-6 text-[#545677]">Satu transaksi dibatalkan agar transaksi lain bisa commit, lalu transaksi korban diulang.</p>
            </div>
        </div>
    </div>

    <!-- Right: SQL & Penjelasan -->
    <aside class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">SQL Simulasi</p>
            <h2 class="mt-2 text-xl font-black">Urutan transaksi</h2>
            <div class="mt-5 space-y-3 text-sm">
                <p class="text-xs font-bold text-[#545677]">TX-A (Abdul)</p>
                <div class="rounded-lg bg-[#011C27] p-4 font-mono text-xs leading-6 text-white">
                    START TRANSACTION;<br>
                    SELECT * FROM produk<br>
                    &nbsp;&nbsp;WHERE id_produk = 1 FOR UPDATE;<br>
                    <span class="text-amber-300">-- menunggu lock produk 2...</span><br>
                    SELECT * FROM produk<br>
                    &nbsp;&nbsp;WHERE id_produk = 2 FOR UPDATE;
                </div>
                <p class="text-xs font-bold text-[#545677]">TX-B (Budi)</p>
                <div class="rounded-lg bg-[#03254E] p-4 font-mono text-xs leading-6 text-white">
                    START TRANSACTION;<br>
                    SELECT * FROM produk<br>
                    &nbsp;&nbsp;WHERE id_produk = 2 FOR UPDATE;<br>
                    <span class="text-amber-300">-- menunggu lock produk 1...</span><br>
                    SELECT * FROM produk<br>
                    &nbsp;&nbsp;WHERE id_produk = 1 FOR UPDATE;
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Resolusi InnoDB</p>
            <h2 class="mt-2 text-xl font-black">Bagaimana MySQL mengatasinya</h2>
            <div class="mt-5 space-y-3">
                <div class="flex gap-3 rounded-lg border border-slate-200 p-3">
                    <span class="mt-0.5 text-base">🔍</span>
                    <div>
                        <p class="text-sm font-black">Deteksi siklus</p>
                        <p class="mt-1 text-xs leading-5 text-[#545677]">InnoDB memeriksa Wait-for Graph dan mendeteksi siklus antara TX-A dan TX-B.</p>
                    </div>
                </div>
                <div class="flex gap-3 rounded-lg border border-slate-200 p-3">
                    <span class="mt-0.5 text-base">🎯</span>
                    <div>
                        <p class="text-sm font-black">Pilih korban</p>
                        <p class="mt-1 text-xs leading-5 text-[#545677]">Transaksi dengan biaya rollback terkecil (TX-B) dipilih sebagai korban dan dibatalkan.</p>
                    </div>
                </div>
                <div class="flex gap-3 rounded-lg border border-slate-200 p-3">
                    <span class="mt-0.5 text-base">✅</span>
                    <div>
                        <p class="text-sm font-black">Commit & retry</p>
                        <p class="mt-1 text-xs leading-5 text-[#545677]">TX-A berhasil commit. Aplikasi melakukan retry untuk TX-B secara otomatis.</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</section>

<script>
(function () {
    const produkA = <?= json_encode($produkA) ?>;
    const produkB = <?= json_encode($produkB) ?>;

    const steps = [
        { delay: 0,    color: '#94a3b8', text: '🚀 SIMULASI DEADLOCK — Checkout Bersamaan' },
        { delay: 200,  color: '#94a3b8', text: '─'.repeat(52) },
        { delay: 500,  color: '#94a3b8', text: `🎯  Produk A: ${produkA}` },
        { delay: 700,  color: '#94a3b8', text: `🎯  Produk B: ${produkB}` },
        { delay: 900,  color: '#94a3b8', text: '─'.repeat(52) },
        { delay: 1200, color: '#60a5fa', text: '[T=0ms]  TX-A memulai → START TRANSACTION' },
        { delay: 1500, color: '#a78bfa', text: '[T=1ms]  TX-B memulai → START TRANSACTION' },
        { delay: 1900, color: '#60a5fa', text: `[T=5ms]  TX-A: SELECT ${produkA} ... LOCK baris ✅` },
        { delay: 2300, color: '#a78bfa', text: `[T=6ms]  TX-B: SELECT ${produkB} ... LOCK baris ✅` },
        { delay: 2800, color: '#fbbf24', text: `[T=10ms] TX-A: SELECT ${produkB} ... menunggu lock ⏳` },
        { delay: 3200, color: '#fbbf24', text: `[T=11ms] TX-B: SELECT ${produkA} ... menunggu lock ⏳` },
        { delay: 3700, color: '#94a3b8', text: '─'.repeat(52) },
        { delay: 4000, color: '#f87171', text: '⚠️  KONFLIK: Siklus tunggu terdeteksi (deadlock)!' },
        { delay: 4400, color: '#f87171', text: '🔍 InnoDB memeriksa Wait-for Graph...' },
        { delay: 4900, color: '#f87171', text: '🎯 Korban dipilih: TX-B (biaya rollback terkecil)' },
        { delay: 5400, color: '#94a3b8', text: '─'.repeat(52) },
        { delay: 5700, color: '#f87171', text: '❌ TX-B → ROLLBACK: ERROR 1213 Deadlock found' },
        { delay: 6200, color: '#34d399', text: `✅ TX-A → mendapat lock ${produkB}` },
        { delay: 6600, color: '#34d399', text: '✅ TX-A → COMMIT berhasil, stok diperbarui' },
        { delay: 7000, color: '#60a5fa', text: '🔄 Aplikasi melakukan RETRY untuk TX-B...' },
        { delay: 7500, color: '#34d399', text: '✅ TX-B (retry) → COMMIT berhasil' },
        { delay: 7900, color: '#94a3b8', text: '─'.repeat(52) },
    ];

    const modal    = document.getElementById('sim-modal');
    const output   = document.getElementById('sim-output');
    const footer   = document.getElementById('sim-footer');
    const btnRun   = document.getElementById('sim-run');
    const btnClose = document.getElementById('sim-close');
    const btnDone  = document.getElementById('sim-done');

    let timers = [];

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        output.innerHTML = '';
        footer.classList.add('hidden');
        if (window.lucide) window.lucide.createIcons();

        timers = [];
        steps.forEach(function (step, i) {
            var t = setTimeout(function () {
                var line = document.createElement('p');
                line.className = 'terminal-line';
                line.style.color = step.color;
                line.style.animationDelay = '0s';
                line.textContent = step.text;
                output.appendChild(line);
                output.scrollTop = output.scrollHeight;
            }, step.delay);
            timers.push(t);
        });

        var lastDelay = steps[steps.length - 1].delay + 600;
        var tFooter = setTimeout(function () {
            footer.classList.remove('hidden');
            footer.classList.add('flex');
            if (window.lucide) window.lucide.createIcons();
        }, lastDelay);
        timers.push(tFooter);
    }

    function closeModal() {
        timers.forEach(clearTimeout);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        footer.classList.add('hidden');
        footer.classList.remove('flex');
    }

    btnRun.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);
    btnDone.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
})();
</script>

<?php zapiere_page_end(); ?>
