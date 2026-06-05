<?php
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/LogModel.php';

// Panggil datanya di bagian atas file
$logs = get_all_logs();

zapiere_page_start('Log Aktivitas', 'admin', 'log', 'Pantau rekam jejak kejadian dan transaksi sistem selama 30 hari terakhir.');
?>

<div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-4">Waktu Kejadian</th>
                    <th class="px-6 py-4">Pelaku / Aktor</th>
                    <th class="px-6 py-4">Detail Aktivitas</th>
                </tr>
            </thead>
            
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-slate-400">Belum ada aktivitas yang tercatat dalam 30 hari terakhir.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="transition-colors hover:bg-slate-50/80">
                            
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-bold text-[#011C27]">
                                    <?= date('d M Y', strtotime($log['tgl_aktifitas'])) ?>
                                </div>
                                <div class="text-xs font-medium text-slate-400 mt-0.5">
                                    <i class="ph ph-clock mr-1"></i><?= date('H:i:s', strtotime($log['tgl_aktifitas'])) ?> WIB
                                </div>
                            </td>
                            
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-bold text-[#03254E]">
                                    <?= e($log['nama_pelaku']) ?>
                                </div>
                                
                                <?php if ($log['role'] === 'admin'): ?>
                                    <span class="mt-1 inline-block rounded border border-purple-200 bg-purple-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-purple-700">Admin</span>
                                <?php elseif ($log['role'] === 'penjual'): ?>
                                    <span class="mt-1 inline-block rounded border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700">Penjual</span>
                                <?php elseif ($log['role'] === 'pembeli'): ?>
                                    <span class="mt-1 inline-block rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700">Pembeli</span>
                                <?php else: ?>
                                    <span class="mt-1 inline-block rounded border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-600">Sistem</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-700 leading-relaxed">
                                    <?= e($log['keterangan']) ?>
                                </p>
                            </td>
                            
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php zapiere_page_end(); ?>