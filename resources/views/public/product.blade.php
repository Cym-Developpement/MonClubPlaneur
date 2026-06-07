@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            @if(request()->get('merci'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Merci&nbsp;! Votre paiement a bien été pris en compte. Un email de confirmation vous a été envoyé.
                </div>
            @endif
            @if(request()->get('annule'))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Paiement annulé. Vous pouvez réessayer ci-dessous.
                </div>
            @endif
            @if(request()->get('erreur'))
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    Une erreur est survenue lors du paiement. Merci de réessayer.
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ $product->title }}</h4>
                </div>
                <div class="card-body">

                    @if($product->image_path)
                        <img src="{{ $product->image_path }}" alt="{{ $product->title }}" class="img-fluid rounded mb-3">
                    @endif

                    @if($product->description)
                        <div class="mb-4">{!! nl2br(e($product->description)) !!}</div>
                    @endif

                    <div class="alert alert-light border d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Montant à régler</span>
                        <span class="fs-4 fw-bold text-primary">{{ $product->amount_eur }}</span>
                    </div>

                    <form method="POST" action="{{ route('public.product.pay') }}">
                        @csrf
                        <input type="hidden" name="slug" value="{{ $product->slug }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Prénom</label>
                                <input type="text" name="payer_firstname" class="form-control"
                                       value="{{ old('payer_firstname') }}" required>
                                @error('payer_firstname')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nom</label>
                                <input type="text" name="payer_lastname" class="form-control"
                                       value="{{ old('payer_lastname') }}" required>
                                @error('payer_lastname')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="payer_email" class="form-control"
                                       value="{{ old('payer_email') }}" required>
                                @error('payer_email')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Message (optionnel)</label>
                                <textarea name="message" class="form-control" rows="2" maxlength="500">{{ old('message') }}</textarea>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-lock me-2"></i>Payer {{ $product->amount_eur }} par carte
                            </button>
                        </div>

                        <p class="text-muted text-center small mt-3 mb-0">
                            <i class="fas fa-shield-alt me-1"></i>Paiement sécurisé via HelloAsso
                        </p>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
