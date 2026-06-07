@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <h2 class="mb-4 text-center">Nos prestations</h2>

            @if($products->isEmpty())
                <p class="text-muted text-center">Aucune prestation disponible pour le moment.</p>
            @else
                <div class="row g-4">
                    @foreach($products as $product)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            @if($product->image_path)
                                <img src="{{ $product->image_path }}" class="card-img-top" alt="{{ $product->title }}">
                            @endif
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $product->title }}</h5>
                                @if($product->description)
                                    <p class="card-text small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 120) }}</p>
                                @endif
                                <div class="mt-auto">
                                    <div class="fs-5 fw-bold text-primary mb-2">{{ $product->amount_eur }}</div>
                                    <a href="{{ route('public.product.show', $product->slug) }}" class="btn btn-success w-100">
                                        <i class="fas fa-credit-card me-1"></i>Payer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
