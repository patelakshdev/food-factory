<?php
require_once __DIR__ . '/../../config/app.php';
require_permission('dashboard.view');
$pageTitle = 'Messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)$_POST['id'];
    $status = (string)$_POST['status'];
    if (in_array($status, ['new', 'read', 'replied', 'archived'], true)) {
        db()->prepare('UPDATE contact_messages SET status = :s WHERE id = :id')->execute(['s' => $status, 'id' => $id]);
    }
    redirect('/admin/messages/index.php');
}

$messages = db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 100')->fetchAll();
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($messages as $m): ?>
            <tr>
                <td><?= e($m['name']) ?></td>
                <td><?= e($m['email']) ?></td>
                <td><?= e($m['message']) ?></td>
                <td><?= e($m['status']) ?></td>
                <td><?= e($m['created_at']) ?></td>
                <td>
                    <form method="post" style="display:flex;gap:6px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <select name="status">
                            <?php foreach (['new','read','replied','archived'] as $s): ?>
                                <option value="<?= e($s) ?>" <?= $m['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-sm">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
