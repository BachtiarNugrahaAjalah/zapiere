<?php
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/OrderModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$user = require_role('penjual');
$sellerId = (int) $user['id_user'];

$produk_terjual = daftar_produk_terjual($sellerId);

zapiere_page_start('Performa Produk', 'penjual', 'lacak', 'Pantau performa penjualan tiap produkmu.');
?>

<section class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Statistik Penjualan</p>
            <h2 class="mt-2 text-2xl font-black">Performa Produk</h2>
            <p class="mt-2 text-sm text-[#545677]">Menampilkan semua produkmu beserta jumlah unit yang laku terjual. (Menggunakan RIGHT JOIN)</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-[#FECEE9] px-4 py-2 text-sm font-black text-[#011C27]">
                Total <?= count($produk_terjual) ?> Produk
            </span>
        </div>
    </div>

    <?php if (empty($produk_terjual)): ?>
        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 py-16">
            <i data-lucide="package-open" class="mb-4 h-12 w-12 text-slate-300"></i>
            <p class="text-sm font-bold text-slate-500">Kamu belum punya produk sama sekali.</p>
        </div>
    <?php else: ?>
        <div class="overflow-hidden rounded-lg border border-slate-200">
            <div class="grid grid-cols-[3fr_1.5fr_1fr] bg-slate-50 px-6 py-4 text-xs font-black uppercase tracking-[0.12em] text-[#545677] md:grid-cols-[3fr_1.5fr_1fr]">
                <span>Produk</span>
                <span class="text-right">Harga</span>
                <span class="text-center">Total Terjual</span>
            </div>
            <div class="divide-y divide-slate-200">
                <?php foreach ($produk_terjual as $p): ?>
                    <div class="grid grid-cols-[3fr_1.5fr_1fr] items-center gap-4 px-6 py-4 transition hover:bg-slate-50/50">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-white p-1">
                                <img src="<?= e(product_image_url($p['foto_barang'] ?? '')) ?>" alt="<?= e($p['nama_produk']) ?>" class="h-full w-full object-contain">
                            </div>
                            <div>
                                <p class="truncate font-black text-[#0a0a0a]"><?= e($p['nama_produk']) ?></p>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-sm font-bold text-[#0a0a0a]">Rp <?= number_format((int)$p['harga'], 0, ',', '.') ?></p>
                        </div>
                        
                        <div class="flex justify-center">
                            <?php if ((int)$p['jumlah_terjual'] > 0): ?>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-4 py-1.5 text-xs font-black text-emerald-700 border border-emerald-100">
                                    <i data-lucide="trending-up" class="h-3.5 w-3.5"></i>
                                    <?= (int)$p['jumlah_terjual'] ?> unit
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-4 py-1.5 text-xs font-bold text-slate-500 border border-slate-200">
                                    <i data-lucide="minus" class="h-3.5 w-3.5"></i>
                                    0 unit
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php zapiere_page_end(); ?>
