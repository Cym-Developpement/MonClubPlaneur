@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cash-register me-2"></i>Facturer un produit en lot
                </div>

                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    {{-- Étape 1 : ce qui est facturé --}}
                    <form method="get" action="/admin/facturationProduits" class="row g-3 align-items-end mb-4">
                        <div class="col-md-3">
                            <label for="productSelect" class="form-label text-muted small text-uppercase fw-bold mb-1">
                                Produit du catalogue
                            </label>
                            <select class="form-select" name="product" id="productSelect" onchange="fillFromProduct();">
                                <option value="">— Produit temporaire —</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-title="{{ $product->title }}"
                                        data-price="{{ number_format($product->amount_cts / 100, 2, '.', '') }}"
                                        @if((string) $productId === (string) $product->id) selected @endif>
                                    {{ $product->title }} ({{ $product->amount_eur }})
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text">Pré-remplit l'intitulé et le prix, qui restent modifiables.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="labelInput" class="form-label text-muted small text-uppercase fw-bold mb-1">
                                Intitulé facturé
                            </label>
                            <input type="text" class="form-control" name="label" id="labelInput"
                                   value="{{ $label }}" placeholder="Ex : Stage montagne 2026" required>
                        </div>
                        <div class="col-md-2">
                            <label for="priceInput" class="form-label text-muted small text-uppercase fw-bold mb-1">
                                Prix unitaire (€)
                            </label>
                            <input type="number" class="form-control" name="price" id="priceInput"
                                   value="{{ $price }}" step="0.01" min="0.01" placeholder="0,00" required>
                        </div>
                        <div class="col-md-2">
                            <label for="dateInput" class="form-label text-muted small text-uppercase fw-bold mb-1">
                                Date
                            </label>
                            <input type="date" class="form-control" name="date" id="dateInput" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-10">
                            <label for="observationInput" class="form-label text-muted small text-uppercase fw-bold mb-1">
                                Observation (optionnelle)
                            </label>
                            <input type="text" class="form-control" name="observation" id="observationInput"
                                   value="{{ $observation }}" placeholder="Précision ajoutée à chaque transaction">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-users me-1"></i>Choisir les membres
                            </button>
                        </div>
                    </form>

                    {{-- Étape 2 : membres facturés --}}
                    @if(count($users) > 0)
                    <hr>
                    <form method="post" action="/admin/facturationProduits" onsubmit="return confirmBilling();">
                        @csrf
                        <input type="hidden" name="label" value="{{ $label }}">
                        <input type="hidden" name="price" id="billingPrice" value="{{ $price }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="observation" value="{{ $observation }}">

                        <div class="alert alert-info">
                            <b>{{ $label }}</b> — {{ number_format(floatval($price), 2, ',', ' ') }} € l'unité
                            au {{ date('d/m/Y', strtotime($date)) }}.
                            Chaque membre coché sera débité de ce prix multiplié par sa quantité.
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" id="filterMembers"
                                       placeholder="Filtrer par nom..." onkeyup="filterMembers();">
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge bg-secondary" id="billingCount">0 membre sélectionné</span>
                                <span class="badge bg-primary" id="billingTotal">0,00 €</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAllMembers"
                                                       onchange="toggleAllMembers(this);">
                                                <label class="form-check-label" for="checkAllMembers">Facturer</label>
                                            </div>
                                        </th>
                                        <th scope="col">Membre</th>
                                        <th scope="col">Quantité</th>
                                        <th scope="col">Montant</th>
                                        <th scope="col">Solde actuel</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr class="memberRow" data-name="{{ strtolower($user->name) }}">
                                        <th scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input billingMember" type="checkbox"
                                                       id="member-{{ $user->id }}" name="users[]"
                                                       value="{{ $user->id }}" onchange="updateBillingTotal();">
                                                <label class="form-check-label" for="member-{{ $user->id }}"></label>
                                            </div>
                                        </th>
                                        <td>{{ $user->name }}</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm billingQuantity"
                                                   style="max-width: 6rem;"
                                                   name="quantity[{{ $user->id }}]" value="1" min="1" step="1"
                                                   aria-label="Quantité pour {{ $user->name }}"
                                                   onchange="updateBillingTotal();" onkeyup="updateBillingTotal();">
                                        </td>
                                        <td class="memberAmount text-muted">—</td>
                                        <td class="{{ $soldes[$user->id] < 0 ? 'text-danger' : '' }}">
                                            {{ number_format($soldes[$user->id] / 100, 2, ',', ' ') }} €
                                        </td>
                                        <td>
                                            @if(in_array($user->id, $billed))
                                            <span class="badge rounded-pill bg-warning text-dark">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Déjà facturé en {{ date('Y', strtotime($date)) }}
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-cash-register me-1"></i>Facturer les membres sélectionnés
                                </button>
                            </div>
                        </div>
                    </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Recopie l'intitulé et le prix du produit choisi dans le catalogue.
    function fillFromProduct()
    {
        var option = document.getElementById('productSelect').selectedOptions[0];
        if (!option.value) {
            return;
        }
        document.getElementById('labelInput').value = option.dataset.title;
        document.getElementById('priceInput').value = option.dataset.price;
    }

    function toggleAllMembers(source)
    {
        $('.memberRow:visible').find('.billingMember').prop('checked', source.checked);
        updateBillingTotal();
    }

    function filterMembers()
    {
        var search = document.getElementById('filterMembers').value.toLowerCase();
        $('.memberRow').each(function () {
            $(this).toggle($(this).data('name').indexOf(search) !== -1);
        });
    }

    function updateBillingTotal()
    {
        var price = parseFloat(document.getElementById('billingPrice').value) || 0;
        var count = 0;
        var total = 0;

        $('.memberRow').each(function () {
            var checked  = $(this).find('.billingMember').prop('checked');
            var quantity = parseInt($(this).find('.billingQuantity').val(), 10) || 1;
            var amount   = $(this).find('.memberAmount');

            if (checked) {
                count++;
                total += price * quantity;
                amount.removeClass('text-muted').html((price * quantity).toFixed(2).replace('.', ',') + ' €');
            } else {
                amount.addClass('text-muted').html('&mdash;');
            }
        });

        document.getElementById('billingCount').innerHTML = count + ' membre' + (count > 1 ? 's' : '') + ' sélectionné' + (count > 1 ? 's' : '');
        document.getElementById('billingTotal').innerHTML = total.toFixed(2).replace('.', ',') + ' €';
    }

    function confirmBilling()
    {
        var count = $('.billingMember:checked').length;
        if (count === 0) {
            alert('Merci de cocher au moins un membre à facturer.');
            return false;
        }
        return confirm('Facturer ' + document.getElementById('billingTotal').innerHTML.replace(' €', ' €')
            + ' au total à ' + count + ' membre(s) ?');
    }
</script>
@endsection
