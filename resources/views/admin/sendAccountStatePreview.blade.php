@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-envelope me-2"></i>Prévisualisation — État de compte adhérents {{ $year }}</span>
                    <a href="/usersList" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Retour
                    </a>
                </div>

                <div class="card-body">
                    @if(count($users) === 0)
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>Aucun adhérent trouvé pour {{ $year }}. Aucun email ne sera envoyé.
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>{{ count($users) }} adhérent(s) {{ $year }}</strong> vont recevoir leur état de compte par email.
                        </div>

                        <table class="table table-sm table-hover mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th class="text-end">Solde actuel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td class="text-muted">{{ $user->email }}</td>
                                    <td class="text-end @if($user->real_amount_account < 0) text-danger fw-bold @else text-success @endif">
                                        {{ number_format($user->real_amount_account / 100, 2) }} €
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <form method="post" action="/sendAccountState/{{ $year }}">
                            @csrf
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Confirmer l'envoi ({{ count($users) }} email(s))
                                </button>
                                <a href="/usersList" class="btn btn-outline-secondary">Annuler</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
