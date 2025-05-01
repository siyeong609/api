<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\UserService;
use CodeIgniter\API\ResponseTrait;
use Exception;

class UserController extends BaseController
{
    use ResponseTrait;

    protected $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    // 사용자 정보 조회 (GET /me)
    public function me()
    {
        try {
            $token = $this->getBearerToken();
            $user = $this->userService->getUserFromToken($token);

            if (!$user) {
                return $this->failNotFound("사용자를 찾을 수 없습니다.");
            }

            return $this->respond($user);
        } catch (Exception $e) {
            return $this->failUnauthorized("토큰 인증 실패");
        }
    }

    // 사용자 정보 수정 (PUT /me)
    public function updateMe()
    {
        try {
            $token = $this->getBearerToken();
            $data = $this->request->getJSON(true);

            $this->userService->updateUserFromToken($token, $data);

            return $this->respondUpdated(["message" => "회원정보 수정 완료"]);
        } catch (Exception $e) {
            return $this->failUnauthorized("토큰 인증 실패");
        }
    }

    // Bearer 토큰 추출
    private function getBearerToken(): string
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        return str_replace('Bearer ', '', $authHeader);
    }
}
