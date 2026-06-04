<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$user = current_user('pembeli');
if (!$user || $user['id_user'] == 0) {
    header("Location: " . url_for('login.php'));
    exit;
}

$dbKategori = db_all("SELECT * FROM kategori");
$dbProduk = products_with_meta(null); // Fetch all products
zapiere_page_start('Dashboard Pembeli', 'pembeli', 'dashboard', 'Temukan elektronik berkualitas di sekitarmu!');
?>
<!-- Inject Phosphor Icons inside body -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="space-y-6">
        <div class="bg-gradient-to-r from-zPink/60 to-zBlush/90 rounded-3xl p-8 sm:p-12 mb-10 flex items-center justify-between relative overflow-hidden border border-zPink/20">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/30 rounded-full blur-3xl"></div>
            <div class="absolute right-10 bottom-0 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
            
            <div class="max-w-xl relative z-10">
                <div class="inline-flex items-center gap-2 bg-white/60 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-zDark mb-4 border border-white/40">
                    <i class="ph-fill ph-lightning"></i> Belanja Praktis & Cepat
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-zDark mb-4 leading-tight">
                    Temukan elektronik berkualitas di sekitarmu!
                </h1>
                <p class="text-zDark/80 mb-6 text-sm sm:text-base leading-relaxed font-medium">
                    Belanja hemat, transaksi aman, pengiriman cepat — semua ada di Zapiere, marketplace elektronik andalan warga Medan.
                </p>
                <button class="bg-zDark hover:bg-opacity-90 text-white px-6 py-2.5 rounded-full font-semibold text-sm transition-colors shadow-lg shadow-zDark/20">
                    Pelajari Lebih Lanjut &rarr;
                </button>
            </div>
            
            <div class="hidden lg:flex w-1/3 justify-end relative z-10">
                <div class="bg-white/40 backdrop-blur-md p-4 rounded-2xl shadow-xl transform rotate-3 border border-white/50">
                    <img src="https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Elektronik" class="rounded-xl w-48 h-48 object-cover">
                </div>
            </div>
        </div>

        <div class="mb-2">
            <div class="flex items-center gap-2 mb-4 border-l-4 border-zDark pl-2">
                <h3 class="font-bold text-zDark text-lg">Kategori Pilihan</h3>
            </div>
        </div>
        <div class="mb-10 overflow-x-auto no-scrollbar pb-4 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="flex gap-3 sm:gap-4 w-max" id="category-container">
            </div>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-zDark flex items-center gap-2 tracking-tight">
                    <i class="ph-fill ph-fire text-orange-500"></i> Temukan Elektronik Impianmu
                </h2>
                <p class="text-zSlate text-sm mt-1.5">Pilih produk terbaik dan checkout dengan cepat.</p>
            </div>
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="ph ph-magnifying-glass text-zSlate text-lg"></i>
                </div>
                <input type="text" id="search-input" oninput="handleSearch()" class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-full text-sm placeholder-gray-400 focus:border-zPink focus:ring-1 focus:ring-zPink transition-colors shadow-sm focus:outline-none" placeholder="Cari barang elektronik...">
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5" id="product-container">
        </div>

    </div>

    <div id="detail-modal" class="fixed inset-0 z-50 hidden bg-[#0a0a0a]/60 backdrop-blur-sm flex justify-center items-center p-4 transition-all opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-all flex flex-col max-h-[90vh]" id="detail-modal-content">
            <div class="relative h-64 bg-white flex-shrink-0 flex justify-center items-center overflow-hidden">
                <img id="modal-img" src="../../assets/images/image.png" alt="Foto Produk" class="w-full h-full object-contain p-4">
                <button onclick="closeModal('detail-modal')" class="absolute top-4 right-4 bg-white/80 backdrop-blur rounded-full p-2 text-zDark hover:bg-zBlush transition-all shadow-sm">
                    <i class="ph ph-x font-bold"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto no-scrollbar flex-grow">
                <span id="modal-category" class="inline-block px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-zBlush text-zDark mb-3">Kategori</span>
                <h2 id="modal-title" class="text-xl sm:text-2xl font-bold text-zDark mb-2 leading-tight">Nama Produk</h2>
                <p id="modal-price" class="text-2xl font-extrabold text-zDark mb-4">Rp 0</p>
                
                <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100">
                    <div class="flex items-center gap-1.5 text-sm text-zSlate">
                        <i class="ph-fill ph-package text-zDark"></i>
                        Stok: <span id="modal-stock" class="font-bold text-zDark">0</span>
                    </div>
                    <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                    <div class="flex items-center gap-1.5 text-sm text-zSlate">
                        <i class="ph-fill ph-storefront text-zDark"></i>
                        <span id="modal-seller" class="font-medium text-zDark">Nama Toko</span>
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
            
            <div class="p-5 max-h-[45vh] overflow-y-auto no-scrollbar bg-gray-50/50" id="checkout-items">
            </div>

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
            <i class="ph-fill ph-check-circle text-green-500 text-xl mt-0.5"></i>
            <div>
                <h4 class="font-bold text-[#0a0a0a] text-sm" id="toast-title">Berhasil!</h4>
                <p class="text-xs text-zSlate mt-1 leading-relaxed" id="toast-message">Pesan notifikasi disini.</p>
            </div>
        </div>
    </div>

    <script>
        const loggedInUser = <?= json_encode($user) ?>;
        const dbKategori = <?= json_encode($dbKategori) ?>;
        
        const products = <?= json_encode($dbProduk) ?>.map(p => ({
            id: parseInt(p.id_produk),
            nama: p.nama,
            harga: parseFloat(p.harga),
            stok: parseInt(p.stok),
            kategori_id: parseInt(p.id_kategori),
            kategori: p.kategori || 'Uncategorized',
            toko: p.penjual || 'Toko',
            lokasi: 'Medan',
            deskripsi: p.nama, 
            foto_barang: p.foto_barang || 'image.png'
        }));

        const categories = [
            { id: 'Semua', name: 'Semua', icon: 'ph-squares-four' },
            ...dbKategori.map(k => ({ 
                id: k.id_kategori, 
                name: k.nama, 
                icon: k.icon || 'ph-tag' 
            }))
        ];

        let activeCategory = 'Semua';
        let searchQuery = '';
        let currentActiveProduct = null;
        let cart = [];

        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        };


        function renderCategories() {
            const container = document.getElementById('category-container');
            container.innerHTML = categories.map(cat => `
                <button onclick="setCategory('${cat.id}')" 
                    class="w-[85px] h-[90px] sm:w-[95px] sm:h-[100px] flex flex-col items-center justify-center gap-2 rounded-2xl border transition-all duration-300 flex-shrink-0
                    ${activeCategory == cat.id 
                        ? 'bg-zBlush/40 border-zDark text-zDark shadow-sm' 
                        : 'bg-white border-gray-200 text-zSlate hover:border-zPink hover:text-zDark hover:bg-gray-50 hover:shadow-sm'}">
                    <i class="ph-light ${cat.icon} text-3xl sm:text-4xl mb-0.5"></i>
                    <span class="text-[10px] sm:text-xs font-semibold text-center leading-tight tracking-wide">${cat.name}</span>
                </button>
            `).join('');
        }

        function setCategory(id) {
            activeCategory = id;
            renderCategories();
            renderProducts();
        }

        function handleSearch() {
            searchQuery = document.getElementById('search-input').value.toLowerCase();
            renderProducts();
        }

        function renderProducts() {
            const container = document.getElementById('product-container');
            
            const filteredProducts = products.filter(p => {
                const matchCategory = activeCategory === 'Semua' || p.kategori_id == activeCategory;
                const matchSearch = p.nama.toLowerCase().includes(searchQuery) || p.deskripsi.toLowerCase().includes(searchQuery);
                return matchCategory && matchSearch;
            });

            if(filteredProducts.length === 0) {
                container.innerHTML = `<div class="col-span-full py-16 text-center text-zSlate">
                    <i class="ph-light ph-package text-5xl mb-3 text-gray-300"></i>
                    <p>Tidak ada produk yang sesuai dengan pencarian atau kategori ini.</p>
                </div>`;
                return;
            }

            container.innerHTML = filteredProducts.map(p => `
                <div class="bg-white rounded-2xl overflow-hidden hover:shadow-xl hover:shadow-zDark/5 transition-all duration-300 group cursor-pointer border border-gray-100 flex flex-col h-full transform hover:-translate-y-1 shadow-sm" onclick="openDetail(${p.id})">
                    <div class="h-44 bg-white flex items-center justify-center relative overflow-hidden">
                        <img src="../assets/images/image.png" alt="${p.nama}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-4 flex flex-col flex-grow bg-white">
                        <span class="text-[10px] font-bold text-zDark/70 uppercase tracking-widest mb-1.5 truncate">${p.kategori}</span>
                        <h3 class="font-bold text-[#0a0a0a] text-[15px] mb-2 line-clamp-2 leading-snug">${p.nama}</h3>
                        <p class="font-extrabold text-zDark text-[17px] mt-auto">${formatRupiah(p.harga)}</p>
                    </div>
                </div>
            `).join('');
        }

        function openDetail(id) {
            const product = products.find(p => p.id === id);
            currentActiveProduct = product;
            
            document.getElementById('modal-title').innerText = product.nama;
            document.getElementById('modal-price').innerText = formatRupiah(product.harga);
            document.getElementById('modal-category').innerText = product.kategori;
            document.getElementById('modal-stock').innerText = product.stok;
            document.getElementById('modal-seller').innerText = product.toko;
            document.getElementById('modal-desc').innerText = product.deskripsi;
            
            const qtyInput = document.getElementById('modal-qty');
            qtyInput.value = 1;
            qtyInput.max = product.stok;

            document.getElementById('btn-add-cart').onclick = () => addToCart(product);

            showModal('detail-modal', 'detail-modal-content');
        }

        function updateQty(change) {
            const input = document.getElementById('modal-qty');
            let newVal = parseInt(input.value) + change;
            if(newVal >= 1 && newVal <= currentActiveProduct.stok) {
                input.value = newVal;
            }
        }

        function addToCart(product) {
            const qty = parseInt(document.getElementById('modal-qty').value);
            
            const existingItem = cart.find(item => item.product.id === product.id);
            
            if(existingItem) {
                if(existingItem.qty + qty > product.stok) {
                    showToast('Gagal Menambahkan', `Stok tidak mencukupi. Sisa stok: ${product.stok}`, false);
                    return;
                }
                existingItem.qty += qty;
            } else {
                cart.push({ product, qty });
            }

            closeModal('detail-modal');
            updateCartUI();
            showToast('Keranjang Diperbarui', `${product.nama} dimasukkan ke keranjang!`, true);
        }

        function updateCartUI() {
            const floatingBtn = document.getElementById('floating-cart');
            const badge = document.getElementById('cart-badge');
            
            if(cart.length > 0) {
                floatingBtn.classList.remove('translate-y-24', 'opacity-0');
                floatingBtn.classList.add('translate-y-0', 'opacity-100');
                
                const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
                badge.innerText = totalItems;
            } else {
                floatingBtn.classList.add('translate-y-24', 'opacity-0');
                floatingBtn.classList.remove('translate-y-0', 'opacity-100');
            }
        }

        function openCheckoutModal() {
            const container = document.getElementById('checkout-items');
            
            container.innerHTML = cart.map((item, index) => `
                <div class="${index < cart.length - 1 ? 'border-b border-gray-200 pb-3 mb-3' : ''}">
                <div class="flex gap-4 bg-white p-3 rounded-xl shadow-sm">
                    <div class="w-16 h-16 bg-gray-50 rounded-lg flex-shrink-0 border border-gray-100 overflow-hidden">
                        <img src="../assets/images/image.png" alt="${item.product.nama}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <h4 class="font-bold text-[#0a0a0a] text-sm line-clamp-1 mb-1">${item.product.nama}</h4>
                        <div class="flex justify-between items-center mt-auto">
                            <p class="text-xs text-zSlate font-medium">${item.qty} Barang</p>
                            <p class="text-sm font-bold text-zDark">${formatRupiah(item.product.harga * item.qty)}</p>
                        </div>
                    </div>
                    <button onclick="removeFromCart(${index})" class="self-center text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                        <i class="ph-fill ph-trash text-lg"></i>
                    </button>
                </div>
                </div>
            `).join('');

            const total = cart.reduce((sum, item) => sum + (item.product.harga * item.qty), 0);
            document.getElementById('checkout-total').innerText = formatRupiah(total);

            showModal('checkout-modal', 'checkout-modal-content');
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartUI();
            if(cart.length === 0) {
                closeModal('checkout-modal');
            } else {
                renderCheckoutItems();
            }
        }

        function renderCheckoutItems() {
            const container = document.getElementById('checkout-items');
            container.innerHTML = cart.map((item, index) => `
                <div class="${index < cart.length - 1 ? 'border-b border-gray-200 pb-3 mb-3' : ''}">
                <div class="flex gap-4 bg-white p-3 rounded-xl shadow-sm">
                    <div class="w-16 h-16 bg-gray-50 rounded-lg flex-shrink-0 border border-gray-100 overflow-hidden">
                        <img src="../assets/images/image.png" alt="${item.product.nama}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <h4 class="font-bold text-[#0a0a0a] text-sm line-clamp-1 mb-1">${item.product.nama}</h4>
                        <div class="flex justify-between items-center mt-auto">
                            <p class="text-xs text-zSlate font-medium">${item.qty} Barang</p>
                            <p class="text-sm font-bold text-zDark">${formatRupiah(item.product.harga * item.qty)}</p>
                        </div>
                    </div>
                    <button onclick="removeFromCart(${index})" class="self-center text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                        <i class="ph-fill ph-trash text-lg"></i>
                    </button>
                </div>
                </div>
            `).join('');
            const total = cart.reduce((sum, item) => sum + (item.product.harga * item.qty), 0);
            document.getElementById('checkout-total').innerText = formatRupiah(total);
        }

        async function processCheckout() {
            const btn = document.querySelector('#checkout-modal .bg-zDark');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<i class="ph-bold ph-spinner animate-spin text-lg"></i> Memproses...`;
            btn.classList.add('opacity-80', 'cursor-not-allowed');

            try {
                // Siapkan data pesanan dari keranjang
                const payload = {
                    cart: cart.map(item => ({
                        id_produk: item.product.id,
                        jumlah: item.qty
                    }))
                };

                // Kirim request ke backend
                const response = await fetch('checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    cart = []; 
                    updateCartUI();
                    closeModal('checkout-modal');
                    
                    showToast('Transaksi Berhasil!', result.message, true);
                    
                    // Reload halaman setelah 1.5 detik untuk memperbarui stok barang yang ditampilkan
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Gagal Checkout', result.message, false);
                }
            } catch (err) {
                showToast('Kesalahan Jaringan', 'Gagal memproses pesanan ke server.', false);
            } finally {
                btn.innerHTML = originalText;
                btn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        }

        function showModal(modalId, contentId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(contentId);
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);
        }

        const modalContentMap = {
            'detail-modal': 'detail-modal-content',
            'checkout-modal': 'checkout-modal-content'
        };

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const contentId = modalContentMap[modalId];
            const content = contentId ? document.getElementById(contentId) : modal.querySelector('div');
            
            modal.classList.add('opacity-0');
            if (content) content.classList.add('scale-95');
            
            return new Promise(resolve => {
                setTimeout(() => {
                    modal.classList.add('hidden');
                    resolve();
                }, 300);
            });
        }

        let toastTimer = null;

        function showToast(title, message, isSuccess) {
            const toast = document.getElementById('toast');
            const icon = toast.querySelector('i');
            
            if (toastTimer) {
                clearTimeout(toastTimer);
                toastTimer = null;
            }

            toast.classList.add('translate-x-full');

            document.getElementById('toast-title').innerText = title;
            document.getElementById('toast-message').innerText = message;
            
            if(isSuccess) {
                toast.querySelector('div').className = "bg-white border-l-4 border-green-500 shadow-2xl rounded-r-xl p-4 flex items-start gap-3 w-80";
                icon.className = "ph-fill ph-check-circle text-green-500 text-xl mt-0.5";
            } else {
                toast.querySelector('div').className = "bg-white border-l-4 border-red-500 shadow-2xl rounded-r-xl p-4 flex items-start gap-3 w-80";
                icon.className = "ph-fill ph-warning-circle text-red-500 text-xl mt-0.5";
            }

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-x-full');
                });
            });
            
            toastTimer = setTimeout(() => {
                toast.classList.add('translate-x-full');
                toastTimer = null;
            }, 3500);
        }

        renderCategories();
        renderProducts();

    </script>
<?php zapiere_page_end(); ?>
