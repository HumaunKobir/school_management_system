<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class Subject extends Authenticatable
{
    use Notifiable,HasFactory;

    protected $table = 'subjects';

    protected $fillable = [
                    'class_id',
                    'group_id',
                    'name',
                    'subject_code'
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

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id','id');
    }
    public function classes()
    {
        return $this->hasMany(Classes::class, 'class_id','id');
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
