<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IvrOption extends Model
{
    use HasFactory;


    protected $fillable = ['option_number', 'description', 'forward_number','status' ,'branch_id', 'country_id'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
   
    // public function branch()
    // {
    //     return $this->belongsTo(branch::class);
    // }

   
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

}




