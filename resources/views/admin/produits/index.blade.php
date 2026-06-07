@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Produits --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-box me-2"></i>Produits & paiements en ligne</span>
                    <a href="{{ route('admin.produits.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nouveau produit
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Montant</th>
                                    <th>Lien public</th>
                                    <th class="text-center">Actif</th>
                                    <th class="text-center">Sur le site</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td>{{ $product->title }}</td>
                                    <td>{{ $product->amount_eur }}</td>
                                    <td>
                                        <div class="input-group input-group-sm" style="max-width: 360px;">
                                            <input type="text" class="form-control" readonly
                                                   value="{{ $product->public_url }}"
                                                   id="lien-{{ $product->id }}">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    onclick="copierLien({{ $product->id }})" title="Copier">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <a class="btn btn-outline-secondary" href="{{ $product->public_url }}"
                                               target="_blank" title="Ouvrir">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($product->active)
                                            <span class="badge bg-success">Oui</span>
                                        @else
                                            <span class="badge bg-secondary">Non</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($product->show_on_site)
                                            <span class="badge bg-info">Oui</span>
                                        @else
                                            <span class="badge bg-secondary">Non</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.produits.edit', $product->id) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Modifier">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <form action="{{ route('admin.produits.destroy', $product->id) }}" method="POST"
                                              class="d-inline" onsubmit="return confirm('Supprimer ce produit ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Aucun produit. Créez-en un pour générer un lien de paiement.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Achats --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-receipt me-2"></i>Derniers achats
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Produit</th>
                                    <th>Payeur</th>
                                    <th>Email</th>
                                    <th>Montant</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $p)
                                <tr>
                                    <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $p->product_title }}</td>
                                    <td>{{ $p->payer_name }}</td>
                                    <td>{{ $p->payer_email }}</td>
                                    <td>{{ $p->amount_eur }}</td>
                                    <td class="text-center">
                                        @if($p->status === 'paid')
                                            <span class="badge bg-success">Payé</span>
                                        @elseif($p->status === 'failed')
                                            <span class="badge bg-danger">Échec</span>
                                        @else
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Aucun achat pour le moment.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function copierLien(id) {
    var input = document.getElementById('lien-' + id);
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        var btn = event.currentTarget;
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(function() { btn.innerHTML = original; }, 1500);
    });
}
</script>
@endsection
