<?php
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/InfraModel.php';

$user = require_role('penjual');
$sellerId = (int) $user['id_user'];

$products = products_with_meta($sellerId);
$sales = sales_rows($sellerId);
$fragments = fragments_data();

// Fetch horizontal and vertical fragmentation tables data
$ringkasanProduk = get_ringkasan_produk();
$komputerFrag = get_produk_komputer();
$handphoneFrag = get_produk_handphone();
$aksesorisFrag = get_produk_aksesoris();
$kameraFrag = get_produk_kamera();
$prtFrag = get_produk_prt();

$sellerStats = get_seller_stats($sellerId);
$revenueRp = $sellerStats['total_omzet_rp'];
$stock = array_sum(array_map(fn($product) => (int) $product['stok'], $products));
$sold = array_sum(array_map(fn($product) => (int) $product['total_terjual'], $products));
$lowStock = array_values(array_filter($products, fn($product) => (int) $product['stok'] <= 10));

$sortedByStock = $products;
usort($sortedByStock, fn($a, $b) => (int) $a['stok'] <=> (int) $b['stok']);
$lowStockTop4 = array_slice($sortedByStock, 0, 4);

zapiere_page_start('Dashboard Penjual', 'penjual', 'dashboard', 'Pantau produk, stok, dan pesanan toko elektronikmu.');
?>

<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <?php metric_card('Produk Toko', count($products), 'boxes', $stock . ' unit stok tersedia'); ?>
    <?php metric_card('Omzet', $revenueRp, 'banknote', count($sales) . ' baris penjualan'); ?>
    <?php metric_card('Barang Terjual', $sold, 'shopping-basket', 'akumulasi dari detail pesanan'); ?>
    <?php metric_card('Stok Rendah', count($lowStock), 'triangle-alert', 'butuh restock prioritas'); ?>
</section>

<section class="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Inventori</p>
            <h2 class="mt-2 text-2xl font-black">Stok paling rendah</h2>
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border border-slate-200">
            <div class="grid grid-cols-[2fr_0.9fr_0.5fr] bg-slate-50 px-4 py-3 text-xs font-black uppercase tracking-[0.12em] text-[#545677]">
                <span>Nama Produk</span>
                <span>Kategori</span>
                <span>Stok</span>
            </div>
            <div class="divide-y divide-slate-200">
                <?php foreach ($lowStockTop4 as $product): ?>
                    <div class="grid grid-cols-[2fr_0.9fr_0.5fr] items-center px-4 py-3 gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="<?= e(product_image_url($product['foto_barang'] ?? '')) ?>" alt="<?= e($product['nama']) ?>" class="h-10 w-10 flex-shrink-0 rounded-lg bg-slate-100 object-contain">
                            <p class="truncate text-sm font-black"><?= e($product['nama']) ?></p>
                        </div>
                        <p class="text-sm font-semibold text-[#545677] truncate"><?= e($product['kategori']) ?></p>
                        <div>
                            <?php if ((int) $product['stok'] <= 10): ?>
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700"><?= e($product['stok']) ?></span>
                            <?php else: ?>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700"><?= e($product['stok']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <aside class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-[#011C27] p-6 text-white shadow-soft">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#FECEE9]">Distribusi Produk</p>
            <h2 class="mt-2 text-2xl font-black">Fragmen inventori toko</h2>
            <p class="mt-3 text-sm leading-6 text-white/75">Produk toko dapat ditempatkan ke fragmen berbeda berdasarkan kategori. Ini memudahkan demo horizontal fragmentation.</p>
            <div class="mt-5 space-y-3">
                <?php foreach (array_slice($fragments, 0, 3) as $fragment): ?>
                    <div class="rounded-lg bg-white/10 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-black"><?= e($fragment['nama_fragmen']) ?></p>
                            <span class="rounded-full bg-[#FECEE9] px-2.5 py-1 text-xs font-black capitalize text-[#011C27]"><?= e($fragment['tipe_fragmentasi']) ?></span>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-white/70"><?= e($fragment['nama_node']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Penjualan Baru</p>
                    <h2 class="mt-2 text-xl font-black">Transaksi masuk</h2>
                </div>
                <a href="<?= e(url_for('views/penjual/riwayat_penjualan.php')) ?>" class="text-sm font-black text-[#03254E] hover:text-[#EB9FEF]">Lihat semua</a>
            </div>
            <div class="space-y-3">
                <?php foreach (array_slice($sales, 0, 5) as $sale): ?>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black"><?= e($sale['produk']) ?></p>
                                <p class="mt-1 text-sm font-semibold text-[#545677]"><?= e($sale['pembeli']) ?> - <?= e($sale['jumlah']) ?> unit</p>
                            </div>
                            <p class="text-sm font-black"><?= e($sale['subtotal_rp'] ?? 'Rp ' . $sale['subtotal']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</section>

<section class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft mt-6">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Inspeksi Database</p>
            <h2 class="mt-2 text-2xl font-black">Fragmentasi Horizontal & Vertikal</h2>
            <p class="mt-1 text-sm text-[#545677]">Melihat data langsung dari tabel-tabel partisi database terdistribusi.</p>
        </div>
        <div class="flex rounded-lg bg-slate-100 p-1">
            <button onclick="switchMainTab('vertical')" id="tab-btn-vertical" class="rounded-md px-4 py-2 text-sm font-bold transition bg-white text-[#011C27] shadow-sm">
                Fragmentasi Vertikal
            </button>
            <button onclick="switchMainTab('horizontal')" id="tab-btn-horizontal" class="rounded-md px-4 py-2 text-sm font-bold transition text-[#545677] hover:text-[#011C27]">
                Fragmentasi Horizontal
            </button>
        </div>
    </div>

    <!-- Tab Content: Vertical Fragmentation -->
    <div id="content-vertical" class="space-y-4">
        <div class="rounded-lg bg-slate-50 p-4 border border-slate-200">
            <div class="flex items-center gap-2 text-xs font-bold text-[#545677] uppercase tracking-[0.12em]">
                <i data-lucide="database" class="h-4 w-4 text-[#EB9FEF]"></i>
                Tabel Source: <span class="text-[#011C27] font-black">ringkasan_produk</span>
            </div>
            <p class="mt-1.5 text-xs text-[#545677]">Skema tabel: <code>(nama_produk VARCHAR, kategori VARCHAR, harga INT, stok INT)</code>. Digunakan untuk query performa tinggi dengan kolom terbatas.</p>
        </div>
        
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full border-collapse text-left text-sm text-slate-500">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-[#545677] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Nama Produk</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3">Harga</th>
                        <th class="px-6 py-3">Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php if (empty($ringkasanProduk)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-slate-400 font-semibold">Tidak ada data di ringkasan_produk</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ringkasanProduk as $row): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3.5 font-bold text-[#011C27]"><?= e($row['nama_produk']) ?></td>
                                <td class="px-6 py-3.5 font-semibold text-[#545677]"><?= e($row['kategori']) ?></td>
                                <td class="px-6 py-3.5 font-black text-[#011C27]">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td class="px-6 py-3.5 font-black text-[#011C27]"><?= e($row['stok']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab Content: Horizontal Fragmentation -->
    <div id="content-horizontal" class="hidden space-y-4">
        <!-- Horizontal Tabs Sub-navigation -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
            <button onclick="switchHorizTab('komputer')" id="horiz-btn-komputer" class="rounded-full px-4 py-1.5 text-xs font-bold transition bg-[#011C27] text-white">
                Komputer & Laptop (produk_komputer)
            </button>
            <button onclick="switchHorizTab('handphone')" id="horiz-btn-handphone" class="rounded-full px-4 py-1.5 text-xs font-bold transition bg-slate-100 text-[#545677] hover:bg-slate-200">
                Handphone & Tablet (produk_handphone)
            </button>
            <button onclick="switchHorizTab('aksesoris')" id="horiz-btn-aksesoris" class="rounded-full px-4 py-1.5 text-xs font-bold transition bg-slate-100 text-[#545677] hover:bg-slate-200">
                Aksesoris (produk_aksesoris)
            </button>
            <button onclick="switchHorizTab('kamera')" id="horiz-btn-kamera" class="rounded-full px-4 py-1.5 text-xs font-bold transition bg-slate-100 text-[#545677] hover:bg-slate-200">
                Kamera (produk_kamera)
            </button>
            <button onclick="switchHorizTab('prt')" id="horiz-btn-prt" class="rounded-full px-4 py-1.5 text-xs font-bold transition bg-slate-100 text-[#545677] hover:bg-slate-200">
                Peralatan Rumah Tangga (produk_prt)
            </button>
        </div>

        <?php
        $horizTables = [
            'komputer' => ['table' => 'produk_komputer', 'data' => $komputerFrag, 'desc' => 'Menampung produk dengan kategori "Komputer & Laptop".'],
            'handphone' => ['table' => 'produk_handphone', 'data' => $handphoneFrag, 'desc' => 'Menampung produk dengan kategori "Handphone & Tablet".'],
            'aksesoris' => ['table' => 'produk_aksesoris', 'data' => $aksesorisFrag, 'desc' => 'Menampung produk dengan kategori "Aksesoris & Periferal".'],
            'kamera' => ['table' => 'produk_kamera', 'data' => $kameraFrag, 'desc' => 'Menampung produk dengan kategori "Kamera & Fotografi".'],
            'prt' => ['table' => 'produk_prt', 'data' => $prtFrag, 'desc' => 'Menampung produk dengan kategori "Peralatan Rumah Tangga".']
        ];
        ?>

        <?php foreach ($horizTables as $key => $meta): ?>
            <div id="horiz-content-<?= $key ?>" class="space-y-4 <?= $key === 'komputer' ? '' : 'hidden' ?> horiz-pane">
                <div class="rounded-lg bg-slate-50 p-4 border border-slate-200">
                    <div class="flex items-center gap-2 text-xs font-bold text-[#545677] uppercase tracking-[0.12em]">
                        <i data-lucide="split" class="h-4 w-4 text-[#EB9FEF]"></i>
                        Tabel Source: <span class="text-[#011C27] font-black"><?= $meta['table'] ?></span>
                    </div>
                    <p class="mt-1.5 text-xs text-[#545677]"><?= $meta['desc'] ?> Skema tabel: <code>(nama_toko VARCHAR, kategori VARCHAR, id_produk INT, nama VARCHAR, harga INT, stok INT)</code>.</p>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full border-collapse text-left text-sm text-slate-500">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-[#545677] border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3">Nama Toko</th>
                                <th class="px-6 py-3">Kategori</th>
                                <th class="px-6 py-3">ID Produk</th>
                                <th class="px-6 py-3">Nama Produk</th>
                                <th class="px-6 py-3">Harga</th>
                                <th class="px-6 py-3">Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <?php if (empty($meta['data'])): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-slate-400 font-semibold">Tidak ada data di <?= $meta['table'] ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($meta['data'] as $row): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-3.5 font-semibold text-[#545677]"><?= e($row['nama_toko'] ?: '-') ?></td>
                                        <td class="px-6 py-3.5 font-semibold text-[#545677]"><?= e($row['kategori']) ?></td>
                                        <td class="px-6 py-3.5 font-mono font-bold text-[#011C27]"><?= e($row['id_produk']) ?></td>
                                        <td class="px-6 py-3.5 font-bold text-[#011C27]"><?= e($row['nama']) ?></td>
                                        <td class="px-6 py-3.5 font-black text-[#011C27]">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                        <td class="px-6 py-3.5 font-black text-[#011C27]"><?= e($row['stok']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
function switchMainTab(tab) {
    const btnVert = document.getElementById('tab-btn-vertical');
    const btnHoriz = document.getElementById('tab-btn-horizontal');
    const contentVert = document.getElementById('content-vertical');
    const contentHoriz = document.getElementById('content-horizontal');
    
    if (tab === 'vertical') {
        btnVert.className = "rounded-md px-4 py-2 text-sm font-bold transition bg-white text-[#011C27] shadow-sm";
        btnHoriz.className = "rounded-md px-4 py-2 text-sm font-bold transition text-[#545677] hover:text-[#011C27]";
        contentVert.classList.remove('hidden');
        contentHoriz.classList.add('hidden');
    } else {
        btnHoriz.className = "rounded-md px-4 py-2 text-sm font-bold transition bg-white text-[#011C27] shadow-sm";
        btnVert.className = "rounded-md px-4 py-2 text-sm font-bold transition text-[#545677] hover:text-[#011C27]";
        contentVert.classList.add('hidden');
        contentHoriz.classList.remove('hidden');
    }
}

function switchHorizTab(tab) {
    // Hide all horiz contents
    document.querySelectorAll('.horiz-pane').forEach(el => el.classList.add('hidden'));
    
    // Deactivate all buttons
    const buttons = {
        'komputer': document.getElementById('horiz-btn-komputer'),
        'handphone': document.getElementById('horiz-btn-handphone'),
        'aksesoris': document.getElementById('horiz-btn-aksesoris'),
        'kamera': document.getElementById('horiz-btn-kamera'),
        'prt': document.getElementById('horiz-btn-prt')
    };
    
    for (const key in buttons) {
        if (buttons[key]) {
            if (key === tab) {
                buttons[key].className = "rounded-full px-4 py-1.5 text-xs font-bold transition bg-[#011C27] text-white";
            } else {
                buttons[key].className = "rounded-full px-4 py-1.5 text-xs font-bold transition bg-slate-100 text-[#545677] hover:bg-slate-200";
            }
        }
    }
    
    // Show current horiz pane
    const target = document.getElementById('horiz-content-' + tab);
    if (target) {
        target.classList.remove('hidden');
    }
}
</script>

<?php zapiere_page_end(); ?>
