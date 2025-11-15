<?php
declare(strict_types=1);

// 회원 가입 
function registerTimeoff(AltoRouter $router): void {
    $router->map('GET', "/timeoff", 'TimeoffController#index'); // designer 휴무 정보 보기
    $router->map('POST', "/timeoff/create", 'TimeoffController#create'); // designer 휴무 작성
    $router->map('PUT', "/timeoff/update/[a:designer_id]", 'TimeoffController#update'); // designer 휴무 수정 
    $router->map('DELETE', "/timeoff/delete/[a:to_id]", 'TimeoffController#delete'); // designer 휴무 삭제
}

?>