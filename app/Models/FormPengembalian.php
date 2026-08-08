<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormPengembalian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'form_pengembalian';

    protected $fillable = [
        'nomor_form',
        'teknisi_id',
        'pengguna_employee_id',
        'tanggal_pengembalian',
        'kondisi',
        'kelengkapan',
        'notes',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'tanggal_pengembalian' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pengguna_employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FormPengembalianItem::class);
    }
}
