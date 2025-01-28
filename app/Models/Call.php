<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;





    public function client()
{
    return $this->belongsTo(Client::class);
}

// public function queries()
// {
//     return $this->hasMany(Query::class);
// }

public function logs()
{
    return $this->hasMany(CallLog::class);
}

}
