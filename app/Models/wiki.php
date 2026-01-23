<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Encryption\Encrypter;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modèle représentant une page du wiki
 * 
 * @property int $id Identifiant unique de la page
 * @property string $pageName Nom de la page
 * @property string|null $parent Page parente
 * @property string $content Contenu de la page
 * @property int $levelRead Niveau requis pour la lecture
 * @property int $levelWrite Niveau requis pour l'écriture
 * @property string|null $UUID Identifiant unique universel de la page
 * @property \DateTime $deleted_at Date de suppression (soft delete)
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class wiki extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'wiki';
    public static $start = 'Accueil';
    public static $defaultLevelRead = 0;
    public static $defaultLevelWrite = 1;
    public static $baseUrl = '/wiki/';
    public static $passwordLength = 16;

     /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        $start = wiki::where('pageName', self::$start)->first();
        if (is_null($start)) {
            $start = new wiki();
            $start->pageName = self::$start;
            $start->parent = null;
            $start->content = 'Page d\'accueil du WIKI';
            $start->levelRead = self::$defaultLevelRead;
            $start->levelWrite = self::$defaultLevelWrite;
            $start->save();
        }
    }

    /**
     * Déchiffre un contenu avec un mot de passe donné
     *
     * @param string $password Mot de passe pour le déchiffrement
     * @param string $encrypted Contenu chiffré
     * @return string Contenu déchiffré ou message d'erreur
     */
    public static function decrypt($password, $encrypted)
    {
        $encrypter = new Encrypter(self::password2AESKey($password), 'AES-256-CBC');
        try {
            return $encrypter->decryptString($encrypted);
        } catch (DecryptException $e) {
            return 'Mot de passe principal invalide !';
        }
    }

    /**
     * Convertit un mot de passe en clé AES
     *
     * @param string $password Mot de passe à convertir
     * @return string Clé AES générée
     */
    private static function password2AESKey($password)
    {
        // Utiliser une variable d'environnement pour la clé maître
        // Si non définie, utiliser une valeur par défaut (à changer en production)
        $masterKey = env('WIKI_MASTER_KEY', 'CHANGEZ_CETTE_CLE_EN_PRODUCTION');
        return substr($password.substr($masterKey, strlen($password)), 0, 32);
    }

    /**
     * Récupère le mot de passe principal du wiki
     *
     * @return string Clé AES du wiki
     */
    private static function getPassword()
    {
        if (!Storage::exists('wiki.key')) {
            $password = Str::random(self::$passwordLength);
            Storage::put('wiki.key', $password);
        }
        return self::password2AESKey(Storage::get('wiki.key'));
    }

    /**
     * Récupère la liste des noms de pages pour le remplacement
     * Filtre selon le niveau d'accès de l'utilisateur
     *
     * @return array Liste des pages accessibles
     */
    public static function getPageNameReplace()
    {
        $userLevel = self::getUserLevel();
        $data = DB::table('wiki')
            ->select('id', 'pageName')
            ->whereNull('deleted_at')
            ->where('levelRead', '<=', $userLevel)
            ->orderBy('pageName', 'DESC')
            ->distinct()
            ->get();
        return $data;
    }

    /**
     * Génère des UUID pour toutes les pages qui n'en ont pas
     *
     * @return void
     */
    public static function generateAllUUID()
    {
        $pages = wiki::whereNull('UUID')->get();
        foreach ($pages as $page) {
            $page->UUID = $page->genUUID();
            $page->save();
        }
    }

    /**
     * Génère un UUID pour la page courante
     *
     * @return string UUID généré
     */
    private function genUUID()
    {
        return sha1($this->pageName.$this->id.time());
    }

    /**
     * Récupère la dernière révision de la page
     *
     * @return wiki|null Dernière révision de la page
     */
    public function getLastRevisionAttribute()
    {
        return wiki::where('UUID', $this->UUID)->orderBy('id', 'DESC')->first();
    }

    public function getUrlAttribute()
    {
        return self::$baseUrl.$this->id.'/'.urlencode($this->pageName);
    }

    public function getLastUrlAttribute()
    {
        $last = $this->last_revision;
        return (!is_null($last)) ? $last->url : $this->parent->url ;
    }

    public function getMenuArrayAttribute()
    {
        return [$this->pageName, $this->id, $this->url];
    }


    public function getParentPageAttribute()
    {
        return wiki::find($this->parent);
    }

    public function getChildStructAttribute()
    {
        $struct = [];
        $childs = wiki::where('parent', $this->id)
                        ->whereNotNull('parent')
                        ->where('levelRead', '<=', wiki::getUserLevel())
                        ->orderBy('pageName', 'ASC')
                        ->get();
        $struct = [];
        foreach ($childs as $child) {
            $struct[$child->pageName] = [$child->menu_array, $child->child_struct];
        }

        return $struct;
    }

    public function getChildListAttribute()
    {
        
        return wiki::where('parent', $this->id)
                        ->whereNotNull('parent')
                        ->where('levelRead', '<=', wiki::getUserLevel())
                        ->orderBy('pageName', 'ASC')
                        ->get();
    }

    public function getNavStructAttribute()
    {
        $struct = [];
        $base = wiki::where('pageName', self::$start)->first();
        $childs = wiki::where('parent', $base->id)
                        ->whereNotNull('parent')
                        ->where('levelRead', '<=', wiki::getUserLevel())
                        ->orderBy('pageName', 'ASC')
                        ->get();
        $struct = [];
        foreach ($childs as $child) {
            $struct[$child->pageName] = [$child->menu_array, $child->child_struct];
        }

        return [$base->pageName => [$base->menu_array, $struct]];
    }

    public function getNavThreadAttribute()
    {
        $struct = [];
        if (!is_null($this->parent_page)) {
            $parent = $this->parent_page;
            $i = 1;
            if ($parent->pageName != self::$start) {
                $struct[] = $parent->menu_array;
            }
            while (!is_null($parent->parent) && $parent->parent !== $parent->id && $i <= 255) {
                $parent = $parent->parent_page;
                if ($parent->pageName != self::$start) {
                    $struct[] = $parent->menu_array;
                }
                
                $i ++;
            }
        }
        return array_reverse($struct);
    }

    public function getDeleteAlertAttribute()
    {
        $message = 'Etes vous sur de vouloir supprimer la page : \n';
        $message .= self::$start;
        foreach ($this->nav_thread as $element) {
            $message .= ' > '.$element[0];
        }
        $message .= ' > '.$this->pageName;
        return $message;
    }

    public static function getNavElement($elements)
    {
        $html = '';
        if (!isset($elements[1])) {
            dd($elements);
        }
        
        foreach ($elements[1] as $key => $element) {
            if (count($element[1]) > 0) {
                $sub = view('wiki.nav.elementWithSub', ['element' => $element[0]]);
                $sub = str_replace('SUBMENU', self::getNavElement($element), $sub);
                $html .= $sub;
            } else {
                $html .= view('wiki.nav.elementWithoutSub', ['element' => $element[0]]);
            }
        }
        
        return $html;
    }


    public function getNavMenuThreadAttribute()
    {
        $struct = $this->nav_struct;
        return self::getNavElement($struct[self::$start]);
    }

    public function getStartMenuArrayAttribute()
    {
        $start = wiki::where('pageName', self::$start)->first();
        return $start->menu_array;
    }

    public static function createPageLink($id, $pageName)
    {
        return '<a href="/wiki/'.$id.'/'.urlencode($pageName).'">'.$pageName.'</a>';
    }

    public static function getCurrentUserName()
    {
        return Auth::user()->name;
    }

    public static function getUserLevel()
    {
        
        return (Auth::user()->isAdmin == 1 && Auth::user()->state == 1) ? 1 : 0 ;
    }

    public function getIsWritableAttribute()
    {
        return $this->levelWrite <= wiki::getUserLevel();
    }

    public function getIsReadableAttribute()
    {
        return $this->levelRead <= wiki::getUserLevel();
    }

    public function getIsDeletableAttribute()
    {
        return ($this->pageName !== self::$start && $this->is_writable);
    }

    public function getActiveAttribute()
    {
        return (is_null($this->deleted_at)) ? true : false ;
    }

    public function getLastUpdateDateFrAttribute()
    {
        $dateTime = explode(' ', $this->updated_at);
        $date = explode('-', $dateTime[0]);
        return $date[2].'/'.$date[1].'/'.$date[0].' '.$dateTime[1];
    }

    public function savePage()
    {
        if ($this->is_writable) {
            $this->save();
        }
    }

    private function replacePageName($content)
    {
        $replace = self::getPageNameReplace();
        foreach ($replace as $link) {
            $content = str_replace(' '.$link->pageName.' ', ' '.self::createPageLink($link->id, $link->pageName).' ', $content);
            $content = str_replace(' '.strtolower($link->pageName).' ', ' '.self::createPageLink($link->id, strtolower($link->pageName)).' ', $content);
        }
        return $content;
    }

    private function cryptPassword($content)
    {
        preg_match_all('/PASS\[\[.+\]\]/m', $content, $array);
        $encrypter = new Encrypter(self::getPassword(), 'AES-256-CBC');
        if (isset($array[0])) {
            foreach ($array[0] as $password) {
                $strReplacePassword = $password;
                $password = trim($password);
                $password = str_replace('PASS[[', '', $password);
                $password = substr($password, 0, (strlen($password)-2));
                
                $encrypted = $encrypter->encryptString($password);
                $replace = 'PASSENCRYPTED[['.$encrypted.']]';
                $content = str_replace($strReplacePassword, $replace, $content);
            }
        }
        return $content;
    }

    private function replacePasswordLink($content)
    {
        //dd($content);
        preg_match_all('/PASSENCRYPTED\[\[[^\[\]]+\]\]/m', $content, $array);
        if (isset($array[0])) {
            $id = 1;
            foreach ($array[0] as $password) {
                $strReplacePassword = $password;
                $password = trim($password);
                $password = str_replace('PASSENCRYPTED[[', '', $password);
                $encrypted = substr($password, 0, (strlen($password)-2));
                $replace = view('wiki.passwordLink', ['encrypted' => $encrypted, 'id' => $id]);
                $id ++;
                $content = str_replace($strReplacePassword, $replace, $content);
            }
        }
        return $content;
    }

    public function getHtmlAttribute()
    {
        $content = $this->content;
        $content = $this->replacePageName($content);
        $content = $this->replacePasswordLink($content);
        return ($this->is_readable) ? $content : '' ;
    }

    public function getFormAttribute()
    {
        return ($this->is_writable) ? $this->content : '' ;
    }

    public function updateContent($newContent)
    {
        //dd($newContent);
        $newContent = $this->cryptPassword($newContent);
        $update = $this->replicate();
        $update->content = $newContent;
        $update->userName = self::getCurrentUserName();
        $update->save();
        DB::statement("UPDATE wiki SET parent=".$update->id." WHERE parent=".$this->id);
        $this->delete();
        return $update->url;
    }

    public function restoreRevision()
    {
        $last = $this->last_revision;
        return $last->updateContent($this->content);
    }

    public static function newPage($current, $name)
    {
        $current = wiki::find($current);
        $new = null;
        $name = ($name == self::$start) ? self::$start.' ' : $name ;
        if ($current->is_writable) {
            $new = new wiki();
            $new->pageName = $name;
            $new->parent = $current->id;
            $new->content = 'Nouvelle page crée.';
            $new->levelRead = self::$defaultLevelRead;
            $new->levelWrite = self::$defaultLevelWrite;
            $new->userName = self::getCurrentUserName();
            $new->save();
            $new->UUID = $new->genUUID();
            $new->save();
        }
        return $new;
    }

    public function getRevisionListAttribute()
    {
        return wiki::withTrashed()->where('UUID', $this->UUID)->orderBy('id', 'DESC')->get();
    }
}
