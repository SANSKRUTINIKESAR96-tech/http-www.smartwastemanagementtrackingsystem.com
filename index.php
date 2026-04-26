<?php
require_once __DIR__ . '/includes/auth.php';

// If already logged in, jump straight to the right dashboard.
if (is_logged_in()) {
    redirect(role_home(current_role()));
}

$pageTitle = 'Smart Waste Management';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Cleaner cities start with <span>smart waste tracking</span></h1>
    <p class="lead">
        EcoTrack connects households, collectors, and administrators on a single platform —
        request pickups, monitor collections, raise complaints, and see real-time analytics.
    </p>
    <div class="hero-actions">
        <a class="btn btn-primary" href="<?= e(url('register.php')) ?>">Get started — it's free</a>
        <a class="btn btn-ghost"   href="<?= e(url('login.php')) ?>">I already have an account</a>
    </div>

    <div class="hero-art">
        <div class="tile"><b>🗑️ Request pickup</b><span class="muted">Submit dry, wet, recyclable or e-waste.</span></div>
        <div class="tile"><b>🚛 Live tracking</b><span class="muted">Pending → Assigned → Collected.</span></div>
        <div class="tile"><b>📊 Smart analytics</b><span class="muted">Daily & monthly reports, charts &amp; KPIs.</span></div>
    </div>
</section>

<section class="section" id="features">
    <h2>Everything you need in one place</h2>
    <p class="lead">Role-based modules for residents, collectors and administrators.</p>
    <div class="features">
        <div class="feature"><div class="ic">👤</div><h3>Resident</h3><p>Raise pickup requests, track status, file complaints, view personal analytics.</p></div>
        <div class="feature"><div class="ic">🚛</div><h3>Collector</h3><p>See assigned tasks, update collection status, review history.</p></div>
        <div class="feature"><div class="ic">🧑‍💼</div><h3>Administrator</h3><p>Manage users, assign collectors, handle complaints, generate reports.</p></div>
        <div class="feature"><div class="ic">🔐</div><h3>Secure</h3><p>PHP sessions, bcrypt passwords, prepared statements, CSRF tokens.</p></div>
        <div class="feature"><div class="ic">📈</div><h3>Analytics</h3><p>Chart.js powered dashboards with daily &amp; monthly trends.</p></div>
        <div class="feature"><div class="ic">📱</div><h3>Responsive</h3><p>Works beautifully on desktop, tablet and mobile.</p></div>
    </div>
</section>

<section class="section" id="how">
    <h2>How it works</h2>
    <p class="lead">A simple 4-step workflow for every pickup.</p>
    <div class="steps">
        <div class="step"><b>Register</b><p>Create a free account as a resident.</p></div>
        <div class="step"><b>Request</b><p>Submit waste details &amp; location.</p></div>
        <div class="step"><b>Assign</b><p>Admin assigns a collector &amp; vehicle.</p></div>
        <div class="step"><b>Collect</b><p>Collector marks it as collected — done!</p></div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
