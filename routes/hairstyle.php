<?php

declare(strict_types=1);

function registerHairstyle(AltoRouter $router) :void {
    $router->map("GET", '/hairstyle', "HairstyleController#index"); // hairstyle 전체 보기
    $router->map("GET", '/hairstyle/[a:hairstyle_id]', "HairstyleController#show");  // hairstyle 세부 보기
    $router->map("POST", '/hairstyle/create', "HairstyleController#create"); // hairstyle 작성
    $router->map("POST", '/hairstyle/image/[a:hairstyle_id]', "HairstyleController#updateImage");  // hairstyle 이미지 수정
    $router->map("PUT", '/hairstyle/update/[a:hairstyle_id]', "HairstyleController#update"); // 특정 hairstyle 수정
    $router->map("DELETE", '/hairstyle/delete/[a:hairstyle_id]', "HairstyleController#delete"); // 특정 hairstyle 삭게하기    
}

?>