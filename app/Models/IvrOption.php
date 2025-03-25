<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IvrOption extends Model
{
    use HasFactory;


    protected $fillable = ['option_number', 'description', 'forward_number','status'];

}




