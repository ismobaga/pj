<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    protected $fillable = ['name', 'slug'];

    /**
     * Get all of the company's phones.
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phone');
    }

    /**
     * Get all of the company's websites.
     */
    public function websites()
    {
        return $this->morphMany(WebSite::class, 'website');
    }

    /**
     * Get the company's address.
     */
    public function address()
    {
        return $this->morphOne(Address::class, 'address');
    }

    /**
     * Get the subcategory relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subCategory(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'companies_sub_categories', 'company_id', 'sub_category_id');
    }

    /**
     * Get the subcategory relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->subCategory()->category();
    }
}
