<?php

namespace App\Models;

class Property extends LegacyModel
{
    protected $table = 'property';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
