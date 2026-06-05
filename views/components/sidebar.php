<?php
function zapiere_nav_items($role)
{
    $items = [
        'admin' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'href' => 'views/admin/dashboard.php'],
            ['key' => 'deadlock', 'label' => 'Simulasi Deadlock', 'icon' => 'git-merge', 'href' => 'views/admin/simulasi_deadlock.php'],
            ['key' => 'backup', 'label' => 'Backup Sistem', 'icon' => 'database-backup', 'href' => 'views/admin/backup_sistem.php'],
            ['key' => 'log', 'label' => 'Log Aktivitas', 'icon' => 'activity', 'href' => 'views/admin/log.php'], 
        ],
        'penjual' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'href' => 'views/penjual/dashboard.php'],
            ['key' => 'kelola', 'label' => 'Kelola Produk', 'icon' => 'package', 'href' => 'views/penjual/kelola_produk.php'],
            ['key' => 'riwayat', 'label' => 'Riwayat Penjualan', 'icon' => 'receipt-text', 'href' => 'views/penjual/riwayat_penjualan.php'],
            ['key' => 'profil', 'label' => 'Profil Toko', 'icon' => 'store', 'href' => 'views/penjual/profil.php'],
        ],
        'pembeli' => [
            ['key' => 'dashboard', 'label' => 'Belanja Produk', 'icon' => 'shopping-bag', 'href' => 'views/pembeli/dashboard.php'],
            ['key' => 'riwayat', 'label' => 'Riwayat Pesanan', 'icon' => 'package-check', 'href' => 'views/pembeli/riwayat_pesanan.php'],
            ['key' => 'profil', 'label' => 'Profil Saya', 'icon' => 'user-round', 'href' => 'views/pembeli/profil.php'],
        ],
    ];

    return $items[$role] ?? $items['pembeli'];
}

function zapiere_sidebar($role, $active)
{
    $items = zapiere_nav_items($role);
    ?>
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-[#011C27] text-white transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
        
        <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#EB9FEF] text-[#011C27]">
                <i data-lucide="zap" class="h-6 w-6"></i>
            </div>
            <div>
                <p class="text-xl font-black tracking-wide">Zapiere</p>
                <p class="text-xs font-medium text-[#FECEE9]">Elektronik Terdistribusi</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto space-y-2 px-4 py-6 no-scrollbar">
            <?php foreach ($items as $item): ?>
                <?php $isActive = $item['key'] === $active; ?>
                <a href="<?= e(url_for($item['href'])) ?>" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition <?= $isActive ? 'bg-[#EB9FEF] text-[#011C27] shadow-lg shadow-[#EB9FEF]/20' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                    <i data-lucide="<?= e($item['icon']) ?>" class="h-5 w-5"></i>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

    </aside>
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-[#011C27]/50 lg:hidden"></div>
    <?php
}