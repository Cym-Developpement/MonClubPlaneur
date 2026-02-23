@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Transfert entre pilotes
                    </h4>
                </div>
                <div class="card-body">
                    {{-- Affichage des notifications --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Affichage du solde actuel --}}
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-wallet me-2"></i>
                        <strong>Votre solde actuel :</strong> 
                        <span class="h5 text-primary">{{ number_format($currentBalance, 2) }} €</span>
                    </div>

                    <form method="POST" action="{{ route('processTransfer') }}">
                        @csrf
                        
                        {{-- Sélection du destinataire --}}
                        <div class="mb-4">
                            <label for="recipient_id" class="form-label">
                                <strong>Destinataire :</strong>
                            </label>
                            <select class="form-control @error('recipient_id') is-invalid @enderror" 
                                    id="recipient_id" 
                                    name="recipient_id" 
                                    required>
                                <option value="">Sélectionnez un pilote</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('recipient_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} {{ $user->firstname }}
                                    </option>
                                @endforeach
                            </select>
                            @error('recipient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Montant --}}
                        <div class="mb-4">
                            <label for="amount" class="form-label">
                                <strong>Montant à transférer :</strong>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control form-control-lg @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       min="0.01" 
                                       max="10000" 
                                       step="0.01" 
                                       placeholder="0.00"
                                       value="{{ old('amount') }}"
                                       required>
                                <span class="input-group-text">€</span>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-body-secondary">
                                Montant minimum : 0,01€ - Montant maximum : 10 000€
                            </small>
                        </div>

                        {{-- Message optionnel --}}
                        <div class="mb-4">
                            <label for="message" class="form-label">
                                <strong>Message (optionnel) :</strong>
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" 
                                      name="message" 
                                      rows="3" 
                                      maxlength="255" 
                                      placeholder="Ajoutez un message pour le destinataire...">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-body-secondary">
                                Maximum 255 caractères
                            </small>
                        </div>

                        {{-- Résumé du transfert --}}
                        <div id="transfer-summary" class="card mb-4" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Résumé du transfert</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6"><strong>Destinataire :</strong></div>
                                    <div class="col-6" id="summary-recipient"></div>
                                </div>
                                <div class="row">
                                    <div class="col-6"><strong>Montant :</strong></div>
                                    <div class="col-6" id="summary-amount"></div>
                                </div>
                                <div class="row">
                                    <div class="col-6"><strong>Nouveau solde :</strong></div>
                                    <div class="col-6" id="summary-balance"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Boutons --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('home') }}" class="btn btn-sm btn-secondary">
                                Retour
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm" id="submit-btn" disabled>
                                Effectuer le transfert
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const recipientSelect = document.getElementById('recipient_id');
    const amountInput = document.getElementById('amount');
    const messageInput = document.getElementById('message');
    const submitBtn = document.getElementById('submit-btn');
    const summaryDiv = document.getElementById('transfer-summary');
    const summaryRecipient = document.getElementById('summary-recipient');
    const summaryAmount = document.getElementById('summary-amount');
    const summaryBalance = document.getElementById('summary-balance');
    
    const currentBalance = {{ $currentBalance }};
    
    function updateSummary() {
        const recipient = recipientSelect.options[recipientSelect.selectedIndex];
        const amount = parseFloat(amountInput.value) || 0;
        
        if (recipient.value && amount > 0) {
            summaryRecipient.textContent = recipient.text;
            summaryAmount.textContent = amount.toFixed(2) + ' €';
            summaryBalance.textContent = (currentBalance - amount).toFixed(2) + ' €';
            summaryDiv.style.display = 'block';
            submitBtn.disabled = false;
        } else {
            summaryDiv.style.display = 'none';
            submitBtn.disabled = true;
        }
    }
    
    recipientSelect.addEventListener('change', updateSummary);
    amountInput.addEventListener('input', updateSummary);
    
    // Validation en temps réel
    amountInput.addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        if (amount > currentBalance) {
            this.setCustomValidity('Montant supérieur à votre solde disponible');
        } else if (amount <= 0) {
            this.setCustomValidity('Le montant doit être supérieur à 0');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Confirmation avant envoi
    document.querySelector('form').addEventListener('submit', function(e) {
        const recipient = recipientSelect.options[recipientSelect.selectedIndex];
        const amount = parseFloat(amountInput.value) || 0;
        
        if (!confirm(`Êtes-vous sûr de vouloir transférer ${amount.toFixed(2)} € vers ${recipient.text} ?`)) {
            e.preventDefault();
        }
    });
});
</script>

@endsection