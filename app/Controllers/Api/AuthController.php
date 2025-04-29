<?php

namespace App\Controllers\Api;

use App\Services\UserService;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    public function register()
    {
        try {
            $userService = new UserService();
            $userService->registerUser($this->request->getJSON(true));

            return $this->respondCreated(['message' => '회원가입 완료']);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
