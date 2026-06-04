<?php
require_once __DIR__ . '/../components/layout.php';

$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (table_exists('simulasi_deadlock_log')) {
        $createdAt = date('Y-m-d H:i:s');
        db_exec("
            INSERT INTO simulasi_deadlock_log (kode_transaksi, sumber_daya, status_lock, aksi_resolusi, created_at)
            VALUES
            ('TX-A', 'produk:1 -> produk:2', 'waiting', 'cycle detected, rollback TX-B', '{$createdAt}'),
            ('TX-B', 'produk:2 -> produk:1', 'resolved', 'retry after TX-A commit', '{$createdAt}')
        ");
        $notice = 'Log simulasi tersimpan ke database.';
    } else {
        $notice = 'Tabel simulasi belum ada, tampilan memakai data demo.';
    }
}

$logs = deadlock_logs();
$products = products_with_meta(null, 4);

zapiere_page_start('Simulasi Deadlock', 'admin', 'deadlock', 'Visualisasi transaksi paralel saat dua checkout berebut lock stok produk.');
?>

<?php if ($notice): ?>
    <div class="rounded-lg border border-[#FECEE9] bg-[#FECEE9]/50 px-4 py-3 text-sm font-bold text-[#011C27]">
        <?= e($notice) ?>
    </div>
<?php endif; ?>

<section class="grid gap-6 xl:grid-cols-[1fr_0.85fr]">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Wait-for Graph</p>
                <h2 class="mt-2 text-2xl font-black">TX-A dan TX-B saling menunggu</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#545677]">Skenario ini cocok untuk menjelaskan deadlock pada checkout ketika transaksi pertama mengunci produk A lalu meminta produk B, sementara transaksi kedua melakukan urutan sebaliknya.</p>
            </div>
            <form method="POST">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
                    <i data-lucide="play" class="h-5 w-5"></i>
                    Catat Simulasi
                </button>
            </form>
        </div>

        <div class="mt-8 grid gap-4 lg:grid-cols-[1fr_120px_1fr]">
            <article id="tx-a" class="rounded-lg border-2 border-[#EB9FEF] bg-[#FECEE9]/40 p-5">
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
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#545677]">Lock pertama</p>
                        <p class="mt-2 font-black"><?= e($products[0]['nama'] ?? 'Produk A') ?></p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Menunggu</p>
                        <p class="mt-2 font-black text-amber-800"><?= e($products[1]['nama'] ?? 'Produk B') ?></p>
                    </div>
                </div>
            </article>

            <div class="flex items-center justify-center">
                <div class="flex h-24 w-24 items-center justify-center rounded-full border border-slate-200 bg-white text-[#03254E] shadow-soft">
                    <i data-lucide="repeat-2" class="h-10 w-10"></i>
                </div>
            </div>

            <article id="tx-b" class="rounded-lg border-2 border-[#03254E] bg-slate-50 p-5">
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
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#545677]">Lock pertama</p>
                        <p class="mt-2 font-black"><?= e($products[1]['nama'] ?? 'Produk B') ?></p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Menunggu</p>
                        <p class="mt-2 font-black text-amber-800"><?= e($products[0]['nama'] ?? 'Produk A') ?></p>
                    </div>
                </div>
            </article>
        </div>

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

    <aside class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">SQL Simulasi</p>
            <h2 class="mt-2 text-xl font-black">Urutan transaksi</h2>
            <div class="mt-5 space-y-3 text-sm">
                <div class="rounded-lg bg-[#011C27] p-4 font-mono text-xs leading-6 text-white">
                    START TRANSACTION;<br>
                    SELECT * FROM produk WHERE id_produk = 1 FOR UPDATE;<br>
                    SELECT * FROM produk WHERE id_produk = 2 FOR UPDATE;
                </div>
                <div class="rounded-lg bg-[#03254E] p-4 font-mono text-xs leading-6 text-white">
                    START TRANSACTION;<br>
                    SELECT * FROM produk WHERE id_produk = 2 FOR UPDATE;<br>
                    SELECT * FROM produk WHERE id_produk = 1 FOR UPDATE;
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Log</p>
                    <h2 class="mt-2 text-xl font-black">Riwayat resolusi</h2>
                </div>
                <span class="rounded-full bg-[#FECEE9] px-3 py-1 text-xs font-black"><?= count($logs) ?></span>
            </div>

            <div class="space-y-3">
                <?php foreach ($logs as $log): ?>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-black"><?= e($log['kode_transaksi']) ?></p>
                            <?= status_badge($log['status_lock']) ?>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-[#545677]"><?= e($log['sumber_daya'] ?? '-') ?></p>
                        <p class="mt-2 text-xs font-medium text-[#545677]"><?= e($log['aksi_resolusi']) ?> - <?= e($log['created_at']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</section>

<?php zapiere_page_end(); ?>
