<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subject extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function learningObjectives()
    {
        return $this->hasMany(LearningObjective::class);
    }
}
