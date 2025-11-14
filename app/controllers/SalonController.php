<?php

namespace App\Controllers;

use Throwable;

require_once __DIR__.'/../db.php';
require_once __DIR__.'/../http.php';

class SalonController {

    // 'GET' -> 정보 보기
    public function index():void{

        try {
            $db = get_db();
            $stmt = $db->prepare("SELECT * FROM Salon");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            json_response([
                'success' => true,
                'date' => ['salon' => $row]
            ]);

        } catch (Throwable $e) {
              error_log('[salon_index]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
            ]],500);
        return;
    }    
}


    // 'PUT' -> salon정보 수정
    public function update():void{
        
        $date = read_json_body();

        $image         = $date['image'] ?? '';
        $intro  = $date['introduction'] ?? '';
        $info   = $date['information'] ?? '';
        $map           = $date['map'] ?? '';
        $traffic       = $date['traffic'] ?? '';

        $salon = [];
        
        if ($image === '' || $intro === '' || $info === '' || 
                $map === '' || $traffic === '') {
                json_response([
                    'success' => false,
                    'error' => ['code' => 'INVALID_REQUEST',
                                'message' => '유효하지 않은 요청입니다.']
                ], 400);
            return;
        }      
        foreach ($date as $key => $value) {
                $value = (string)$value;
                $v = $key . "=" . "?";
                array_push($salon, $v);
        }

        try {
            $db = get_db();
            $stmt = $db->prepare("UPDATE Salon SET "
                    .implode(",",$salon));
            $stmt->bind_param('sssss', $image, $intro, $info, $map, $traffic);
            $stmt->execute();

            if ($stmt->affected_rows === 0){
                json_response([
                    'success' => false
                ], 404);
            }

            json_response([
                'success' => true
            ]);


        } catch (Throwable $e) {
              error_log('[salon_update]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
            ]],500);
        return;
    }    
}

}














?>