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
                    <div class="mb-3">
                      <label for="modUserMailInput">Adresse e-mail</label>
                      <input name="email" type="email" class="form-control" id="modUserMailInput" aria-describedby="emailHelp" placeholder="email" value="{{ $user->email }}">
                    </div>
                    <div class="mb-3">
                      <label for="modUserNameInput">Nom Complet</label>
                      <input name="name" type="text" class="form-control" id="modUserNameInput" placeholder="Nom Prénom"  value="{{ $user->name }}">
                    </div>
                    <div class="mb-3">
                      <label for="modUserLicNumberInput">Numéro Licence</label>
                      <input name="licenceNumber" type="text" class="form-control" id="modUserLicNumberInput" placeholder="XXXXX" value="{{ $user->licenceNumber }}">
                    </div>
                    <div class="alert alert-danger" role="alert" id="modUserHelpName" style="display: none;">
                        Merci de remplir tout les champs ci-dessus!
                    </div>
                    <hr>
                    @include('admin.user.blockAttributes', ['block' => 'mod'])

                    <hr>
                    <h3>Accès administrateur</h3>
                    <div class="alert alert-warning py-2">
                        <i class="fas fa-exclamation-triangle"></i> L'accès administrateur donne un contrôle total sur l'application.
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="modUserIsAdmin" name="isAdmin" value="1"
                            @if($user->isAdmin == 1) checked @endif
                            onchange="toggleAdminPerms(this.checked)">
                        <label class="form-check-label" for="modUserIsAdmin">Administrateur</label>
                    </div>

                    <div id="adminPermsBlock" @if($user->isAdmin != 1) style="display:none" @endif>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-info-circle"></i>
                            Cochez les sections accessibles. <strong>Si aucune case n'est cochée, l'admin a accès à tout.</strong>
                            Retirer le rôle admin supprimera toutes ces permissions.
                        </p>
                        <div class="row">
                            @foreach(\App\Models\usersAttributes::$userRights as $right => $info)
                            @php $inputKey = 'perm_' . str_replace(':', '_', $right); @endphp
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input"
                                        id="{{ $inputKey }}" name="{{ $inputKey }}"
                                        @if($user->isAttr($right)) checked @endif>
                                    <label class="form-check-label" for="{{ $inputKey }}">
                                        <strong>{{ $info['name'] }}</strong>
                                        <br><small class="text-muted">{{ $info['description'] }}</small>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @push('scripts')
                    <script>
                        function toggleAdminPerms(checked) {
                            document.getElementById('adminPermsBlock').style.display = checked ? '' : 'none';
                        }
                    </script>
                    @endpush

                    <div class="alert alert-danger" role="alert" id="modUserHelpServerError" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
