<?php


 namespace App\Controllers;

    use Throwable;

    require_once __DIR__ . "/../db.php";
    require_once __DIR__ . "/../http.php";

class TimeoffController {

    // 'GET' -> 디자이너 전체 휴무 출력
    public function index():void {
        
        try {
            $db = get_db();

            $stmt = $db->prepare("SELECT * FROM TimeOff ORDER BY start_at ASC, designer_id ASC");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                json_response([
                    'success' => false
                ],404);
                return;
            }

            $timeoff = [];
            while ($row = $result->fetch_assoc()){
                array_push($timeoff, $row);
            }

            json_response([
                'success' => true,
                'date' => ['timeoff' => $timeoff]
            ]);
            
        } catch (Throwable $e) {
                error_log('[timeoff_index]'.$e->getMessage());
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
        
        $date = read_json_body();
        
        $user_id = (int)$date['user_id'] ?? '';
        $start_at =    (string)$date['start_at'] ?? '';
        $end_at =    (string)$date['end_at'] ?? '';

            if ($user_id === '' || $start_at == ''|| $end_at === '') {
                json_response([
                    'success' => false,
                    'error' => ['code' => 'INVALID_REQUEST',
                                'message' => '유효하지 않은 요청입니다.']
                ], 400);
                return;
            }

        try {
            
            $db = get_db();

            $stmt = $db->prepare("INSERT INTO TimeOff (designer_id, start_at, end_at) 
                                    VALUES (?,?,?)");
            $stmt->bind_param('iss', $user_id, $start_at, $end_at);
            $stmt->execute();

            if ($stmt->affected_rows === 0){
                json_response([
                    'success' => false,
                ], 404);
                return;
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

}





?>