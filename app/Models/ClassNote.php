<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class ClassNote extends Authenticatable
{
    use Notifiable,HasFactory;

    protected $table = 'classnotes';

    protected $fillable = [
                   'session_id', 'class_id','section_id','group_id','subject_id','teacher_id','date','class_note','note_photo','note_pdf',
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
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id','id');
    }
    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'teacher_id','id');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id','id');
    }
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'subject_id','id');
    }
    public function getPhotoAttribute($value)
    {
        return (!is_null($value)) ? env('APP_URL') . '/public/storage/' . $value : null;
    }

    public function getNotePdfAttribute($value)
    {
        return (!is_null($value)) ? env('APP_URL') . '/public/storage/' . $value : null;
    }
}
