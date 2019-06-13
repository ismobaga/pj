<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['city', 'formatted_address', 'country'];
      /**
     * Get all of the models that own websies.
     */
    public function addressable()
    {
        return $this->morphTo();
    }
}
