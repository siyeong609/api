<?php

namespace Config;

use Dotenv\Dotenv;

class DotenvLoader
{
    public function __construct()
    {
        // .env 파일을 자동으로 로드
        $dotenv = Dotenv::createImmutable(APPPATH);  // .env 파일 위치
        $dotenv->load();
    }
}
