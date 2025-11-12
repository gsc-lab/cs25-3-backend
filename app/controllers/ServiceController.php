<?php
    
    namespace App\Controllers;

use Throwable;
use ValueError;

    require_once __DIR__. '/../db.php';
    require_once __DIR__. '/../http.php';

    class ServiceController{

        // 'GET' -> service내용 전체 반환하기
        public function index() :void {
            
            try{
                // DB접속
                $db = get_db();

                // Service테이블의 전체 내용 가져오기
                // sql문
                $stmt = $db->prepare("SELECT service_id, service_name, price, duration_min FROM Service");
                // 실행
                $stmt->execute();
                // 결과 받기
                $result = $stmt->get_result();
                
                // 모둔 Service 정보를 넣는 리스터
                $services = [];
                
                // 반복문을 사용해서 모든 레코드를 리스트에 넣기
                while($row = $result->fetch_assoc()){
                    array_push($services, $row);
                }
                
                // 프런트엔드에 리스터를 반환
                json_response([
                    "success" => true,
                    "date" => ['service' => $services] 
                ]);

            } catch (Throwable $e) {
                error_log('[service_index]' . $e->getMessage());
                json_response([
                    "success" => false,
                    "error" => ['code' => 'INTERNAL_SERVER_ERROR',
                                'message' => '서버 내부 오류가 발생했습니다.']
                ], 500);
            }
        }

        // 'POST' -> service 메뉴 작성
        public function create():void{
            
            // 프론트에서 데이터를 받는다
            $date = read_json_body();

            // 하나씩 꺼내기
            $service_name = (string)$date['service_name'] ?? '';
            $price        = (string)$date['price'] ?? '';
            $duration_min = (int)$date['duration_min'] ?? '';

            // 유호성 검사
            if ($service_name === '' || $price === '' || $duration_min === '') {
                json_response([
                    "success" => false,
                    "error" => ['code' => 'VALIDATION_ERROR', 
                                'message' => '필수 필드가 비었습니다.']
                ], 422);
                return;
            }

            try {
                // DB연결
                $db = get_db();
            
                // sql문
                $stmt = $db->prepare("INSERT INTO Service 
                                        (service_name, price, duration_min) 
                                    VALUES(?,?,?)");
                // binding
                $stmt->bind_param('ssi', $service_name, $price, $duration_min);
                // 실행
                $stmt->execute();

                json_response([
                    "success" => true
                ], 201);
                
                $stmt->close(); 
            } catch (Throwable $e) {
                error_log('[service_create]'.$e->getMessage());
                json_response([
                    "success" => false,
                    "error" => ['code' => 'INTERNAL_SERVER_ERROR',
                                'message' => '서버 내부 오류가 발생했습니다.']
                ], 400);
            }
        } 


        // 'PUT' -> service내용 수정
        public function update(string $service_id) : void {
            // 프론트에서 입력 정보 받기
            $date = read_json_body();

            $id = (int)$service_id ?? 0;

            if ($id <= 0) {
                json_response([
                    "success" => false,
                    "error" => ["code" => "INVALID_REQUEST",
                                "message" => "유효하지 않은 요청입니다."
                    ]
                ],400);
                return;
            }

            // update한 내용을 저장하는 리스터
            $update_service = [];
            
            // 반복문을 사용해서 Key => Value형태로 리스터에 넣기
            foreach ($date as $key => $value) {
                // 값의 유호성 확인
                $value = trim((string)$value) ?? '';
                    if ($value === '') {
                        json_response([
                        "success" => false,
                        "error" => ['code' , 'VALIDATION_ERROR',
                                    'message' , '필수 필드가 비었습니다.'] 
                        ], 422); 
                        return;
                    }
                // 유호하면 예) service_name = 'SHAMPOO' 형태로 변환하기
                $update_value = $key ."="."'".$value."'";
                // 리스터에 넣기
                array_push($update_service, $update_value);
            }            
            
            try {
                // DB접속
                $db = get_db();

                // update sql문
                $stmt = $db->prepare("UPDATE Service SET "
                                    .implode(",", $update_service).
                                    " WHERE service_id=?");
                $stmt->bind_param('i', $id);
                // 실행
                $stmt->execute();
                if($db->affected_rows === 0){
                    json_response([
                        "success" => false,
                        "error" => [
                                "code" => "NO_FIELDS_PROVIDED",
                                "message" => "수정할 필드가 없습니다."
                        ]
                    ], 400);
                    return;
                }
                // 수정 성공
                json_response([
                    "success" => true,
                    "date" => ['service' => $update_service]
                ], 201);
            
            } catch (Throwable $e) {
                error_log('[service_update]'.$e->getMessage());
                json_response([
                    "success" => false,
                    "error" => ['code' => 'INTERNAL_SERVER_ERROR',
                                'message' => '서버 내부 오류가 발생했습니다.']
                ], 500);
                return;
            }
        }


        // 'DELETE' -> service 내용 삭제
        public function delete(string $serevice_id):void{
            
            try {
                // service_id검중
                $id = (int)$serevice_id ?? 0;

                if ($id <= 0) {
                    json_response([
                        "success" => false,
                        "error" => ['code' => 'INVALID_REQUEST',
                                    'message' => '유효하지 않은 요청입니다.']
                    ], 400);
                    return;
                }
                // DB접속
                $db = get_db();
                
                // sql문 
                $stmt = $db->prepare("DELETE FROM Service WHERE service_id=?");
                // 실행
                $stmt->bind_param('i', $id);
                $stmt->execute();
                if ($db->affected_rows === 0){
                    json_response([
                        "success" => false,
                        "error" => ['code' => 'RESOURCE_NOT_FOUND',
                                    'message' => '요청한 리소스를 찾을 수 없습니다.']
                    ], 404);
                    return;
                }

                // 성공
                json_response([
                "success" => true,
                "message" => "삭제되었습니다."
            ], 200);   

            } catch (Throwable $e) {
                error_log('[service_delete]' .$e->getMessage());
                json_response([
                        "success" => false,
                        "error" => ["code" => "PERMISSION_DENIED", 
                                    "message" => "이 작업을 수행할 권한이 없습니다." ]
                ], 500);
                return;
            }
        }
    }

?>