<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    protected $fillable = [
        'directorate_id',
        'name',
        'code',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function departements(): HasMany
    {
        return $this->hasMany(Departement::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
