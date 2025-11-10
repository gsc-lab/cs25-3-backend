<?php

namespace App\Controllers;

// DB 로딩
require_once __DIR__."/../db.php";
// http.php 불러오기
require_once __DIR__."/../http.php";

class UsersController
{

    // 'GET' -> 회원 정보 보기
    public function show(string $account_id) :void {

    

        // 유호성 확인
        if ($account === '') {
            json_response([
                'success' => false,
                'error' => ['code' => 'VALIDATION_ERROR', 
                            'message' => '필수 필드가 비었습니다.']
            ], 400);
            return;
        }

        try {
            // DB접속
            $db = get_db();
            // SQL문 (SELECT)
            $stmt = $db->prepare("SELECT * FROM Users WHERE account=?");
            $stmt->bind_param('s',$account);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows === 0){ 
                json_response([
                    'success' => false,
                    'error' => ['code' => 'USER_NOT_FOUND', 
                                'message' => '해당 회원을 찾을 수 없습니다.']
                ], 404);
                return;
            }
            
            $row = $result->fetch_assoc();
            json_response([
                'success' => true,
                'data' => ['user' => $row]
            ],200);
        
            // 오류시 
        } catch (Throwable $e) {
            error_log('[users_show]'. $e->getMessage());
            json_response([
                'success' => false,
                'error' => ['code' => 'INTERNAL_SERVER_ERROR"', 'message' => '서버 오류']
                ], 500);
                return;
            }     
    }


    // 'POST' -> 회원 가입  {account}
    public function create() :void {
        try{
            // 값을 받기
            $date = read_json_body();

            // 입력 값 꺼내기
            $account      = trim((string)($date['account'] ?? ''));
            $password_raw = trim((string)($date['password'] ?? ''));
            $user_name    = trim((string)($date['user_name'] ?? ''));
            $role         = trim((string)($date['role'] ?? ''));
            $gender       = trim((string)($date['gender'] ?? ''));
            $phone        = trim((string)($date['phone'] ?? ''));
            $birth        = trim((string)($date['birth'] ?? ''));

            // 유호성 확인
            if ($account === '' || $password_raw === '' || $user_name === '' ||
                $role === '' || $gender === '' || $birth === '') {
                // 유호하지 않으면 json_responce 반환
                echo json_response([
                    'success' => false,
                    'error' => ['code' => 'VALIDATION_ERROR"', 'message' => '필수 필드가 비었습니다.']
                ], 400);
                return;
            }

            // DB접속
            $db = get_db();
            $stmtSelect = $db->prepare("SELECT 1 FROM Users WHERE account=? LIMIT 1");
            $stmtSelect->bind_param('s', $account);
            $stmtSelect->execute();
            $result = $stmtSelect->get_result();

            // 중복된 account 여부를 확인
            if ($result->num_rows > 0){
                echo json_response([
                    'success' => false,
                    'error' => ['code' => '"ACCOUNT_DUPLICATED', 'message' => '중복된 ID입니다.']
                ], 409);
                return;
            }
            
            // 없으면 password hash처리해 저장
            $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
            
            $stmtInsert = $db->prepare("INSERT INTO Users
                                        (account, password, user_name, role, gender, phone, birth)
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->bind_param('sssssss', $account, $password_hash, $user_name, $role, $gender, $phone, $birth);
            $stmtInsert->execute();

            json_response([
            'account'        => $account,
            'user_name'      => $user_name,
            'birth'          => $birth,
            'phone'          => $phone
        ], 201);
            

        } catch (Throwable $e) {
            error_log('[users_create]' .$e->getMessage());
            json_response([
                'success' => false,
                'error' => ['code' => 'INTERNAL_SERVER_ERROR', 'message' => '서버 내부 오류가 발생했습니다.']
            ], 500);
        }
    } 

    // 회원 정부 수정
    public function update(string $account) :void {
        // DB접속
        $db   = get_db();
        // Reqest버디(JSON)를 배열으로 받는다
        $date = read_json_body();
        // UPDATE하기 위한 배열
        $sets = [];

        foreach ($date as $key => $value) {
            // 값을 문자열으로 바꿔서 앞뒤 공백을 제거
            $val = trim((string)$value);
            // password 변경 때는 hash처리
            if ($key === 'password') {
                $val = password_hash($val, PASSWORD_DEFAULT);
            }
            // 예: user_name='Alice' 같은 형태의 문자열을 만들어서 배열에 추가
            $sets[] = $key."='".(string)$val."'";
            
            // SQL문
            $sql = "UPDATE Users SET ". implode(',' , $sets) . " WHERE account='" . $account ."'";
            
            try {
                // query
                if (!$db->query($sql)) {
                    error_log('[users_update] SQL error: '. $db->error);
                    json_response(['error' => '수정 중 오류가 발생했습니다.'], 409);
                    return;
                }

                // 
                if ($db->affected_rows === 0) {
                    json_response(['error' => '수정된 내용이 없습니다.'], 404);
                    return;
                }

                json_response(['ok' => true]);

            } catch (Throwable $e) {
                error_log('[users_update' . $e->getMessage());
                json_response(['error' => '서버 오류'], 500);
            }
        }
    }

    // DELETE 탈퇴
    public function delete(string $account) :void {

        try{
            // db 접속
            $db = get_db();
            $account = trim((string)$account ?? '');
            $stmt = $db->prepare("DELETE FROM Users WHERE account=?");
            $stmt->bind_param('s',$account);
            $stmt->execute();

            http_response_code(204);
            return;
        } catch (Throwable $e) {
            error_log('[users_delete]' . $e->getMessage());
            json_response(['error' => '서버 오류'], 500);
        }      
    } 

    // POST login
    public function login(): void {

        // JSON데이터를 받는다
        $data = read_json_body();
        $account = (string)($data['account'] ?? '');
        $password = (string)($data['password'] ?? '');
        
        
        // DB접속
        $db = get_db();
        // account를 불어오기
        $stmt = $db->prepare("SELECT user_name, user_id, role, password, account FROM Users WHERE account=?");
        $stmt->bind_param('s',$account);
        $stmt->execute();
        $result = $stmt->get_result();
        // account 일치하면 password 비겨
        if($result->num_rows === 0){
            echo json_response([
                'success' => false,
                'error' => ['code' => 'AUTHENTICATION_FAILED', 'massage' => 'ID가 일치하지 않습니다.']
            ], 401);
            return;
        }

        $row = $result->fetch_assoc();

        // 비밀번호 비겨
        if (password_verify($password , $row['password'])) {
            echo json_response([
                'success' => false,
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => '비밀번호가 일치하지 않습니다.']
            ], 401);
            return;
        }

        // 성공하면 SESSION에 저장
        $_SESSION['user'] = [
            'user_id'   => (int)$row['user_id'],
            'account'   => $row['account'],
            'role'      => $row['role'],
            'user_name' => $row['user_name']
        ];

        echo json_response([
            'success' => true,
            'data' => [
                'user' => [
                    'user_id'    => (int)$row['user_id'],
                    'account'    => $row['account'],
                    'user_name'  => $row['user_name'],
                    'role'       => $row['role']
                ],
                'session' => [
                    'id'  =>  session_id()
                ]
            ]
        ], 200);

    }

    // logout
    public function logout() :void {
        // 지금 session상태가 어떤지 check
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
                        session_destroy();
            }
            http_response_code(204);
        }
    }
}


?>