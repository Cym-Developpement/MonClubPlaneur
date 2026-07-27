@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Import de membres</div>

                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    @empty($rows)
                    @empty($imported)
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <form enctype="multipart/form-data" action="/admin/importMembres" method="post">
                                @csrf
                                <label for="membersFile" class="form-label text-muted small text-uppercase fw-bold mb-1">
                                    <i class="fas fa-file-csv me-1"></i>Fichier CSV des membres
                                </label>
                                <div class="input-group">
                                    <input type="file" class="form-control" accept=".csv,.txt" name="members" id="membersFile" required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-file-import me-1"></i>Analyser
                                    </button>
                                </div>
                                <div class="form-text">
                                    Colonnes reconnues (séparateur <code>;</code>) : ID, Nom, Prénom, Email, Qualité,
                                    Date de naissance, Téléphone, N° FFVP, Club, Statut, Date d'inscription.
                                    Seuls Nom, Prénom et Email sont obligatoires. Aucun compte n'est créé à cette étape.
                                </div>
                            </form>
                        </div>
                    </div>
                    @endempty
                    @endempty

                    @isset($rows)
                    <form action="/admin/importMembres/save" method="post">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAllMembers"
                                                       onchange="$('.importMemberRow').prop('checked', this.checked);">
                                                <label class="form-check-label" for="checkAllMembers">Importer</label>
                                            </div>
                                        </th>
                                        <th scope="col">Nom</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">N° FFVP</th>
                                        <th scope="col">Naissance</th>
                                        <th scope="col">Téléphone</th>
                                        <th scope="col">Club</th>
                                        <th scope="col">Statut du membre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                    @php $importable = count($row['blockers']) === 0; @endphp
                                    <tr class="{{ $importable ? '' : 'text-danger' }}">
                                        <th scope="row">
                                            @if($importable)
                                            <div class="form-check">
                                                <input class="form-check-input importMemberRow" type="checkbox"
                                                       id="member-{{ $row['idx'] }}" name="import[]"
                                                       value="{{ json_encode($row) }}" checked>
                                                <label class="form-check-label" for="member-{{ $row['idx'] }}"></label>
                                            </div>
                                            @else
                                                @foreach($row['blockers'] as $blocker)
                                                <span class="badge rounded-pill bg-danger">{{ $blocker }}</span>
                                                @endforeach
                                            @endif
                                        </th>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['email'] }}</td>
                                        <td>{{ $row['licence'] }}</td>
                                        <td>{{ $row['birthDate'] }}</td>
                                        <td>{{ $row['phone'] }}</td>
                                        <td>{{ $row['club'] }}</td>
                                        <td>
                                            @if($importable)
                                            <select class="form-select form-select-sm" name="role[{{ $row['idx'] }}]">
                                                @foreach(App\MemberImport::$roles as $role)
                                                <option value="{{ $role }}" @if($row['role'] === $role) selected @endif>{{ $role }}</option>
                                                @endforeach
                                            </select>
                                            @else
                                            <span class="text-muted">{{ $row['quality'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-envelope me-1"></i>
                            Chaque membre créé recevra un email lui confirmant l'ouverture de son compte
                            et l'invitant à définir son mot de passe.
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-6">
                                <button class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-user-plus me-1"></i>Créer les comptes sélectionnés
                                </button>
                            </div>
                        </div>
                    </form>
                    @endisset

                    @isset($imported)
                    <p>
                        <b>{{ count($imported) }}</b> membre(s) créé(s),
                        <b>{{ count($imported) - count($mailFailed) }}</b> email(s) d'ouverture de compte envoyé(s).
                    </p>
                    @if(count($imported) > 0)
                    <ul class="list-group mb-3">
                        @foreach($imported as $user)
                        <li class="list-group-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <a href="/userMod/{{ $user->id }}">{{ $user->name }}</a>
                            <span class="text-muted">— {{ $user->email }}</span>
                            @if(isset($mailFailed[$user->id]))
                            <span class="badge rounded-pill bg-warning text-dark ms-2">
                                <i class="fas fa-envelope me-1"></i>Email non envoyé
                            </span>
                            <div class="small text-muted">{{ $mailFailed[$user->id] }}</div>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    @if(count($ignored) > 0)
                    <p><b>{{ count($ignored) }}</b> ligne(s) ignorée(s) :</p>
                    <ul class="list-group mb-3">
                        @foreach($ignored as $item)
                        <li class="list-group-item text-danger">
                            <i class="fas fa-times me-2"></i>
                            {{ $item['row']['name'] ?? $item['row']['email'] }}
                            <span class="text-muted">— {{ implode(', ', $item['blockers']) }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <a class="btn btn-secondary btn-sm" href="/admin/importMembres">
                        <i class="fas fa-file-import me-1"></i>Nouvel import
                    </a>
                    @endisset

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
