<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use NotificationChannels\WebPush\HasPushSubscriptions;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Adldap\Laravel\Traits\AdldapUserModelTrait;
use Adldap\Laravel\Traits\HasLdapUser;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends Authenticatable
{

    use HasFactory, Notifiable,HasPushSubscriptions, HasLdapUser,  HasRoles, Authorizable;

    protected $table = 'Cust_User';

    // public $timestamps = false;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';
    const DELETED_AT = 'DeletedOn';


    // protected $primaryKey = 'ID';
    protected $primaryKey = 'EmployeeCode';

    protected $appends = ['full_name'];

    /**
     * The attributes that are mass assignabl   e.
     *
     * @var array
     */
    protected $fillable = [
            'ID',
            'EmployeeCode',
            'FristName',
            'LastName',
            'password',
            'Email',
            'OfficeID',
            'roles',
            'LoginID',
            'objectguid',
            'OfficeCode',
            'CreatedBy',
    ];
    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ID' => 'string',
    ];
    public function adminlte_image()
    {
        // return 'https://picsum.photos/300/300';
        return 'https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif';
    }

    public function adminlte_desc()
    {
        return 'Happy '.date('M');
    }

    public function adminlte_profile_url()
    {
        return 'users/'.Auth::user()->ID;
    }
    public function fullName()
    {
        // return ucwords($this->FirstName . ' ' . $this->LastName);
        return ucwords($this->name);
    }

    public function getFullNameAttribute()
    {
        // return $this->FirstName . ' ' . $this->LastName;
        return $this->LoginID;
    }

    // user office relationship
    public function office()
    {
        return $this->belongsTo(Office::class, 'OfficeID', 'ID');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function generateUniqueEmployeeCode()
    {
        do {
            $code = random_int(100000, 999999);
        } while ($this->where('EmployeeCode', '=', $code)->first());
        return $code;
    }

    /**
     * Get the entity's notifications.
     */
    public function notifications()
    {
        return $this->morphMany(NewNotifications::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'UserID', 'ID');
    }
}
