<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../components/layout_pembeli.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$user = require_role('pembeli');

$kategoriList = db_all("SELECT * FROM kategori ORDER BY id_kategori");
$produkList   = get_all_data_produk();

$activeKat = (int) ($_GET['kat'] ?? 0);

zapiere_pembeli_page_start('Dashboard Pembeli', 'dashboard');
?>

<div class="space-y-6">

    <div class="bg-gradient-to-r from-zPink/60 to-zBlush/90 rounded-3xl p-8 sm:p-12 flex items-center justify-between relative overflow-hidden border border-zPink/20">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/30 rounded-full blur-3xl"></div>
        <div class="absolute right-10 bottom-0 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
        <div class="max-w-xl relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/60 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-zDark mb-4 border border-white/40">
                <i class="ph-fill ph-lightning"></i> Belanja Praktis &amp; Cepat
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-zDark mb-4 leading-tight">
                Temukan elektronik berkualitas di sekitarmu!
            </h1>
            <p class="text-zDark/80 mb-6 text-sm sm:text-base leading-relaxed font-medium">
                Belanja hemat, transaksi aman, pengiriman cepat semua ada di Zapiere.
            </p>
        </div>
        <div class="hidden lg:flex w-1/3 justify-end relative z-10">
            <div class="bg-white/40 backdrop-blur-md p-4 rounded-2xl shadow-xl transform rotate-3 border border-white/50">
                <img src="https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Elektronik" class="rounded-xl w-48 h-48 object-cover">
            </div>
        </div>
    </div>

    <div>
        <div class="flex items-center gap-2 mb-4 border-l-4 border-zDark pl-2">
            <h3 class="font-bold text-zDark text-lg">Kategori Pilihan</h3>
        </div>
        <div class="overflow-x-auto no-scrollbar pb-4 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="flex gap-3 sm:gap-4 w-max">
                <button
                    onclick="setCategory(0)"
                    data-kat="0"
                    class="kat-btn w-[85px] h-[90px] sm:w-[95px] sm:h-[100px] flex flex-col items-center justify-center gap-2 rounded-2xl border transition-all duration-300 flex-shrink-0 <?= $activeKat === 0 ? 'bg-zBlush/40 border-zDark text-zDark shadow-sm' : 'bg-white border-gray-200 text-zSlate hover:border-zPink hover:text-zDark hover:bg-gray-50' ?>">
                    <i class="ph-light ph-squares-four text-3xl sm:text-4xl mb-0.5"></i>
                    <span class="text-[10px] sm:text-xs font-semibold text-center leading-tight tracking-wide">Semua</span>
                </button>
                <?php foreach ($kategoriList as $kat): ?>
                <button
                    onclick="setCategory(<?= (int)$kat['id_kategori'] ?>)"
                    data-kat="<?= (int)$kat['id_kategori'] ?>"
                    class="kat-btn w-[85px] h-[90px] sm:w-[95px] sm:h-[100px] flex flex-col items-center justify-center gap-2 rounded-2xl border transition-all duration-300 flex-shrink-0 <?= $activeKat === (int)$kat['id_kategori'] ? 'bg-zBlush/40 border-zDark text-zDark shadow-sm' : 'bg-white border-gray-200 text-zSlate hover:border-zPink hover:text-zDark hover:bg-gray-50' ?>">
                    <?php
                        $katIcons = [
                            1 => 'ph-laptop',
                            2 => 'ph-device-mobile',
                            3 => 'ph-mouse',
                            4 => 'ph-camera',
                            5 => 'ph-cooking-pot',
                        ];
                        $icon = $katIcons[$kat['id_kategori']] ?? 'ph-tag';
                    ?>
                    <i class="ph-light <?= $icon ?> text-3xl sm:text-4xl mb-0.5"></i>
                    <span class="text-[10px] sm:text-xs font-semibold text-center leading-tight tracking-wide"><?= e($kat['nama']) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-zDark flex items-center gap-2 tracking-tight">
                <i class="ph-fill ph-fire text-orange-500"></i> Temukan Elektronik Impianmu
            </h2>
            <p class="text-zSlate text-sm mt-1.5">Pilih produk terbaik dan checkout dengan cepat.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5" id="product-container">
        <?php foreach ($produkList as $p): ?>
        <div
            class="product-card bg-white rounded-2xl overflow-hidden hover:shadow-xl hover:shadow-zDark/5 transition-all duration-300 group cursor-pointer border border-gray-100 flex flex-col h-full transform hover:-translate-y-1 shadow-sm"
            data-id="<?= (int)$p['id_produk'] ?>"
            data-kat="<?= (int)$p['id_kategori'] ?>"
            data-nama="<?= e(strtolower($p['nama'])) ?>"
            onclick="openDetail(this)"
        >
            <div class="h-44 bg-white flex items-center justify-center relative overflow-hidden">
                <img src="<?= e(product_image_url($p)) ?>" alt="<?= e($p['nama']) ?>" class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-500">
            </div>
            <div class="p-4 flex flex-col flex-grow bg-white">
                <span class="text-[10px] font-bold text-zDark/70 uppercase tracking-widest mb-1.5 truncate"><?= e($p['kategori'] ?? '-') ?></span>
                <h3 class="font-bold text-[#0a0a0a] text-[15px] mb-2 line-clamp-2 leading-snug"><?= e($p['nama']) ?></h3>
                <p class="font-extrabold text-zDark text-[17px] mt-auto"><?= e($p['harga_rp'] ?? 'Rp ' . number_format($p['harga'], 0, ',', '.')) ?></p>
            </div>

            <span class="hidden modal-nama"><?= e($p['nama']) ?></span>
            <span class="hidden modal-harga"><?= e($p['harga_rp'] ?? 'Rp ' . number_format($p['harga'], 0, ',', '.')) ?></span>
            <span class="hidden modal-harga-raw"><?= (int)$p['harga'] ?></span>
            <span class="hidden modal-kategori"><?= e($p['kategori'] ?? '-') ?></span>
            <span class="hidden modal-stok"><?= (int)$p['stok'] ?></span>
            <span class="hidden modal-penjual"><?= e($p['penjual'] ?? '-') ?></span>
            <span class="hidden modal-deskripsi"><?= e($p['deskripsi'] ?? 'Tidak ada deskripsi.') ?></span>
            <span class="hidden modal-img-src"><?= e(product_image_url($p)) ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <div id="no-result" class="hidden col-span-full py-16 text-center text-zSlate">
        <i class="ph-light ph-package text-5xl mb-3 text-gray-300 block"></i>
        <p>Tidak ada produk yang sesuai.</p>
    </div>

</div>

<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-[#0a0a0a]/60 backdrop-blur-sm flex justify-center items-center p-4 transition-all opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all flex flex-col max-h-[90vh]" id="detail-modal-content">
        <div class="relative h-64 bg-white flex-shrink-0 flex justify-center items-center overflow-hidden">
            <img id="modal-img" src="" alt="" class="w-full h-full object-contain p-4">
            <button onclick="closeModal('detail-modal')" class="absolute top-4 right-4 bg-white/80 backdrop-blur rounded-full p-2 text-zDark hover:bg-zBlush transition-all shadow-sm">
                <i class="ph ph-x font-bold"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto no-scrollbar flex-grow">
            <span id="modal-category" class="inline-block px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-zBlush text-zDark mb-3"></span>
            <h2 id="modal-title" class="text-xl sm:text-2xl font-bold text-zDark mb-2 leading-tight"></h2>
            <p id="modal-price" class="text-2xl font-extrabold text-zDark mb-4"></p>
            <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100">
                <div class="flex items-center gap-1.5 text-sm text-zSlate">
                    <i class="ph-fill ph-package text-zDark"></i>
                    Stok: <span id="modal-stock" class="font-bold text-zDark ml-1">0</span>
                </div>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center gap-1.5 text-sm text-zSlate">
                    <i class="ph-fill ph-storefront text-zDark"></i>
                    <span id="modal-seller" class="font-medium text-zDark"></span>
                </div>
            </div>
            <div class="mb-6">
                <h4 class="text-sm font-bold text-zDark mb-2 flex items-center gap-2">
                    <i class="ph ph-info"></i> Deskripsi Produk
                </h4>
                <p id="modal-desc" class="text-sm text-zSlate leading-relaxed"></p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-4 mt-auto pt-2">
                <div class="flex items-center justify-between border border-gray-300 rounded-xl w-32 overflow-hidden bg-white shadow-sm flex-shrink-0">
                    <button onclick="updateQty(-1)" class="w-10 h-10 flex items-center justify-center text-zSlate hover:text-zDark hover:bg-zBlush transition-colors font-bold text-lg">-</button>
                    <input type="number" id="modal-qty" value="1" min="1" class="w-10 text-center bg-transparent text-zDark font-bold focus:outline-none pointer-events-none" readonly>
                    <button onclick="updateQty(1)" class="w-10 h-10 flex items-center justify-center text-zSlate hover:text-zDark hover:bg-zBlush transition-colors font-bold text-lg">+</button>
                </div>
                <button id="btn-add-cart" class="w-full bg-zDark hover:bg-opacity-90 text-white font-bold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-zDark/20">
                    <i class="ph-bold ph-shopping-cart-simple text-lg"></i>
                    Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<button id="floating-cart" onclick="openCheckoutModal()" class="fixed bottom-6 right-6 bg-zPink text-zDark hover:bg-zDark hover:text-white shadow-2xl rounded-full p-4 flex items-center justify-center transition-all transform translate-y-24 opacity-0 z-40 group border-2 border-white">
    <i class="ph-fill ph-shopping-bag text-3xl transition-transform group-hover:scale-110"></i>
    <span id="cart-badge" class="absolute -top-2 -right-2 bg-zDark text-white text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full border-2 border-white shadow-sm">0</span>
</button>

<div id="checkout-modal" class="fixed inset-0 z-50 hidden bg-[#0a0a0a]/60 backdrop-blur-sm flex justify-center items-center p-4 transition-all opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-all flex flex-col" id="checkout-modal-content">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-white">
            <h2 class="text-lg font-bold text-zDark flex items-center gap-2">
                <i class="ph-fill ph-shopping-cart text-zDark text-xl"></i> Checkout
            </h2>
            <button onclick="closeModal('checkout-modal')" class="text-zSlate hover:text-red-500 bg-gray-50 p-1.5 rounded-full transition-colors">
                <i class="ph ph-x text-lg font-bold"></i>
            </button>
        </div>
        <div class="p-5 max-h-[45vh] overflow-y-auto no-scrollbar bg-gray-50/50" id="checkout-items"></div>
        <div class="p-6 bg-white border-t border-gray-100">
            <div class="flex justify-between items-end mb-5">
                <span class="text-zSlate text-sm font-medium">Total Pembayaran</span>
                <span id="checkout-total" class="text-2xl font-extrabold text-zDark leading-none">Rp 0</span>
            </div>
            <button onclick="processCheckout()" class="w-full bg-zDark hover:bg-opacity-90 text-white font-bold py-3.5 px-4 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-zDark/20">
                <i class="ph-bold ph-check-circle text-lg"></i>
                Konfirmasi Checkout
            </button>
        </div>
    </div>
</div>

<div id="toast" class="fixed top-24 right-0 z-50 transform translate-x-full transition-transform duration-300">
    <div class="bg-white border-l-4 border-green-500 shadow-xl rounded-r-xl p-4 flex items-start gap-3 w-80">
        <i class="ph-fill ph-check-circle text-green-500 text-xl mt-0.5" id="toast-icon"></i>
        <div>
            <h4 class="font-bold text-[#0a0a0a] text-sm" id="toast-title">Berhasil!</h4>
            <p class="text-xs text-zSlate mt-1 leading-relaxed" id="toast-message"></p>
        </div>
    </div>
</div>

<script>
    let activeKat    = 0;
    let currentProduct = null;
    let cart         = [];
    const formatRp   = n => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

    function setCategory(id) {
        activeKat = id;
        document.querySelectorAll('.kat-btn').forEach(btn => {
            const isActive = parseInt(btn.dataset.kat) === id;
            btn.className = btn.className
                .replace(/bg-zBlush\/40 border-zDark text-zDark shadow-sm/g, '')
                .replace(/bg-white border-gray-200 text-zSlate hover:border-zPink hover:text-zDark hover:bg-gray-50/g, '')
                .trim();
            btn.classList.add(...(isActive
                ? ['bg-zBlush/40', 'border-zDark', 'text-zDark', 'shadow-sm']
                : ['bg-white', 'border-gray-200', 'text-zSlate', 'hover:border-zPink', 'hover:text-zDark', 'hover:bg-gray-50']));
        });
        filterProducts();
    }

    function handleSearch() {
        filterProducts();
    }

    function filterProducts() {
        const q     = document.getElementById('search-input').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.product-card');
        let visible = 0;
        cards.forEach(card => {
            const matchKat  = activeKat === 0 || parseInt(card.dataset.kat) === activeKat;
            const matchQ    = !q || card.dataset.nama.includes(q);
            const show      = matchKat && matchQ;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        document.getElementById('no-result').style.display = visible === 0 ? 'block' : 'none';
    }

    function openDetail(card) {
        currentProduct = {
            id:     parseInt(card.dataset.id),
            nama:   card.querySelector('.modal-nama').innerText,
            harga:  parseInt(card.querySelector('.modal-harga-raw').innerText),
            hargaRp: card.querySelector('.modal-harga').innerText,
            stok:   parseInt(card.querySelector('.modal-stok').innerText),
            penjual: card.querySelector('.modal-penjual').innerText,
            kategori: card.querySelector('.modal-kategori').innerText,
            deskripsi: card.querySelector('.modal-deskripsi').innerText,
            imgSrc: card.querySelector('.modal-img-src').innerText,
        };

        document.getElementById('modal-img').src       = currentProduct.imgSrc;
        document.getElementById('modal-img').alt       = currentProduct.nama;
        document.getElementById('modal-title').innerText    = currentProduct.nama;
        document.getElementById('modal-price').innerText    = currentProduct.hargaRp;
        document.getElementById('modal-category').innerText = currentProduct.kategori;
        document.getElementById('modal-stock').innerText    = currentProduct.stok;
        document.getElementById('modal-seller').innerText   = currentProduct.penjual;
        document.getElementById('modal-desc').innerText     = currentProduct.deskripsi;

        const qtyInput = document.getElementById('modal-qty');
        qtyInput.value = 1;
        qtyInput.max   = currentProduct.stok;

        document.getElementById('btn-add-cart').onclick = () => addToCart();
        showModal('detail-modal', 'detail-modal-content');
    }

    function updateQty(change) {
        const input  = document.getElementById('modal-qty');
        const newVal = parseInt(input.value) + change;
        if (newVal >= 1 && newVal <= currentProduct.stok) input.value = newVal;
    }

    function addToCart() {
        const qty      = parseInt(document.getElementById('modal-qty').value);
        const existing = cart.find(i => i.product.id === currentProduct.id);
        if (existing) {
            if (existing.qty + qty > currentProduct.stok) {
                showToast('Gagal', `Stok tidak cukup. Sisa: ${currentProduct.stok}`, false);
                return;
            }
            existing.qty += qty;
        } else {
            cart.push({ product: currentProduct, qty });
        }
        closeModal('detail-modal');
        updateCartUI();
        showToast('Keranjang Diperbarui', `${currentProduct.nama} dimasukkan ke keranjang!`, true);
    }

    function updateCartUI() {
        const btn   = document.getElementById('floating-cart');
        const badge = document.getElementById('cart-badge');
        if (cart.length > 0) {
            btn.classList.remove('translate-y-24', 'opacity-0');
            btn.classList.add('translate-y-0', 'opacity-100');
            badge.innerText = cart.reduce((s, i) => s + i.qty, 0);
        } else {
            btn.classList.add('translate-y-24', 'opacity-0');
            btn.classList.remove('translate-y-0', 'opacity-100');
        }
    }

    function openCheckoutModal() {
        renderCheckoutItems();
        showModal('checkout-modal', 'checkout-modal-content');
    }

    function renderCheckoutItems() {
        const container = document.getElementById('checkout-items');
        container.innerHTML = cart.map((item, idx) => `
            <div class="${idx < cart.length - 1 ? 'border-b border-gray-200 pb-3 mb-3' : ''}">
                <div class="flex gap-4 bg-white p-3 rounded-xl shadow-sm">
                    <div class="w-16 h-16 bg-gray-50 rounded-lg flex-shrink-0 border border-gray-100 overflow-hidden">
                        <img src="${item.product.imgSrc}" alt="${item.product.nama}" class="w-full h-full object-contain">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <h4 class="font-bold text-[#0a0a0a] text-sm line-clamp-1 mb-1">${item.product.nama}</h4>
                        <div class="flex justify-between items-center mt-auto">
                            <p class="text-xs text-zSlate font-medium">${item.qty} Barang</p>
                            <p class="text-sm font-bold text-zDark">${formatRp(item.product.harga * item.qty)}</p>
                        </div>
                    </div>
                    <button onclick="removeFromCart(${idx})" class="self-center text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                        <i class="ph-fill ph-trash text-lg"></i>
                    </button>
                </div>
            </div>
        `).join('');
        document.getElementById('checkout-total').innerText = formatRp(cart.reduce((s, i) => s + i.product.harga * i.qty, 0));
    }

    function removeFromCart(idx) {
        cart.splice(idx, 1);
        updateCartUI();
        if (cart.length === 0) closeModal('checkout-modal');
        else renderCheckoutItems();
    }

    async function processCheckout() {
        const btn = document.querySelector('#checkout-modal .bg-zDark');
        const original = btn.innerHTML;
        btn.innerHTML = `<i class="ph-bold ph-spinner animate-spin text-lg"></i> Memproses...`;
        btn.disabled  = true;
        try {
            const res  = await fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart.map(i => ({ id_produk: i.product.id, jumlah: i.qty })) })
            });
            const data = await res.json();
            if (data.success) {
                cart = [];
                updateCartUI();
                closeModal('checkout-modal');
                showToast('Transaksi Berhasil!', data.message, true);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Gagal Checkout', data.message, false);
            }
        } catch {
            showToast('Kesalahan Jaringan', 'Gagal terhubung ke server.', false);
        } finally {
            btn.innerHTML = original;
            btn.disabled  = false;
        }
    }

    const modalMap = { 'detail-modal': 'detail-modal-content', 'checkout-modal': 'checkout-modal-content' };

    function showModal(id, contentId) {
        const m = document.getElementById(id);
        m.classList.remove('hidden');
        setTimeout(() => {
            m.classList.remove('opacity-0');
            document.getElementById(contentId).classList.remove('scale-95');
        }, 10);
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        const c = document.getElementById(modalMap[id]);
        m.classList.add('opacity-0');
        if (c) c.classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }

    let toastTimer = null;
    function showToast(title, msg, ok) {
        const toast = document.getElementById('toast');
        if (toastTimer) { clearTimeout(toastTimer); toastTimer = null; }
        toast.classList.add('translate-x-full');
        document.getElementById('toast-title').innerText   = title;
        document.getElementById('toast-message').innerText = msg;
        const wrap = toast.querySelector('div');
        const icon = document.getElementById('toast-icon');
        if (ok) {
            wrap.className = 'bg-white border-l-4 border-green-500 shadow-2xl rounded-r-xl p-4 flex items-start gap-3 w-80';
            icon.className = 'ph-fill ph-check-circle text-green-500 text-xl mt-0.5';
        } else {
            wrap.className = 'bg-white border-l-4 border-red-500 shadow-2xl rounded-r-xl p-4 flex items-start gap-3 w-80';
            icon.className = 'ph-fill ph-warning-circle text-red-500 text-xl mt-0.5';
        }
        requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.remove('translate-x-full')));
        toastTimer = setTimeout(() => { toast.classList.add('translate-x-full'); toastTimer = null; }, 3500);
    }

    document.getElementById('detail-modal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal('detail-modal'); });
    document.getElementById('checkout-modal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal('checkout-modal'); });
</script>

<?php zapiere_pembeli_page_end(); ?>
