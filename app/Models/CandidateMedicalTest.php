<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateMedicalTest extends Model
{
    use HasFactory;
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
//    public function medical()
//    {
//        return $this->belongsTo(MedicalTestList::class, 'medical_id', 'id');
//    }
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id', 'id');
    }
    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by', 'id');
    }
}
