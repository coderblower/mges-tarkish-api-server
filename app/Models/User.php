<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
//    public function role()
//    {
//        return $this->belongsTo(Role::class);
//    }
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'id', 'user_id');
    }
    public function report()
    {
        return $this->belongsTo(CandidateMedicalTest::class, 'id', 'user_id');
    }
    public function preskilled()
    {
        return $this->belongsTo(PreSkilledTest::class, 'id', 'user_id');
    }
    public function skill()
    {
        return $this->belongsTo(SkillTest::class, 'id', 'user_id');
    }
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'id', 'user_id');
    }
    public function medical_center()
    {
        return $this->belongsTo(User::class, 'id', 'medical_center_id');
    }
    public function createdby()
    {
        return $this->belongsTo(User::class,'created_by', 'id');
    }
    public function child()
    {
        return $this->hasMany(User::class,'created_by', 'id');
    }
    public function candidateMedicalTests()
    {
        return $this->hasMany(CandidateMedicalTest::class, 'user_id');
    }

}
