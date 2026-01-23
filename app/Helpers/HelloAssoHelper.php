<?php

namespace App\Helpers;

class HelloAssoHelper
{
    /**
     * Génère le HTML pour le bouton "Payer avec HelloAsso"
     *
     * @param array $options Options pour le bouton
     * @return string
     */
    public static function payButton(array $options = [])
    {
        $defaults = [
            'amount' => 0,
            'itemName' => 'Paiement',
            'containsDonation' => false,
            'payer' => [],
            'metadata' => [],
            'terms' => [],
            'buttonText' => 'Payer avec HelloAsso',
            'buttonClass' => 'btn btn-primary btn-lg',
            'buttonId' => 'helloasso-pay-button',
            'formId' => 'helloasso-payment-form',
            'showAmount' => true,
            'currency' => '€'
        ];

        $options = array_merge($defaults, $options);

        // Convertir le montant en centimes si nécessaire
        $amountInCents = is_float($options['amount']) ? (int)($options['amount'] * 100) : (int)$options['amount'];

        return view('components.helloasso-pay-button', compact('options', 'amountInCents'))->render();
    }

    /**
     * Génère un formulaire de paiement HelloAsso
     *
     * @param array $options Options pour le formulaire
     * @return string
     */
    public static function paymentForm(array $options = [])
    {
        $defaults = [
            'amount' => 0,
            'itemName' => 'Paiement',
            'containsDonation' => false,
            'payer' => [],
            'metadata' => [],
            'terms' => [],
            'formClass' => 'helloasso-payment-form',
            'formId' => 'helloasso-payment-form',
            'buttonText' => 'Payer avec HelloAsso',
            'buttonClass' => 'btn btn-primary btn-lg',
            'showAmount' => true,
            'currency' => '€'
        ];

        $options = array_merge($defaults, $options);

        // Convertir le montant en centimes si nécessaire
        $amountInCents = is_float($options['amount']) ? (int)($options['amount'] * 100) : (int)$options['amount'];

        return view('components.helloasso-payment-form', compact('options', 'amountInCents'))->render();
    }

    /**
     * Formate un montant pour l'affichage
     *
     * @param int $amountInCents Montant en centimes
     * @param string $currency Devise
     * @return string
     */
    public static function formatAmount(int $amountInCents, string $currency = '€'): string
    {
        $amount = $amountInCents / 100;
        return number_format($amount, 2, ',', ' ') . ' ' . $currency;
    }

    /**
     * Génère les données de paiement pour HelloAsso
     *
     * @param array $options
     * @return array
     */
    public static function buildPaymentData(array $options = []): array
    {
        $defaults = [
            'amount' => 0,
            'itemName' => 'Paiement',
            'containsDonation' => false,
            'payer' => [],
            'metadata' => [],
            'terms' => []
        ];

        $options = array_merge($defaults, $options);

        // Convertir le montant en centimes
        $amountInCents = is_float($options['amount']) ? (int)($options['amount'] * 100) : (int)$options['amount'];

        return [
            'totalAmount' => $amountInCents,
            'initialAmount' => $amountInCents,
            'itemName' => $options['itemName'],
            'containsDonation' => $options['containsDonation'],
            'payer' => $options['payer'],
            'metadata' => $options['metadata'],
            'terms' => $options['terms']
        ];
    }
}