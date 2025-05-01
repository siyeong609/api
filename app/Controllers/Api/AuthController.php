<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\UserService;

class AuthController extends BaseController
{
    public function register()
    {
        $data = $this->request->getJSON(true);

        $service = new UserService();
        $result = $service->registerUser($data);

        $status = $result['success'] ? 200 : 400;
        return $this->response->setStatusCode($status)->setJSON($result);
    }
}
