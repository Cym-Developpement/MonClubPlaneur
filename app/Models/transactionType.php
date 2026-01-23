<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class transactionType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactionType';

    public function getNameYearAttribute()
    {
        return $this->name.' '.date('Y');
    }
}
