<?php

require __DIR__ . '/../vendor/autoload.php';

// 라우터 등록 함수 선언

$router = new AltoRouter();   // 라우터 인스턴스 생성
$router->setBasePath('');   // 기본 경로 설정
// 라우트 등록 함수 호출

$match = $router->match();

 
if ($match) {
    $target = $match['target'];

    if (is_callable($target)) {
        $result = call_user_func_array($target, $match['params']);
        if (is_string($result)) {
            // 문자 반환 시에만 안전하게 출력
            echo json_response([$result]);
        }
    } elseif (is_string($target) && strpos($target, '#') !== false) {
        // "Controller#method" 형태 지원 (선택)
        [$controller, $method] = explode('#', $target, 2);
        $fqcn = "\\App\\Controllers\\{$controller}";
        (new $fqcn())->{$method}(...array_values($match['params']));
    } else {
        echo json_response(['error' => 'Bad route target'], 500);
    }
} else {
    echo json_response(['error' => '404 Not Found'], 404);
}