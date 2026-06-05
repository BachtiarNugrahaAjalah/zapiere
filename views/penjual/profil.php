<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/UserModel.php';

$user = require_role('penjual');
$userId = (int) $user['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'topup') {
    $nominal = (int) str_replace('.', '', $_POST['nominal'] ?? '0'); 
    if ($nominal >= 10000) {
        $result = proses_topup($userId, $nominal);
        if ($result['success']) {
            $_SESSION['zapiere_user']['saldo'] += $nominal;
        }
        $_SESSION['toast'] = [
            'title' => $result['success'] ? 'Berhasil' : 'Gagal', 
            'msg' => $result['message'], 
            'ok' => $result['success']
        ];
    } else {
        $_SESSION['toast'] = ['title' => 'Gagal', 'msg' => 'Minimal top-up adalah Rp 10.000', 'ok' => false];
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$toast = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

$fullUser = db_one("SELECT * FROM users WHERE id_user = {$userId}");
$user = $fullUser ?: $user;

$sellerStats = get_seller_stats($userId);
$totalProduk = $sellerStats['total_produk'];
$totalOmzet  = $sellerStats['total_omzet_rp'];

zapiere_page_start('Profil Toko', 'penjual', 'profil', 'Informasi akun dan detail toko elektronikmu.');
?>

<div id="modal-topup" class="fixed inset-0 z-50 hidden bg-[#0a0a0a]/60 backdrop-blur-sm flex justify-center items-center p-4 opacity-0 transition-opacity duration-300">
    <div id="modal-topup-content" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden p-6 relative transform scale-95 transition-transform duration-300">
        
        <button onclick="tutupModalTopup()" class="absolute top-4 right-4 text-gray-400 hover:text-[#011C27] transition-colors p-1 rounded-full hover:bg-gray-100">
            <i data-lucide="x" class="h-5 w-5"></i>
        </button>
        
        <div class="flex items-center gap-3 mb-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FECEE9] text-[#011C27]">
                <i data-lucide="wallet" class="h-6 w-6"></i>
            </div>
            <h3 class="text-xl font-extrabold text-[#011C27]">Top Up Saldo</h3>
        </div>
        <p class="text-sm text-[#545677] mb-6">Masukkan nominal saldo yang ingin ditambahkan ke akun Zapiere kamu.</p>
        
        <form method="POST" onsubmit="btnLoading(this)">
            <input type="hidden" name="_action" value="topup">
            
            <div class="mb-6 relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-extrabold text-[#011C27] text-lg">Rp</span>
                <input 
                    type="number" 
                    name="nominal" 
                    required 
                    min="10000" 
                    step="1000" 
                    placeholder="50000" 
                    class="w-full rounded-xl border-2 border-gray-100 bg-gray-50 py-3.5 pl-12 pr-4 text-xl font-black text-[#011C27] focus:border-[#EB9FEF] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#FECEE9]/50 transition-all"
                >
            </div>
            
            <button type="submit" id="btn-submit-topup" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#011C27] py-3.5 text-sm font-bold text-white transition-all hover:bg-[#03254E] hover:shadow-lg hover:shadow-[#011C27]/20 active:scale-95">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                <span>Konfirmasi Top Up</span>
            </button>
        </form>
    </div>
</div>

<?php if ($toast): ?>
<div id="toast-msg" class="fixed top-24 right-0 z-[60] flex transform translate-x-full transition-transform duration-500 ease-out pr-4 sm:pr-8">
    <div class="bg-white border-l-4 <?= $toast['ok'] ? 'border-green-500' : 'border-red-500' ?> shadow-2xl rounded-r-xl p-4 flex items-start gap-3 w-80">
        <i data-lucide="<?= $toast['ok'] ? 'check-circle' : 'alert-circle' ?>" class="<?= $toast['ok'] ? 'text-green-500' : 'text-red-500' ?> h-6 w-6 mt-0.5 shadow-sm"></i>
        <div class="flex-1">
            <h4 class="font-bold text-[#0a0a0a] text-sm"><?= htmlspecialchars($toast['title']) ?></h4>
            <p class="text-xs font-medium text-[#545677] mt-1 leading-relaxed"><?= htmlspecialchars($toast['msg']) ?></p>
        </div>
        <button onclick="document.getElementById('toast-msg').remove()" class="text-gray-400 hover:text-gray-600">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    </div>
</div>
<?php endif; ?>

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
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-soft flex flex-col justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Saldo</p>
                    <p class="mt-3 text-2xl font-black text-[#011C27]">Rp <?= number_format((int)($user['saldo'] ?? 0), 0, ',', '.') ?></p>
                    <p class="mt-1 text-xs text-slate-400">Saldo tersedia</p>
                </div>
                <button onclick="bukaModalTopup()" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-[#FECEE9] py-2 text-sm font-bold text-[#011C27] transition-colors hover:bg-[#EB9FEF]">
                    <i data-lucide="plus-circle" class="h-4 w-4"></i>
                    Isi Saldo
                </button>
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

<script>
    function bukaModalTopup() {
        const modal = document.getElementById('modal-topup');
        const content = document.getElementById('modal-topup-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);
    }

    function tutupModalTopup() {
        const modal = document.getElementById('modal-topup');
        const content = document.getElementById('modal-topup-content');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    document.getElementById('modal-topup').addEventListener('click', function(e) {
        if (e.target === this) {
            tutupModalTopup();
        }
    });

    function btnLoading(form) {
        const btn = form.querySelector('#btn-submit-topup');
        btn.innerHTML = '<span>Memproses...</span>';
        btn.classList.add('opacity-80', 'cursor-not-allowed');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toast = document.getElementById('toast-msg');
        if(toast) {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-x-full');
                });
            });
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    });
</script>

<?php zapiere_page_end(); ?>
