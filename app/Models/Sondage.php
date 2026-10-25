<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sondage extends Model
{
    protected $fillable = ['operateur_id', 'user_id', 'titre', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function operateur()
    {
        return $this->belongsTo(Operateur::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class);
    }
}