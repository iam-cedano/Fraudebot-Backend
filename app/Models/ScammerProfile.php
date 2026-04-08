<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScammerProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scammer_id',
        'name',
        'social_media',
        'contact',
        'is_active',
    ];

    /**
     * Get the scammer that owns the profile.
     */
    public function scammer()
    {
        return $this->belongsTo(Scammer::class);
    }
}
