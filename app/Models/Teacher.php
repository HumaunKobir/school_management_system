<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class Teacher extends Authenticatable
{
    use Notifiable,HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
                    'teacher_id',
                    'name',
                    'father_name',
                    'mother_name',
                    'phone',
                    'email',
                    'address',
                    'date_of_birth',
                    'education_level',
                    'jonning_date',
                    'file',
                    'photo',
                    'password',
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

    public function getPhotoAttribute($value)
    {
        return (!is_null($value)) ? env('APP_URL') . '/public/storage/' . $value : null;
    }

    public function getFileAttribute($value)
    {
        return (!is_null($value)) ? env('APP_URL') . '/public/storage/' . $value : null;
    }
}
