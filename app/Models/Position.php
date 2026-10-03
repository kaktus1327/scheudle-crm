<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
