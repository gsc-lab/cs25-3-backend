<?php
declare(strict_types=1);

// 회원 가입 
function registerReservation(AltoRouter $router): void {
    $router->map('GET', "/reservation", 'ReservationController#show'); // (클라이언트) 자기 예약 정보 보기
    $router->map('POST', "/reservation/create", 'ReservationController#create'); // 예약하기
    $router->map('PUT', "/reservation/update/[a:reservation_id]", 'ReservationController#update'); // (클라이언트) 예약 cancel
}

?>