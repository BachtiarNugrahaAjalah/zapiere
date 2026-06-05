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

    <div class="flex flex-col justify-between rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Manajemen Data</p>
            <h2 class="mt-2 text-2xl font-black">Backup Sistem</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">Backup secara manual atau otomatis agar data aman.</p>
        </div>
        <a href="<?= e(url_for('views/admin/backup_sistem.php')) ?>" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg border-2 border-[#011C27] bg-transparent px-5 py-3 text-sm font-black text-[#011C27] transition hover:bg-slate-50">
            <i data-lucide="database-backup" class="h-5 w-5"></i>
            Kelola Backup
        </a>
    </div>
</section>

<section class="mt-6">

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
