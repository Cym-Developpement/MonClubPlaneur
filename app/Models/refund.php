<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\refundCategory;

/**
 * Modèle représentant un remboursement dans le système
 * 
 * @property int $id Identifiant unique du remboursement
 * @property int $idUser ID de l'utilisateur concerné
 * @property int $category ID de la catégorie de remboursement
 * @property string $observation Observation ou description
 * @property string $file Nom du fichier justificatif
 * @property int $time Timestamp de la demande
 * @property float $value Montant du remboursement
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class refund extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'refund';

    /**
     * Retourne l'intitulé formaté du remboursement
     * Combine la catégorie et l'observation si nécessaire
     *
     * @return string Intitulé du remboursement
     */
    public function getIntituleAttribute()
    {
    	if ($this->category > 0) {
    		$category = refundCategory::find($this->category);
    		return 'Achat '.$category->name;
    	} else {
    		return 'Achat autres : '.$this->observation;
    	}
    	
    }

    /**
     * Génère le nom de fichier formaté pour le justificatif
     * Format : nom_utilisateur_categorie_date.extension
     *
     * @return string Nom de fichier formaté
     */
    public function getFilenameAttribute()
    {
    	$user = User::find($this->idUser);
    	
    	if ($this->category > 0) {
    		$category = refundCategory::find($this->category)->name;
    	} else {
    		$category =  'Autres achat';
    	}
    	
    	$date = date('d-m-Y', $this->time);
    	$extension = explode('.', $this->file)[count(explode('.', $this->file))-1];
    	return $user->name.'_'.$category.'_'.$date.'.'.$extension;
    }
}
