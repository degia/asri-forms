<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubDepartement extends Model
{
    protected $fillable = [
        'departement_id',
        'name',
        'code',
    ];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
