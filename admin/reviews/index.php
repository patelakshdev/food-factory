<?php
require_once __DIR__ . '/../../config/app.php';
require_permission('reviews.moderate');
$pageTitle = 'Reviews';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)$_POST['id'];
    if (isset($_POST['status'])) {
        $status = (string)$_POST['status'];
        if (in_array($status, ['pending', 'approved', 'rejected', 'hidden'], true)) {
            db()->prepare('UPDATE reviews SET status = :s WHERE id = :id')->execute(['s' => $status, 'id' => $id]);
            audit_log(current_user()['id'], 'review.status_update', 'review', $id, "→ {$status}");
        }
    }
    if (isset($_POST['admin_reply'])) {
        db()->prepare('UPDATE reviews SET admin_reply = :r WHERE id = :id')
            ->execute(['r' => trim($_POST['admin_reply']) ?: null, 'id' => $id]);
        audit_log(current_user()['id'], 'review.reply', 'review', $id);
    }
    flash('success', 'Review updated.');
    redirect('/admin/reviews/index.php');
}

$reviews = db()->query('SELECT * FROM reviews ORDER BY created_at DESC LIMIT 100')->fetchAll();
require __DIR__ . '/../../includes/admin-header.php';
?>
<?php foreach ($reviews as $r): ?>
    <div class="admin-card">
        <strong><?= e($r['name']) ?></strong> — <?= str_repeat('★', (int)$r['rating']) ?>
        <span class="status-badge status-<?= $r['status'] === 'approved' ? 'completed' : ($r['status'] === 'rejected' ? 'cancelled' : 'pending') ?>"><?= e($r['status']) ?></span>
        <p><?= e($r['review_text']) ?></p>
        <form method="post" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button type="submit" name="status" value="approved" class="btn-sm">Approve</button>
            <button type="submit" name="status" value="rejected" class="btn-sm secondary">Reject</button>
            <button type="submit" name="status" value="hidden" class="btn-sm secondary">Hide</button>
        </form>
        <form method="post" style="margin-top:8px;display:flex;gap:8px;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="text" name="admin_reply" placeholder="Reply to this review..." value="<?= e($r['admin_reply'] ?? '') ?>" style="flex:1;padding:6px;">
            <button type="submit" class="btn-sm">Save Reply</button>
        </form>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
