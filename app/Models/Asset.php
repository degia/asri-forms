<?php

namespace App\Models;

use App\Livewire\Dashboard\Index as DashboardIndex;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Picqer\Barcode\BarcodeGeneratorSVG;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kategori',
        'brand',
        'tipe',
        'nama_perangkat',
        'no_serial',
        'spesifikasi',
        'foto',
        'no_asset',
        'qr_code',
        'status',
        'operating_unit',
        'site_location_asset',
        'assigned_employee_id',
    ];

    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }

        return '/storage/'.ltrim($this->foto, '/');
    }

    public function getBarcodeSvgAttribute(): ?string
    {
        if (empty($this->no_asset)) {
            return null;
        }

        $generator = new BarcodeGeneratorSVG;
        $svg = $generator->getBarcode($this->no_asset, BarcodeGeneratorSVG::TYPE_CODE_128);

        // Double height, viewBox, and rect heights
        $svg = preg_replace_callback('/height="(\d+)"/', function ($m) {
            return 'height="'.($m[1] * 2).'"';
        }, $svg, 1);

        $svg = preg_replace_callback('/viewBox="0 0 (\d+) (\d+)"/', function ($m) {
            return 'viewBox="0 0 '.$m[1].' '.($m[2] * 2).'"';
        }, $svg, 1);

        $svg = preg_replace('/<rect ([^>]*)height="(\d+)"\s*\/>/', '<rect $1height="60" />', $svg);

        return $svg;
    }

    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(FormPemeriksaan::class);
    }

    public function perawatan(): HasMany
    {
        return $this->hasMany(FormPerawatan::class);
    }

    public function operatingUnitSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'operating_unit', 'id_site');
    }

    public function siteAsset(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_location_asset', 'id_site');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    protected static function booted(): void
    {
        static::saved(fn () => DashboardIndex::clearAllDashboardCache());
        static::deleted(function (Asset $asset) {
            FormPemeriksaan::where('asset_id', $asset->id)->where('status', '!=', 'selesai')->each(fn ($f) => $f->delete());
            FormPerawatan::where('asset_id', $asset->id)->where('status', '!=', 'selesai')->each(fn ($f) => $f->delete());
            FormPengembalianItem::where('asset_id', $asset->id)->delete();
            DashboardIndex::clearAllDashboardCache();
        });
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->assigned_employee_id !== null;
    }

    public function getLastPemeriksaanAtAttribute(): ?Carbon
    {
        return $this->last_pemeriksaan_at_raw
            ? Carbon::parse($this->last_pemeriksaan_at_raw)
            : null;
    }

    public function getLastPerawatanAtAttribute(): ?Carbon
    {
        return $this->last_perawatan_at_raw
            ? Carbon::parse($this->last_perawatan_at_raw)
            : null;
    }
}
