<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use  Notifiable, HasFactory, HasRoles;

    protected $table = 'students';

    protected $fillable = [
        'user_id',
        'class_id',
        'section',
        'health_info',
        'adminssion_date',
        'password',
        'email',
        'image'
    ];
}
