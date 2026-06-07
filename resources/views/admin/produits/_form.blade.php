@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label class="form-label fw-bold">Titre</label>
    <input type="text" name="title" class="form-control"
           value="{{ old('title', $product->title ?? '') }}" required>
    <div class="form-text">Le lien public sera généré automatiquement à partir du titre.</div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Slug (optionnel)</label>
    <div class="input-group">
        <span class="input-group-text">{{ url('/payer') }}/</span>
        <input type="text" name="slug" class="form-control"
               value="{{ old('slug', $product->slug ?? '') }}"
               placeholder="laisser vide pour générer depuis le titre">
    </div>
    <div class="form-text">Lettres minuscules, chiffres et tirets uniquement.</div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Description</label>
    <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Montant (€)</label>
    <input type="number" name="amount" class="form-control" step="0.01" min="1" max="10000"
           value="{{ old('amount', isset($product) ? number_format($product->amount_cts / 100, 2, '.', '') : '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Texte ajouté à l'email de confirmation (optionnel)</label>
    <textarea name="email_extra" class="form-control" rows="3">{{ old('email_extra', $product->email_extra ?? '') }}</textarea>
    <div class="form-text">Ex : modalités, lieu de rendez-vous, prochaines étapes…</div>
</div>

<div class="form-check form-switch mb-2">
    <input type="hidden" name="active" value="0">
    <input class="form-check-input" type="checkbox" name="active" value="1" id="active"
           {{ old('active', $product->active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="active">Lien actif (paiement possible)</label>
</div>

<div class="form-check form-switch mb-3">
    <input type="hidden" name="show_on_site" value="0">
    <input class="form-check-input" type="checkbox" name="show_on_site" value="1" id="show_on_site"
           {{ old('show_on_site', $product->show_on_site ?? false) ? 'checked' : '' }}>
    <label class="form-check-label" for="show_on_site">Afficher sur la page publique des prestations (/produits)</label>
</div>
