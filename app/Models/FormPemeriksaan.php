<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormPemeriksaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'form_pemeriksaan';

    protected $fillable = [
        'nomor_form',
        'user_id',
        'pengguna_employee_id',
        'asset_id',
        'site_location',
        'location_detail',
        'kondisi',
        'kondisi_keterangan',
        'notes',
        'tindakan_categories',
        'tindakan_solution',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'tindakan_categories' => 'array',
    ];

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pengguna_employee_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_location', 'id_site');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FormPemeriksaanItem::class);
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(FormApproval::class, 'approvable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(FormAttachment::class, 'attachable');
    }
}
