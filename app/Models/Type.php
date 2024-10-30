<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    
    protected $fillable = ['title', 'slug'];

    public function products() : BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }


}
