<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/UserModel.php';

$user = require_role('penjual');
$userId = (int) $user['id_user'];

$fullUser = db_one("SELECT * FROM users WHERE id_user = {$userId}");
$user = $fullUser ?: $user;

$sellerStats = get_seller_stats($userId);
$totalProduk = $sellerStats['total_produk'];
$totalOmzet  = $sellerStats['total_omzet_rp'];

zapiere_page_start('Profil Toko', 'penjual', 'profil', 'Informasi akun dan detail toko elektronikmu.');
?>

<div class="grid gap-6 xl:grid-cols-[340px_1fr]">

    <div class="flex flex-col gap-6">
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft">
            <div class="h-28 bg-gradient-to-br from-[#011C27] via-[#03254E] to-[#545677]"></div>
            <div class="px-6 pb-6">
                <div class="-mt-10 mb-4 flex items-end justify-between">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-[#FECEE9] text-3xl font-black text-[#011C27] ring-4 ring-white shadow-lg">
                        <?= e(strtoupper(substr($user['nama'] ?? 'Z', 0, 1))) ?>
                    </div>
                    <span class="mb-1 rounded-full bg-[#011C27] px-3 py-1 text-xs font-black text-[#EB9FEF] capitalize">
                        <?= e($user['role'] ?? 'penjual') ?>
                    </span>
                </div>
                <h2 class="text-xl font-black text-[#011C27]"><?= e($user['nama'] ?? '-') ?></h2>
                <?php if (!empty($user['nama_toko'])): ?>
                    <p class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold text-[#545677]">
                        <i data-lucide="store" class="h-4 w-4 flex-shrink-0"></i>
                        <?= e($user['nama_toko']) ?>
                    </p>
                <?php endif; ?>
                <p class="mt-1 text-xs text-slate-400 font-medium">@<?= e($user['username'] ?? '-') ?></p>
            </div>
        </div>

        <a href="<?= e(url_for('logout.php')) ?>"
           class="flex items-center justify-center gap-2 rounded-xl border border-rose-200 py-3 text-sm font-bold text-rose-500 transition hover:bg-rose-50 hover:border-rose-400 bg-white">
            <i data-lucide="log-out" class="h-4 w-4"></i>
            Keluar dari Akun
        </a>
    </div>

    <div class="flex flex-col gap-6">

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Saldo</p>
                <p class="mt-3 text-2xl font-black text-[#011C27]">Rp <?= number_format((int)($user['saldo'] ?? 0), 0, ',', '.') ?></p>
                <p class="mt-1 text-xs text-slate-400">Saldo tersedia</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Total Produk</p>
                <p class="mt-3 text-2xl font-black text-[#011C27]"><?= e($totalProduk) ?></p>
                <p class="mt-1 text-xs text-slate-400">Produk terdaftar</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-soft">
                <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Omzet</p>
                <p class="mt-3 text-2xl font-black text-[#011C27]"><?= e($totalOmzet) ?></p>
                <p class="mt-1 text-xs text-slate-400">Total omzet toko</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Informasi Akun</p>
                <h3 class="mt-1 text-lg font-black text-[#011C27]">Data Diri & Toko</h3>
            </div>
            <div class="divide-y divide-slate-50">
                <?php
                $rows = [
                    ['pencil-line', 'Nama Lengkap',   $user['nama'] ?? '-'],
                    ['at-sign',     'Username',        '@' . ($user['username'] ?? '-')],
                    ['store',       'Nama Toko',       $user['nama_toko'] ?: '-'],
                    ['shield',      'Tipe Akun',       ucfirst($user['role'] ?? '-')],
                    ['wallet',      'Saldo',           'Rp ' . number_format((int)($user['saldo'] ?? 0), 0, ',', '.')],
                ];
                foreach ($rows as [$icon, $label, $value]):
                ?>
                <div class="flex items-center gap-4 px-6 py-4">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-[#FECEE9]/60 text-[#011C27]">
                        <i data-lucide="<?= $icon ?>" class="h-4 w-4"></i>
                    </div>
                    <div class="flex flex-1 items-center justify-between gap-4 min-w-0">
                        <span class="text-sm font-semibold text-[#545677]"><?= e($label) ?></span>
                        <span class="text-sm font-black text-[#011C27] text-right"><?= e($value) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<?php zapiere_page_end(); ?>
