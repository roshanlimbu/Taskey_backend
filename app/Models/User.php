<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Role constants
    const ROLE_MASTER_ADMIN = 0;
    const ROLE_COMPANY_OWNER = 1;
    const ROLE_PROJECT_LEAD = 2;
    const ROLE_USER = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'github_id',
        'name',
        'email',
        'password',
        'github_token',
        'github_refresh_token',
        'role',
        'company_id',
        'profile_image',
        'dev_role',
        'is_user_verified',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'github_token',
        'github_refresh_token',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'integer', // Cast role as integer
    ];

    /**
     * Check if the user is a master admin (role 0).
     *
     * @return bool
     */
    public function isMasterAdmin()
    {
        return $this->role === self::ROLE_MASTER_ADMIN;
    }

    /**
     * Check if the user is a company owner.
     *
     * @return bool
     */
    public function isCompanyOwner()
    {
        return $this->role === self::ROLE_COMPANY_OWNER;
    }

    /**
     * Check if the user is a project lead/admin.
     *
     * @return bool
     */
    public function isProjectLead()
    {
        return $this->role === self::ROLE_PROJECT_LEAD;
    }

    /**
     * Check if the user has admin access (master admin or company owner).
     *
     * @return bool
     */
    public function hasAdminAccess()
    {
        return $this->isMasterAdmin() || $this->isCompanyOwner();
    }

    /**
     * Check if the user is a regular user.
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Get projects associated with this user
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id');
    }

    /**
     * Get tasks assigned to this user
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user', 'user_id', 'task_id');
    }


    /**
     * Get the company associated with the user.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
