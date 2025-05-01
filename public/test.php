<?php

// 에러 보기 좋게 표시
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 환경 변수 확인
echo "<h2>환경 설정 확인 페이지</h2>";

echo "<p><strong>CI_ENVIRONMENT:</strong> " . getenv('CI_ENVIRONMENT') . "</p>";
echo "<p><strong>JWT_SECRET:</strong> " . getenv('JWT_SECRET') . "</p>";
