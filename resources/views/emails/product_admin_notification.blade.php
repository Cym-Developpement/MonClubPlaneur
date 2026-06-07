<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel achat en ligne</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#1a3a6b;padding:22px 40px;">
                            <p style="color:#ffffff;font-size:18px;font-weight:bold;margin:0;">{{ $nomCourt }} — Nouvel achat en ligne</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 40px;">
                            <p style="margin:0 0 20px;font-size:15px;color:#333;line-height:1.6;">
                                Un paiement vient d'être validé pour le produit
                                <strong>{{ $purchase->product_title }}</strong>.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4fa;border-radius:6px;margin-bottom:24px;">
                                <tr><td style="padding:18px 22px;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr><td style="padding:4px 0;font-size:14px;color:#555;width:140px;">Montant</td><td style="padding:4px 0;font-size:14px;color:#333;font-weight:bold;">{{ $purchase->amount_eur }}</td></tr>
                                        <tr><td style="padding:4px 0;font-size:14px;color:#555;">Payeur</td><td style="padding:4px 0;font-size:14px;color:#333;">{{ $purchase->payer_name }}</td></tr>
                                        <tr><td style="padding:4px 0;font-size:14px;color:#555;">Email</td><td style="padding:4px 0;font-size:14px;color:#333;">{{ $purchase->payer_email }}</td></tr>
                                        @if($purchase->message)
                                        <tr><td style="padding:4px 0;font-size:14px;color:#555;vertical-align:top;">Message</td><td style="padding:4px 0;font-size:14px;color:#333;">{{ $purchase->message }}</td></tr>
                                        @endif
                                    </table>
                                </td></tr>
                            </table>
                            <p style="margin:0;font-size:14px;">
                                <a href="{{ $adminUrl }}" style="color:#1a3a6b;font-weight:bold;text-decoration:none;">Voir les achats dans l'administration →</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
