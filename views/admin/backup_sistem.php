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
            $cmd = "schtasks /create /tn \"ZapiereAutoBackup\" /tr \"$batPath\" /sc daily /st 00:00 /f";
            exec($cmd, $output, $return_var);
            if ($return_var === 0) {
                $action_message = "Auto backup berhasil diaktifkan (Jadwal: Setiap hari jam 00:00)!";
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



zapiere_page_start('Backup Sistem', 'admin', 'backup', 'Monitoring backup, replikasi, dan penempatan fragmen data Zapiere.');
?>

<?php if ($action_message): ?>
<div class="mb-6 rounded-lg border <?= $action_status === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' ?> p-4 text-sm font-semibold">
    <?= e($action_message) ?>
</div>
<?php endif; ?>



<section class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-soft">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between items-center justify-center">
            <form method="POST" class="flex flex-col gap-2 sm:flex-row">
                <?php if ($auto_active): ?>
                    <button type="submit" name="action" value="disable_auto" class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-3 text-sm font-black text-red-600 transition hover:bg-red-100">
                        <i data-lucide="x-circle" class="h-5 w-5"></i>
                        Matikan Auto Backup
                    </button>
                <?php else: ?>
                    <button type="submit" name="action" value="enable_auto" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#011C27]/10 bg-slate-50 px-5 py-3 text-sm font-black text-[#011C27] transition hover:bg-slate-100">
                        <i data-lucide="clock" class="h-5 w-5"></i>
                        Auto Backup (00:00)
                    </button>
                <?php endif; ?>
                <button type="submit" name="action" value="backup_now" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#011C27] px-5 py-3 text-sm font-black text-white transition hover:bg-[#03254E]">
                    <i data-lucide="play-circle" class="h-5 w-5"></i>
                    Backup Sekarang
                </button>
            </form>
        </div>
    </div>
</section>

<?php zapiere_page_end(); ?>
