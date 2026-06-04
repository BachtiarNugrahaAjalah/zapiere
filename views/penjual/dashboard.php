<?php
require_once __DIR__ . '/../components/layout.php';

$user = current_user('penjual');
$sellerId = (int) ($user['id_user'] ?? 2);
$products = products_with_meta($sellerId);
$sales = sales_rows($sellerId);
$fragments = fragments_data();

$sellerStats = get_seller_stats($sellerId);
$revenueRp = $sellerStats['total_omzet_rp'];
$stock = array_sum(array_map(fn($product) => (int) $product['stok'], $products));
$sold = array_sum(array_map(fn($product) => (int) $product['total_terjual'], $products));
$lowStock = array_values(array_filter($products, fn($product) => (int) $product['stok'] <= 10));

zapiere_page_start('Dashboard Penjual', 'penjual', 'dashboard', 'Pantau produk, stok, dan pesanan toko elektronikmu.');
?>

<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <?php metric_card('Produk Toko', count($products), 'boxes', $stock . ' unit stok tersedia'); ?>
    <?php metric_card('Omzet', $revenueRp, 'banknote', count($sales) . ' baris penjualan'); ?>
    <?php metric_card('Barang Terjual', $sold, 'shopping-basket', 'akumulasi dari detail pesanan'); ?>
    <?php metric_card('Stok Rendah', count($lowStock), 'triangle-alert', 'butuh restock prioritas'); ?>
</section>

<section class="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Inventori</p>
                <h2 class="mt-2 text-2xl font-black">Produk elektronik toko</h2>
            </div>
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
                <i data-lucide="plus" class="h-5 w-5"></i>
                Tambah Produk
            </button>
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border border-slate-200">
            <div class="hidden grid-cols-[1.4fr_0.8fr_0.6fr_0.8fr] bg-slate-50 px-4 py-3 text-xs font-black uppercase tracking-[0.12em] text-[#545677] md:grid">
                <span>Produk</span>
                <span>Kategori</span>
                <span>Stok</span>
                <span>Harga</span>
            </div>
            <div class="divide-y divide-slate-200">
                <?php foreach ($products as $product): ?>
                    <div class="grid gap-3 px-4 py-4 md:grid-cols-[1.4fr_0.8fr_0.6fr_0.8fr] md:items-center">
                        <div class="flex items-center gap-3">
                            <img src="<?= e(product_image_url($product)) ?>" alt="<?= e($product['nama']) ?>" class="h-14 w-14 rounded-lg bg-slate-950 object-contain">
                            <div class="min-w-0">
                                <p class="truncate font-black"><?= e($product['nama']) ?></p>
                                <p class="mt-1 text-xs font-semibold text-[#545677]"><?= e($product['total_terjual']) ?> terjual</p>
                            </div>
                        </div>
                        <p class="text-sm font-semibold text-[#545677]"><?= e($product['kategori']) ?></p>
                        <div>
                            <?php if ((int) $product['stok'] <= 10): ?>
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700"><?= e($product['stok']) ?> unit</span>
                            <?php else: ?>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700"><?= e($product['stok']) ?> unit</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm font-black"><?= e($product['harga_rp'] ?? 'Rp ' . $product['harga']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <aside class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-[#011C27] p-6 text-white shadow-soft">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#FECEE9]">Distribusi Produk</p>
            <h2 class="mt-2 text-2xl font-black">Fragmen inventori toko</h2>
            <p class="mt-3 text-sm leading-6 text-white/75">Produk toko dapat ditempatkan ke fragmen berbeda berdasarkan kategori. Ini memudahkan demo horizontal fragmentation.</p>
            <div class="mt-5 space-y-3">
                <?php foreach (array_slice($fragments, 0, 3) as $fragment): ?>
                    <div class="rounded-lg bg-white/10 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-black"><?= e($fragment['nama_fragmen']) ?></p>
                            <span class="rounded-full bg-[#FECEE9] px-2.5 py-1 text-xs font-black capitalize text-[#011C27]"><?= e($fragment['tipe_fragmentasi']) ?></span>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-white/70"><?= e($fragment['nama_node']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Penjualan Baru</p>
                    <h2 class="mt-2 text-xl font-black">Transaksi masuk</h2>
                </div>
                <a href="<?= e(url_for('views/penjual/riwayat_penjualan.php')) ?>" class="text-sm font-black text-[#03254E] hover:text-[#EB9FEF]">Lihat semua</a>
            </div>
            <div class="space-y-3">
                <?php foreach (array_slice($sales, 0, 5) as $sale): ?>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black"><?= e($sale['produk']) ?></p>
                                <p class="mt-1 text-sm font-semibold text-[#545677]"><?= e($sale['pembeli']) ?> - <?= e($sale['jumlah']) ?> unit</p>
                            </div>
                            <p class="text-sm font-black"><?= e($sale['subtotal_rp'] ?? 'Rp ' . $sale['subtotal']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</section>

<?php zapiere_page_end(); ?>
