<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormPemeriksaanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_pemeriksaan_id',
        'template_item_id',
        'category',
        'name',
        'status',
        'value',
        'keterangan',
        'full_charge_capacity',
        'design_capacity',
        'sort_order',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormPemeriksaan::class, 'form_pemeriksaan_id');
    }

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplateItem::class, 'template_item_id');
    }
}
