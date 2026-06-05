<?php
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/OrderModel.php';
require_once __DIR__ . '/../../models/InfraModel.php';
require_once __DIR__ . '/../../models/LogModel.php';

require_role('admin');

$products = products_with_meta();
$orders = orders_with_total();
$users = users_data();
$nodes = nodes_data();
$fragments = fragments_data();
$logs = recent_logs(6);

$totalRevenueRp = get_total_revenue();
$totalStock = array_sum(array_map(fn($product) => (int) $product['stok'], $products));
$sellerCount = count(array_filter($users, fn($user) => $user['role'] === 'penjual'));
$buyerCount = count(array_filter($users, fn($user) => $user['role'] === 'pembeli'));
$lowStock = array_values(array_filter($products, fn($product) => (int) $product['stok'] <= 10));
$topProducts = $products;
usort($topProducts, fn($a, $b) => (int) $b['total_terjual'] <=> (int) $a['total_terjual']);
$topProducts = array_slice($topProducts, 0, 4);

zapiere_page_start('Dashboard Admin', 'admin', 'dashboard', 'Ringkasan operasional Zapiere dan simulasi arsitektur data terdistribusi.');
?>

<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <?php metric_card('Total Produk', count($products), 'box', $totalStock . ' unit stok aktif'); ?>
    <?php metric_card('Nilai Transaksi', $totalRevenueRp, 'wallet-cards', count($orders) . ' pesanan tercatat'); ?>
    <?php metric_card('Pengguna', count($users), 'users', $sellerCount . ' penjual, ' . $buyerCount . ' pembeli'); ?>
    <?php metric_card('Node Database', count($nodes), 'server', count($fragments) . ' fragmen aktif'); ?>
</section>

<section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Topologi Distribusi</p>
                <h2 class="mt-2 text-2xl font-black">Status node penyimpanan</h2>
            </div>
            <a href="<?= e(url_for('views/admin/backup_sistem.php')) ?>" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-[#011C27] transition hover:border-[#EB9FEF] hover:bg-[#FECEE9]/40">
                <i data-lucide="database-backup" class="h-4 w-4"></i>
                Backup
            </a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <?php foreach ($nodes as $node): ?>
                <article class="rounded-lg border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-black"><?= e($node['nama_node']) ?></h3>
                            <p class="mt-1 text-sm font-medium text-[#545677]"><?= e($node['lokasi']) ?> - <?= e($node['tipe_node']) ?></p>
                        </div>
                        <?= status_badge($node['status_node']) ?>
                    </div>
                    <div class="mt-5 space-y-3">
                        <div>
                            <div class="mb-1 flex justify-between text-xs font-bold text-[#545677]">
                                <span>Beban</span>
                                <span><?= e($node['beban_persen']) ?>%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-[#EB9FEF]" style="width: <?= e(min(100, (int) $node['beban_persen'])) ?>%"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                            <span class="font-semibold text-[#545677]">Latency</span>
                            <span class="font-black"><?= e($node['latency_ms']) ?> ms</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-[#011C27] p-6 text-white shadow-soft">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#FECEE9]">Deadlock Demo</p>
        <h2 class="mt-2 text-2xl font-black">Checkout paralel pada stok produk</h2>
        <p class="mt-3 text-sm leading-6 text-white/75">Skenario memakai dua transaksi yang mengambil lock produk dengan urutan berbeda. Halaman simulasi menampilkan wait-for graph dan resolusi rollback.</p>
        <div class="mt-6 grid gap-3">
            <div class="rounded-lg bg-white/10 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold">TX-A</span>
                    <span class="rounded-full bg-[#EB9FEF] px-3 py-1 text-xs font-black text-[#011C27]">Lock P1</span>
                </div>
                <p class="mt-3 text-xs font-medium text-white/70">Menunggu produk P2 yang sedang dikunci TX-B.</p>
            </div>
            <div class="rounded-lg bg-white/10 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold">TX-B</span>
                    <span class="rounded-full bg-[#FECEE9] px-3 py-1 text-xs font-black text-[#011C27]">Lock P2</span>
                </div>
                <p class="mt-3 text-xs font-medium text-white/70">Menunggu produk P1 yang sedang dikunci TX-A.</p>
            </div>
        </div>
        <a href="<?= e(url_for('views/admin/simulasi_deadlock.php')) ?>" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#EB9FEF] px-5 py-3 text-sm font-black text-[#011C27] transition hover:bg-[#FECEE9]">
            <i data-lucide="play-circle" class="h-5 w-5"></i>
            Buka Simulasi
        </a>
    </div>
</section>

<section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Fragmentasi</p>
                <h2 class="mt-2 text-xl font-black">Pemetaan data ke node</h2>
            </div>
            <span class="rounded-full bg-[#FECEE9] px-3 py-1 text-xs font-black text-[#011C27]"><?= count($fragments) ?> fragmen</span>
        </div>

        <div class="space-y-3">
            <?php foreach ($fragments as $fragment): ?>
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="font-black"><?= e($fragment['nama_fragmen']) ?></h3>
                            <p class="mt-1 text-sm text-[#545677]"><?= e($fragment['aturan_fragmentasi']) ?></p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black capitalize text-[#03254E]"><?= e($fragment['tipe_fragmentasi']) ?></span>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs font-bold text-[#545677]">
                        <i data-lucide="table-2" class="h-4 w-4"></i>
                        <?= e($fragment['nama_tabel']) ?> di <?= e($fragment['nama_node']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Produk & Aktivitas</p>
                <h2 class="mt-2 text-xl font-black">Barang elektronik yang bergerak</h2>
            </div>
            <?php if ($lowStock): ?>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700"><?= count($lowStock) ?> stok rendah</span>
            <?php endif; ?>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="space-y-3">
                <?php foreach ($topProducts as $product): ?>
                    <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                        <img src="<?= e(product_image_url($product['foto_barang'] ?? '')) ?>" alt="<?= e($product['nama']) ?>" class="h-14 w-14 rounded-lg bg-slate-950 object-contain">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-black"><?= e($product['nama']) ?></p>
                            <p class="mt-1 text-xs font-semibold text-[#545677]"><?= e($product['kategori']) ?> - <?= e($product['total_terjual']) ?> terjual</p>
                        </div>
                        <p class="text-sm font-black"><?= e($product['harga_rp'] ?? 'Rp ' . $product['harga']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="space-y-3">
                <?php foreach ($logs as $log): ?>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-bold"><?= e($log['keterangan']) ?></p>
                            <span class="shrink-0 rounded-full bg-white px-2 py-1 text-[11px] font-black capitalize text-[#545677]"><?= e($log['role'] ?? '-') ?></span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-[#545677]"><?= e($log['nama'] ?? 'Sistem') ?> - <?= e($log['tgl_aktifitas']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php zapiere_page_end(); ?>
