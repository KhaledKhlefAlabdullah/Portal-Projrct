<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Notifications\Notification;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid, SoftDeletes;

    protected $keyType = 'string';
    protected $primaryKey = 'id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'industrial_area_id',
        'email',
        'stakeholder_type',
        'password',
        'is_active',
        'deleted_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function user_profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function portal_settings(): HasMany
    {
        return $this->hasMany(PortalSetting::Class, 'user_id');
    }

    public function pages()
    {
        return $this->hasMany(Page::class, 'user_id');
    }

    public function participating_entities()
    {
        return $this->hasMany(ParticipatingEntity::class, 'user_id');
    }

    public function contact_us_messages()
    {
        return $this->hasMany(ContactUsMessage::class, 'user_id');
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_members', 'user_id', 'chat_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function dams(): HasMany
    {
        return $this->hasMany(Dam::class, 'user_id');
    }

    public function monitoring_points(): HasMany
    {
        return $this->hasMany(MonitoringPoint::class, 'user_id');
    }

    public function stakeholder()
    {
        return $this->hasOne(Stakeholder::class, 'user_id');
    }

    public function industrial_area()
    {
        return $this->belongsTo(IndustrialArea::class, 'industrial_area_id');
    }
}