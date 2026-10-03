<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    protected $fillable = ['name', 'remind_days_before'];

    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }
}
