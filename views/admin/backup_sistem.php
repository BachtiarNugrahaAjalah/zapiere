<?php
require_once __DIR__ . '/../components/layout.php';

$nodes = nodes_data();
$fragments = fragments_data();
$backups = backups_data();

$successCount = count(array_filter($backups, fn($backup) => strtolower($backup['status_backup']) === 'berhasil'));
$runningCount = count(array_filter($backups, fn($backup) => strtolower($backup['status_backup']) === 'berjalan'));
$totalSize = array_sum(array_map(fn($backup) => (float) $backup['ukuran_mb'], $backups));
$avgLatency = $nodes ? round(array_sum(array_map(fn($node) => (float) $node['latency_ms'], $nodes)) / count($nodes)) : 0;

zapiere_page_start('Backup Sistem', 'admin', 'backup', 'Monitoring backup, replikasi, dan penempatan fragmen data Zapiere.');
?>

<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <?php metric_card('Backup Berhasil', $successCount, 'check-circle-2', 'snapshot siap dipulihkan'); ?>
    <?php metric_card('Backup Berjalan', $runningCount, 'loader-circle', 'sinkronisasi aktif'); ?>
    <?php metric_card('Ukuran Snapshot', number_format($totalSize, 1, ',', '.') . ' MB', 'hard-drive', 'akumulasi backup demo'); ?>
    <?php metric_card('Rata-rata Latency', $avgLatency . ' ms', 'activity', 'antar node database'); ?>
</section>

<section class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Backup Jobs</p>
                <h2 class="mt-2 text-2xl font-black">Snapshot dan replikasi terakhir</h2>
            </div>
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
                <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                Sync Manual
            </button>
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border border-slate-200">
            <div class="hidden grid-cols-[1.2fr_1fr_0.7fr_0.8fr_1fr] bg-slate-50 px-4 py-3 text-xs font-black uppercase tracking-[0.12em] text-[#545677] md:grid">
                <span>Nama Backup</span>
                <span>Target Node</span>
                <span>Status</span>
                <span>Ukuran</span>
                <span>Waktu</span>
            </div>

            <div class="divide-y divide-slate-200">
                <?php foreach ($backups as $backup): ?>
                    <div class="grid gap-3 px-4 py-4 md:grid-cols-[1.2fr_1fr_0.7fr_0.8fr_1fr] md:items-center">
                        <div>
                            <p class="font-black"><?= e($backup['nama_backup']) ?></p>
                            <p class="mt-1 text-xs font-semibold text-[#545677] md:hidden"><?= e($backup['target_node']) ?></p>
                        </div>
                        <p class="hidden text-sm font-semibold text-[#545677] md:block"><?= e($backup['target_node']) ?></p>
                        <div><?= status_badge($backup['status_backup']) ?></div>
                        <p class="text-sm font-black"><?= e(number_format((float) $backup['ukuran_mb'], 1, ',', '.')) ?> MB</p>
                        <p class="text-sm font-semibold text-[#545677]"><?= e($backup['waktu_backup']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Node Health</p>
        <h2 class="mt-2 text-2xl font-black">Kesiapan replikasi</h2>

        <div class="mt-6 space-y-4">
            <?php foreach ($nodes as $node): ?>
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-black"><?= e($node['nama_node']) ?></p>
                            <p class="mt-1 text-sm font-medium text-[#545677]"><?= e($node['lokasi']) ?> - <?= e($node['tipe_node']) ?></p>
                        </div>
                        <?= status_badge($node['status_node']) ?>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#545677]">Latency</p>
                            <p class="mt-2 text-xl font-black"><?= e($node['latency_ms']) ?> ms</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#545677]">Beban</p>
                            <p class="mt-2 text-xl font-black"><?= e($node['beban_persen']) ?>%</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#545677]">Distribusi Fragmen</p>
            <h2 class="mt-2 text-2xl font-black">Data yang ikut strategi backup</h2>
        </div>
        <span class="rounded-full bg-[#FECEE9] px-3 py-1 text-xs font-black text-[#011C27]"><?= count($fragments) ?> aturan</span>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($fragments as $fragment): ?>
            <article class="rounded-lg border border-slate-200 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FECEE9] text-[#011C27]">
                    <i data-lucide="split-square-horizontal" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-4 font-black"><?= e($fragment['nama_fragmen']) ?></h3>
                <p class="mt-2 text-sm leading-6 text-[#545677]"><?= e($fragment['aturan_fragmentasi']) ?></p>
                <div class="mt-4 flex items-center justify-between gap-3 text-xs font-bold">
                    <span class="rounded-full bg-slate-100 px-3 py-1 capitalize text-[#03254E]"><?= e($fragment['tipe_fragmentasi']) ?></span>
                    <span class="text-[#545677]"><?= e($fragment['nama_node']) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php zapiere_page_end(); ?>
