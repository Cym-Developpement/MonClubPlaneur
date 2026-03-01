<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre compte au CVVT</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);">

                    {{-- En-tête avec logo --}}
                    <tr>
                        <td align="center" style="background-color:#1a3a6b;padding:28px 40px;">
                            @if($logo)
                                <img src="{{ $logo }}" alt="{{ $nomCourt }}" style="max-height:70px;max-width:200px;margin-bottom:10px;"><br>
                            @endif
                            <p style="color:#ffffff;font-size:22px;font-weight:bold;margin:0;letter-spacing:1px;">{{ $nomCourt }}</p>
                            <p style="color:#a8c4e8;font-size:13px;margin:6px 0 0;">{{ $nomComplet }}</p>
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:36px 40px;">
                            <p style="margin:0 0 16px;font-size:15px;color:#333333;">
                                Bonjour <strong>{{ $userNameTxt }}</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:15px;color:#333333;line-height:1.6;">
                                Vous trouverez en pièce jointe le récapitulatif de votre compte au CVVT.
                            </p>
                            <p style="margin:0 0 8px;font-size:15px;color:#333333;line-height:1.6;">
                                Pour toute question concernant votre compte :
                                <a href="mailto:{{ $emailClub }}" style="color:#1a3a6b;text-decoration:none;font-weight:bold;">{{ $emailClub }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Séparateur --}}
                    <tr>
                        <td style="padding:0 40px;">
                            <hr style="border:none;border-top:1px solid #e8e8e8;margin:0;">
                        </td>
                    </tr>

                    {{-- Signature --}}
                    <tr>
                        <td style="padding:24px 40px 36px;">
                            <p style="margin:0;font-size:14px;color:#555555;line-height:1.7;">
                                Cordialement,<br>
                                <strong style="color:#1a3a6b;">{{ $tresorier }}</strong><br>
                                <span style="color:#888888;">Trésorier {{ $nomCourt }}</span>
                            </p>
                        </td>
                    </tr>

                    {{-- Pied de page --}}
                    <tr>
                        <td align="center" style="background-color:#f0f4fa;padding:16px 40px;">
                            <p style="margin:0;font-size:11px;color:#aaaaaa;">
                                Cet email a été envoyé automatiquement par l'application MonClubPlaneur — merci de ne pas y répondre directement.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
