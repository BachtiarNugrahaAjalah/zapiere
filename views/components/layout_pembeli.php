<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/session_guard.php';

function zapiere_pembeli_page_start(string $title, string $active): void
{
    $user = require_role('pembeli');
    
    
    $userId = (int) $user['id_user'];
    $dbUser = db_one("SELECT saldo FROM users WHERE id_user = {$userId}");
    if ($dbUser) {
        $user['saldo'] = $dbUser['saldo'];
        $_SESSION['zapiere_user']['saldo'] = $dbUser['saldo'];
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> - Zapiere</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            dark: '#0a0a0a',
                            primary: '#011C27',
                            muted: '#545677',
                            accent: '#EB9FEF',
                            soft: '#FECEE9',
                            zDark: '#011C27',
                            zNavy: '#03254E',
                            zSlate: '#545677',
                            zPink: '#EB9FEF',
                            zBlush: '#FECEE9'
                        },
                        fontFamily: {
                            sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
        <script src="https://unpkg.com/@phosphor-icons/web"></script>
        <style>
            html { scroll-behavior: smooth; }
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
    </head>
    <body class="bg-gray-100 text-[#0a0a0a] font-sans antialiased min-h-screen flex flex-col relative overflow-x-hidden">

    <nav class="bg-white shadow-sm sticky top-0 z-40 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center gap-4">

                <a href="<?= e(url_for('views/pembeli/dashboard.php')) ?>" class="flex items-center gap-2 flex-shrink-0">
                    <div class="bg-[#011C27] text-white p-1.5 rounded-lg">
                        <i class="ph-fill ph-storefront text-xl"></i>
                    </div>
                    <span class="font-extrabold text-xl text-[#011C27] tracking-tight hidden sm:block">Zapiere</span>
                </a>

                <?php if ($active === 'dashboard'): ?>
                <div class="flex-grow max-w-2xl hidden md:flex">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-magnifying-glass text-[#545677] text-lg"></i>
                        </div>
                        <input type="text" id="search-input"
                               oninput="if(typeof handleSearch==='function') handleSearch()"
                               class="block w-full pl-11 pr-4 py-2.5 bg-gray-100 border-transparent rounded-full text-sm placeholder-gray-400 focus:border-[#011C27] focus:bg-white focus:ring-1 focus:ring-[#011C27] transition-colors"
                               placeholder="Mau cari barang elektronik apa hari ini?">
                    </div>
                </div>
                <?php else: ?>
                <div class="flex-grow hidden md:flex items-center">
                    <span class="text-sm font-bold text-[#545677]"><?= e($title) ?></span>
                </div>
                <?php endif; ?>

                <div class="flex items-center gap-5 flex-shrink-0">

                    <div class="hidden sm:flex flex-col items-end">
                        <span class="text-[10px] text-[#545677] font-semibold uppercase tracking-wider leading-none mb-1">Saldo</span>
                        <span class="text-sm font-extrabold text-[#011C27] leading-none">
                            Rp <?= number_format((int)($user['saldo'] ?? 0), 0, ',', '.') ?>
                        </span>
                    </div>

                    <div class="relative group">
                        <button class="flex items-center gap-2.5 focus:outline-none py-1">
                            <div class="h-9 w-9 rounded-full bg-[#FECEE9] text-[#011C27] flex items-center justify-center font-bold border border-[#EB9FEF]/60 group-hover:bg-[#EB9FEF] transition-colors text-sm select-none">
                                <?= e(strtoupper(substr($user['nama'] ?? 'Z', 0, 1))) ?>
                            </div>
                            <div class="hidden lg:flex flex-col items-start">
                                <span class="font-bold text-sm text-[#0a0a0a] leading-none mb-1"><?= e($user['nama'] ?? 'User') ?></span>
                                <span class="text-[10px] text-[#545677] leading-none capitalize"><?= e($user['role'] ?? 'pembeli') ?></span>
                            </div>
                            <i class="ph ph-caret-down text-[#545677] text-sm hidden lg:block group-hover:text-[#011C27] transition-colors"></i>
                        </button>

                        <div class="absolute right-0 top-full mt-0 pt-1 w-56 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/60">
                                    <p class="text-sm font-bold text-[#0a0a0a] truncate"><?= e($user['nama'] ?? 'User') ?></p>
                                    <p class="text-[11px] text-[#545677] mt-0.5 font-semibold">
                                        Saldo: Rp <?= number_format((int)($user['saldo'] ?? 0), 0, ',', '.') ?>
                                    </p>
                                </div>
                                <div class="py-1.5">
                                    <a href="<?= e(url_for('views/pembeli/dashboard.php')) ?>"
                                       class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors <?= $active === 'dashboard' ? 'bg-[#FECEE9]/50 text-[#011C27]' : 'text-[#0a0a0a] hover:bg-[#FECEE9]/30 hover:text-[#011C27]' ?>">
                                        <i class="ph ph-shopping-bag text-lg text-[#011C27]"></i>
                                        Belanja Produk
                                    </a>
                                    <a href="<?= e(url_for('views/pembeli/riwayat_pesanan.php')) ?>"
                                       class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors <?= $active === 'riwayat' ? 'bg-[#FECEE9]/50 text-[#011C27]' : 'text-[#0a0a0a] hover:bg-[#FECEE9]/30 hover:text-[#011C27]' ?>">
                                        <i class="ph ph-package-check text-lg text-[#011C27]"></i>
                                        Riwayat Pesanan
                                    </a>
                                    <a href="<?= e(url_for('views/pembeli/profil.php')) ?>"
                                       class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-colors <?= $active === 'profil' ? 'bg-[#FECEE9]/50 text-[#011C27]' : 'text-[#0a0a0a] hover:bg-[#FECEE9]/30 hover:text-[#011C27]' ?>">
                                        <i class="ph ph-user-round text-lg text-[#011C27]"></i>
                                        Profil Saya
                                    </a>
                                </div>
                                <div class="border-t border-gray-100 py-1.5">
                                    <a href="<?= e(url_for('logout.php')) ?>"
                                       class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-red-500 hover:bg-red-50 transition-colors">
                                        <i class="ph ph-sign-out text-lg"></i>
                                        Keluar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    <?php
}

function zapiere_pembeli_page_end(): void
{
    ?>
    </main>
    </body>
    </html>
    <?php
}
