<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../components/layout_pembeli.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/OrderModel.php';

$user = require_role('pembeli');
$userId = (int) $user['id_user'];

$fullUser = db_one("SELECT * FROM users WHERE id_user = {$userId}");
$user = $fullUser ?: $user;

$totalBelanja = get_buyer_total_spent($userId);
$orders       = orders_with_total($userId);
$totalPesanan = count($orders);

zapiere_pembeli_page_start('Profil Saya', 'profil');
?>

<div class="grid gap-6 xl:grid-cols-[320px_1fr]">

    <div class="flex flex-col gap-6">
        <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="h-28 bg-gradient-to-br from-[#EB9FEF]/60 via-[#FECEE9]/80 to-[#FECEE9]/40"></div>
            <div class="px-6 pb-6">
                <div class="-mt-10 mb-4 flex items-end justify-between">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white text-3xl font-black text-[#011C27] ring-4 ring-white shadow-lg border border-[#EB9FEF]/30">
                        <?= e(strtoupper(substr($user['nama'] ?? 'Z', 0, 1))) ?>
                    </div>
                    <span class="mb-1 rounded-full bg-[#011C27] px-3 py-1 text-xs font-black text-[#FECEE9] capitalize">
                        <?= e($user['role'] ?? 'pembeli') ?>
                    </span>
                </div>
                <h2 class="text-xl font-extrabold text-[#011C27]"><?= e($user['nama'] ?? '-') ?></h2>
                <p class="mt-0.5 text-sm text-[#545677] font-medium">@<?= e($user['username'] ?? '-') ?></p>
            </div>
        </div>

        <a href="<?= e(url_for('logout.php')) ?>"
           class="flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-white py-3 text-sm font-bold text-red-500 transition hover:bg-red-50 hover:border-red-400">
            <i class="ph ph-sign-out text-lg"></i>
            Keluar dari Akun
        </a>
    </div>

    <div class="flex flex-col gap-6">

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Saldo</p>
                <p class="mt-3 text-2xl font-extrabold text-[#011C27]">Rp <?= number_format((int)($user['saldo'] ?? 0), 0, ',', '.') ?></p>
                <p class="mt-1 text-xs text-gray-400">Saldo tersedia</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Total Pesanan</p>
                <p class="mt-3 text-2xl font-extrabold text-[#011C27]"><?= $totalPesanan ?></p>
                <p class="mt-1 text-xs text-gray-400">Transaksi berhasil</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Total Belanja</p>
                <p class="mt-3 text-2xl font-extrabold text-[#011C27]">Rp <?= number_format($totalBelanja, 0, ',', '.') ?></p>
                <p class="mt-1 text-xs text-gray-400">Akumulasi pembelian</p>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-50 px-6 py-4">
                <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Informasi Akun</p>
                <h3 class="mt-1 text-lg font-extrabold text-[#011C27]">Data Diri</h3>
            </div>
            <div class="divide-y divide-gray-50">
                <?php
                $rows = [
                    ['ph ph-pencil-line',         'Nama Lengkap', $user['nama'] ?? '-'],
                    ['ph ph-at',                  'Username',     '@' . ($user['username'] ?? '-')],
                    ['ph ph-shield-check',         'Tipe Akun',    ucfirst($user['role'] ?? '-')],
                    ['ph-fill ph-wallet',          'Saldo',        'Rp ' . number_format((int)($user['saldo'] ?? 0), 0, ',', '.')],
                ];
                foreach ($rows as [$icon, $label, $value]):
                ?>
                <div class="flex items-center gap-4 px-6 py-4">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-[#FECEE9]/50 text-[#011C27]">
                        <i class="<?= $icon ?> text-lg"></i>
                    </div>
                    <div class="flex flex-1 items-center justify-between gap-4 min-w-0">
                        <span class="text-sm font-semibold text-[#545677]"><?= e($label) ?></span>
                        <span class="text-sm font-extrabold text-[#011C27] text-right"><?= e($value) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($orders)): ?>
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-50 px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Riwayat</p>
                    <h3 class="mt-1 text-lg font-extrabold text-[#011C27]">3 Pesanan Terakhir</h3>
                </div>
                <a href="<?= e(url_for('views/pembeli/riwayat_pesanan.php')) ?>" class="text-xs font-bold text-[#011C27] hover:text-[#EB9FEF] transition">Lihat semua →</a>
            </div>
            <div class="divide-y divide-gray-50">
                <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-[#011C27]"><?= e($order['produk'] ?? 'Pesanan #' . $order['id_pesanan']) ?></p>
                        <p class="mt-0.5 text-xs text-[#545677]"><?= e($order['tanggal'] ?? '-') ?></p>
                    </div>
                    <p class="flex-shrink-0 text-sm font-extrabold text-[#011C27]">
                        <?= e($order['total_bayar_rp'] ?? ('Rp ' . number_format((int)($order['total_bayar'] ?? 0), 0, ',', '.'))) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php zapiere_pembeli_page_end(); ?>
