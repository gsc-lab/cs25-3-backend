<?php

declare(strict_types=1);

// salon
function registerSalon(AltoRouter $router): void {
    $router->map('GET', "/salon", 'SalonController#index'); // 정보 보기
    $router->map('PUT', "/salon", 'SalonController#update'); // salon 정보 수정
}

?>