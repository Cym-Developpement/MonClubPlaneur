@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">{{ $user->name }}</div>

                <div class="card-body">
                  <form method="post">
                    @csrf
                    <div class="form-group">
                      <label for="modUserMailInput">Adresse e-mail</label>
                      <input name="email" type="email" class="form-control" id="modUserMailInput" aria-describedby="emailHelp" placeholder="email" value="{{ $user->email }}">
                    </div>
                    <div class="form-group">
                      <label for="modUserNameInput">Nom Complet</label>
                      <input name="name" type="text" class="form-control" id="modUserNameInput" placeholder="Nom Prénom"  value="{{ $user->name }}">
                    </div>
                    <div class="form-group">
                      <label for="modUserLicNumberInput">Numéro Licence</label>
                      <input name="licenceNumber" type="text" class="form-control" id="modUserLicNumberInput" placeholder="XXXXX" value="{{ $user->licenceNumber }}">
                    </div>
                    <div class="alert alert-danger" role="alert" id="modUserHelpName" style="display: none;">
                        Merci de remplir tout les champs ci-dessus!
                    </div>
                    <hr>
                    @include('admin.user.blockAttributes', ['block' => 'mod'])
                    <div class="alert alert-danger" role="alert" id="modUserHelpServerError" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
