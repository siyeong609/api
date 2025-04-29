<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users'; // 실제는 test_users
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'username', 'password', 'email', 'phone',
        'provider', 'provider_id', 'status',
        'created_at', 'updated_at'
    ];
}
