<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\UserService;
use CodeIgniter\API\ResponseTrait;

class PasswordController extends BaseController
{
    use ResponseTrait;

    protected $userService;

    public function __construct()
    {
        // UserService 인스턴스를 로드합니다.
        $this->userService = new UserService();
    }

    public function forgotPassword()
    {
        // JSON 형식으로 email 값을 받기 위해 getJSON 사용
        $email = $this->request->getJSON(true)['email']; // JSON 파싱해서 email 추출

        if (!$email) {
            return $this->failValidationErrors("이메일을 입력해주세요.");
        }

        // 서비스에서 비밀번호 재설정 링크 생성 및 이메일 전송
        $result = $this->userService->sendPasswordResetLink($email);

        if ($result['success']) {
            return $this->respond([
                'success' => true,
                'message' => '비밀번호 재설정 링크가 이메일로 전송되었습니다.',
            ]);
        }

        return $this->fail($result['message']);
    }


    // 비밀번호 재설정 처리
    public function resetPassword()
    {
        // JSON 형식으로 값을 받기 위해 getJSON 사용
        $token = $this->request->getJSON(true)['token']; // JSON 파싱해서 token 추출
        $newPassword = $this->request->getJSON(true)['newPassword']; // JSON 파싱해서 newPassword 추출

        if (!$token || !$newPassword) {
            return $this->failValidationErrors("필요한 값을 입력해주세요.");
        }

        // 서비스에서 토큰 확인 및 비밀번호 변경 처리
        $result = $this->userService->resetPassword($token, $newPassword);

        if ($result['success']) {
            return $this->respond([
                'message' => '비밀번호가 성공적으로 변경되었습니다.',
            ]);
        }

        return $this->fail($result['message']);
    }
}
