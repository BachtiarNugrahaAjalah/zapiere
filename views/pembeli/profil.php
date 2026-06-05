<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../components/layout_pembeli.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/OrderModel.php';

$user = require_role('pembeli');
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

$totalBelanja = get_buyer_total_spent($userId);
$orders       = orders_with_total($userId);
$totalPesanan = count($orders);

zapiere_pembeli_page_start('Profil Saya', 'profil');
?>

<div id="modal-topup" class="fixed inset-0 z-50 hidden bg-[#0a0a0a]/60 backdrop-blur-sm flex justify-center items-center p-4 opacity-0 transition-opacity duration-300">
    <div id="modal-topup-content" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden p-6 relative transform scale-95 transition-transform duration-300">
        
        <button onclick="tutupModalTopup()" class="absolute top-4 right-4 text-gray-400 hover:text-[#011C27] transition-colors p-1 rounded-full hover:bg-gray-100">
            <i class="ph-bold ph-x text-lg"></i>
        </button>
        
        <div class="flex items-center gap-3 mb-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FECEE9] text-[#011C27]">
                <i class="ph-fill ph-wallet text-xl"></i>
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
                <i class="ph-bold ph-check-circle text-lg"></i>
                <span>Konfirmasi Top Up</span>
            </button>
        </form>
    </div>
</div>

<?php if ($toast): ?>
<div id="toast-msg" class="fixed top-24 right-0 z-[60] flex transform translate-x-full transition-transform duration-500 ease-out pr-4 sm:pr-8">
    <div class="bg-white border-l-4 <?= $toast['ok'] ? 'border-green-500' : 'border-red-500' ?> shadow-2xl rounded-r-xl p-4 flex items-start gap-3 w-80">
        <i class="<?= $toast['ok'] ? 'ph-fill ph-check-circle text-green-500' : 'ph-fill ph-warning-circle text-red-500' ?> text-2xl mt-0.5 shadow-sm"></i>
        <div class="flex-1">
            <h4 class="font-bold text-[#0a0a0a] text-sm"><?= htmlspecialchars($toast['title']) ?></h4>
            <p class="text-xs font-medium text-[#545677] mt-1 leading-relaxed"><?= htmlspecialchars($toast['msg']) ?></p>
        </div>
        <button onclick="document.getElementById('toast-msg').remove()" class="text-gray-400 hover:text-gray-600">
            <i class="ph-bold ph-x text-sm"></i>
        </button>
    </div>
</div>
<?php endif; ?>

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

    <div class="flex flex-col gap-6 min-w-0">

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#545677]">Saldo</p>
                    <p class="mt-3 text-2xl font-extrabold text-[#011C27]">Rp <?= number_format((int)($user['saldo'] ?? 0), 0, ',', '.') ?></p>
                    <p class="mt-1 text-xs text-gray-400">Saldo tersedia</p>
                </div>
                <button onclick="bukaModalTopup()" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-[#FECEE9] py-2 text-sm font-bold text-[#011C27] transition-colors hover:bg-[#EB9FEF]">
                    <i class="ph-bold ph-plus-circle text-lg"></i>
                    Isi Saldo
                </button>
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
                        <span class="text-sm font-semibold text-[#545677] flex-shrink-0"><?= e($label) ?></span>
                        <span class="text-sm font-extrabold text-[#011C27] text-right truncate"><?= e($value) ?></span>
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
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-sm font-bold text-[#011C27] max-w-[200px] sm:max-w-xs md:max-w-sm lg:max-w-md xl:max-w-lg" title="<?= e($order['produk'] ?? 'Pesanan #' . $order['id_pesanan']) ?>"><?= e($order['produk'] ?? 'Pesanan #' . $order['id_pesanan']) ?></p>
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
        btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin text-lg"></i><span>Memproses...</span>';
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

<?php zapiere_pembeli_page_end(); ?>
