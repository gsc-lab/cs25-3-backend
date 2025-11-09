<?php

namespace App\Controllers;

use Throwable;

require_once __DIR__.'/../db.php';
require_once __DIR__.'/../http.php';

class NewsController {




    public function create():void{
        // 데이터 받기
        $date = read_json_body();

        $title = (string)($date['title'] ?? '');
        $content = (string)($date['content'] ?? '');
        $file = (string)($date['file'] ?? '');

        // 유호성 확인
        if ($title === '' || $content === '') {
            json_response([
                'success' => false,
                'error' => ['code' => 'VALIDATION_ERROR', 'massege' => '필수 필드가 비었습니다.']
            ], 400);
            return;
        }

        try{
            // DB 접속
            $db = get_db();

            // sql문
            $stmt = $db->prepare("INSERT INTO News (title, content, file) VALUES (?,?,?)");
            $stmt->bind_param('sss',$title, $content, $file);
            $stmt->execute();

            json_response([
                'title' => $title,
                'content' => $content
            ],201);

        } catch (Throwable $e){
            error_log('[news_create]'. $e->getMessage());
            json_response(['error' => '서버 오류'], 500);
        }
        
        

    }
}

?>