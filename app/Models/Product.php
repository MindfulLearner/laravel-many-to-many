<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'cover_image',
        'slug',
        'published',
        'types'
    ];




    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
