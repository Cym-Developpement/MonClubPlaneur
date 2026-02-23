@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Connexion</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3 row">
                            <label for="email" class="col-md-4 col-form-label text-md-end">Adresse e-mail</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="password" class="col-md-4 col-form-label text-md-end">Mot de passe</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        Rester connecté
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-0 row">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Se connecter
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        Mot de passe perdu ?
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                    <hr>
                    <h6>Info LFCT : </h6>
                    <p id="infoAirfield"></p>
                    
                    <hr>
                    <div class="text-center">
                        <h6>Informations utiles :</h6>
                        <a href="{{ route('tarifs-public') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-euro-sign"></i> Consulter nos tarifs
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        $.get("https://flightbook.glidernet.org/api/logbook/lfct/{{ date('Y-m-d') }}", function(data) {
            console.log(data.airfield.time_info);
            time = data.airfield.time_info;
            console.log(data);
            $('#infoAirfield').html('Lever du soleil : '+time.sunrise+' coucher du soleil : '+time.sunset);
        });
    });
</script>
@endsection
