@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-archive"></i> Sauvegardes</span>
                    <form method="POST" action="/backups/create">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Créer une sauvegarde
                        </button>
                    </form>
                </div>

                <div class="card-body">
                    @if(count($files) === 0)
                        <p class="text-muted text-center my-3">Aucune sauvegarde disponible.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Fichier</th>
                                        <th>Date</th>
                                        <th>Taille</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($files as $file)
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-archive text-secondary me-1"></i>
                                            {{ $file['name'] }}
                                        </td>
                                        <td>{{ date('d/m/Y H:i:s', $file['mtime']) }}</td>
                                        <td>{{ $file['size'] }}</td>
                                        <td class="text-end">
                                            <a href="/backups/download/{{ urlencode($file['name']) }}"
                                               class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-download"></i> Télécharger
                                            </a>
                                            <form method="POST" action="/backups/delete/{{ urlencode($file['name']) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Supprimer cette sauvegarde ?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="card-footer text-muted small">
                    Les sauvegardes contiennent le stockage de l'application (<code>storage/app</code>) et la base de données SQLite.
                    Elles sont stockées dans <code>storage/backups/</code>.
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
