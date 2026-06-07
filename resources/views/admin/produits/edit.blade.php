@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-pencil-alt me-2"></i>Modifier le produit</span>
                    <a href="{{ $product->public_url }}" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-external-link-alt me-1"></i>Voir le lien
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.produits.update', $product->id) }}">
                        @csrf
                        @method('PUT')
                        @include('admin.produits._form')

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.produits.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
