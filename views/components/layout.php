<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/sidebar.php';

function zapiere_page_start($title, $role, $active, $subtitle = '')
{
    $user = current_user($role);
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
                            zDark: '#011C27',
                            zNavy: '#03254E',
                            zSlate: '#545677',
                            zPink: '#EB9FEF',
                            zBlush: '#FECEE9'
                        },
                        fontFamily: {
                            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Arial', 'sans-serif']
                        },
                        boxShadow: {
                            soft: '0 18px 50px rgba(1, 28, 39, 0.08)'
                        }
                    }
                }
            }
        </script>
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-[#011C27] antialiased">
        <div class="min-h-screen lg:flex">
            <?php zapiere_sidebar($role, $active); ?>

            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                    <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button id="mobile-menu-button" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-[#011C27] lg:hidden">
                                <i data-lucide="menu" class="h-5 w-5"></i>
                            </button>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#545677]"><?= e($role) ?></p>
                                <h1 class="truncate text-xl font-black sm:text-2xl"><?= e($title) ?></h1>
                                <?php if ($subtitle): ?>
                                    <p class="mt-1 hidden text-sm text-[#545677] sm:block"><?= e($subtitle) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-bold"><?= e($user['nama'] ?? 'User Zapiere') ?></p>
                                <p class="text-xs capitalize text-[#545677]"><?= e($user['role'] ?? $role) ?></p>
                            </div>
                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#FECEE9] text-sm font-black text-[#011C27]">
                                <?= e(strtoupper(substr($user['nama'] ?? 'Z', 0, 1))) ?>
                            </div>
                            <a href="<?= e(url_for('logout.php')) ?>" class="hidden h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-[#545677] transition hover:border-[#EB9FEF] hover:text-[#011C27] sm:inline-flex" title="Keluar">
                                <i data-lucide="log-out" class="h-5 w-5"></i>
                            </a>
                        </div>
                    </div>
                </header>

                <main class="space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <?php
}

function zapiere_page_end()
{
    ?>
                </main>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.lucide) {
                    window.lucide.createIcons();
                }

                const button = document.getElementById('mobile-menu-button');
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');

                function closeSidebar() {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }

                if (button && sidebar && overlay) {
                    button.addEventListener('click', function () {
                        sidebar.classList.toggle('-translate-x-full');
                        overlay.classList.toggle('hidden');
                    });

                    overlay.addEventListener('click', closeSidebar);
                }
            });
        </script>
    </body>
    </html>
    <?php
}

function metric_card($label, $value, $icon, $caption = '')
{
    ?>
    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-[#545677]"><?= e($label) ?></p>
                <p class="mt-3 text-2xl font-black tracking-tight"><?= e($value) ?></p>
                <?php if ($caption): ?>
                    <p class="mt-2 text-xs font-medium text-[#545677]"><?= e($caption) ?></p>
                <?php endif; ?>
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#FECEE9] text-[#011C27]">
                <i data-lucide="<?= e($icon) ?>" class="h-5 w-5"></i>
            </div>
        </div>
    </div>
    <?php
}

function status_badge($status)
{
    $status = strtolower((string) $status);
    $classes = [
        'online' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'syncing' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'offline' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'berhasil' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'berjalan' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'gagal' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'waiting' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];

    $class = $classes[$status] ?? 'bg-slate-100 text-[#545677] ring-slate-200';
    return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold capitalize ring-1 ring-inset ' . $class . '">' . e($status ?: '-') . '</span>';
}
