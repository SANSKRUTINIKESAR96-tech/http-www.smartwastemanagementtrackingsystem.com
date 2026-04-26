<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(role_home(current_role()));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Invalid session. Please reload and try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Please enter both email and password.';
        } else {
            $user = db_one(
                'SELECT id, name, email, password, role, phone, address, status
                   FROM users WHERE email = ? LIMIT 1',
                's',
                [$email]
            );
            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Invalid email or password.';
            } elseif ($user['status'] !== 'active') {
                $error = 'Your account is inactive. Please contact the administrator.';
            } else {
                login_user($user);
                flash('auth', 'Welcome back, ' . explode(' ', $user['name'])[0] . '!', 'success');
                redirect(role_home($user['role']));
            }
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h1>Welcome back</h1>
        <p class="sub">Log in to continue to your EcoTrack dashboard.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form">
            <?= csrf_field() ?>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?= e($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com">
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="Enter your password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </form>

        <p class="auth-alt">
            New here? <a href="<?= e(url('register.php')) ?>">Create an account</a>
        </p>
        <p class="auth-alt muted" style="font-size:12.5px">
            Demo accounts after running <code>install.php</code>:<br>
            admin@wms.test · collector@wms.test · user@wms.test<br>
            (all use password <b>Password@123</b>)
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
