<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departement extends Model
{
    protected $fillable = [
        'divisi_id',
        'name',
        'code',
    ];

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function subDepartements(): HasMany
    {
        return $this->hasMany(SubDepartement::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
