<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalTest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'enrolled_by',
        'status',
        'file_path',
    ];
    
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'user_id', 'user_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by', 'id');
    }
}
