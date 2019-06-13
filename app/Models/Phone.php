<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $fillable = ['city', 'formatted_address', 'country'];
    /**
     * Get all of the models that own phones.
     */
    public function phonable()
    {
        return $this->morphTo();
    }
}
