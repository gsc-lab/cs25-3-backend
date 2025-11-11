<?php

namespace App\Controllers;

use Throwable;

require_once __DIR__.'/../db.php';
require_once __DIR__.'/../http.php';

class NewsController {

    // 'POST' -> news 글 작성하기
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
                'error' => ['code' => 'VALIDATION_ERROR', 
                            'massege' => '필수 필드가 비었습니다.'
            ]], 422);
            return;
        }

        try{
            // DB 접속
            $db = get_db();

            // sql문
            $stmt = $db->prepare("INSERT INTO News (title, content, file) VALUES (?,?,?)");
            $stmt->bind_param('sss',$title, $content, $file);
            $stmt->execute();

            // 프론트엔드에 반환하는 값
            json_response([
                'success' => true,
                'date' =>[
                    'title' => $title,
                    'content' => $content
                ]
            ],201);

        // 예외 처리
        } catch (Throwable $e){
            error_log('[news_create]'. $e->getMessage());
            json_response(['error' => '서버 오류'], 500);
            return;
        }
    }

    // 'GET' -> News 글 전체 보기
    public function index():void{

        try {
            // DB접속
            $db = get_db();
            // SELECT로 News테이블에 있는 모둔 글을 불어오기
            $stmt = $db->prepare("SELECT 
                    news_id, title, content, file, created_at 
                    FROM News ORDER BY news_id DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
                ]],500);
                return;
            }
            
            // News전체 글을 넣는 리스트 만들기
            $rows = [];
            
            // 행을 하나 씩 $rows에 넣는다 
            while($row = $result->fetch_assoc()){
                array_push($rows, $row);
            }
            
            // 프론트엔드에 정보 보내기
            json_response([
                "success" => true,
                'date' => ['news' => $rows]
            ]);

        } catch (Throwable $e){
            error_log('[news_index]'. $e->getMessage());
            json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
            ]],500);
            return;
        }
        
    }


    // 해당 글 자세히 보기
    public function show(string $news_id):void {
        
        // news_id를 받는다
        $id = isset($news_id) ? (string)$news_id : 0;

        // 유호성 검중
        if ($id <= 0) {
            json_response([
                "success" => false,
                "error" => [
                    'code' => 'INVALID_REQUEST',
                    'message' => '유효하지 않은 요청입니다.'] 
            ], 400);
            return;
        }
        
        try {
            // DB접속
            $db = get_db();

            // 해당 news 글을 가져오기
            // SQL문
            $stmt = $db->prepare("SELECT 
                                        news_id, title, content, file, created_at 
                                        FROM News WHERE news_id=?"); 
            $stmt->bind_param('s',$id);
            $stmt->execute();

            // 해당 데이터를 가져 오기
            $result = $stmt->get_result();

            // 요청한 news글을 찾을 수 없었을 때
            if ($result->num_rows === 0) {
                json_response([
                    "success" => false,
                    "error" => [
                        'code' => 'RESOURCE_NOT_FOUND',
                        'message' => '요청한 리소스를 찾을 수 없습니다.']
                ], 404);
                return;
            }
            
            // date를 저장하는 리스트
            $sets = [];

            // date를 리스터로 저장
            while($row = $result->fetch_assoc()){
                array_push($sets, $row);
            }

            $stmt->close();

            // 값을 프런트에 보내기
            json_response([
                "success" => true,
                "date" => ['news' => $sets]
            ]);

        } catch (Throwable $e) {
            error_log('[news_show]'. $e->getMessage());
            json_response([
                "success" => false,
                "error" => [
                    'code' => 'INTERNAL_SERVER_ERROR', 
                    'message' => '서버 오류가 발생했습니다.'
            ]],500);
            return;
        }
    }


    // 'PUT' -> 해당 글을 Update
    public function update(string $news_id):void{
        // 프론트엔드에서 전달된 json파일을 받기 
        $date = read_json_body();

        // 안에 데이터를 꺼내기 (title, content, file)
        $title = (string)($date['title']) ?? '';
        $content = (string)($date['content']) ?? '';
        $file = (string)($date['file']) ?? '';
        $id = (string)$news_id ?? 0;

        // 유호성 확인
        if ($title === '' || $content === '') {
            json_response([
                    "success" => false,
                    "error" => [
                        "code" => "VALIDATION_ERROR",
                        "message" => " 필수 필드가 비었습니다."]
            ], 422);
            return;
        }

        try{
            // DB 접속
            $db = get_db();

            // update SQL문
            $stmt = $db->prepare("UPDATE News SET
                                        title=?, content=?, file=?, updated_at=NOW()
                                        WHERE news_id=?");
            $stmt->bind_param('ssss', $title, $content, $file, $id);
            $stmt->execute();           
            $stmt->close();

            // update 정보 가져오기
            $stmt2 = $db->prepare("SELECT title, content, file, created_at, updated_at FROM News WHERE News_id = ?");
            $stmt2->bind_param('s',$id);
            $stmt2->execute();
            $result = $stmt2->get_result();
            
            // update한 내용을 넣는 리스트
            $set = [];
            while ($row = $result->fetch_assoc()) {
                array_push($set, $row);
            }
            $stmt2->close();
            
            // update date데이터를 반환
            json_response([
                "date" => $set
            ]);

        } catch (Throwable $e) {
            error_log('[news_update]'.$e->getMessage());
            json_response([
                "success" => false,
                "error" => ["code" => "VALIDATION_ERROR",
                            "message" => "서버 오류가 발생했습니다." ]
            ], 500);
            return;
        }

        
    }


    // 'DELETE' -> 글을 삭제
    public function delete(int $news_id):void{
        
        // news_id 받기
        $id = isset($news_id) ? (int)$news_id : 0;
        // id 유호성 검중
        if ($id <= 0) {
            json_response([
                 "success" => false,
                 "erroe" => ['code' => 'INVALID_REQUEST',
                            'message' => '유효하지 않은 요청입니다.']
            ], 400);
            return;
        }

        try {
            // $db접속
            $db = get_db();

            // news 테이블에서 해당 글을 삭제 하기
            $stmt = $db->prepare("DELETE FROM News WHERE news_id =?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            // DELETE SQL문의 영향을 받는 행이 없으면 삭제할 데이터 없음
            if ($stmt->affected_rows === 0) {
                json_response([
                     "success" => false,
                     "error" => ['code' => 'RESOURCE_NOT_FOUND',
                                'message' => '삭제할 데이터를 찾을 수 없습니다.']
                ], 404);
                return;
            }

            json_response([
                     "success" => true
                ]);

            $stmt->close();
        } catch (Throwable $e) {
            error_log('[news_delet]'. $e);
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