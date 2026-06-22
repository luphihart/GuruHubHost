<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LearningObjective extends Model
{
    use HasUuids;

    protected $table = 'learning_objectives';

    protected $fillable = [
        'subject_id',
        'class_id',
        'teacher_id',
        'code',
        'description',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}
