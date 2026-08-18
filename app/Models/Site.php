<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'sites';
    protected $primaryKey = 'id_site';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_site',
        'site',
        'buss',
        'id_corp',
        'country',
        'provincy',
        'city',
        'address',
        'url_maps',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Site $site) {
            Employee::where('site', $site->id_site)->each(fn($e) => $e->delete());
            Asset::where('operating_unit', $site->id_site)->each(fn($a) => $a->delete());
        });
    }
}
