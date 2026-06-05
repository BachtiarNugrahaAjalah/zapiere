<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$user = require_role('penjual');
$sellerId = (int) $user['id_user'];
$sales = sales_rows($sellerId);

zapiere_page_start('Riwayat Penjualan', 'penjual', 'riwayat', 'Semua transaksi penjualan dari tokomu.');
?>

<section class="rounded-lg border border-slate-200 bg-white shadow-soft">
    <div class="flex items-center justify-between gap-3 p-6 border-b border-slate-200">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Transaksi</p>
            <h2 class="mt-2 text-2xl font-black">Riwayat Penjualan</h2>
        </div>
        <span class="rounded-full bg-[#FECEE9] px-3 py-1 text-xs font-black text-[#011C27]"><?= count($sales) ?> baris</span>
    </div>

    <?php if (empty($sales)): ?>
        <div class="p-12 text-center">
            <p class="text-[#545677] font-semibold">Belum ada penjualan tercatat untuk toko ini.</p>
        </div>
    <?php else: ?>
        <div class="overflow-hidden">
            <div class="hidden grid-cols-[1.5fr_1fr_0.5fr_0.8fr_0.8fr] bg-slate-50 px-6 py-3 text-xs font-black uppercase tracking-[0.12em] text-[#545677] md:grid">
                <span>Produk</span>
                <span>Pembeli</span>
                <span>Qty</span>
                <span>Subtotal</span>
                <span>Tanggal</span>
            </div>
            <div class="divide-y divide-slate-200">
                <?php foreach ($sales as $sale): ?>
                    <div class="grid gap-2 px-6 py-4 md:grid-cols-[1.5fr_1fr_0.5fr_0.8fr_0.8fr] md:items-center">
                        <p class="font-black truncate"><?= e($sale['produk'] ?? '-') ?></p>
                        <p class="text-sm font-semibold text-[#545677]"><?= e($sale['pembeli'] ?? '-') ?></p>
                        <p class="text-sm font-black"><?= e($sale['jumlah'] ?? '-') ?> pcs</p>
                        <p class="text-sm font-black"><?= e($sale['subtotal_rp'] ?? 'Rp ' . ($sale['subtotal'] ?? '-')) ?></p>
                        <p class="text-xs font-semibold text-[#545677]"><?= e($sale['tanggal'] ?? '-') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php zapiere_page_end(); ?>
