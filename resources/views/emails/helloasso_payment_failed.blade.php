<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement HelloAsso à valider</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);">

                    <tr>
                        <td align="center" style="background-color:#b8331c;padding:24px 40px;">
                            @if($logo)
                                <img src="{{ $logo }}" alt="{{ $nomCourt }}" style="max-height:60px;max-width:200px;margin-bottom:8px;"><br>
                            @endif
                            <p style="color:#ffffff;font-size:18px;font-weight:bold;margin:0;">⚠ Paiement HelloAsso à valider manuellement</p>
                            <p style="color:#ffd5cb;font-size:13px;margin:4px 0 0;">{{ $nomCourt }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 40px;">
                            <p style="margin:0 0 18px;font-size:15px;color:#333;line-height:1.6;">
                                Un paiement HelloAsso n'a pas pu être vérifié automatiquement après 5 tentatives.
                                L'API de vérification a échoué (probablement un blocage Cloudflare côté HelloAsso).
                            </p>
                            <p style="margin:0 0 24px;font-size:15px;color:#333;line-height:1.6;">
                                <strong>Action requise :</strong> validez manuellement ce paiement dans l'interface d'administration.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f7f7;border-radius:6px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:18px 22px;">
                                        <p style="margin:0 0 10px;font-size:13px;font-weight:bold;color:#b8331c;text-transform:uppercase;letter-spacing:0.5px;">Détail du paiement</p>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;width:140px;">Payeur</td><td style="padding:3px 0;font-size:14px;color:#333;font-weight:bold;">{{ $pending->payer_name ?: '—' }}</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;">Email</td><td style="padding:3px 0;font-size:14px;color:#333;">{{ $pending->payer_email }}</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;">Montant</td><td style="padding:3px 0;font-size:14px;color:#333;font-weight:bold;">{{ number_format($pending->amount / 100, 2, ',', ' ') }} €</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;">Reçu le</td><td style="padding:3px 0;font-size:14px;color:#333;">{{ $pending->created_at?->format('d/m/Y H:i') }}</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;">Payment ID</td><td style="padding:3px 0;font-size:13px;color:#333;font-family:monospace;">{{ $pending->payment_id }}</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;">Order ID</td><td style="padding:3px 0;font-size:13px;color:#333;font-family:monospace;">{{ $pending->order_id }}</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;">Tentatives</td><td style="padding:3px 0;font-size:14px;color:#333;">{{ $pending->attempts }}/5</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;vertical-align:top;">Dernière erreur</td><td style="padding:3px 0;font-size:13px;color:#666;">{{ $pending->error_message ?: '—' }}</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if($apiError)
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#fff5f3;border:1px solid #f5c2b8;border-radius:6px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:18px 22px;">
                                        <p style="margin:0 0 10px;font-size:13px;font-weight:bold;color:#b8331c;text-transform:uppercase;letter-spacing:0.5px;">Réponse HelloAsso (dernière tentative)</p>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;width:140px;">Endpoint</td><td style="padding:3px 0;font-size:13px;color:#333;font-family:monospace;word-break:break-all;">{{ $apiError['endpoint'] ?? '—' }}</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;">Statut HTTP</td><td style="padding:3px 0;font-size:14px;color:#333;font-weight:bold;">{{ ($apiError['status'] ?? 0) ?: '—' }}</td></tr>
                                            <tr><td style="padding:3px 0;font-size:14px;color:#666;vertical-align:top;">Résumé</td><td style="padding:3px 0;font-size:14px;color:#333;">{{ $apiError['summary'] ?? '—' }}</td></tr>
                                        </table>
                                        @if(!empty($apiError['body']))
                                            <p style="margin:14px 0 6px;font-size:12px;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:0.5px;">Corps de la réponse (extrait)</p>
                                            <pre style="margin:0;padding:10px 12px;background-color:#1e1e1e;color:#d4d4d4;font-size:11px;font-family:Consolas,Monaco,monospace;line-height:1.4;border-radius:4px;max-height:280px;overflow:auto;white-space:pre-wrap;word-break:break-all;">{{ \Illuminate\Support\Str::limit(strip_tags($apiError['body']), 2000) }}</pre>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="text-align:center;margin:0 0 12px;">
                                <a href="{{ $adminUrl }}" style="display:inline-block;background-color:#1a3a6b;color:#ffffff;padding:12px 28px;text-decoration:none;border-radius:4px;font-size:14px;font-weight:bold;">
                                    Ouvrir l'administration →
                                </a>
                            </p>

                            <p style="margin:24px 0 0;font-size:12px;color:#999;line-height:1.5;text-align:center;">
                                Une fois sur la page, le paiement apparaît dans le bloc « Paiements HelloAsso en attente ». Cliquez sur <strong>Créditer</strong> pour valider.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
