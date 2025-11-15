<?php


 namespace App\Controllers;

    use Throwable;

    require_once __DIR__ . "/../db.php";
    require_once __DIR__ . "/../http.php";

class TimeoffController {

    // 'GET' -> 디자이너 전체 휴무 출력
    public function index():void {
        
        try {
            $db = get_db(); // DB 연결

            // TimeOff 테이블에서 전체 휴무 조회 + 정렬
            $stmt = $db->prepare("SELECT 
                                    t.to_id,
                                    u.user_name,
                                    t.user_id, 
                                    t.start_at, 
                                    t.end_at              
                                FROM TimeOff AS t
                                JOIN Users AS u
                                    ON t.user_id = u.user_id
                                ORDER BY t.start_at ASC, t.user_id ASC");
            $stmt->execute();
            $result = $stmt->get_result();

            // 조회된 데이터가 없으면 404 반환
            if ($result->num_rows === 0) {
                json_response([
                    'success' => false
                ],404);
                return;
            }

            // 조회된 데이터를 배열에 담기
            $timeoff = [];
            while ($row = $result->fetch_assoc()){
                array_push($timeoff, $row);
            }

            // 성공 응답 반환
            json_response([
                'success' => true,
                'date' => ['timeoff' => $timeoff]
            ]);
        
        // 예외 처리 (서버내 오류 발생지) 
        } catch (Throwable $e) {
            // 에러 내용을 서버 로그에 기록
            error_log('[timeoff_index]'.$e->getMessage());
            // 500 서버 오류 전달
            json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
                ]],500);
            return;
        }
    }


    // 'POST' => designer 휴무 작성
    public function create():void {
        
        $date = read_json_body(); // JSON 요청 파싱
        
        // 필드 값 받기 (형변환 포함)
        $user_id = (int)$date['user_id'] ?? '';
        $start_at =    (string)$date['start_at'] ?? '';
        $end_at =    (string)$date['end_at'] ?? '';

        // 필드 검증
        if ($user_id === '' || $start_at == ''|| $end_at === '') {
            json_response([
                'success' => false,
                'error' => ['code' => 'INVALID_REQUEST',
                            'message' => '유효하지 않은 요청입니다.']
            ], 400);
            return;
        }

        try {
            
            $db = get_db(); // DB 연결

            // INSERT 문 작성
            $stmt = $db->prepare("INSERT INTO TimeOff (user_id, start_at, end_at) 
                                    VALUES (?,?,?)");
            $stmt->bind_param('iss', $user_id, $start_at, $end_at);
            $stmt->execute(); // 실행

            // 영향받은 행이 없다면 실패 처리
            if ($stmt->affected_rows === 0){
                json_response([
                    'success' => false,
                ], 404);
                return;
            } 

            // 성공 응답
            json_response([
                'success' => true
            ]);

        // 예외 처리 (서버내 오류 발생지) 
        } catch (Throwable $e) {
                error_log('[timeoff_create]'.$e->getMessage());
                json_response([
                "success" => false,
                "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                            'message' => '서버 오류가 발생했습니다.'
            ]],500);
            return;
        }
    }

    // 'PUT' -> designer 휴무 수정
    public function update(string $to_id) :void {
        
        # "10", "7", "5" -> ok, int형으로 바꿈 ,  "abc"、""、"0"、"-3" -> fals
        $to_id = filter_var($to_id, FILTER_VALIDATE_INT);

        if ($to_id === false || $to_id <= 0) {
            json_response([
                'success' => false,
                'error' => ['code' => 'INVALID_ID',
                            'massege' => 'ID가 잘못되었습니다. 올바른 숫자 ID를 지정하십시오.']
            ], 400);
            return;
        }

        $to_id = (int)$to_id; // 형변환 확정
        
        // 프론트에서 데이터를 받는다
        $date = read_json_body();
    
        $start_at = (string)$date['start_at'] ?? '';
        $end_at = (string)$date['end_at'] ?? '';
        
        // 유호성 확인
        if ($start_at === '' || $end_at === '' ) {
            json_response([
                    'success' => false,
                    'error' => ['code' => 'VALIDATION_ERROR',
                                'message' => '필수 필드가 비었습니다..']
                ], 422);
            return;
        }
        // update한 데이터를 넣는 리스트
        $timeoff = [];
        
        // key = value 형태로 리스트에 저장
        foreach ($date as $key => $value) {
                $value = "?";
                $v = $key."=".$value;
                array_push($timeoff, $v);   
        }

        try {
            // DB 접속
            $db = get_db();

            // UPDATE SQL문 => imploder() 사용
            $stmt = $db->prepare("UPDATE TimeOff SET "
                                .implode(",", $timeoff). 
                                " WHERE to_id=?");               
            $stmt->bind_param('ssi', $start_at, $end_at, $to_id);
            $stmt->execute();
            
            // 수정된 행이 없다면 데이터 없음 처리
            if ($stmt->affected_rows === 0) {
                json_response([
                    
                     "success" => false,
                     "error" => ['code' => 'RESOURCE_NOT_FOUND',
                                'message' => '수정할 데이터를 찾을 수 없습니다.']
                ], 404);
                return;
            } 
            
            // 성공 응답
            json_response([
                'success' => true
            ]);
        
        // 예외 처리 (서버내 오류 발생지)
        } catch (Throwable $e) {
                error_log('[timeoff_update]'.$e->getMessage());
                json_response([
                    "success" => false,
                    "error" => ['code' => 'INTERNAL_SERVER_ERROR', 
                                'message' => '서버 오류가 발생했습니다.'
                ]],500);
                return;
        }
}


    // 'DELETE' -> designer 휴무 삭제
    public function delete(string $to_id):void{

        // ID 정수 검증
        $to_id = filter_var($to_id, FILTER_VALIDATE_INT);
        
        if ($to_id === false || $to_id <= 0) {
            json_response([
                    'success' => false,
                    'error' => ['code' => 'VALIDATION_ERROR',
                                'message' => '필수 필드가 비었습니다.']
            ], 422);
            return;
        }

        try {

            $db = get_db(); // DB 연결

            // delete문 SQL
            $stmt = $db->prepare("DELETE FROM TimeOff WHERE to_id=?");
            $stmt->bind_param('i',$to_id);
            // 실행
            $stmt->execute();

            // 삭제된 행이 없으면 오류
            if ($stmt->affected_rows === 0){
                    json_response([
                     "success" => false,
                     "error" => ['code' => 'RESOURCE_NOT_FOUND',
                                'message' => '삭제할 데이터를 찾을 수 없습니다.']
                ], 404);
                return;
            }

            // 성공 응답
            json_response([
                'success' => true
            ]);

        // 예외 처리 (서버내 오류 발생지)
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