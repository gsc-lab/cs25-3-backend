<?php
declare(strict_types=1);

// 회원 가입 
function registerUsers(AltoRouter $router): void {
    $router->map('GET', "/users/[a:account]", 'UsersController#show'); // 정보 보기
    $router->map('POST', "/users", 'UsersController#create'); // 회원 가입
    $router->map('PUT', "/users/[a:account]", 'UsersController#update'); // 회원 정보 수정
    $router->map('DELETE', "/users/[a:account]", 'UsersController#delete'); // 회원 탈퇴
    $router->map('POST', "/users/login", 'UsersController#login'); // 로그인
    $router->map('DELETE', "/users/logout", 'UsersController#logout'); // 로그아웃
}



?>