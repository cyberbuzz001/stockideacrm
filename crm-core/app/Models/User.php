<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'Admin', 'Manager', 'SBA', 'BA'
        'parent_id', // Reporting Manager
        'commission_rate',
        'lead_weight',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function team()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function targets()
    {
        return $this->hasMany(UserTarget::class);
    }

    public function messagesSent()
    {
        return $this->hasMany(EmployeeMessage::class, 'sender_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Lead::class, 'assigned_to', 'lead_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function currentTarget()
    {
        return $this->targets()->where('month_year', now()->format('Y-m'))->first();
    }

    /**
     * Get all cascading team IDs for reporting
     */
    public function getAllTeamIds()
    {
        $visited = [];
        return $this->collectTeamIds($visited);
    }

    private function collectTeamIds(array &$visited): array
    {
        if (in_array($this->id, $visited, true)) {
            return [];
        }
        $visited[] = $this->id;

        $ids = [$this->id];
        foreach ($this->team as $member) {
            $ids = array_merge($ids, $member->collectTeamIds($visited));
        }
        return array_values(array_unique($ids));
    }
    /**
     * Check if user has a specific permission based on the Role Access Matrix
     */
    public function hasPermission(string $module, string $action): bool
    {
        // Cache permissions to avoid repeated JSON decoding
        return \Illuminate\Support\Facades\Cache::remember("user_perm_{$this->role}_{$module}_{$action}", 60, function () use ($module, $action) {
            $matrix = json_decode(\App\Models\SystemSetting::get('role_access_matrix', '{}'), true);
            
            if (isset($matrix[$module][$action])) {
                return in_array($this->role, $matrix[$module][$action]);
            }

            // Admins have everything by default if not specified
            return $this->role === 'Admin';
        });
    }
}
