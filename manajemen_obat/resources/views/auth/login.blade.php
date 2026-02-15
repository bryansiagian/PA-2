<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Warehouse System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { margin-top: 100px; max-width: 400px; }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <h3 class="text-center mb-4 fw-bold text-primary">WMS LOGISTICS</h3>
            <p class="text-center text-muted">Silakan masuk ke akun Anda</p>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
            </form>

            <div class="text-center mt-3">
                <small>Belum punya akun? <a href="/register">Daftar sekarang</a></small>
            </div>
        </div>
    </div>
</div>
</body>
</html>