<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    use HasFactory;



    public function call()
{
    return $this->belongsTo(Call::class);
}

public function officer()
{
    return $this->belongsTo(Officer::class);
}

}
