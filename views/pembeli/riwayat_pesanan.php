<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../components/layout_pembeli.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/OrderModel.php';

$user = require_role('pembeli');
$orders = orders_with_total((int) $user['id_user']);

zapiere_pembeli_page_start('Riwayat Pesanan', 'riwayat');
?>

<section class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 p-6 border-b border-gray-100">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#545677] mb-1">Transaksi</p>
            <h2 class="text-2xl font-extrabold text-[#011C27]">Riwayat Pesanan</h2>
        </div>
        <span class="rounded-full bg-[#FECEE9] px-4 py-1.5 text-xs font-extrabold text-[#011C27]">
            <?= count($orders) ?> pesanan
        </span>
    </div>

    <?php if (empty($orders)): ?>
        <div class="py-16 text-center">
            <i class="ph-light ph-package text-6xl text-gray-200 block mb-4"></i>
            <p class="text-[#545677] font-semibold text-sm mb-5">Belum ada pesanan. Yuk mulai belanja!</p>
            <a href="<?= e(url_for('views/pembeli/dashboard.php')) ?>"
               class="inline-flex items-center gap-2 rounded-full bg-[#011C27] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#03254E]">
                <i class="ph ph-shopping-bag"></i>
                Belanja Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="hidden grid-cols-[0.5fr_1.5fr_1fr_0.8fr] bg-gray-50 px-6 py-3 text-xs font-extrabold uppercase tracking-widest text-[#545677] md:grid">
            <span>#</span>
            <span>Produk</span>
            <span>Tanggal</span>
            <span class="text-right">Total</span>
        </div>
        <div class="divide-y divide-gray-50">
            <?php foreach ($orders as $order): ?>
                <div class="grid gap-2 px-6 py-4 md:grid-cols-[0.5fr_1.5fr_1fr_0.8fr] md:items-center hover:bg-gray-50/50 transition-colors">
                    <p class="text-sm font-bold text-[#545677]">#<?= e($order['id_pesanan'] ?? '-') ?></p>
                    <p class="font-bold text-[#0a0a0a] text-sm leading-snug"><?= e($order['produk'] ?? 'Pesanan') ?></p>
                    <p class="text-sm text-[#545677] font-semibold"><?= e($order['tanggal'] ?? '-') ?></p>
                    <p class="text-sm font-extrabold text-[#011C27] md:text-right">
                        <?= e($order['total_rp'] ?? ('Rp ' . number_format((int)($order['total_bayar'] ?? 0), 0, ',', '.'))) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php zapiere_pembeli_page_end(); ?>
