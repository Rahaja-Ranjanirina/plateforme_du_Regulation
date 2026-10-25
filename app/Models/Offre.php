<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $fillable = ['operateur_id', 'titre', 'description'];

    public function operateur()
    {
        return $this->belongsTo(Operateur::class);
    }
}