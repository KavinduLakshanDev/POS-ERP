<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'username',
        'email',
        'password',
        'previous_password1',
        'previous_password2',
        'previous_password3',
        'first_name',
        'last_name',
        'phone',
        'user_type',
        'company_code',
        'section_code',
        'delivery_section_code',
        'role_id',
        'avatar_url',
        'is_active',
        'is_verified',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'password_reset_token',
    ];

    protected $appends = ['name'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_code', 'section_code');
    }

    public function deliverySection()
    {
        return $this->belongsTo(Section::class, 'delivery_section_code', 'section_code');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hasPermission($permissionSlug)
    {
        if ($this->user_type === 'super_admin') {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        if ($this->role->level === 'super_admin' || $this->role->level === 'company_admin') {
            return true;
        }

        return $this->role->permissions->contains('slug', $permissionSlug);
    }

    public function hasAnyPermission(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function isSuperAdmin()
    {
        return $this->user_type === 'super_admin';
    }

    public function serviceJobs()
    {
        return $this->hasMany(ServiceJob::class, 'assigned_technician_id');
    }

    /**
     * Vehicles assigned to this user (sales rep / driver)
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'assigned_user_id');
    }

    /**
     * Delivery routes assigned to this user (many-to-many)
     */
    public function routes()
    {
        return $this->belongsToMany(DeliveryRoute::class, 'delivery_route_user', 'user_id', 'delivery_route_id')
            ->withTimestamps();
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // When a user's password is updated, sync it with any company sharing the same email
        static::updated(function ($user) {
            if ($user->isDirty('password')) {
                \Illuminate\Support\Facades\DB::table('companies')
                    ->where('email', $user->email)
                    ->update(['password' => $user->password]);
            }
        });
    }
}

