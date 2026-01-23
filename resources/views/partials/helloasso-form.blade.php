{{-- Formulaire de paiement HelloAsso complet --}}
@php
    $amount = $amount ?? 0;
    $itemName = $itemName ?? 'Paiement';
    $containsDonation = $containsDonation ?? false;
    $buttonText = $buttonText ?? 'Payer avec HelloAsso';
    $buttonClass = $buttonClass ?? 'btn btn-primary btn-lg';
    $formId = $formId ?? 'helloasso-payment-form';
    $showAmount = $showAmount ?? true;
    $currency = $currency ?? '€';
    $payer = $payer ?? [];
    $metadata = $metadata ?? [];
    $terms = $terms ?? [];
    
    // Convertir le montant en centimes
    $amountInCents = is_float($amount) ? (int)($amount * 100) : (int)$amount;
@endphp
<style>
      .HaPay {
        width: fit-content;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
      }

      .HaPay * {
        font-family: "Open Sans", "Trebuchet MS", "Lucida Sans Unicode",
          "Lucida Grande", "Lucida Sans", Arial, sans-serif;
        transition: all 0.3s ease-out;
      }

      .HaPayButton {
        align-items: stretch;
        -webkit-box-pack: stretch;
        -ms-flex-pack: stretch;
        background: none;
        border: none;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        padding: 0;
        border-radius: 8px;
      }

      .HaPayButton:hover {
        cursor: pointer;
      }

      .HaPayButton:not(:disabled):focus {
        box-shadow: 0 0 0 0.25rem rgba(73, 211, 138, 0.25);
        -webkit-box-shadow: 0 0 0 0.25rem rgba(73, 211, 138, 0.25);
      }

      .HaPayButton:not(:disabled):hover .HaPayButtonLabel,
      .HaPayButton:not(:disabled):focus .HaPayButtonLabel {
        background-color: #483dbe;
      }

      .HaPayButton:not(:disabled):hover .HaPayButtonLogo,
      .HaPayButton:not(:disabled):focus .HaPayButtonLogo,
      .HaPayButton:not(:disabled):hover .HaPayButtonLabel,
      .HaPayButton:not(:disabled):focus .HaPayButtonLabel {
        border: 1px solid #483dbe;
      }

      .HaPayButton:disabled {
        cursor: not-allowed;
      }

      .HaPayButton:disabled .HaPayButtonLogo,
      .HaPayButton:disabled .HaPayButtonLabel {
        border: 1px solid #d1d6de;
      }

      .HaPayButtonLogo {
        background-color: #ffffff;
        border: 1px solid #4c40cf;
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
        padding: 10px 16px;
        width: 30px;
      }

      .HaPayButtonLabel {
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: space-between;
        column-gap: 5px;
        background-color: #4c40cf;
        border: 1px solid #4c40cf;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
        color: #ffffff;
        font-size: 16px;
        font-weight: 800;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        padding: 0 16px;
      }

      .HaPayButton:disabled .HaPayButtonLabel {
        background-color: #d1d6de;
        color: #505870;
      }

      .HaPaySecured {
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: space-between;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        column-gap: 5px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #2e2f5e;
      }

      .HaPay svg {
        fill: currentColor;
      }
    </style>
<div class="helloasso-payment-form-container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-credit-card me-2"></i>
                Paiement sécurisé
            </h5>
        </div>
        <div class="card-body">
            @if($showAmount && $amountInCents > 0)
                <div class="helloasso-amount-display mb-4">
                    <div class="row">
                        <div class="col-6">
                            <strong>Description :</strong>
                        </div>
                        <div class="col-6 text-end">
                            <strong>{{ $itemName }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Montant total :</strong>
                        </div>
                        <div class="col-6 text-end">
                            <span class="h4 text-primary">{{ number_format($amountInCents / 100, 2, ',', ' ') }} {{ $currency }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <form id="{{ $formId }}" action="{{ route('helloasso.create-payment') }}" method="POST">
                @csrf
                
                {{-- Champs cachés pour les données de paiement --}}
                <input type="hidden" name="totalAmount" value="{{ $amountInCents }}">
                <input type="hidden" name="initialAmount" value="{{ $amountInCents }}">
                <input type="hidden" name="itemName" value="{{ $itemName }}">
                <input type="hidden" name="containsDonation" value="{{ $containsDonation ? '1' : '0' }}">
                
                @if(!empty($payer))
                    @foreach($payer as $key => $value)
                        <input type="hidden" name="payer[{{ $key }}]" value="{{ $value }}">
                    @endforeach
                @endif
                
                @if(!empty($metadata))
                    @foreach($metadata as $key => $value)
                        <input type="hidden" name="metadata[{{ $key }}]" value="{{ $value }}">
                    @endforeach
                @endif
                
                @if(!empty($terms))
                    @foreach($terms as $index => $term)
                        <input type="hidden" name="terms[{{ $index }}][amount]" value="{{ $term['amount'] ?? '' }}">
                        <input type="hidden" name="terms[{{ $index }}][date]" value="{{ $term['date'] ?? '' }}">
                    @endforeach
                @endif

                {{-- Informations de sécurité --}}
                <div class="helloasso-security-info mb-3">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <i class="fas fa-shield-alt text-success"></i>
                            <small class="d-block">Paiement sécurisé</small>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-lock text-success"></i>
                            <small class="d-block">Données cryptées</small>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-certificate text-success"></i>
                            <small class="d-block">Certifié PCI DSS</small>
                        </div>
                    </div>
                </div>

                {{-- Bouton HelloAsso officiel --}}
                <div class="text-center">
                    @include('partials.helloasso-button', [
                        'amount' => $amountInCents / 100,
                        'itemName' => $itemName,
                        'containsDonation' => $containsDonation,
                        'payer' => $payer,
                        'metadata' => $metadata,
                        'terms' => $terms,
                        'showAmount' => false,
                        'formId' => $formId
                    ])
                </div>
            </form>

            {{-- Logo et informations HelloAsso --}}
            <div class="helloasso-footer mt-4">
                <div class="text-center">
                    <small class="text-muted">
                        Paiement sécurisé par 
                        <img src="https://www.helloasso.com/assets/img/logo-helloasso.png" 
                             alt="HelloAsso" 
                             style="height: 20px; vertical-align: middle; margin: 0 5px;">
                    </small>
                    <br>
                    <small class="text-muted">
                        Vous serez redirigé vers la plateforme de paiement HelloAsso
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.helloasso-payment-form-container {
    max-width: 500px;
    margin: 0 auto;
}

.helloasso-amount-display {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}



.helloasso-security-info {
    background: #e8f5e8;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #c3e6c3;
}

.helloasso-security-info i {
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.helloasso-footer {
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
}

.card {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
}
</style>

