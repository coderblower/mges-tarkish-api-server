<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quota extends Model
{
    use HasFactory;
    public function country()
    {
        return $this->belongsTo(Country::class,'country_id', 'id');
    }
    public function agent()
    {
        return $this->belongsTo(User::class,'agent', 'id');
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class,'designation_id', 'id');
    }
}
