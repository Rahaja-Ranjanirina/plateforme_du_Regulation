<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['expediteur_id', 'operateur_id', 'sujet', 'contenu', 'lu'];

    protected $casts = ['lu' => 'boolean'];

    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    public function operateur()
    {
        return $this->belongsTo(Operateur::class);
    }
}