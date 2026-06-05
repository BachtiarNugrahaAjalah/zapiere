<?php
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$user = require_role('penjual');
$sellerId = (int) $user['id_user'];

$deleted = false;
$deleteError = '';
$saveMessage = '';
$saveError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
    if ($_POST['_action'] === 'delete') {
        $idProduk = (int) ($_POST['id_produk'] ?? 0);
        if ($idProduk > 0) {
            $check = db_one("SELECT id_produk FROM produk WHERE id_produk = {$idProduk} AND id_user = {$sellerId}");
            if ($check) {
                // delete
                $deleted = db_exec("DELETE FROM produk WHERE id_produk = {$idProduk} AND id_user = {$sellerId}");
                if (!$deleted) $deleteError = 'Gagal menghapus produk.';
            } else {
                $deleteError = 'Produk tidak ditemukan atau bukan milik toko Anda.';
            }
        }
    } elseif ($_POST['_action'] === 'save') {
        $idProduk = (int) ($_POST['id_produk'] ?? 0);
        $namaBarang = trim($_POST['nama_barang'] ?? '');
        $harga = (int) ($_POST['harga'] ?? 0);
        $stok = (int) ($_POST['stok'] ?? 0);
        $idKategori = (int) ($_POST['id_kategori'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $fotoBarang = '';

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['foto']['name']));
            $targetPath = __DIR__ . '/../../assets/images/' . $fileName;
            
            if (move_uploaded_file($tmpName, $targetPath)) {
                $fotoBarang = $fileName;
            }
        }

        if ($idProduk > 0) {
            // (query edit)
            $saveMessage = 'Produk berhasil diupdate.';
        } else {
            // (query tambah)
            $saveMessage = 'Produk berhasil ditambahkan.';
        }
    }
}

$products = get_all_data_produk($sellerId);
$categories = db_all("SELECT * FROM kategori ORDER BY nama ASC");

zapiere_page_start('Kelola Produk', 'penjual', 'kelola', 'Daftar semua produk yang terdaftar di tokomu.');
?>

<?php if ($deleted): ?>
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 mb-4">
        Produk berhasil dihapus.
    </div>
<?php endif; ?>
<?php if ($deleteError): ?>
    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 mb-4">
        <?= e($deleteError) ?>
    </div>
<?php endif; ?>
<?php if ($saveMessage): ?>
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 mb-4">
        <?= e($saveMessage) ?>
    </div>
<?php endif; ?>
<?php if ($saveError): ?>
    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 mb-4">
        <?= e($saveError) ?>
    </div>
<?php endif; ?>

<div class="flex items-center justify-between gap-4">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Inventori</p>
        <h2 class="mt-1 text-2xl font-black"><?= count($products) ?> produk terdaftar</h2>
    </div>
    <button type="button" id="btn-tambah" class="inline-flex items-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
        <i data-lucide="plus" class="h-5 w-5"></i>
        Tambah Produk
    </button>
</div>

<?php if (empty($products)): ?>
    <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white py-20 text-center">
        <i data-lucide="package-open" class="mb-4 h-12 w-12 text-slate-300"></i>
        <p class="font-black text-slate-500">Belum ada produk</p>
        <p class="mt-1 text-sm text-slate-400">Klik "Tambah Produk" untuk mulai menambahkan produk.</p>
    </div>
<?php else: ?>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($products as $product): ?>
            <div class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-soft transition hover:shadow-lg hover:-translate-y-0.5 duration-200">

                <div class="relative h-44 w-full overflow-hidden bg-slate-950 flex-shrink-0">
                    <img
                        src="<?= e(product_image_url($product)) ?>"
                        alt="<?= e($product['nama']) ?>"
                        class="h-full w-full object-contain p-3 transition duration-300 group-hover:scale-105"
                    >
                    <?php if ((int) $product['stok'] <= 0): ?>
                        <span class="absolute top-3 left-3 rounded-full bg-rose-500 px-2.5 py-1 text-[10px] font-black text-white">Habis</span>
                    <?php elseif ((int) $product['stok'] <= 10): ?>
                        <span class="absolute top-3 left-3 rounded-full bg-amber-400 px-2.5 py-1 text-[10px] font-black text-[#011C27]">Stok Tipis</span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-1 flex-col p-4">
                    <span class="mb-1 text-[10px] font-bold uppercase tracking-widest text-[#545677]"><?= e($product['kategori'] ?? '-') ?></span>
                    <h3 class="line-clamp-2 text-sm font-black leading-snug text-[#011C27]"><?= e($product['nama']) ?></h3>

                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-base font-black text-[#011C27]"><?= e($product['harga_rp'] ?? 'Rp ' . number_format($product['harga'], 0, ',', '.')) ?></p>
                        <span class="text-xs font-semibold text-[#545677]">Stok: <?= e($product['stok']) ?></span>
                    </div>

                    <div class="mt-4 flex items-end justify-between gap-2">
                        <div class="flex gap-2">
                            <button
                                type="button"
                                onclick="openDetail(<?= (int) $product['id_produk'] ?>)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-[#011C27] transition hover:border-[#EB9FEF] hover:bg-[#FECEE9]/40"
                            >
                                <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                                Detail
                            </button>
                            <button
                                type="button"
                                onclick="openEdit(<?= (int) $product['id_produk'] ?>)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-[#011C27] transition hover:border-[#EB9FEF] hover:bg-[#FECEE9]/40"
                            >
                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                Edit
                            </button>
                        </div>

                        <form method="POST" onsubmit="return confirmDelete(event, '<?= e($product['nama']) ?>')">
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id_produk" value="<?= (int) $product['id_produk'] ?>">
                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 text-rose-400 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-400">
                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div id="modal-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-[#011C27]/60 backdrop-blur-sm p-4">
    <div class="relative flex w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 max-h-[90vh]">
        <div class="relative h-64 flex-shrink-0 bg-slate-950 flex items-center justify-center overflow-hidden">
            <img id="d-img" src="" alt="" class="h-full w-full object-contain p-4">
            <button onclick="closeModal('modal-detail')" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 backdrop-blur text-[#011C27] hover:bg-white transition">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>
        <div class="overflow-y-auto p-6 space-y-3">
            <span id="d-kategori" class="inline-block rounded-full bg-[#FECEE9] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-[#011C27]"></span>
            <h2 id="d-nama" class="text-xl font-black text-[#011C27] leading-snug"></h2>
            <p id="d-harga" class="text-2xl font-black text-[#011C27]"></p>
            <div class="flex items-center gap-4 border-t border-b border-slate-100 py-3 text-sm">
                <div class="flex items-center gap-2 text-[#545677]">
                    <i data-lucide="package" class="h-4 w-4"></i>
                    <span>Stok: <strong id="d-stok" class="text-[#011C27]"></strong></span>
                </div>
                <div class="flex items-center gap-2 text-[#545677]">
                    <i data-lucide="shopping-basket" class="h-4 w-4"></i>
                    <span>Terjual: <strong id="d-terjual" class="text-[#011C27]"></strong></span>
                </div>
            </div>
            <div>
                <p class="mb-1.5 text-xs font-bold uppercase tracking-widest text-[#545677]">Deskripsi</p>
                <p id="d-deskripsi" class="text-sm leading-relaxed text-[#545677]"></p>
            </div>
        </div>
    </div>
</div>

<div id="modal-form" class="fixed inset-0 z-50 hidden items-center justify-center bg-[#011C27]/60 backdrop-blur-sm p-4">
    <div class="relative flex w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 id="modal-form-title" class="text-lg font-black text-[#011C27]">Tambah Produk</h2>
            <button onclick="closeModal('modal-form')" type="button" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-[#011C27] transition">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>
        <div class="overflow-y-auto p-6">
            <form id="form-produk" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="_action" value="save">
                <input type="hidden" name="id_produk" id="f-id_produk" value="0">
                
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-[#545677]">Nama Barang</label>
                    <input type="text" name="nama_barang" id="f-nama_barang" required class="block w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-[#011C27] focus:border-[#EB9FEF] focus:outline-none focus:ring-1 focus:ring-[#EB9FEF]">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-[#545677]">Harga (Rp)</label>
                        <input type="number" name="harga" id="f-harga" required min="0" class="block w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-[#011C27] focus:border-[#EB9FEF] focus:outline-none focus:ring-1 focus:ring-[#EB9FEF]">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-[#545677]">Stok</label>
                        <input type="number" name="stok" id="f-stok" required min="0" class="block w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-[#011C27] focus:border-[#EB9FEF] focus:outline-none focus:ring-1 focus:ring-[#EB9FEF]">
                    </div>
                </div>
                
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-[#545677]">Kategori</label>
                    <select name="id_kategori" id="f-id_kategori" required class="block w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-[#011C27] focus:border-[#EB9FEF] focus:outline-none focus:ring-1 focus:ring-[#EB9FEF]">
                        <option value="">Pilih Kategori...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id_kategori'] ?>"><?= e($cat['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-[#545677]">Foto Barang</label>
                    <input type="file" name="foto" id="f-foto" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-[#FECEE9] file:px-4 file:py-2 file:text-xs file:font-bold file:text-[#011C27] hover:file:bg-[#EB9FEF] transition cursor-pointer">
                    <p class="mt-1.5 text-xs text-slate-400">Pilih foto baru jika ingin mengubah. Biarkan kosong jika tidak diubah.</p>
                </div>
                
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-[#545677]">Deskripsi</label>
                    <textarea name="deskripsi" id="f-deskripsi" rows="3" class="block w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-[#011C27] focus:border-[#EB9FEF] focus:outline-none focus:ring-1 focus:ring-[#EB9FEF]"></textarea>
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const productsData = <?= json_encode(array_values($products)) ?>;

    function openDetail(id) {
        const p = productsData.find(x => parseInt(x.id_produk) === id);
        if (!p) return;
        document.getElementById('d-img').src       = p.foto_barang && !['asus_rog.png','logitech_g304.png','iphone15.png','soundcore.png','monitor_lg.png','default.png'].includes(p.foto_barang)
                                                        ? '<?= e(url_for('assets/images/')) ?>' + p.foto_barang
                                                        : '<?= e(url_for('assets/images/image.png')) ?>';
        document.getElementById('d-img').alt       = p.nama;
        document.getElementById('d-kategori').innerText  = p.kategori ?? '-';
        document.getElementById('d-nama').innerText      = p.nama;
        document.getElementById('d-harga').innerText     = p.harga_rp ?? 'Rp ' + parseInt(p.harga).toLocaleString('id-ID');
        document.getElementById('d-stok').innerText      = p.stok;
        document.getElementById('d-terjual').innerText   = p.total_terjual ?? 0;
        document.getElementById('d-deskripsi').innerText = p.deskripsi || 'Tidak ada deskripsi.';

        const modal = document.getElementById('modal-detail');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) window.lucide.createIcons();
    }

    function openEdit(id) {
        const p = productsData.find(x => parseInt(x.id_produk) === id);
        if (!p) return;
        
        document.getElementById('f-id_produk').value = p.id_produk;
        document.getElementById('f-nama_barang').value = p.nama;
        document.getElementById('f-harga').value = p.harga;
        document.getElementById('f-stok').value = p.stok;
        document.getElementById('f-id_kategori').value = p.id_kategori;
        document.getElementById('f-deskripsi').value = p.deskripsi;
        
        document.getElementById('modal-form-title').innerText = 'Edit Produk';
        
        const modal = document.getElementById('modal-form');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function confirmDelete(e, nama) {
        if (!confirm('Hapus produk "' + nama + '"? Tindakan ini tidak bisa dibatalkan.')) {
            e.preventDefault();
            return false;
        }
        return true;
    }

    document.getElementById('btn-tambah').addEventListener('click', function () {
        document.getElementById('form-produk').reset();
        document.getElementById('f-id_produk').value = '0';
        document.getElementById('modal-form-title').innerText = 'Tambah Produk';
        
        const modal = document.getElementById('modal-form');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });

    document.getElementById('modal-detail').addEventListener('click', function (e) {
        if (e.target === this) closeModal('modal-detail');
    });
</script>

<?php zapiere_page_end(); ?>
