<?php
require_once __DIR__ . '/../components/layout.php';
require_once __DIR__ . '/../../models/InfraModel.php';

require_role('admin');

$action_message = '';
$action_status = '';
$batPath = "C:\\laragon\\www\\zapiere\\db\\backup\\backup.bat";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'backup_now') {
            exec("cmd /c \"$batPath\"", $output, $return_var);
            if ($return_var === 0) {
                $action_message = "Backup manual berhasil dijalankan!";
                $action_status = 'success';
            } else {
                $action_message = "Gagal menjalankan backup manual.";
                $action_status = 'error';
            }
        } elseif ($_POST['action'] === 'enable_auto') {
            $backup_time = $_POST['backup_time'] ?? '00:00';
            if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $backup_time)) {
                $backup_time = '00:00';
            }
            $cmd = "schtasks /create /tn \"ZapiereAutoBackup\" /tr \"$batPath\" /sc daily /st {$backup_time} /f";
            exec($cmd, $output, $return_var);
            if ($return_var === 0) {
                $action_message = "Auto backup berhasil diaktifkan (Jadwal: Setiap hari jam {$backup_time})!";
                $action_status = 'success';
            } else {
                $action_message = "Gagal mengaktifkan auto backup.";
                $action_status = 'error';
            }
        } elseif ($_POST['action'] === 'disable_auto') {
            $cmd = "schtasks /delete /tn \"ZapiereAutoBackup\" /f";
            exec($cmd, $output, $return_var);
            if ($return_var === 0) {
                $action_message = "Auto backup berhasil dinonaktifkan!";
                $action_status = 'success';
            } else {
                $action_message = "Gagal menonaktifkan auto backup.";
                $action_status = 'error';
            }
        }
    }
}

// Check if auto backup is active
$auto_active = false;
exec("schtasks /query /tn \"ZapiereAutoBackup\" 2>nul", $out, $ret);
if ($ret === 0) {
    $auto_active = true;
}

$nodes = nodes_data();
$fragments = fragments_data();
$backups = backups_data();

$successCount = count(array_filter($backups, fn($backup) => strtolower($backup['status_backup']) === 'berhasil'));
$runningCount = count(array_filter($backups, fn($backup) => strtolower($backup['status_backup']) === 'berjalan'));
$totalSize = array_sum(array_map(fn($backup) => (float) $backup['ukuran_mb'], $backups));
$avgLatency = $nodes ? round(array_sum(array_map(fn($node) => (float) $node['latency_ms'], $nodes)) / count($nodes)) : 0;

zapiere_page_start('Backup Sistem', 'admin', 'backup', 'Monitoring backup, replikasi, dan penempatan fragmen data Zapiere.');
?>

<?php if ($action_message): ?>
<div class="mb-6 rounded-lg border <?= $action_status === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' ?> p-4 text-sm font-semibold">
    <?= e($action_message) ?>
</div>
<?php endif; ?>

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
            <form method="POST" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <?php if ($auto_active): ?>
                    <button type="submit" name="action" value="disable_auto" class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-3 text-sm font-black text-red-600 transition hover:bg-red-100">
                        <i data-lucide="x-circle" class="h-5 w-5"></i>
                        Matikan Auto Backup
                    </button>
                <?php else: ?>
                    <input type="time" name="backup_time" value="00:00" class="h-11 rounded-lg border border-slate-200 px-3 text-sm font-black text-[#011C27] focus:border-[#011C27] focus:outline-none focus:ring-1 focus:ring-[#011C27]" required title="Pilih waktu backup">
                    <button type="submit" name="action" value="enable_auto" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-[#011C27]/10 bg-slate-50 px-5 text-sm font-black text-[#011C27] transition hover:bg-slate-100">
                        <i data-lucide="clock" class="h-5 w-5"></i>
                        Set Auto Backup
                    </button>
                <?php endif; ?>
                <button type="submit" name="action" value="backup_now" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
                    <i data-lucide="play-circle" class="h-5 w-5"></i>
                    Backup Sekarang
                </button>
            </form>
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
