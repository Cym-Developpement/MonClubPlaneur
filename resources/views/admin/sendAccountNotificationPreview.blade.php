@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-envelope me-2"></i>Prévisualisation — Email de compte débiteur</span>
                    <a href="/usersList" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Retour
                    </a>
                </div>

                <div class="card-body">
                    @if(count($users) === 0)
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>Aucun compte débiteur actif. Aucun email ne sera envoyé.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>{{ count($users) }} utilisateur(s)</strong> vont recevoir un email de notification de compte débiteur.
                        </div>

                        <table class="table table-sm table-hover mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th class="text-end">Solde</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @foreach($users as $user)
                                @php $total += $user->real_amount_account; @endphp
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td class="text-muted">{{ $user->email }}</td>
                                    <td class="text-end text-danger fw-bold">
                                        {{ number_format($user->real_amount_account / 100, 2) }} €
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th class="text-end">{{ number_format($total / 100, 2) }} €</th>
                                </tr>
                            </tfoot>
                        </table>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="d-flex gap-2 flex-wrap">
                            <form method="post" action="/usersSendAccountNotification">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-paper-plane me-2"></i>Confirmer l'envoi ({{ count($users) }} email(s))
                                </button>
                            </form>
                            <form method="post" action="/usersSendAccountNotification/test">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-flask me-2"></i>Envoi de test à {{ auth()->user()->email }}
                                </button>
                            </form>
                            <a href="/usersList" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
