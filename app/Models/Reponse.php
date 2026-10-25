<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    protected $fillable = ['sondage_id', 'user_id', 'sms', 'voix', 'internet', 'credit', 'satisfait'];

    protected $casts = ['satisfait' => 'boolean'];

    public function sondage()
    {
        return $this->belongsTo(Sondage::class);
    }

    public function visiteur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}