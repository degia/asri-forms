<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $primaryKey = 'email';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'status',
        'theme_preference',
        'locale',
        'signature_path',
    ];

    public const STATUS_ACTIVE = 'Enable';

    public const STATUS_RESIGNED = 'Disable';

    public const LOCALE_ID = 'id';

    public const LOCALE_EN = 'en';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->nik !== null) {
                Employee::where('nik', $user->nik)->update(['email' => null]);
            }
        });
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id', 'email');
    }

    public function assignedAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_employee_id', 'nik');
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class, 'nik', 'nik');
    }

    public function syncEmployeeLink(): void
    {
        if (empty($this->nik)) {
            return;
        }

        $employee = Employee::where('nik', $this->nik)->first();

        if (! $employee) {
            $employee = Employee::create([
                'name' => $this->name,
                'nik' => $this->nik,
                'email' => $this->email,
                'status' => Employee::STATUS_ACTIVE,
                'akun_login' => $this->status === self::STATUS_RESIGNED ? 'No Access' : 'Connect',
            ]);

            return;
        }

        $employee->update([
            'akun_login' => $this->status === self::STATUS_RESIGNED ? 'No Access' : 'Connect',
        ]);
    }

    public function getSiteNameAttribute(): ?string
    {
        return $this->employee?->siteName;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
