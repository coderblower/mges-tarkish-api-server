<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{


    protected $fillable = [
        'passport_all_page',
        'cv',
        'resume',
        'birth_certificate'
    ];

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
    public function country()
    {
        return $this->belongsTo(Country::class, 'country'); // Adjust 'country_id' if the column name is different
    }

}
