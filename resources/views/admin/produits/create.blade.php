@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus me-2"></i>Nouveau produit
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.produits.store') }}">
                        @csrf
                        @include('admin.produits._form')

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.produits.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
