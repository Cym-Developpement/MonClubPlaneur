<?php
namespace App;

use App\Models\transaction;
use App\Models\User;
use App\Models\usersData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Import de membres depuis un fichier CSV (export d'inscriptions).
 *
 * Le fichier est reconnu par le libellé de ses colonnes (et non par leur
 * position), afin de rester tolérant à un changement d'ordre ou à l'absence
 * d'une colonne facultative.
 */
class MemberImport
{
    /**
     * Correspondance libellé de colonne (normalisé) => clé interne.
     *
     * @var array<string, string>
     */
    public static array $columnsMap = [
        'id'               => 'externalId',
        'nom'              => 'lastName',
        'prenom'           => 'firstName',
        'email'            => 'email',
        'mail'             => 'email',
        'qualite'          => 'quality',
        'datedenaissance'  => 'birthDate',
        'datenaissance'    => 'birthDate',
        'telephone'        => 'phone',
        'tel'              => 'phone',
        'nffvp'            => 'licence',
        'numerolicence'    => 'licence',
        'numerodelicence'  => 'licence',
        'licence'          => 'licence',
        'club'             => 'club',
        'statut'           => 'status',
        'dateinscription'  => 'registrationDate',
        'datedinscription' => 'registrationDate',
    ];

    /**
     * Colonnes indispensables pour qu'un fichier soit exploitable.
     * Clé = clé interne, valeur = libellé affiché en cas d'absence.
     *
     * @var array<string, string>
     */
    public static array $requiredColumns = [
        'lastName'  => 'Nom',
        'firstName' => 'Prénom',
        'email'     => 'Email',
    ];

    /**
     * Rôles proposés à l'import (identiques aux cases de la fiche membre).
     *
     * @var string[]
     */
    public static array $roles = [
        'Licence associative',
        'Elève',
        'Pilote',
        'Instructeur Planeur',
        'Instructeur ULM',
        'Remorqueur',
    ];

    /**
     * Données complémentaires enregistrées dans la table usersData.
     *
     * @var string[]
     */
    public static array $extraData = ['birthDate', 'phone', 'club', 'registrationDate', 'externalId'];

    /**
     * Analyse le contenu d'un fichier CSV et retourne les lignes prêtes à être
     * affichées dans l'écran de prévisualisation.
     *
     * @param string $content Contenu brut du fichier
     * @return array{rows: array<int, array<string, mixed>>, error: string|null}
     */
    public static function parse($content)
    {
        $content  = self::toUtf8($content);
        $lines    = preg_split("/\r\n|\n|\r/", $content);
        $header   = null;
        $rows     = [];
        $seen     = [];
        $index    = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $csv = str_getcsv($line, ';', '"', '');

            if (is_null($header)) {
                $header = self::mapHeader($csv);
                $missing = array_diff_key(self::$requiredColumns, array_flip($header));
                if (count($missing) > 0) {
                    return ['rows' => [], 'error' => 'Colonnes manquantes dans le fichier : ' . implode(', ', $missing) . '. Le fichier doit contenir au moins Nom, Prénom et Email.'];
                }
                continue;
            }

            $row = self::mapRow($header, $csv);
            if ($row['email'] === '' && $row['lastName'] === '' && $row['firstName'] === '') {
                continue;
            }

            $row['idx']      = $index;
            $row['name']     = self::formatName($row['lastName'], $row['firstName']);
            $row['role']     = self::qualityToRole($row['quality']);
            $row['blockers'] = self::blockers($row, $seen);

            $seen[]  = strtolower($row['email']);
            $rows[]  = $row;
            $index++;
        }

        if (is_null($header)) {
            return ['rows' => [], 'error' => 'Le fichier est vide.'];
        }

        return ['rows' => $rows, 'error' => null];
    }

    /**
     * Crée un membre à partir d'une ligne analysée.
     *
     * @param array<string, mixed> $row Ligne issue de parse()
     * @param string|null $role Rôle retenu par l'administrateur
     * @return User
     */
    public static function createUser($row, $role = null)
    {
        $role = in_array($role, self::$roles) ? $role : self::qualityToRole($row['quality'] ?? '');

        return DB::transaction(function () use ($row, $role) {
            $user                = new User();
            $user->email         = $row['email'];
            $user->name          = $row['name'] ?? self::formatName($row['lastName'] ?? '', $row['firstName'] ?? '');
            $user->licenceNumber = $row['licence'] ?? '';
            $user->password      = Hash::make(Str::random(32));
            $user->state         = 1;
            $user->isSupervisor  = Str::contains(Str::lower($role), 'instructeur') ? 1 : 0;
            $user->save();

            $user->saveAttr([$role]);

            foreach (self::$extraData as $key) {
                if (! isset($row[$key]) || $row[$key] === '') {
                    continue;
                }
                $data            = new usersData();
                $data->userId    = $user->id;
                $data->dataName  = $key;
                $data->dataValue = $row[$key];
                $data->save();
            }

            $account         = new transaction();
            $account->idUser = $user->id;
            $account->name   = 'Ouverture de compte';
            $account->value  = 0;
            $account->solde  = 0;
            $account->year   = date('Y');
            $account->time   = time();
            $account->save();

            return $user;
        });
    }

    /**
     * Liste les raisons pour lesquelles une ligne ne peut pas être importée.
     *
     * @param array<string, mixed> $row
     * @param string[] $seen Emails déjà rencontrés dans le fichier
     * @return string[]
     */
    public static function blockers($row, $seen = [])
    {
        $blockers = [];

        if ($row['email'] === '' || ! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $blockers[] = 'Email invalide';
        } elseif (in_array(strtolower($row['email']), $seen)) {
            $blockers[] = 'Doublon dans le fichier';
        } elseif (! is_null(User::where('email', $row['email'])->first())) {
            $blockers[] = 'Déjà existant';
        }

        if ($row['name'] === '') {
            $blockers[] = 'Nom manquant';
        }

        if ($row['status'] !== '' && ! Str::startsWith(self::normalize($row['status']), 'valid')) {
            $blockers[] = 'Inscription non validée';
        }

        return $blockers;
    }

    /**
     * Associe chaque colonne du fichier à une clé interne.
     *
     * @param string[] $csv Ligne d'entête
     * @return array<int, string>
     */
    private static function mapHeader($csv)
    {
        $header = [];
        foreach ($csv as $position => $label) {
            $key = self::normalize($label);
            if (isset(self::$columnsMap[$key])) {
                $header[$position] = self::$columnsMap[$key];
            }
        }

        return $header;
    }

    /**
     * Construit une ligne interne à partir d'une ligne du fichier.
     *
     * @param array<int, string> $header
     * @param string[] $csv
     * @return array<string, string>
     */
    private static function mapRow($header, $csv)
    {
        $row = array_fill_keys(array_values(self::$columnsMap), '');

        foreach ($header as $position => $key) {
            $row[$key] = isset($csv[$position]) ? trim($csv[$position]) : '';
        }

        $row['birthDate']        = self::formatDate($row['birthDate']);
        $row['registrationDate'] = self::formatDate($row['registrationDate']);
        $row['phone']            = self::formatPhone($row['phone']);
        $row['email']            = strtolower($row['email']);

        return $row;
    }

    /**
     * Met le nom en majuscules et le prénom en capitales initiales.
     *
     * @param string $lastName
     * @param string $firstName
     * @return string
     */
    private static function formatName($lastName, $firstName)
    {
        $lastName  = mb_strtoupper(trim($lastName), 'UTF-8');
        $firstName = mb_convert_case(mb_strtolower(trim($firstName), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        return trim($lastName . ' ' . $firstName);
    }

    /**
     * Convertit une date jj/mm/aaaa (éventuellement suivie d'une heure) au
     * format aaaa-mm-jj.
     *
     * @param string $date
     * @return string
     */
    private static function formatDate($date)
    {
        $date = trim($date);
        if ($date === '' || ! preg_match('#^(\d{2})/(\d{2})/(\d{4})#', $date, $matches)) {
            return $date;
        }

        if (! checkdate(intval($matches[2]), intval($matches[1]), intval($matches[3]))) {
            return $date;
        }

        return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
    }

    /**
     * Normalise un numéro de téléphone français (espaces retirés, +33 => 0).
     *
     * @param string $phone
     * @return string
     */
    private static function formatPhone($phone)
    {
        $phone = preg_replace('/[\s.\-]/', '', trim($phone));
        if (Str::startsWith($phone, '+33')) {
            $phone = '0' . substr($phone, 3);
        }

        return $phone;
    }

    /**
     * Déduit le rôle du club à partir de la qualité indiquée dans le fichier.
     *
     * @param string $quality
     * @return string
     */
    private static function qualityToRole($quality)
    {
        $quality = self::normalize($quality);

        if (Str::contains($quality, 'instructeur')) {
            return Str::contains($quality, 'ulm') ? 'Instructeur ULM' : 'Instructeur Planeur';
        }
        if (Str::contains($quality, 'remorqu')) {
            return 'Remorqueur';
        }
        if (Str::contains($quality, 'eleve')) {
            return 'Elève';
        }
        if (Str::contains($quality, 'associativ')) {
            return 'Licence associative';
        }

        return 'Pilote';
    }

    /**
     * Réduit un libellé à une forme comparable (minuscules, sans accent ni
     * caractère de ponctuation).
     *
     * @param string $value
     * @return string
     */
    private static function normalize($value)
    {
        $value = Str::ascii(trim($value));

        return preg_replace('/[^a-z0-9]/', '', strtolower($value));
    }

    /**
     * Convertit le contenu du fichier en UTF-8 quel que soit son encodage.
     *
     * @param string $content
     * @return string
     */
    private static function toUtf8($content)
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-15');
        }

        return $content;
    }
}
