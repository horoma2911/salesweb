<div class="card" style="max-width:460px;margin:40px auto;">
    <h2>Sign in to Selling Shop</h2>
    <p class="muted">Use the seeded admin account to get started.</p>
    <form method="post" action="<?= url('do-login') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
        </div>
        <div class="right">
            <button class="btn btn-primary" type="submit">Login</button>
        </div>
    </form>
</div>
