<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'test_users'; // 실제 테이블명 정확히 설정
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'userid', 'password', 'email', 'phone',
        'provider', 'provider_id', 'status'
    ];
}
