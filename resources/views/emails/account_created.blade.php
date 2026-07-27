<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ouverture de votre compte</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                    {{-- En-tête --}}
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
                                Bonjour <strong>{{ $userName }}</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:15px;color:#333333;line-height:1.6;">
                                Votre compte vient d'être ouvert sur l'application de gestion du {{ $nomCourt }}.
                                Vous pourrez y consulter vos vols, le solde de votre compte et vos factures.
                            </p>
                            <p style="margin:0 0 8px;font-size:15px;color:#333333;line-height:1.6;">
                                Votre identifiant de connexion est votre adresse e-mail :
                            </p>
                            <p style="margin:0 0 24px;font-size:15px;">
                                <strong style="color:#1a3a6b;">{{ $userEmail }}</strong>
                            </p>
                            <p style="margin:0 0 24px;font-size:15px;color:#333333;line-height:1.6;">
                                Pour choisir votre mot de passe, cliquez sur le bouton ci-dessous et saisissez
                                cette adresse e-mail : vous recevrez un lien permettant de le définir.
                            </p>
                            <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td align="center" style="border-radius:4px;background-color:#1a3a6b;">
                                        <a href="{{ $passwordUrl }}" style="display:inline-block;color:#ffffff;font-size:14px;font-weight:bold;text-decoration:none;padding:12px 28px;border-radius:4px;">
                                            Définir mon mot de passe
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0;font-size:14px;color:#666666;line-height:1.6;">
                                Une fois votre mot de passe défini, connectez-vous à tout moment sur
                                <a href="{{ $appUrl }}" style="color:#1a3a6b;">{{ $appUrl }}</a>.
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
                                Bons vols,<br>
                                <strong style="color:#1a3a6b;">{{ $nomCourt }}</strong>
                                @if($emailClub)
                                    <br><a href="mailto:{{ $emailClub }}" style="color:#1a3a6b;">{{ $emailClub }}</a>
                                @endif
                            </p>
                        </td>
                    </tr>
                    {{-- Pied de page --}}
                    <tr>
                        <td align="center" style="background-color:#f0f4fa;padding:16px 40px;">
                            <p style="margin:0 0 6px;font-size:11px;color:#aaaaaa;">
                                Cet email a été envoyé automatiquement par l'application MonClubPlaneur — merci de ne pas y répondre directement.
                            </p>
                            <p style="margin:0;font-size:11px;color:#cccccc;">
                                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                                <a href="{{ $passwordUrl }}" style="color:#1a3a6b;word-break:break-all;">{{ $passwordUrl }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
