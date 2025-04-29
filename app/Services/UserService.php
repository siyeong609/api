<?php

namespace App\Services;

use App\Models\UserModel;

class UserService
{
    public function registerUser($requestData)
    {
        $validation = \Config\Services::validation();

        $rules = [
            'username' => 'required|min_length[3]|is_unique[test_users.username]',
            'phone'    => 'required|regex_match[/^[0-9]{10,11}$/]|is_unique[test_users.phone]',
            'password' => 'required|min_length[4]',
        ];

        if (!$validation->setRules($rules)->run($requestData)) {
            throw new \Exception(json_encode($validation->getErrors()));
        }

        $userModel = new UserModel();
        $userData = [
            'username' => $requestData['username'],
            'phone'    => $requestData['phone'],
            'email'    => $requestData['email'] ?? null,
            'password' => password_hash($requestData['password'], PASSWORD_DEFAULT),
            'provider' => 'local',
        ];

        if (!$userModel->insert($userData)) {
            throw new \Exception('회원가입 실패');
        }

        return true;
    }

}
