@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Alimenter mon compte pilote
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

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="helloasso-payment-form" action="{{ route('helloasso.create-payment') }}" method="POST">
                        @csrf
                        
                        {{-- Champs cachés --}}
                        <input type="hidden" name="itemName" value="Alimenter mon compte pilote">
                        <input type="hidden" name="containsDonation" value="0">
                        <input type="hidden" name="totalAmount" id="totalAmount" value="">
                        <input type="hidden" name="initialAmount" id="initialAmount" value="">
                        
                        {{-- Informations de l'acheteur --}}
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-user me-2"></i>
                                Informations de l'acheteur
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="payerPrenom" class="form-label">
                                            <strong>Prénom :</strong>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="payerPrenom" 
                                               name="payerPrenom" 
                                               value="{{ $userPrenom }}"
                                               placeholder="Votre prénom"
                                               readonly
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="payerNom" class="form-label">
                                            <strong>Nom :</strong>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="payerNom" 
                                               name="payerNom" 
                                               value="{{ $userNom }}"
                                               placeholder="Votre nom"
                                               readonly
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="payerEmail" class="form-label">
                                            <strong>Adresse e-mail :</strong>
                                        </label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="payerEmail" 
                                               name="payerEmail" 
                                               value="{{ $userEmail }}"
                                               placeholder="votre@email.com"
                                               readonly
                                               required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Sélection du montant --}}
                        <div class="mb-4">
                            <label for="amount" class="form-label">
                                <strong>Montant à verser :</strong>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control form-control-lg" 
                                               id="amount" 
                                               name="amount" 
                                               min="1" 
                                               max="1000" 
                                               step="0.01" 
                                               placeholder="0.00"
                                               required>
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary" onclick="setAmount(100)">100€</button>
                                        <button type="button" class="btn btn-outline-primary" onclick="setAmount(250)">250€</button>
                                        <button type="button" class="btn btn-outline-primary" onclick="setAmount(500)">500€</button>
                                        <button type="button" class="btn btn-outline-primary" onclick="setAmount(1000)">1000€</button>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Montant minimum : 1€ - Montant maximum : 1000€
                            </small>
                        </div>

                        {{-- Choix du type de paiement --}}
                        <div class="mb-4">
                            <label class="form-label">
                                <strong>Mode de paiement :</strong>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentType" id="paymentSingle" value="single" checked>
                                        <label class="form-check-label" for="paymentSingle">
                                            <strong>Paiement unique</strong>
                                            <br><small class="text-muted">Paiement immédiat du montant total</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentType" id="paymentInstallments" value="installments">
                                        <label class="form-check-label" for="paymentInstallments">
                                            <strong>Paiement en plusieurs fois</strong>
                                            <br><small class="text-muted">Étaler le paiement sur plusieurs mois</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Configuration des échéances (masqué par défaut) --}}
                        <div id="installments-config" class="mb-4" style="display: none;">
                            <h6 class="mb-3">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Configuration des échéances
                            </h6>
                            <!--<div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Conditions :</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Maximum 12 mois d'échéances</li>
                                    <li>Une échéance par mois maximum</li>
                                    <li>Pas d'échéance après le 27 du mois</li>
                                    <li>Première échéance au mois suivant</li>
                                </ul>
                            </div>-->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="initialAmount" class="form-label">
                                            <strong>Montant initial (immédiat) :</strong>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="initialAmountInput" 
                                                   name="initialAmountInput" 
                                                   min="1" 
                                                   step="0.01" 
                                                   placeholder="0.00">
                                            <span class="input-group-text">€</span>
                                        </div>
                                        <small class="form-text text-muted">
                                            Montant payé immédiatement
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="installmentsCount" class="form-label">
                                            <strong>Nombre d'échéances :</strong>
                                        </label>
                                        <select class="form-control" id="installmentsCount" name="installmentsCount">
                                            <option value="2">2 fois (1 échéance)</option>
                                            <option value="3">3 fois (2 échéances)</option>
                                            <option value="4">4 fois (3 échéances)</option>
                                            <option value="5">5 fois (4 échéances)</option>
                                            <option value="6">6 fois (5 échéances)</option>
                                            <option value="7">7 fois (6 échéances)</option>
                                            <option value="8">8 fois (7 échéances)</option>
                                            <option value="9">9 fois (8 échéances)</option>
                                            <option value="10">10 fois (9 échéances)</option>
                                            <option value="12">12 fois (11 échéances)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="installments-preview" class="mt-3">
                                <!-- Aperçu des échéances généré par JavaScript -->
                            </div>
                        </div>

                        {{-- Affichage du montant sélectionné --}}
                        <div id="amount-display" class="helloasso-amount-display mb-4" style="display: none;">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Description :</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <strong>Alimenter mon compte pilote</strong>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Montant total :</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="h4 text-primary" id="display-amount">0,00 €</span>
                                </div>
                            </div>
                        </div>
                        {{-- Bouton HelloAsso officiel --}}
                        <div class="text-center">
                            <div class="HaPay" style="margin: 0 auto;">
                                <button type="submit" class="HaPayButton" id="helloasso-pay-button" disabled>
                                    <img
                                        src="https://api.helloasso.com/v5/img/logo-ha.svg"
                                        alt=""
                                        class="HaPayButtonLogo"
                                    />
                                    <div class="HaPayButtonLabel">
                                        <span> Payer avec </span>
                                        <svg
            width="73"
            height="14"
            viewBox="0 0 73 14"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M72.9992 8.78692C72.9992 11.7371 71.242 13.6283 68.4005 13.6283C65.5964 13.6283 63.8018 11.9073 63.8018 8.74909C63.8018 5.79888 65.559 3.90771 68.4005 3.90771C71.2046 3.90771 72.9992 5.64759 72.9992 8.78692ZM67.2041 8.74909C67.2041 10.5457 67.5779 11.2265 68.4005 11.2265C69.223 11.2265 69.5969 10.5079 69.5969 8.78692C69.5969 6.99031 69.223 6.30949 68.4005 6.30949C67.5779 6.30949 67.1854 7.04705 67.2041 8.74909Z"
            />
            <path
              d="M62.978 5.08045L61.8003 6.89597C61.1647 6.47991 60.4356 6.25297 59.6692 6.23406C59.1084 6.23406 58.9214 6.40426 58.9214 6.65011C58.9214 6.9527 59.0149 7.08508 60.716 7.61461C62.4172 8.14413 63.3332 8.88169 63.3332 10.527C63.3332 12.3803 61.576 13.6474 59.1084 13.6474C57.5381 13.6474 56.0986 13.0801 55.1826 12.2101L56.7529 10.4514C57.3885 10.962 58.211 11.3402 59.0336 11.3402C59.6131 11.3402 59.9683 11.1511 59.9683 10.7918C59.9683 10.3568 59.7813 10.2622 58.2484 9.78945C56.5847 9.27883 55.65 8.31434 55.65 6.85814C55.65 5.23174 57.0333 3.92684 59.5383 3.92684C60.8656 3.90793 62.1555 4.36181 62.978 5.08045Z"
            />
            <path
              d="M54.7358 5.08045L53.5581 6.89597C52.9225 6.47991 52.1934 6.25297 51.427 6.23406C50.8662 6.23406 50.6792 6.40426 50.6792 6.65011C50.6792 6.9527 50.7727 7.08508 52.4738 7.61461C54.175 8.14413 55.091 8.88169 55.091 10.527C55.091 12.3803 53.3338 13.6474 50.8662 13.6474C49.2959 13.6474 47.8564 13.0801 46.9404 12.2101L48.5107 10.4514C49.1463 10.962 49.9689 11.3402 50.7914 11.3402C51.3709 11.3402 51.7261 11.1511 51.7261 10.7918C51.7261 10.3568 51.5391 10.2622 50.0062 9.78945C48.3238 9.27883 47.4078 8.31434 47.4078 6.85814C47.4078 5.23174 48.7911 3.92684 51.2961 3.92684C52.6234 3.90793 53.9133 4.36181 54.7358 5.08045Z"
            />
            <path
              d="M46.7721 11.4156L46.0991 13.5526C44.9401 13.477 44.1923 13.1555 43.6876 12.3045C43.0333 13.3068 42.0051 13.6283 40.9956 13.6283C39.201 13.6283 38.042 12.418 38.042 10.7537C38.042 8.74909 39.5375 7.65222 42.3603 7.65222H42.9959V7.42528C42.9959 6.51752 42.6968 6.27167 41.706 6.27167C40.9209 6.30949 40.1357 6.4797 39.4067 6.74446L38.6963 4.62636C39.8179 4.17248 41.0143 3.94554 42.2294 3.90771C45.0709 3.90771 46.23 5.00459 46.23 7.23616V10.3566C46.23 10.9996 46.3795 11.2643 46.7721 11.4156ZM43.0146 10.7348V9.39209H42.6594C41.7247 9.39209 41.2947 9.71359 41.2947 10.4133C41.2947 10.9239 41.5752 11.2643 42.0238 11.2643C42.4164 11.2643 42.7903 11.0563 43.0146 10.7348Z"
            />
            <path
              d="M37.5363 8.78692C37.5363 11.7371 35.7791 13.6283 32.9376 13.6283C30.1335 13.6283 28.3389 11.9073 28.3389 8.74909C28.3389 5.79888 30.0961 3.90771 32.9376 3.90771C35.7417 3.90771 37.5363 5.64759 37.5363 8.78692ZM31.7412 8.74909C31.7412 10.5457 32.1151 11.2265 32.9376 11.2265C33.7601 11.2265 34.134 10.5079 34.134 8.78692C34.134 6.99031 33.7601 6.30949 32.9376 6.30949C32.1151 6.30949 31.7225 7.04705 31.7412 8.74909Z"
            />
            <path
              d="M23.8154 10.6972V0.692948L27.1243 0.352539V10.527C27.1243 10.8296 27.2551 10.9809 27.5355 10.9809C27.6477 10.9809 27.7786 10.962 27.8907 10.9052L28.4889 13.2881C27.8907 13.4961 27.2738 13.6096 26.6569 13.5907C24.8249 13.6285 23.8154 12.5505 23.8154 10.6972Z"
            />
            <path
              d="M18.8057 10.6972V0.692948L22.1145 0.352539V10.527C22.1145 10.8296 22.2454 10.9809 22.5071 10.9809C22.6192 10.9809 22.7501 10.962 22.8623 10.9052L23.4418 13.2881C22.8436 13.4961 22.2267 13.6096 21.6098 13.5907C19.8151 13.6285 18.8057 12.5505 18.8057 10.6972Z"
            />
            <path
              d="M17.9071 9.71359H12.4859C12.6728 11.0185 13.3084 11.2454 14.2805 11.2454C14.9161 11.2454 15.533 10.9807 16.2994 10.4511L17.6454 12.2856C16.6172 13.1555 15.3087 13.6283 13.9627 13.6283C10.6912 13.6283 9.13965 11.5858 9.13965 8.78692C9.13965 6.13929 10.6352 3.90771 13.5888 3.90771C16.2247 3.90771 17.9632 5.60976 17.9632 8.63562C17.9819 8.93821 17.9445 9.39209 17.9071 9.71359ZM14.7291 7.70895C14.7105 6.80119 14.5235 6.04473 13.6823 6.04473C12.9719 6.04473 12.6167 6.46079 12.4859 7.84134H14.7291V7.70895Z"
            />
            <path
              d="M8.24307 6.61229V13.2692H4.93423V7.21746C4.93423 6.49882 4.7286 6.32862 4.4295 6.32862C4.07431 6.32862 3.70043 6.61229 3.30786 7.21746V13.2503H-0.000976562V0.692948L3.30786 0.352539V5.06154C4.07431 4.24834 4.82207 3.90793 5.83154 3.90793C7.32706 3.90793 8.24307 4.89133 8.24307 6.61229Z"
            />
          </svg>
                                    </div>
                                </button>
                                <div class="HaPaySecured">
                                    <svg
                                        width="9"
                                        height="10"
                                        viewBox="0 0 11 12"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M3.875 3V4.5H7.625V3C7.625 1.96875 6.78125 1.125 5.75 1.125C4.69531 1.125 3.875 1.96875 3.875 3ZM2.75 4.5V3C2.75 1.35938 4.08594 0 5.75 0C7.39062 0 8.75 1.35938 8.75 3V4.5H9.5C10.3203 4.5 11 5.17969 11 6V10.5C11 11.3438 10.3203 12 9.5 12H2C1.15625 12 0.5 11.3438 0.5 10.5V6C0.5 5.17969 1.15625 4.5 2 4.5H2.75ZM1.625 6V10.5C1.625 10.7109 1.78906 10.875 2 10.875H9.5C9.6875 10.875 9.875 10.7109 9.875 10.5V6C9.875 5.8125 9.6875 5.625 9.5 5.625H2C1.78906 5.625 1.625 5.8125 1.625 6Z"
                                        />
                                    </svg>
                                    <span>Paiement sécurisé</span>
                                    <img
                                    src="https://helloassodocumentsprod.blob.core.windows.net/public-documents/bouton_payer_avec_helloasso/logo-visa.svg"
                                    alt="Logo Visa"
                                    />
                                    <img
                                    src="https://helloassodocumentsprod.blob.core.windows.net/public-documents/bouton_payer_avec_helloasso/logo-mastercard.svg"
                                    alt="Logo Mastercard"
                                    />
                                    <img
                                    src="https://helloassodocumentsprod.blob.core.windows.net/public-documents/bouton_payer_avec_helloasso/logo-cb.svg"
                                    alt="Logo CB"
                                    />
                                    <img
                                    src="https://helloassodocumentsprod.blob.core.windows.net/public-documents/bouton_payer_avec_helloasso/logo-pci.svg"
                                    alt="Logo PCI"
                                    />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
function setAmount(amount) {
    document.getElementById('amount').value = amount;
    updateAmount();
}

function updateAmount() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const amountInCents = Math.round(amount * 100);
    const paymentType = document.querySelector('input[name="paymentType"]:checked').value;
    
    // Mettre à jour les champs cachés
    document.getElementById('totalAmount').value = amountInCents;
    
    if (paymentType === 'installments') {
        const initialAmount = parseFloat(document.getElementById('initialAmountInput').value) || 0;
        document.getElementById('initialAmount').value = Math.round(initialAmount * 100);
    } else {
        document.getElementById('initialAmount').value = amountInCents;
    }
    
    // Mettre à jour l'affichage
    document.getElementById('display-amount').textContent = amount.toFixed(2).replace('.', ',') + ' €';
    
    // Afficher/masquer la section montant
    const amountDisplay = document.getElementById('amount-display');
    const payButton = document.getElementById('helloasso-pay-button');
    
    if (amount > 0) {
        amountDisplay.style.display = 'block';
        payButton.disabled = false;
    } else {
        amountDisplay.style.display = 'none';
        payButton.disabled = true;
    }
    
    // Mettre à jour l'aperçu des échéances si nécessaire
    if (paymentType === 'installments') {
        updateInstallmentsPreview();
    }
}

// Écouter les changements dans le champ montant
document.getElementById('amount').addEventListener('input', updateAmount);

// Gestion des échéances
function toggleInstallmentsConfig() {
    const paymentType = document.querySelector('input[name="paymentType"]:checked').value;
    const installmentsConfig = document.getElementById('installments-config');
    
    if (paymentType === 'installments') {
        installmentsConfig.style.display = 'block';
        
        // Initialiser les valeurs par défaut si elles ne sont pas définies
        const totalAmount = parseFloat(document.getElementById('amount').value) || 0;
        const initialAmountInput = document.getElementById('initialAmountInput');
        
        if (totalAmount > 0 && !initialAmountInput.value) {
            // Par défaut, montant initial = total / nombre total de paiements
            const totalPayments = parseInt(document.getElementById('installmentsCount').value) || 2;
            const initialAmount = Math.round((totalAmount / totalPayments) * 100) / 100;
            initialAmountInput.value = initialAmount;
        }
        
        updateInstallmentsPreview();
        updateAmount(); // Mettre à jour les champs cachés
    } else {
        installmentsConfig.style.display = 'none';
    }
    
    updateAmount();
}

function updateInstallmentsPreview() {
    const totalAmount = parseFloat(document.getElementById('amount').value) || 0;
    const initialAmount = parseFloat(document.getElementById('initialAmountInput').value) || 0;
    const totalPayments = parseInt(document.getElementById('installmentsCount').value) || 2;
    
    if (totalAmount <= 0) {
        document.getElementById('installments-preview').innerHTML = '';
        return;
    }
    
    // Le nombre d'échéances = nombre total de paiements - 1 (car le premier est le paiement initial)
    const installmentsCount = totalPayments - 1;
    
    if (installmentsCount <= 0) {
        document.getElementById('installments-preview').innerHTML = '';
        return;
    }
    
    const remainingAmount = totalAmount - initialAmount;
    
    // Calculer le montant de chaque échéance en centimes pour éviter les erreurs d'arrondi
    const remainingAmountCents = Math.round(remainingAmount * 100);
    const installmentAmountCents = Math.floor(remainingAmountCents / installmentsCount);
    const lastInstallmentAdjustment = remainingAmountCents - (installmentAmountCents * installmentsCount);
    
    const installmentAmount = installmentAmountCents / 100;
    const lastInstallmentAmount = (installmentAmountCents + lastInstallmentAdjustment) / 100;
    
    const preview = document.getElementById('installments-preview');
    
    if (remainingAmount <= 0) {
        preview.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Le montant initial ne peut pas être supérieur ou égal au montant total.
            </div>
        `;
        return;
    }
    
    preview.innerHTML = `
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Aperçu des échéances</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6"><strong>Paiement initial :</strong></div>
                    <div class="col-6 text-end"><strong>${initialAmount.toFixed(2).replace('.', ',')} €</strong></div>
                </div>
                <div class="row">
                    <div class="col-6"><strong>${installmentsCount} échéance${installmentsCount > 1 ? 's' : ''} :</strong></div>
                    <div class="col-6 text-end">
                        ${installmentsCount === 1 ? 
                            `<strong>${installmentAmount.toFixed(2).replace('.', ',')} €</strong>` :
                            `<strong>${installmentAmount.toFixed(2).replace('.', ',')} €</strong> (${installmentsCount - 1} × ${installmentAmount.toFixed(2).replace('.', ',')} € + 1 × ${lastInstallmentAmount.toFixed(2).replace('.', ',')} €)`
                        }
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6"><strong>Total (${totalPayments} paiements) :</strong></div>
                    <div class="col-6 text-end"><strong>${totalAmount.toFixed(2).replace('.', ',')} €</strong></div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Les échéances seront prélevées le 14 de chaque mois.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function generateInstallmentsData() {
    const totalAmount = parseFloat(document.getElementById('amount').value) || 0;
    const initialAmount = parseFloat(document.getElementById('initialAmountInput').value) || 0;
    const totalPayments = parseInt(document.getElementById('installmentsCount').value) || 2;
    
    if (totalAmount <= 0) return [];
    
    // Le nombre d'échéances = nombre total de paiements - 1 (car le premier est le paiement initial)
    const installmentsCount = totalPayments - 1;
    
    if (installmentsCount <= 0) return [];
    
    const remainingAmount = totalAmount - initialAmount;
    
    // Calculer le montant de chaque échéance en centimes pour éviter les erreurs d'arrondi
    const remainingAmountCents = Math.round(remainingAmount * 100);
    const installmentAmountCents = Math.floor(remainingAmountCents / installmentsCount);
    const lastInstallmentAdjustment = remainingAmountCents - (installmentAmountCents * installmentsCount);
    
    const terms = [];
    const now = new Date();
    
    for (let i = 1; i <= installmentsCount; i++) {
        const installmentDate = new Date(now.getFullYear(), now.getMonth() + i, 15); // 15 du mois suivant
        
        // La dernière échéance récupère l'ajustement pour que la somme soit exacte
        let amount = installmentAmountCents;
        if (i === installmentsCount) {
            amount += lastInstallmentAdjustment;
        }
        
        terms.push({
            amount: amount, // En centimes
            date: installmentDate.toISOString().split('T')[0] // Format YYYY-MM-DD
        });
    }
    
    return terms;
}

// Initialiser quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les changements de type de paiement
    const paymentTypeRadios = document.querySelectorAll('input[name="paymentType"]');
    paymentTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleInstallmentsConfig);
    });
    
    // Écouter les changements dans la configuration des échéances
    const initialAmountInput = document.getElementById('initialAmountInput');
    if (initialAmountInput) {
        initialAmountInput.addEventListener('input', updateInstallmentsPreview);
    }
    
    const installmentsCountSelect = document.getElementById('installmentsCount');
    if (installmentsCountSelect) {
        installmentsCountSelect.addEventListener('change', function() {
            // Recalculer le montant initial quand le nombre d'échéances change
            const totalAmount = parseFloat(document.getElementById('amount').value) || 0;
            if (totalAmount > 0 && initialAmountInput) {
                const totalPayments = parseInt(this.value) || 2;
                const newInitialAmount = Math.round((totalAmount / totalPayments) * 100) / 100;
                initialAmountInput.value = newInitialAmount;
            }
            updateInstallmentsPreview();
            updateAmount(); // Mettre à jour les champs cachés
        });
    }
    
    // Définir une valeur par défaut pour le montant initial
    const amountInput = document.getElementById('amount');
    
    // Quand le montant total change, mettre à jour le montant initial
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const totalAmount = parseFloat(this.value) || 0;
            if (totalAmount > 0 && initialAmountInput && !initialAmountInput.value) {
                // Par défaut, montant initial = total / nombre total de paiements
                const totalPayments = parseInt(document.getElementById('installmentsCount').value) || 2;
                const initialAmount = Math.round((totalAmount / totalPayments) * 100) / 100;
                initialAmountInput.value = initialAmount;
                updateInstallmentsPreview();
            }
            // S'assurer que updateAmount() est appelé pour mettre à jour le bouton
            updateAmount();
        });
    }
    
    // Gérer la soumission du formulaire
    const form = document.getElementById('helloasso-payment-form');
    if (form) {
        form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('helloasso-pay-button');
    const originalText = submitButton.innerHTML;
    
    // Désactiver le bouton et afficher le texte de chargement
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirection vers HelloAsso...';
    
    // Mettre à jour les champs cachés avant la soumission
    updateAmount();
    
    // Préparer les données du formulaire
    const formData = new FormData(this);
    
    // Ajouter les données d'échéances si nécessaire
    const paymentType = document.querySelector('input[name="paymentType"]:checked').value;
    if (paymentType === 'installments') {
        const terms = generateInstallmentsData();
        formData.append('terms', JSON.stringify(terms));
        
        // Debug: afficher les valeurs
        console.log('Données envoyées:', {
            totalAmount: formData.get('totalAmount'),
            initialAmount: formData.get('initialAmount'),
            terms: terms
        });
    }
    
    // Faire un appel AJAX pour obtenir l'URL de redirection HelloAsso
    fetch('{{ route("helloasso.create-payment") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.redirected) {
            // Si la réponse est une redirection, suivre l'URL
            window.location.href = response.url;
        } else {
            return response.json();
        }
    })
    .then(data => {
        if (data && data.success && data.redirect_url) {
            // Rediriger vers l'URL HelloAsso
            window.location.href = data.redirect_url;
        } else {
            // En cas d'erreur, réactiver le bouton
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
            const errorMessage = data && data.message ? data.message : 'Erreur lors de la création du paiement. Veuillez réessayer.';
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        // En cas d'erreur, réactiver le bouton
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
            alert('Erreur de connexion. Veuillez vérifier votre connexion internet et réessayer.');
        });
        });
    }
});
</script>

@endsection
