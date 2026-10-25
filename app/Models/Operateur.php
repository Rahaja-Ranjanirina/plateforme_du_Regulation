<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operateur extends Model
{
    protected $fillable = ['nom', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sondages()
    {
        return $this->hasMany(Sondage::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function offres()
    {
        return $this->hasMany(Offre::class);
    }

    public function actualites()
    {
        return $this->hasMany(Actualite::class);
    }
}