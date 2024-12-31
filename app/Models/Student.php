<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class Student extends Authenticatable
{
    use Notifiable,HasFactory;

    protected $table = 'students';

    protected $fillable = [
                    'student_id',
                    'name',
                    'father_name',
                    'mother_name',
                    'phone',
                    'email',
                    'address',
                    'date_of_birth',
                    'admission_date',
                    'photo',
                    'session_id',
                    'class_id',
                    'section_id',
                    'group_id',
                    'password',
                    'status',
                ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_at = date('Y-m-d H:i:s');
        });

        static::updating(function ($model) {
            $model->updated_at = date('Y-m-d H:i:s');
        });
    }    
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id','id');
    }
    public function sessions()
    {
        return $this->hasMany(Session::class, 'session_id','id');
    }
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id','id');
    }
    public function classes()
    {
        return $this->hasMany(Classes::class, 'class_id','id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id','id');
    }
    public function sections()
    {
        return $this->hasMany(Section::class, 'section_id','id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id','id');
    }
    public function groups()
    {
        return $this->hasMany(Group::class, 'group_id','id');
    }
    public function getImageAttribute($value)
    {
        return (!is_null($value)) ? env('APP_URL') . '/public/storage/' . $value : null;
    }

    public function getFileAttribute($value)
    {
        return (!is_null($value)) ? env('APP_URL') . '/public/storage/' . $value : null;
    }
}
