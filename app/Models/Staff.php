<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Staff extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'tbl_staff';

    protected $fillable = [
        'name',
        'email',
        'mobile_no',
        'emp_code',
        'status',
        'permission_id',
        'password',
    ];

    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];
}
