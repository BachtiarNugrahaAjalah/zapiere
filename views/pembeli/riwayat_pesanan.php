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
        <div class="hidden grid-cols-[0.5fr_1.5fr_1fr_0.8fr_0.5fr] bg-gray-50 px-6 py-3 text-xs font-extrabold uppercase tracking-widest text-[#545677] md:grid">
            <span>#</span>
            <span>Produk Utama</span>
            <span>Tanggal</span>
            <span class="text-right">Total</span>
            <span class="text-center">Aksi</span>
        </div>
        <div class="divide-y divide-gray-50">
            <?php foreach ($orders as $order): ?>
                <?php $details = detail_pesanan((int)$order['id_pesanan']); ?>
                <div class="grid gap-4 px-6 py-4 md:grid-cols-[0.5fr_1.5fr_1fr_0.8fr_0.5fr] md:items-center hover:bg-gray-50/50 transition-colors">
                    <p class="text-sm font-bold text-[#545677]">#<?= e($order['id_pesanan'] ?? '-') ?></p>
                    <p class="font-bold text-[#0a0a0a] text-sm leading-snug truncate" title="<?= e($order['produk'] ?? 'Pesanan') ?>"><?= e($order['produk'] ?? 'Pesanan') ?></p>
                    <p class="text-sm text-[#545677] font-semibold"><?= e($order['tanggal'] ?? '-') ?></p>
                    <p class="text-sm font-extrabold text-[#011C27] md:text-right">
                        <?= e($order['total_rp'] ?? ('Rp ' . number_format((int)($order['total_bayar'] ?? 0), 0, ',', '.'))) ?>
                    </p>
                    <div class="md:text-center mt-2 md:mt-0">
                        <button onclick="document.getElementById('modal_order_<?= $order['id_pesanan'] ?>').showModal()"
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#FECEE9]/50 text-[#011C27] px-4 py-1.5 text-xs font-bold transition hover:bg-[#EB9FEF] hover:text-white border border-[#EB9FEF]/30 w-full justify-center md:w-auto">
                            <i class="ph ph-list-magnifying-glass text-sm"></i>
                            Detail
                        </button>
                    </div>
                </div>

                <!-- Modal Dialog for Detail Pesanan -->
                <dialog id="modal_order_<?= $order['id_pesanan'] ?>" class="backdrop:bg-black/50 p-0 rounded-2xl shadow-2xl border-0 m-auto open:animate-in open:fade-in open:zoom-in-95 w-[90%] max-w-xl max-h-[85vh]">
                    <div class="flex flex-col h-full max-h-[85vh]">
                        <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-white">
                            <div>
                                <h3 class="text-lg font-extrabold text-[#011C27]">Detail Pesanan #<?= e($order['id_pesanan']) ?></h3>
                                <p class="text-xs text-[#545677] font-semibold mt-1">Tanggal: <?= e($order['tanggal']) ?></p>
                            </div>
                            <button onclick="document.getElementById('modal_order_<?= $order['id_pesanan'] ?>').close()" class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-800 transition">
                                <i class="ph-bold ph-x text-sm"></i>
                            </button>
                        </div>
                        
                        <div class="p-5 overflow-y-auto bg-gray-50/30 flex-grow">
                            <div class="space-y-4">
                                <?php foreach ($details as $item): ?>
                                    <div class="flex gap-4 p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                                        <div class="h-20 w-20 flex-shrink-0 rounded-lg overflow-hidden border border-gray-100 bg-gray-50">
                                            <img src="<?= e(url_for('assets/images/' . ($item['foto_barang'] ?: 'default.png'))) ?>" 
                                                 alt="<?= e($item['nama_produk']) ?>" 
                                                 class="h-full w-full object-cover">
                                        </div>
                                        <div class="flex-grow flex flex-col justify-between py-1">
                                            <div>
                                                <h4 class="font-bold text-[#0a0a0a] text-sm leading-snug line-clamp-2"><?= e($item['nama_produk']) ?></h4>
                                                <p class="text-xs text-[#545677] font-semibold mt-1">
                                                    Rp <?= number_format((int)$item['harga'], 0, ',', '.') ?> <span class="font-normal mx-1">x</span> <?= (int)$item['jumlah'] ?>
                                                </p>
                                            </div>
                                            <p class="text-sm font-extrabold text-[#011C27] mt-2">
                                                Rp <?= number_format((int)$item['harga'] * (int)$item['jumlah'], 0, ',', '.') ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="p-5 border-t border-gray-100 bg-white">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-[#545677]">Total Belanja</span>
                                <span class="text-lg font-extrabold text-[#011C27]">
                                    <?= e($order['total_rp'] ?? ('Rp ' . number_format((int)($order['total_bayar'] ?? 0), 0, ',', '.'))) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </dialog>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php zapiere_pembeli_page_end(); ?>
