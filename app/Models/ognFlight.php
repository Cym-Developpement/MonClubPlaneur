<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\aircraft;

/**
 * Modèle représentant un vol OGN (Open Glider Network) dans le système
 * 
 * @property int $id Identifiant unique du vol OGN
 * @property string $date Date du vol
 * @property array $data Données brutes du vol au format JSON
 * @property bool $imported Indique si le vol a été importé
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class ognFlight extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['date', 'data'];

    protected $casts = [
        'data' => 'array'
    ];

    /**
     * Récupère les données de vol depuis l'API OGN pour un aéroport et une date donnés
     *
     * @param string $airport Code OACI de l'aéroport
     * @param string|null $date Date au format Y-m-d (aujourd'hui par défaut)
     * @return void
     */
    public static function getDataFromApi($airport, $date = null)
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }

        $data = json_decode(file_get_contents("https://flightbook.glidernet.org/api/logbook/$airport/$date"), true);
        if (!is_null($data) && isset($data['flights']) && count($data['flights']) > 0) {

            $ogn = ognFlight::firstOrCreate([
                'date' => $date
            ]);

            $ogn->data = $data;
            $ogn->save();
        }
    }

    /**
     * Compte le nombre de vols non importés
     *
     * @return int Nombre de vols non importés
     */
    public static function getNbNotImported()
    {
        return count(self::where('imported', 0)->get());
    }

    /**
     * Récupère l'aéronef correspondant à une adresse OGN
     *
     * @param string $address Adresse OGN de l'aéronef
     * @return aircraft|null L'aéronef correspondant ou null si non trouvé
     */
    public function getAircraft($address)
    {
        return aircraft::where('ognAddress', $address)->first();
    }

    /**
     * Retourne la liste des vols avec leurs informations détaillées
     *
     * @return array Liste des vols avec leurs aéronefs et appareils associés
     */
    public function getFlightsAttribute()
    {
        $flights = [];

        foreach ($this->data['flights'] as $flight) {
            $flights[] = ['flight' => $flight, 'aircraft' => $this->getAircraft($this->data['devices'][$flight['device']]), 'device' => $this->data['devices'][$flight['device']]];
        }

        return $flights;
    }
}
