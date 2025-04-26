<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use HasFactory,
    SoftDeletes;

    protected $fillable = [
        'name',
        'channel',
        'module',
        'content',
        'placeholders',
        'owner_type',
        'owner_id',
    ];

    protected $casts = [
        'placeholders' => 'array',
    ];

    /**
     * Get the owner of the template (User, Vendor, Admin, etc.)
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * Replace the placeholders in the content with the provided data
     *
     * @param array $variables
     * @return string
     */
    public function getFormattedContent(array $variables)
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace(
                ["{{{$key}}}", "{%{$key}%}", "(($key))"],
                $value,
                $content
            );
        }

        return $content;
    }

    /**
     * Get the expected placeholders as an array
     *
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders ?? [];
    }
}
