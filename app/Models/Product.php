<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Produit public payable par CB (ex : baptême planeur/ULM).
 *
 * @property int $id
 * @property string $title       Titre affiché
 * @property string $slug        Slug pour l'URL publique (/payer/{slug})
 * @property string|null $description
 * @property int $amount_cts     Montant en centimes
 * @property bool $active        Lien actif ou non
 * @property bool $show_on_site  Affiché sur la page publique des produits
 * @property string|null $image_path
 * @property string|null $email_extra Texte additionnel dans l'email de confirmation
 */
class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'amount_cts',
        'active',
        'show_on_site',
        'image_path',
        'email_extra',
    ];

    protected $casts = [
        'amount_cts'   => 'integer',
        'active'       => 'boolean',
        'show_on_site' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Génère un slug unique à partir du titre si non fourni
        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = self::uniqueSlug($product->title, $product->id);
            }
        });
    }

    /**
     * Construit un slug unique (suffixe -2, -3, ... en cas de collision).
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'produit';
        $slug = $base;
        $i    = 2;

        while (self::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Prix formaté (ex : "50,00 €").
     */
    public function getAmountEurAttribute(): string
    {
        return number_format($this->amount_cts / 100, 2, ',', ' ') . ' €';
    }

    /**
     * URL publique de paiement.
     */
    public function getPublicUrlAttribute(): string
    {
        return url('/payer/' . $this->slug);
    }
}
