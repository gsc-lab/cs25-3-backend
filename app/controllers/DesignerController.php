<?php

    namespace App\Controllers;

use Throwable;

    require_once __DIR__ . "/../db.php";
    require_once __DIR__ . "/../http.php";

    class DesignerController{

        // 'GET' -> Designer정보 전체 보기
        public function index():void{

            try {
                // DB접속
                $db = get_db();
                // SQL문 Designer테이블에서 정보 가져오기
                $stmt = $db->prepare("SELECT * FROM Designer ORDER BY designer_id DESC");
                // 실행
                $stmt->execute();
                $result = $stmt->get_result();
                
                $designers = [];

                // 리스터에 저장
                while($row = $result->fetch_assoc()){
                    array_push($designers, $row);
                }
                
                // 프론트에 반환
                json_response([
                    'date' => ['designer' => $designers]
                ]);

            } catch (Throwable $e) {
                error_log('[designer_index]'. $e->getMessage());
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