<?php require 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">SIGN IN </div>
            <div class="card-body">
                <form action="/login-action" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
<p class="mt-3 text-center">Don't have an account? <a href="/register" class="fw-bold">Register</a></p>                
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>