@if(request()->get('iframe') == '1')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tarifs - {{ config('app.name', 'Laravel') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .table th {
            border-top: none;
            font-weight: 600;
        }
        .badge {
            font-size: 0.875em;
        }
        code {
            background-color: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        @yield('content')
    </div>
</body>
</html>
@else
@extends('layouts.app')
@endif

@section('content')
<div class="container p-0">
    <div class="row justify-content-center">
        <div class="col-md-12 p-0 m-0">
            
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
                <div class="card-header">
                    <h3 class="mb-0">Tarifs {{ date('Y') }}</h3>
                </div>

                <div class="card-body">
                    <!-- Section Paramètres -->
                    @if($parametres->count() > 0)
                    <div class="mb-5">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-info-circle"></i> Informations
                        </h4>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <tbody>
                                    @foreach ($parametres as $parametre)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $parametre->title }}</strong>
                                            @if($parametre->description)
                                                <br><span class="badge text-bg-secondary">{{ $parametre->description }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($parametre->monetary == 1)
                                                {{ number_format($parametre->value, 2) }} €
                                            @else
                                                {{ $parametre->value }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Section Aéronefs -->
                    <div class="mb-5">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-plane"></i> Aéronefs
                        </h4>
                        
                        @if($aircrafts->count() > 0)
                            @php
                                $hasMinPrice = $aircrafts->where('minPrice', '>', 0)->count() > 0;
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Nom</th>
                                            <th scope="col">Immatriculation</th>
                                            <th scope="col">Tarif Cellule</th>
                                            <th scope="col">Tarif Moteur</th>
                                            @if($hasMinPrice)
                                                <th scope="col">Tarification Minimum</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($aircrafts as $aircraft)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="badge text-bg-{{ $aircraft->type == 1 ? 'info' : 'success' }}">
                                                    {{ $aircraft->type_str }}
                                                </span>
                                            </td>
                                            <td><strong>{{ $aircraft->name ?? 'Non renseigné' }}</strong></td>
                                            <td><code>{{ $aircraft->register }}</code></td>
                                            <td class="text-success font-weight-bold">
                                                @if($aircraft->basePrice > 0)
                                                    {{ number_format($aircraft->basePrice, 2) }} €/Heure
                                                @else
                                                    ---
                                                @endif
                                            </td>
                                            <td class="text-success font-weight-bold">
                                                @if($aircraft->motorPrice > 0)
                                                    {{ number_format($aircraft->motorPrice, 2) }} €/Heure
                                                @else
                                                    ---
                                                @endif
                                            </td>
                                            @if($hasMinPrice)
                                                <td class="text-danger font-weight-bold">
                                                    @if($aircraft->minPrice > 0)
                                                        {{ number_format(($aircraft->minPrice/100), 2) }} €
                                                    @else
                                                        ---
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucun aéronef actif disponible pour le moment.
                            </div>
                        @endif
                    </div>

                    <!-- Section Moyens de mise en l'air -->
                    <div class="mb-5">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-rocket"></i> Moyens de mise en l'air
                        </h4>
                        
                        @if($startPrices->count() > 0)
                            <div class="row">
                                @foreach ($startPrices as $startPrice)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-rocket"></i> {{ $startPrice->name }}
                                            </h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <h3 class="text-success font-weight-bold">
                                                {{ number_format(($startPrice->basePrice/100), 2) }} €
                                            </h3>
                                            <p class="text-muted mb-0">
                                                @if($startPrice->byMinutes)
                                                    par minute
                                                @else
                                                    par départ
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucun moyen de mise en l'air disponible pour le moment.
                            </div>
                        @endif
                    </div>

                    <!-- Section Vols d'initiation -->
                    @if($viTypes->count() > 0)
                    <div class="mb-5">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-plane"></i> Vols d'initiation
                        </h4>
                        <div class="row">
                            @foreach($viTypes as $vt)
                            @php
                                $label = trim(explode('-', $vt->nom, 2)[1] ?? $vt->nom);
                                $prixEur = number_format($vt->value / 100, 2, ',', ' ');
                            @endphp
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100 border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-plane-departure me-1"></i> {{ $label }}
                                        </h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="text-success fw-bold">{{ $prixEur }} €</h3>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Section Informations -->
                    <div class="mt-5">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Informations importantes</h5>
                            <ul class="mb-0">
                                <li>Les tarifs sont donnés à titre indicatif et peuvent être modifiés sans préavis.</li>
                                <li>Moyens de paiement acceptés : Chèque vacances, carte bleue, virement.</li>
                                <li>Contactez le club pour plus d'informations sur les conditions de vol.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.875em;
}

code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}
</style>
@if(request()->get('iframe') != '1')
@endsection
@endif 