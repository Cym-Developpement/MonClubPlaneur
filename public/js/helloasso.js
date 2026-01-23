/**
 * Script pour gérer les boutons de paiement HelloAsso
 */

document.addEventListener('DOMContentLoaded', function() {
    // Gérer tous les formulaires de paiement HelloAsso
    const helloAssoForms = document.querySelectorAll('form[action*="helloasso/create-payment"]');
    
    helloAssoForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = form.querySelector('.helloasso-pay-button');
            const originalText = submitButton.innerHTML;
            const loadingText = submitButton.getAttribute('data-loading-text') || 'Redirection...';
            
            // Désactiver le bouton et afficher le texte de chargement
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + loadingText;
            
            // Ajouter une classe de chargement
            form.classList.add('helloasso-loading');
            
            // Soumettre le formulaire après un court délai pour l'effet visuel
            setTimeout(function() {
                form.submit();
            }, 500);
        });
    });
    
    // Gérer les clics sur les boutons de paiement
    const payButtons = document.querySelectorAll('.helloasso-pay-button');
    
    payButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            // Ajouter un effet de clic
            button.style.transform = 'scale(0.95)';
            setTimeout(function() {
                button.style.transform = '';
            }, 150);
        });
    });
});

/**
 * Fonction utilitaire pour créer un bouton de paiement HelloAsso dynamiquement
 */
function createHelloAssoButton(options) {
    const defaults = {
        amount: 0,
        itemName: 'Paiement',
        containsDonation: false,
        buttonText: 'Payer avec HelloAsso',
        buttonClass: 'btn btn-primary btn-lg',
        containerId: 'helloasso-button-container'
    };
    
    const config = Object.assign({}, defaults, options);
    
    // Créer le conteneur
    const container = document.getElementById(config.containerId);
    if (!container) {
        console.error('Conteneur HelloAsso non trouvé:', config.containerId);
        return;
    }
    
    // Créer le formulaire
    const form = document.createElement('form');
    form.action = '/helloasso/create-payment';
    form.method = 'POST';
    form.className = 'd-inline';
    
    // Ajouter le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    // Ajouter les champs cachés
    const fields = {
        totalAmount: config.amount * 100, // Convertir en centimes
        initialAmount: config.amount * 100,
        itemName: config.itemName,
        containsDonation: config.containsDonation ? '1' : '0'
    };
    
    Object.keys(fields).forEach(function(key) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    });
    
    // Créer le bouton
    const button = document.createElement('button');
    button.type = 'submit';
    button.className = config.buttonClass + ' helloasso-pay-button';
    button.innerHTML = '<i class="fas fa-credit-card me-2"></i>' + config.buttonText;
    
    form.appendChild(button);
    container.appendChild(form);
    
    // Ajouter l'événement de soumission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirection...';
        
        setTimeout(function() {
            form.submit();
        }, 500);
    });
}

/**
 * Fonction pour formater un montant
 */
function formatHelloAssoAmount(amountInCents, currency = '€') {
    const amount = amountInCents / 100;
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    }).format(amount);
}

/**
 * Fonction pour valider les données de paiement
 */
function validateHelloAssoPayment(data) {
    const errors = [];
    
    if (!data.amount || data.amount <= 0) {
        errors.push('Le montant doit être supérieur à 0');
    }
    
    if (!data.itemName || data.itemName.trim() === '') {
        errors.push('La description est obligatoire');
    }
    
    if (data.itemName && data.itemName.length > 250) {
        errors.push('La description ne peut pas dépasser 250 caractères');
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}