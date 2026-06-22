<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SchoolClass extends Model
{
    use HasUuids;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'level',
        'school_year_id',
        'semester_id',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function learningObjectives()
    {
        return $this->hasMany(LearningObjective::class, 'class_id');
    }
}
