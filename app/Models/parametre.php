<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un paramètre de configuration dans le système
 * 
 * @property int $id Identifiant unique du paramètre
 * @property string $nom Nom du paramètre
 * @property string $value Valeur du paramètre
 * @property string $type Type de données du paramètre
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class parametre extends Model
{
    use HasFactory;

    /**
     * Retourne le titre du paramètre extrait du nom
     * Format attendu du nom : "categorie-titre"
     *
     * @return string Titre du paramètre
     */
    public function getTitleAttribute()
    {
        $title = explode('-', $this->nom);
        return (isset($title[1])) ? $title[1] : $this->title;       
    }

    /**
     * Retourne la catégorie du paramètre extraite du nom
     * Format attendu du nom : "categorie-titre"
     *
     * @return string Catégorie du paramètre, 'Divers' par défaut
     */
    public function getCategorieAttribute()
    {
        $cat = explode('-', $this->nom);
        return (count($cat) > 1) ? $cat[0] : 'Divers';
    }

    /**
     * Récupère un paramètre existant ou en crée un nouveau avec une valeur par défaut
     *
     * @param string $nom Nom du paramètre
     * @param mixed $default Valeur par défaut
     * @return parametre Instance du paramètre
     */
    public static function getOrCreate($nom, $default)
    {
        $parametre = parametre::where('nom', $nom)->first(); 
        

        if (is_null($parametre)) {
            $parametre = new parametre();
            $parametre->nom = $nom;
            $parametre->type = gettype($default);
            settype($default, 'string');
            $parametre->value = $default;
            $parametre->save();
        }

        return $parametre;
    }

    /**
     * Récupère la valeur d'un paramètre avec le type approprié
     *
     * @param string $nom Nom du paramètre
     * @param mixed $default Valeur par défaut si le paramètre n'existe pas
     * @return mixed Valeur du paramètre convertie dans le bon type
     */
    public static function getValue($nom, $default)
    {
        $parametre = self::getOrCreate($nom, $default);
        $value = $parametre->value;
        settype($value, $parametre->type);
        return $value;
    }

    /**
     * Retourne la valeur du paramètre formatée pour JavaScript
     * Convertit notamment les booléens en 'true'/'false'
     *
     * @return string Valeur formatée pour JavaScript
     */
    public function getJsValueAttribute()
    {
        $value = $this->value;
        settype($value, $this->type);

        if ($this->type == 'boolean') {
            if ($value) {
                $value = 'true';
            } else {
                $value = 'false';
            }
        }

        return strval($value);
    }
}
