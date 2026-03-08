<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailingLog extends Model
{
    protected $fillable = [
        'sent_by',
        'subject',
        'body',
        'filter',
        'recipient_count',
        'test_email',
    ];

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
