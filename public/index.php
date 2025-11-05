<?php
declare(strict_types=1);

// 필요하면 세션
session_start();

// 공용 헬퍼
require_once __DIR__ . '/../app/http.php';

// 프리플라이트(OPTIONS)는 여기서 종료 → 라우터로 보내지 않음
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 라우터는 마지막에 include (실제 라우팅/컨트롤러 실행)
require_once __DIR__ . '/../app/router.php';
