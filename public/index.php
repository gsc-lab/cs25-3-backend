<?php
declare(strict_types=1);

// 필요하면 세션
session_start();

// 공용 헬퍼
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/../app/http.php';
require_once __DIR__ . '/../routes/router.php';


// 프리플라이트(OPTIONS)는 여기서 종료 → 라우터로 보내지 않음
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new AltoRouter();
$router->setBasePath('/v1');
registerAllRoutes($router);

// 매칭
$match = $router->match();

// 라우터 
if ($match) {
    $target = $match['target'];
    
    if (is_callable($target)) {
        $result = call_user_func_array($target, $match['params']);
        if (is_string($result)) {
            echo json_response([$result]);
        }
    } elseif (is_string($target) && strpos($target, '#') !== false) {
        // "Controller#method" 형태 지원
        [$controller, $method] = explode('#', $target, 2);
        $fqcn = "\\App\\Controllers\\{$controller}";

        // 문자열 그대로 반환
        $params = array_values($match['params']);

        (new $fqcn())->{$method}(...$params);
    } else {
        echo json_response(['error' => 'Bad route target'], 500);
    }
} else {
    echo json_response(['error' => '404 Not Found'], 404);
}