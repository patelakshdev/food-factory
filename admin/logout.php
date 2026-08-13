<?php
require_once __DIR__ . '/../config/app.php';
if (is_logged_in()) {
    audit_log(current_user()['id'], 'admin.logout');
}
logout_user();
redirect('/admin/login.php');
