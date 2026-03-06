<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation vol d'initiation</title>
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
                            @if($nomComplet)
                                <p style="color:#a8c4e8;font-size:13px;margin:6px 0 0;">{{ $nomComplet }}</p>
                            @endif
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:36px 40px;">
                            <p style="margin:0 0 16px;font-size:15px;color:#333333;">
                                Bonjour <strong>{{ $vi->prenom }} {{ $vi->nom }}</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:15px;color:#333333;line-height:1.6;">
                                Votre bon de vol d'initiation a bien été activé. Nous vous contacterons prochainement pour fixer la date de votre vol.
                            </p>

                            {{-- Récapitulatif --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4fa;border-radius:6px;padding:0;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p style="margin:0 0 12px;font-size:13px;font-weight:bold;color:#1a3a6b;text-transform:uppercase;letter-spacing:0.5px;">Récapitulatif de votre bon</p>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:4px 0;font-size:14px;color:#555555;width:140px;">Code</td>
                                                <td style="padding:4px 0;font-size:14px;color:#333333;font-family:monospace;font-weight:bold;letter-spacing:0.1em;">{{ $vi->code }}</td>
                                            </tr>
                                            @if($vi->type)
                                            <tr>
                                                <td style="padding:4px 0;font-size:14px;color:#555555;">Type de vol</td>
                                                <td style="padding:4px 0;font-size:14px;color:#333333;">{{ $vi->type }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:4px 0;font-size:14px;color:#555555;">Nom</td>
                                                <td style="padding:4px 0;font-size:14px;color:#333333;">{{ $vi->prenom }} {{ $vi->nom }}</td>
                                            </tr>
                                            @if($vi->email)
                                            <tr>
                                                <td style="padding:4px 0;font-size:14px;color:#555555;">Email</td>
                                                <td style="padding:4px 0;font-size:14px;color:#333333;">{{ $vi->email }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;font-size:15px;color:#333333;line-height:1.6;">
                                Pour toute question :
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

                    {{-- Pied de page --}}
                    <tr>
                        <td align="center" style="background-color:#f0f4fa;padding:16px 40px;">
                            <p style="margin:0;font-size:11px;color:#aaaaaa;">
                                Cet email a été envoyé automatiquement — merci de ne pas y répondre directement.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
