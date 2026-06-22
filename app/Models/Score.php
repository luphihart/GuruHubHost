<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Score extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'learning_objective_id',
        'score',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }
}
