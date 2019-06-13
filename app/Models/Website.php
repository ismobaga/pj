<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $fillable = ['type', 'url'];

      /**
     * Get all of the models that own websies.
     */
    public function websitable()
    {
        return $this->morphTo();
    }
}
