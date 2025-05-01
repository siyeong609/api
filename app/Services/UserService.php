<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use App\Models\UserModel;
use App\Helpers\EmailHelper;

class UserService
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ✅ 회원가입 처리
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

        $userData = [
            'name'     => $data['name'],
            'userid'   => $data['userid'],
            'phone'    => $data['phone'],
            'email'    => $data['email'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'provider' => 'local',
        ];

        if (!$this->userModel->insert($userData)) {
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

    // ✅ 로그인 처리 및 JWT 발급
    public function login(array $data): array
    {
        $user = $this->userModel->where('userid', $data['userid'])->first();

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return [
                'success' => false,
                'message' => '아이디 또는 비밀번호가 일치하지 않습니다.',
            ];
        }

        $key = getenv('JWT_SECRET');
        $payload = [
            'iss' => 'ci4-app',
            'sub' => $user['id'],
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return [
            'success' => true,
            'message' => '로그인 성공',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'userid' => $user['userid'],
                'name' => $user['name']
            ]
        ];
    }

    // ✅ 토큰을 이용해 사용자 정보 반환
    public function getUserFromToken(string $token): ?array
    {
        $key = getenv('JWT_SECRET');
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        $user = $this->userModel->find($decoded->sub);

        if (!$user) {
            return null;
        }

        unset($user['password']);
        return $user;
    }

    // ✅ 토큰을 이용해 사용자 정보 수정
    public function updateUserFromToken(string $token, array $data): void
    {
        $key = getenv('JWT_SECRET');
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $userId = $decoded->sub;

        $updateData = [];

        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("올바른 이메일 형식이 아닙니다.");
            }
            $updateData['email'] = $data['email'];
        }

        if (!empty($data['phone'])) {
            if (!preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
                throw new \Exception("올바른 전화번호 형식이 아닙니다.");
            }
            $updateData['phone'] = $data['phone'];
        }

        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!empty($updateData)) {
            $this->userModel->update($userId, $updateData);
        }
    }

    // 비밀번호 재설정 링크 이메일로 전송
    public function sendPasswordResetLink(string $email): array
    {
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => '이메일 주소가 등록되지 않았습니다.'
            ];
        }

        // 토큰 생성
        $token = bin2hex(random_bytes(32)); // 랜덤 토큰 생성
        // 토큰을 DB에 업데이트
        $updateResult = $this->userModel->update($user['id'], ['reset_token' => $token]);

        // 업데이트 결과 확인
        if (!$updateResult) {
            log_message('error', 'Failed to update reset_token for user ID ' . $user['id']);
            return [
                'success' => false,
                'message' => '토큰 업데이트 실패'
            ];
        }

        // 비밀번호 재설정 링크
        $resetLink = 'http://119.71.19.150:8082/reset-password/' . $token;
        $message = "비밀번호 재설정을 위해 아래 링크를 클릭해주세요:\n" . $resetLink;

        $emailSent = EmailHelper::sendEmail($user['email'], '비밀번호 재설정', $message);

        if (!$emailSent) {
            return [
                'success' => false,
                'message' => '이메일 전송에 실패했습니다.'
            ];
        }

        return [
            'success' => true,
            'message' => '비밀번호 재설정 링크가 이메일로 전송되었습니다.'
        ];
    }

    // 비밀번호 재설정
    public function resetPassword(string $token, string $newPassword): array
    {
        $user = $this->userModel->where('reset_token', $token)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => '유효하지 않은 토큰입니다.'
            ];
        }

        $this->userModel->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'reset_token' => null
        ]);

        return [
            'success' => true,
            'message' => '비밀번호가 성공적으로 변경되었습니다.'
        ];
    }
}
