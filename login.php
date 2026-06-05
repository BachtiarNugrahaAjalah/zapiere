<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/UserModel.php';

if (!empty($_SESSION['zapiere_user'])) {
    header('Location: ' . dashboard_url_for_role($_SESSION['zapiere_user']['role']));
    exit;
}

$error = '';
$demoUsers = users_data();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = db_escape($_POST['username']);
    $password = $_POST['password'];

    $user = db_one("SELECT * FROM users WHERE username = '{$username}'");

    if ($user && $user['password'] === $password) {
        set_current_user($user);
        header('Location: ' . dashboard_url_for_role($user['role']));
        exit;
    }

    $error = 'Username atau password belum sesuai.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Zapiere</title>
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
                    boxShadow: {
                        soft: '0 18px 50px rgba(1, 28, 39, 0.08)'
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="min-h-screen bg-slate-50 text-[#011C27] antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="flex items-center px-6 py-10 sm:px-10 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-10 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#011C27] text-[#EB9FEF]">
                        <i data-lucide="zap" class="h-7 w-7"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black">Zapiere</p>
                        <p class="text-sm font-medium text-[#545677]">Marketplace elektronik terdistribusi</p>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft sm:p-8">
                    <div class="mb-6">
                        <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Login</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight">Masuk ke dashboard</h1>
                    </div>

                    <?php if ($error): ?>
                        <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-4">
                        <label class="block">
                            <span class="text-sm font-bold text-[#011C27]">Username</span>
                            <input name="username" type="text" required class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none transition focus:border-[#EB9FEF] focus:ring-4 focus:ring-[#FECEE9]" placeholder="admin_zapiere">
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-[#011C27]">Password</span>
                            <input name="password" type="password" required class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none transition focus:border-[#EB9FEF] focus:ring-4 focus:ring-[#FECEE9]" placeholder="admin123">
                        </label>

                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
                            <i data-lucide="log-in" class="h-5 w-5"></i>
                            Masuk
                        </button>
                    </form>
                    
                    <p class="mt-6 text-center text-sm font-semibold text-[#545677]">
                        Belum punya akun? 
                        <a href="<?= e(url_for('register.php')) ?>" class="font-black text-[#011C27] underline transition hover:text-[#EB9FEF]">Daftar di sini</a>
                    </p>
                </div>

            </div>
        </section>

        <section class="hidden bg-[#011C27] p-10 lg:flex lg:items-center">
            <div class="mx-auto max-w-lg">
                <div class="rounded-lg bg-white p-6 shadow-2xl">
                    <img src="<?= e(asset_url('assets/images/default.png')) ?>" alt="Laptop elektronik Zapiere" class="aspect-[4/3] w-full rounded-lg object-contain bg-slate-950">
                </div>
                <div class="mt-8 grid grid-cols-3 gap-3">
                    <div class="rounded-lg bg-white/10 p-4 text-white">
                        <p class="text-2xl font-black">3</p>
                        <p class="mt-1 text-xs font-semibold text-white/70">Node demo</p>
                    </div>
                    <div class="rounded-lg bg-white/10 p-4 text-white">
                        <p class="text-2xl font-black">4</p>
                        <p class="mt-1 text-xs font-semibold text-white/70">Fragmen data</p>
                    </div>
                    <div class="rounded-lg bg-white/10 p-4 text-white">
                        <p class="text-2xl font-black">1</p>
                        <p class="mt-1 text-xs font-semibold text-white/70">Simulasi lock</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
