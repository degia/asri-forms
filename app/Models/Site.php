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
}
