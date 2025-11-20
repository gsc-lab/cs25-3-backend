<?php

    namespace App\Controllers;

use Throwable;

    require_once __DIR__ . "/../db.php";
    require_once __DIR__ . "/../http.php";

    class DesignerController{
        // ===============================
        // 'GET' -> Designer정보 전체 보기
        // ===============================
        public function index():void {

            try {
                // DB접속
                $db = get_db();
                // Designer + Users JOIN해서 전체 정보 조회
                $stmt = $db->prepare("SELECT 
                                            u.user_name,
                                            d.experience,
                                            d.good_at,
                                            d.personality,
                                            d.message
                                            FROM Designer AS d
                                            JOIN Users AS u
                                                ON d.user_id = u.user_id
                                            ORDER BY designer_id DESC");
                // 실행
                $stmt->execute();
                // SELECT 결과 가져오기
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
            
            // 예외 처리 (서버내 오류 발생지)
            } catch (Throwable $e) {
                // 에러 로그 기록
                error_log('[designer_index]'. $e->getMessage());
                // 에러 응답 반환
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
                ]],500);
                return;
            }  
        }

        // ===============================
        // 'GET' -> 해당 Designer정보 보기
        // ===============================
        public function show(string $designer_id):void {
            
            // designer_id 유호성 검사  
            $designer_id = filter_var($designer_id, FILTER_VALIDATE_INT);

            if ($designer_id === false || $designer_id <= 0) {
                    json_response([
                    'success' => false,
                    'error' => ['code' => 'INVALID_ID',
                                'message' => 'ID가 잘못되었습니다. 올바른 숫자 ID를 지정하십시오..']
                ], 400);
                return;
            }

            try{
                $db = get_db(); // DB접속
                
                // JOIN으로 디자이너 상세 정보 조회
                $stmt = $db->prepare("SELECT
                                    u.user_name,
                                    d.experience,
                                    d.good_at,
                                    d.personality,
                                    d.message
                                    FROM Designer AS d
                                    JOIN Users AS u
                                        ON d.user_id = u.user_id
                                    WHERE d.designer_id=?");
                $stmt->bind_param('i',$designer_id);
                // 실행
                $stmt->execute();
                $result = $stmt->get_result();

                // 결과가 없는 경우 오류  
                if ($result->num_rows === 0) {
                    json_response([
                        'success' => false,
                        'error' =>['code' => 'RESOURCE_NOT_FOUND',
                                    'message' => '해당 디자이너를 찾을 수 없습니다.']
                    ], 404);
                    return;
                }
                
                $row = $result->fetch_assoc();

                // JSON 응답
                json_response([
                    'success' => true,
                    'date' => ['designer' => $row]
                ]);

            // 예외 처리 (서버내 오류 발생지)
            } catch (Throwable $e) {
                error_log('[designer_show]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
                ]],500);
                return;
            }
        }


        // ===============================
        // 'POST' -> Designer정보 작성
        // ===============================
        public function create():void {
            
            // 프론트에서 데이터 받기
            $date = read_json_body();

            // 필드별 유효성 검사
            $image = (string)$date['image'] ?? '';
            $experience = filter_var($date['experience'], FILTER_VALIDATE_INT);
            $good_at = (string)$date['good_at'] ?? '';
            $personality = (string)$date['personality'] ?? '';
            $message = (string)$date['message'] ?? '';

            // 필수 값 검증
            if ($experience === false || $experience <= 0 || $image === ''|| 
                $good_at === '' || $personality === '' || $message === '') {
                    json_response([
                    'success' => false,
                    'error' => ['code' => 'VALIDATION_ERROR',
                                'message' => '필수 필드가 비었습니다..']
                ], 422);
                return;
            }

            try {

                $db = get_db(); // DB접속

                // INSERT문
                $stmt = $db->prepare("INSERT INTO Designer
                                        (user_id, image, experience, good_at, personality, message)
                                        VALUES (?,?,?,?,?,?)");
                $stmt->bind_param('isisss',
                    $_SESSION['user']['user_id'],$image,  $experience, 
                        $good_at, $personality, $message
                    );

                $stmt->execute();
    
                json_response([
                    'success' => true
                ]);
            
            // 예외 처리 (서버내 오류 발생지)
            } catch (Throwable $e) {
                error_log('[designer_create]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
                ]],500);
                return;
            }
        }


        
        // ===============================
        // 'PUT' -> Designer정보 수정
        // ===============================
        public function update(string $designer_id):void {
            
            // ID 정수 유효성 검사
            $designer_id = filter_var($designer_id, FILTER_VALIDATE_INT);
        
            if ($designer_id === false || $designer_id <= 0) {
                json_response([
                    'success' => false,
                    'error' => ['code' => 'INVALID_ID',
                                'message' => 'ID가 잘못되었습니다. 올바른 숫자 ID를 지정하십시오..']
                ], 400);
                return;
            }
            
            // 프론트에서 받은 데이터
            $date = read_json_body();

            $image = (string)$date['image'] ?? '';
            $good_at = (string)$date['good_at'] ?? '';
            $personality = (string)$date['personality'] ?? '';
            $message = (string)$date['message'] ?? '';

            // 필수 값 검증
            if ( $image === ''|| $good_at === '' || 
                $personality === '' || $message === '') {
                    json_response([
                    'success' => false,
                    'error' => ['code' => 'VALIDATION_ERROR',
                                'message' => '필수 필드가 비었습니다..']
                ], 422);
                return;
            }

            // 입력된 데이터 기준으로 SET 절 동적 생성 
            $set = [];
            foreach($date as $key => $value) {
                // 모든 값을 '?'로 치환
                $value = "?";
                $v = $key."=".$value;
                array_push($set, $v);
            }

            try {

                $db = get_db();
                // UPDATE문 조립
                $stmt = $db->prepare("UPDATE Designer SET "
                                    .implode("," , $set) 
                                    ." WHERE designer_id=?");
                
                $stmt->bind_param('ssssi', $image, $good_at, $personality, $message, $designer_id);
                $stmt->execute();

                // 수정된 행이 없을 경우
                if ($stmt->affected_rows === 0) {
                    json_response([
                     "success" => false,
                     "error" => ['code' => 'RESOURCE_NOT_FOUND',
                                'message' => '수정할 데이터를 찾을 수 없습니다.']
                    ], 404);
                    return;
                }

                json_response([
                    'success' => true
                ]);
            
            // 예외 처리 (서버내 오류 발생지)
            } catch (Throwable $e) {
                error_log('[designer_update]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
                ]],500);
                return;
            }     
        }


        // ===============================
        // 'DELETE' -> Designer정보 삭제
        // ===============================
        public function delete(string $designer_id):void {
            
            $designer_id = filter_var($designer_id, FILTER_VALIDATE_INT);

            //검증
            if ($designer_id === false || $designer_id <= 0) {
                json_response([
                    'success' => false,
                    'error' => ['code' => 'INVALID_ID',
                                'message' => 'ID가 잘못되었습니다. 올바른 숫자 ID를 지정하십시오..']
                ], 400);
                return;
            }

            try {
                
                $db = get_db(); // DB접속

                // DELETE SQL문
                $stmt = $db->prepare("DELETE FROM Designer WHERE designer_id=?");
                $stmt->bind_param('i', $designer_id);
                // 실행
                $stmt->execute();

                // 삭제된 행이 없는 경우 오류 표시
                if ($stmt->affected_rows === 0){
                    json_response([
                     "success" => false,
                     "error" => ['code' => 'RESOURCE_NOT_FOUND',
                                'message' => '삭제할 데이터를 찾을 수 없습니다.']
                    ], 404);
                    return;
                }
            
                json_response([
                    'success' => true
                ]);
            
            // 예외 처리 (서버내 오류 발생지)
            } catch (Throwable $e) {
                error_log('[designer_delete]'.$e->getMessage());
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