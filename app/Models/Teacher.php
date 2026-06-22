<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Teacher extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'nip',
        'name',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class);
    }

    public function mentorStudents()
    {
        return $this->hasMany(MentorStudent::class);
    }

    public function learningObjectives()
    {
        return $this->hasMany(LearningObjective::class);
    }
}
