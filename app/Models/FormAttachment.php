<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FormAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'form_item_id',
        'file_path',
        'file_type',
        'caption',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
