<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actualite extends Model
{
    protected $fillable = ['operateur_id', 'titre', 'lien'];

    public function operateur()
    {
        return $this->belongsTo(Operateur::class);
    }
}