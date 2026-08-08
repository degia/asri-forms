<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormPerawatanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_perawatan_id',
        'template_item_id',
        'category',
        'name',
        'status',
        'keterangan',
        'sort_order',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormPerawatan::class, 'form_perawatan_id');
    }

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplateItem::class, 'template_item_id');
    }
}
