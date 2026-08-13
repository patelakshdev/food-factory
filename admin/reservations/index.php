<?php
require_once __DIR__ . '/../../config/app.php';
require_permission('reservations.view');
$pageTitle = 'Reservations';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    require_permission('reservations.update');
    $id = (int)$_POST['id'];
    $status = (string)$_POST['status'];
    $allowed = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];
    if (in_array($status, $allowed, true)) {
        db()->prepare('UPDATE reservations SET status = :s WHERE id = :id')->execute(['s' => $status, 'id' => $id]);
        audit_log(current_user()['id'], 'reservation.status_update', 'reservation', $id, "→ {$status}");
        flash('success', 'Reservation updated.');
    }
    redirect('/admin/reservations/index.php');
}

$reservations = db()->query('SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC LIMIT 100')->fetchAll();
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Date</th><th>Time</th><th>Guests</th><th>Contact</th><th>Status</th><?php if (user_can('reservations.update')): ?><th>Update</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?= e($r['name']) ?></td>
                <td><?= e($r['reservation_date']) ?></td>
                <td><?= e($r['reservation_time']) ?></td>
                <td><?= (int)$r['guests'] ?></td>
                <td><?= e($r['phone']) ?><br><small><?= e($r['email']) ?></small></td>
                <td><span class="status-badge status-pending"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
                <?php if (user_can('reservations.update')): ?>
                <td>
                    <form method="post" style="display:flex;gap:6px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <select name="status">
                            <?php foreach (['pending','confirmed','seated','completed','cancelled','no_show'] as $s): ?>
                                <option value="<?= e($s) ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= e(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-sm">Save</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
