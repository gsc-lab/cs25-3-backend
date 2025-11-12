<?php
    namespace App\Controllers;

    use Throwable;

    require_once __DIR__ . "/../db.php";
    require_once __DIR__ . "/../http.php";

    class HairstyleController {


        // 'GET' -> 
        public function index():void{
                
            try {
                // DB 접속
                $db = get_db();

                // HairStyle테이블의 모든 정보 받기
                // SQL
                $stmt = $db->prepare("SELECT * FROM HairStyle ORDER BY hair_id DESC");
                // 실행
                $stmt->execute();
                $result = $stmt->get_result();
                
                // 모든 데이터를 넣는 리스트
                $hairstyle = [];

                // 프론트엔드에 데이터 보내기
                while($row = $result->fetch_assoc()){
                    array_push($hairstyle, $row);
                }

                json_response([
                    'success' => true,
                    'date'    =>['hairstyle' => $hairstyle]
                ]);

            } catch (Throwable $e) {
                error_log('[hairstyle_index]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
            ]],500);
            return;
            }
        }


        // 'POST' -> hairstyle 작성
        public function create():void{
            // 프런트엔드에서 정보를 받는다
            $date = read_json_body();

            // 하나씩 정보 꺼내기
            $title = (string)$date['title'] ?? '';
            $image = (string)$date['image'] ?? '';
            $description = (string)$date['description'] ?? '';

            // 유호성 확인
            if ($title === '' || $image === '' || $description === '') {
                json_response([
                    'success' => false,
                    'error' => ['code' => 'VALIDATION_ERROR',
                                'message' => '요청 데이터의 형식이 올바르지 않습니다.']
                ], 422);
                return;
            }

            try {
                // DB접속
                $db = get_db();

                // INSERT SQL문
                $stmt = $db->prepare("INSERT INTO HairStyle
                                            (title, image, description) 
                                            VALUES (?,?,?)");
                $stmt->bind_param('sss', $title, $image, $description);
                // 실행
                $stmt->execute();

                if ($db->affected_rows === 0) {
                    json_response([
                        'success' => false,
                        'error' => ['code' => 'NO_RECORD_INSERTED', 
                                    'massege' => '삽입 처리가 수행되지 않았습니다.']
                    ], 400);
                }
                
                json_response([
                    'success' => true
                ]);
            
            } catch (Throwable $e) {
                error_log('[hairstyle]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
            ]],500);
                return;
            }          
        }


        // 'PUT' -> 해당 HairStyle 수정
        public function update(string $hair_id):void{
            // 프론트엔드에서 데이터를 받는다
            $date = read_json_body();

            $id = (int)$hair_id ?? 0;

            if ($id <= 0) {
                json_response([
                    'success' => false,
                    'error' => ['code' => 'INVALID_REQUEST',
                                'message' => '유효하지 않은 요청입니다.']
                ], 400);
                return;
            }

            $hairstyle = [];
            foreach ($date as $key => $value) {
                if ($value === '') {
                    json_response([
                        'sucess' => false,
                        'error' => ['code' => 'VALIDATION_ERRO',
                                    'massege' => '요청 데이터의 형식이 올바르지 않습니다.'] 
                    ], 422);
                    return;
                }
                $value = (string)$value;
                $v = $key ."=". "'". $value. "'";
                array_push($hairstyle, $v);
            }

            try {
                $db = get_db();

                $stmt = $db->prepare("UPDATE HairStyle SET " 
                                                . implode(",", $hairstyle) .   
                                            " WHERE hair_id=?");
                
                $stmt->bind_param('s', $id);
                $stmt->execute();
                if ($db->affected_rows === 0) {
                    json_response([
                        "success" => false,
                        "error" => ['code' => 'RESOURCE_NOT_FOUND',
                                    'message' => '수정할 데이터를 찾을 수 없습니다.']
                    ], 404);
                    return;
                }

                $stmt2 = $db->prepare("SELECT * FROM HairStyle WHERE hair_id=?");
                $stmt2->bind_param('s', $id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $row = $result->fetch_assoc();

                json_response([
                    'success' => true,
                    'date' => ['hairstyle' => $row]
                ]);
            
            } catch (Throwable $e) {
                error_log('[hairstyle]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
            ]],500);
            return;
            }
        }


        // 'DELETE' -> 해당 HairStyle 삭제 
        public function delete(string $hair_id) :void {
            $id = (int)$hair_id ?? 0;

            if ($id <= 0) {
                json_response([
                    'success' => false,
                    'error' => ['code' => 'INVALID_REQUEST',
                                'message' => '유효하지 않은 요청입니다.']
                ], 400);
                return;
            }

            try {
                $db = get_db();

                $stmt = $db->prepare("DELETE FROM HairStyle WHERE hair_id=?");
                $stmt->bind_param('s', $id);
                $stmt->execute();
                
                if ($db->affected_rows === 0) {
                    json_response([
                        "success" => false,
                        "error" => ['code' => 'RESOURCE_NOT_FOUND',
                                    'message' => '삭제할 데이터를 찾을 수 없습니다.']
                    ], 404);
                    return;
                }

                http_response_code(204);
            } catch (Throwable $e) {
                error_log('[hairstyle]'.$e->getMessage());
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