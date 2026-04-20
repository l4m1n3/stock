<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Ilyken</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #F8F5FF, #EDE7F6);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #fff;
            border-radius: 25px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 35px rgba(106, 13, 173, 0.2);
        }

        .btn-violet {
            background: #6A0DAD;
            color: white;
            border-radius: 30px;
            padding: 12px;
            font-weight: 600;
        }

        .btn-violet:hover {
            background: #7B1FA2;
        }

        .form-control:focus {
            border-color: #6A0DAD;
            box-shadow: 0 0 0 0.2rem rgba(106, 13, 173, 0.2);
        }

        .text-violet {
            color: #6A0DAD;
        }
    </style>
</head>

<body>

<div class="login-card">

    <h3 class="text-center mb-4 fw-bold text-violet">Ilyken Services</h3>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email') }}" required autofocus>

            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>

            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember + Forgot -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input type="checkbox" name="remember" class="form-check-input">
                <label class="form-check-label">Se souvenir de moi</label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-decoration-none text-violet small">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <!-- Button -->
        <button type="submit" class="btn btn-violet w-100">
            Se connecter
        </button>
    </form>

</div>

</body>
</html>