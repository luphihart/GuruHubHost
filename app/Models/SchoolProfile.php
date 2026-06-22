<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    protected $table = 'school_profiles';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', // 'singleton'
        'yayasan_name',
        'school_name',
        'address',
        'phone',
        'email',
        'website',
        'headmaster',
        'headmaster_nip',
    ];
}
