<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(role_home(current_role()));
}

$errors = [];
$old    = ['name' => '', 'email' => '', 'phone' => '', 'address' => '', 'role' => 'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Invalid session. Please reload and try again.';
    } else {
        $old['name']    = trim($_POST['name']    ?? '');
        $old['email']   = trim($_POST['email']   ?? '');
        $old['phone']   = trim($_POST['phone']   ?? '');
        $old['address'] = trim($_POST['address'] ?? '');
        $role           = $_POST['role']         ?? 'user';
        $password       = (string)($_POST['password']  ?? '');
        $confirm        = (string)($_POST['password2'] ?? '');

        if ($old['name'] === '' || mb_strlen($old['name']) < 2)          $errors[] = 'Please enter your full name.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))            $errors[] = 'Please provide a valid email.';
        if (!in_array($role, ['user', 'collector'], true))                $errors[] = 'Invalid role selected.';
        if (strlen($password) < 6)                                        $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm)                                       $errors[] = 'Passwords do not match.';

        if (!$errors) {
            $exists = db_one('SELECT id FROM users WHERE email = ? LIMIT 1', 's', [$old['email']]);
            if ($exists) {
                $errors[] = 'An account with this email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $res = db_exec(
                    'INSERT INTO users (name, email, password, phone, address, role)
                     VALUES (?, ?, ?, ?, ?, ?)',
                    'ssssss',
                    [$old['name'], $old['email'], $hash, $old['phone'], $old['address'], $role]
                );
                if ($res['insert_id']) {
                    $user = db_one('SELECT * FROM users WHERE id = ?', 'i', [$res['insert_id']]);
                    login_user($user);
                    flash('auth', 'Account created. Welcome to EcoTrack!', 'success');
                    redirect(role_home($user['role']));
                } else {
                    $errors[] = 'Could not create account. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h1>Create your account</h1>
        <p class="sub">Join EcoTrack and start managing waste the smart way.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <?= csrf_field() ?>
            <div>
                <label>Full name</label>
                <input type="text" name="name" required value="<?= e($old['name']) ?>" placeholder="Jane Doe">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" required value="<?= e($old['email']) ?>" placeholder="you@example.com">
            </div>
            <div class="form-row">
                <div>
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?= e($old['phone']) ?>" placeholder="Optional">
                </div>
                <div>
                    <label>Role</label>
                    <select name="role">
                        <option value="user"      <?= ($old['role'] ?? '') === 'user' ? 'selected' : '' ?>>Resident / User</option>
                        <option value="collector" <?= ($old['role'] ?? '') === 'collector' ? 'selected' : '' ?>>Waste Collector</option>
                    </select>
                </div>
            </div>
            <div>
                <label>Address</label>
                <input type="text" name="address" value="<?= e($old['address']) ?>" placeholder="Street, area, city">
            </div>
            <div class="form-row">
                <div>
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="At least 6 characters">
                </div>
                <div>
                    <label>Confirm password</label>
                    <input type="password" name="password2" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create account</button>
        </form>

        <p class="auth-alt">
            Already have an account? <a href="<?= e(url('login.php')) ?>">Log in</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
