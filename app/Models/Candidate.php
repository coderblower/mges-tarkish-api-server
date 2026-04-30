<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'passport_all_page',
        'cv',
        'resume',
        'birth_certificate',
        'designation_id', // ✅ FIX: missing but required for relation
        'country',        // (keep if your column is actually named "country")
    ];

    protected $casts = [
        'delete_files' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country'); 
        // ⚠️ better would be 'country_id' if possible
    }

    public function medicalTests()
    {
        return $this->hasMany(CandidateMedicalTest::class, 'candidate_id');
    }
}