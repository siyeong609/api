<?php

namespace App\Services;

use App\Models\UserModel;

class UserService
{
    public function registerUser(array $data): array
    {
        $validation = \Config\Services::validation();

        $rules = [
            'name' => [
                'label' => '이름',
                'rules' => 'required|min_length[2]',
                'errors' => [
                    'required'   => '이름을 입력하세요.',
                    'min_length' => '이름은 최소 2자 이상이어야 합니다.',
                ],
            ],
            'userid' => [
                'label' => '아이디',
                'rules' => 'required|min_length[3]|is_unique[test_users.userid]',
                'errors' => [
                    'required'   => '아이디를 입력하세요.',
                    'min_length' => '아이디는 최소 3자 이상이어야 합니다.',
                    'is_unique'  => '이미 사용 중인 아이디입니다.',
                ],
            ],
            'phone' => [
                'label' => '휴대폰 번호',
                'rules' => 'required|regex_match[/^[0-9]{10,11}$/]|is_unique[test_users.phone]',
                'errors' => [
                    'required'    => '휴대폰 번호를 입력하세요.',
                    'regex_match' => '휴대폰 번호 형식이 올바르지 않습니다.',
                    'is_unique'   => '이미 사용 중인 휴대폰 번호입니다.',
                ],
            ],
            'password' => [
                'label' => '비밀번호',
                'rules' => 'required|min_length[4]',
                'errors' => [
                    'required'   => '비밀번호를 입력하세요.',
                    'min_length' => '비밀번호는 최소 4자 이상이어야 합니다.',
                ],
            ],
        ];

        if (!$validation->setRules($rules)->run($data)) {
            $errors = $validation->getErrors();
            $firstKey = array_key_first($errors);

            return [
                'success' => false,
                'message' => $errors[$firstKey],
                'errors'  => $errors
            ];
        }

        $userModel = new UserModel();
        $userData = [
            'name'     => $data['name'],
            'userid'   => $data['userid'],
            'phone'    => $data['phone'],
            'email'    => $data['email'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'provider' => 'local',
        ];

        if (!$userModel->insert($userData)) {
            return [
                'success' => false,
                'message' => "회원가입에 실패했습니다.\n관리자에게 문의해주세요."
            ];
        }

        return [
            'success' => true,
            'message' => '회원가입이 완료되었습니다.'
        ];
    }
}
