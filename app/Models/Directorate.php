<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Directorate extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function divisis(): HasMany
    {
        return $this->hasMany(Divisi::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
