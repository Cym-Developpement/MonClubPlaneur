<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vol d'initiation — {{ config('app.name') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
</head>
<body style="background-image: url('/img/back.jpg'); background-repeat: no-repeat; background-attachment: fixed;">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="text-center mb-4">
                @php $logo = \App\Models\parametre::getValue('club-logo', ''); @endphp
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo du club" style="max-height:90px;max-width:220px;" class="mb-3">
                @endif
                <h2 class="fw-bold">{{ config('app.name') }}</h2>
                <p class="text-muted">Vol d'initiation</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <i class="fas fa-plane me-2"></i>Activer votre bon de vol
                </div>
                <div class="card-body">

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <p class="text-muted small mb-3">
                        Saisissez le code à 8 caractères figurant sur votre bon de vol d'initiation.
                    </p>

                    <form method="POST" action="{{ route('vi.lookup') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Code du bon</label>
                            <input type="text" name="code"
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                   placeholder="ex : ab3x7q2m"
                                   maxlength="8"
                                   autocomplete="off"
                                   autofocus
                                   value="{{ old('code') }}"
                                   style="letter-spacing: 0.2em; font-family: monospace;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-arrow-right me-1"></i>Accéder à mon bon
                        </button>
                    </form>

                </div>
            </div>

            <p class="text-center text-muted small mt-3">
                {{ config('app.name') }} &mdash; &copy; {{ date('Y') }}
            </p>

        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/9724d9dada.js" crossorigin="anonymous"></script>
</body>
</html>
