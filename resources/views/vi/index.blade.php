@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    @php $logo = \App\Models\parametre::getValue('club-logo', ''); @endphp
                    @if($logo)
                        <img src="{{ $logo }}" alt="Logo du club" style="max-height:70px;max-width:180px;" class="mb-2 d-block mx-auto">
                    @endif
                    <strong><i class="fas fa-plane me-2"></i>Vol d'initiation</strong>
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
        </div>
    </div>
</div>
@endsection
