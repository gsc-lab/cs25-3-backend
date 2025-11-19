<?php

function require_guest():void{
    if (!empty($_SESSION['user'])) {
        json_response([
            'success' => false,
            'error' => ['code' => '',
                        'message' => '로그인 중입니다.'] 
        ],401);
    exit;
    }
}
?>