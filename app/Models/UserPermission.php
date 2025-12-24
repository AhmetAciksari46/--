<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPermission extends Model
{
    use HasFactory;

    protected $table = 'user_permissions';

    protected $fillable = [
        'user_id',
        'permission_id',
        'granted_by',
    ];

    /**
     * Bu permission hangi kullanıcıya ait?
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hangi permission verilmiş?
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Kim verdi? (manager / admin)
     */
    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
