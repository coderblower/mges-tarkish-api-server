<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $casts = [
        'delete_files' => 'array',
    ];

    use HasFactory;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class,'designation_id', 'id');
    }
    public function medicalTests()
    {
        return $this->hasMany(CandidateMedicalTest::class, 'candidate_id');
    }
}
