<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormPengembalianItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_pengembalian_id',
        'asset_id',
    ];

    public function formPengembalian(): BelongsTo
    {
        return $this->belongsTo(FormPengembalian::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
