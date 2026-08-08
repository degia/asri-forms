<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'nik';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'nik',
        'site',
        'directorate_id',
        'divisi_id',
        'departement_id',
        'sub_departement_id',
        'position_id',
        'no_telepon',
        'email',
        'akun_login',
        'date_resign',
        'status',
    ];

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_RESIGNED = 'Resigned';

    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (empty($employee->nik)) {
                $employee->nik = static::nextAvailableNik();
            }
        });

        static::deleting(function (Employee $employee) {
            $employee->email = null;
            User::where('nik', $employee->nik)->update(['nik' => null]);
        });
    }

    private static function nextAvailableNik(): string
    {
        $last = static::withTrashed()
            ->where('nik', 'like', 'NIK-%')
            ->orderByRaw('LENGTH(nik) DESC, nik DESC')
            ->value('nik');

        $sequence = $last ? ((int) substr($last, 4)) + 1 : 1;

        do {
            $candidate = 'NIK-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (static::withTrashed()->where('nik', $candidate)->exists());

        return $candidate;
    }

    public function siteDetail(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site', 'id_site');
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function subDepartement(): BelongsTo
    {
        return $this->belongsTo(SubDepartement::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'nik', 'nik');
    }

    public function assignedAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_employee_id');
    }

    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(FormPemeriksaan::class, 'pengguna_employee_id');
    }

    public function perawatan(): HasMany
    {
        return $this->hasMany(FormPerawatan::class, 'pengguna_employee_id');
    }

    public function pengembalian(): HasMany
    {
        return $this->hasMany(FormPengembalian::class, 'pengguna_employee_id');
    }

    public function getSiteNameAttribute(): ?string
    {
        return $this->siteDetail?->site ?? $this->site;
    }

    public function getOrganizationPathAttribute(): ?string
    {
        return collect([
            $this->directorate?->name,
            $this->divisi?->name,
            $this->departement?->name,
            $this->subDepartement?->name,
        ])
            ->filter()
            ->implode(' / ') ?: null;
    }
}
