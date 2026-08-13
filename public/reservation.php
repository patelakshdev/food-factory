<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Table Reservation - Food Factory';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (!rate_limit('reservation_submit', 8, 300)) {
        flash('error', 'Too many attempts. Please try again later.');
        redirect('/public/reservation.php');
    }

    $v = new Validator($_POST);
    $v->required('name', 'Full name')->maxLength('name', 120, 'Full name')
      ->required('email', 'Email')->email('email')
      ->required('phone', 'Phone')->phone('phone')
      ->required('date', 'Date')->futureDate('date', 'Date')
      ->required('time', 'Time')
      ->required('guests', 'Number of guests')->range('guests', 1, 20, 'Number of guests');

    if ($v->fails()) {
        $errors = $v->errors();
        set_old($_POST);
    } else {
        $user = current_user();
        $stmt = db()->prepare(
            'INSERT INTO reservations (user_id, name, email, phone, reservation_date, reservation_time, guests, status)
             VALUES (:uid, :name, :email, :phone, :date, :time, :guests, "pending")'
        );
        $stmt->execute([
            'uid'    => $user['id'] ?? null,
            'name'   => trim($_POST['name']),
            'email'  => trim($_POST['email']),
            'phone'  => trim($_POST['phone']),
            'date'   => $_POST['date'],
            'time'   => $_POST['time'],
            'guests' => (int)$_POST['guests'],
        ]);
        clear_old();
        redirect('/public/reservation_success.php?name=' . urlencode(trim($_POST['name'])));
    }
}

require __DIR__ . '/../includes/header.php';
?>

<section class="reservation section-wrap" id="Reservation" style="max-width:800px;">
    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:8px;">Dining Experience</div>
    <h2>Book Your Table</h2>
    <p class="reservation-text">Reserve your table today and enjoy a delicious dining experience at Food Factory.</p>

    <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom:24px;text-align:left;">
            <span>⚠️</span>
            <div><?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?></div>
        </div>
    <?php endif; ?>

    <form action="/public/reservation.php" method="POST" style="background:var(--card-bg);border:1px solid var(--border);padding:36px;border-radius:20px;box-shadow:var(--shadow-lg);">
        <?= csrf_field() ?>
        <div>
            <input type="text" name="name" placeholder="Full Name" value="<?= old('name') ?>" required>
        </div>
        <div>
            <input type="email" name="email" placeholder="Email Address" value="<?= old('email') ?>" required>
        </div>
        <div>
            <input type="tel" name="phone" placeholder="Phone Number" value="<?= old('phone') ?>" required>
        </div>
        <div>
            <input type="number" name="guests" placeholder="Number of Guests (1-20)" min="1" max="20" value="<?= old('guests') ?>" required>
        </div>
        <div>
            <input type="date" name="date" min="<?= date('Y-m-d') ?>" value="<?= old('date') ?>" required>
        </div>
        <div>
            <input type="time" name="time" value="<?= old('time') ?>" required>
        </div>
        <button type="submit" class="btn full-col" style="margin-top:8px;">📅 Confirm Reservation</button>
    </form>
</section>

<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
