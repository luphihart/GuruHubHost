<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Semester extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'semester_id');
    }
}
