<?php

namespace App\Controllers;

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../http.php';

class StudentsController
{
    public function index(): void
    {
        try {
            $db = get_db();
            $res = $db->query("SELECT std_id, name, birth, gender, status FROM student ORDER BY std_id DESC");
            $rows = [];
            while ($row = $res->fetch_assoc()) $rows[] = $row;
            json_response($rows);
        } catch (Throwable $e) {
            error_log('[students_index] '.$e->getMessage());
            json_response(['error' => '서버 오류'], 500);
        }
    }

    public function show(int $std_id): void
    {
        try {
            $db = get_db();
            $stmt = $db->prepare("SELECT std_id, name, birth, gender, email, admission_year, current_year, status FROM student WHERE std_id = ?");
            $stmt->bind_param('i', $std_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            if (!$row) { json_response(['error'=>'없음'], 404); return; }
            json_response($row);
        } catch (Throwable $e) {
            error_log('[students_show] '.$e->getMessage());
            json_response(['error' => '서버 오류'], 500);
        }
    }

    public function create(): void
    {
        try {
            $in = read_json_body();

            // 입력 값 꺼내기
            $std_id         = isset($in['std_id'])         ? trim((string)$in['std_id'])         : '';
            $email          = isset($in['email'])          ? trim((string)$in['email'])          : '';
            $password_raw   = isset($in['password'])       ? (string)$in['password']             : '';
            $name           = isset($in['name'])           ? trim((string)$in['name'])           : '';
            $birth          = isset($in['birth'])          ? trim((string)$in['birth'])          : '';
            $gender         = isset($in['gender'])         ? trim((string)$in['gender'])         : '';
            $admission_year = isset($in['admission_year']) ? (int)$in['admission_year']          : 0;
            $current_year   = isset($in['current_year'])   ? (int)$in['current_year']            : 0;
            $status         = isset($in['status'])         ? trim((string)$in['status'])         : '재학'; // 기본값

            // 필수값 체크
            if ($std_id === '' || $email === '' || $password_raw === '' || $name === '' || $birth === '' || $gender === '') {
                json_response(['error' => 'std_id, email, password, name, birth, gender는 필수입니다.'], 422);
                return;
            }

            // 형식/도메인 유효성 검사
            // 추후 구현

            // 비밀번호 해시
            $password_hashed = password_hash($password_raw, PASSWORD_BCRYPT);
            if ($password_hashed === false) {
                json_response(['error' => '비밀번호 해시에 실패했습니다.'], 500); return;
            }

            // DB 연결
            $db = get_db(); // mysqli

            // 이스케이프
            $e_std_id       = $db->real_escape_string($std_id);
            $e_email        = $db->real_escape_string($email);
            $e_password     = $db->real_escape_string($password_hashed);
            $e_name         = $db->real_escape_string($name);
            $e_birth        = $db->real_escape_string($birth);
            $e_gender       = $db->real_escape_string($gender);
            $e_status       = $db->real_escape_string($status);
            $e_adm_year     = (int)$admission_year; // 숫자는 그대로
            $e_current_year = (int)$current_year;

            // 명시적 컬럼 지정 (스키마와 동일한 순서/이름)
            $sql = "
                INSERT INTO student
                    (std_id, email, password, name, birth, gender, admission_year, current_year, status)
                VALUES
                    ('{$e_std_id}', '{$e_email}', '{$e_password}', '{$e_name}', '{$e_birth}', '{$e_gender}', {$e_adm_year}, {$e_current_year}, '{$e_status}')
            ";

            if (!$db->query($sql)) {
                // UNIQUE(email) 충돌, PK(std_id) 충돌 등
                error_log('[students_create] SQL error: '.$db->error);
                json_response(['error' => '데이터 저장 중 오류가 발생했습니다. (중복 여부를 확인해주세요)'], 409);
                return;
            }

            json_response([
                'std_id'         => $std_id,
                'email'          => $email,
                'name'           => $name,
                'birth'          => $birth,
                'gender'         => $gender,
                'admission_year' => $admission_year,
                'current_year'   => $current_year,
                'status'         => $status
            ], 201);

        } catch (Throwable $e) {
            error_log('[students_create] '.$e->getMessage());
            json_response(['error' => '서버 오류'], 500);
        }
    }

    public function update(int $std_id): void
    {
        $db   = get_db();
        $in   = read_json_body();
        $sets = [];

        // 전달된 JSON key → 컬럼으로 간주하고 업데이트
        foreach ($in as $key => $value) {
            $sets[] = $key . "='" . $db->real_escape_string(trim((string)$value)) . "'";
        }

        if (!$sets) {
            json_response(['error' => '변경값 없음'], 422);
            return;
        }

        $e_std_id = $db->real_escape_string($std_id);
        $sql = "UPDATE student SET " . implode(',', $sets) . " WHERE std_id='" . $e_std_id . "'";

        try {
            if (!$db->query($sql)) {
                error_log('[students_update] SQL error: ' . $db->error);
                json_response(['error' => '수정 중 오류가 발생했습니다.'], 409);
                return;
            }

            if ($db->affected_rows === 0) {
                json_response(['error' => '수정된 내용이 없습니다.'], 404);
                return;
            }

            json_response(['ok' => true]);
        } catch (Throwable $e) {
            error_log('[students_update] ' . $e->getMessage());
            json_response(['error' => '서버 오류'], 500);
        }
    }

    public function delete(int $std_id): void
    {
        $db = get_db();
        $e_std_id = $db->real_escape_string($std_id);

        $sql = "DELETE FROM student WHERE std_id='" . $e_std_id . "'";

        try {
            if (!$db->query($sql)) {
                error_log('[students_delete] SQL error: ' . $db->error);
                json_response(['error' => '삭제 중 오류가 발생했습니다.'], 409);
                return;
            }

            if ($db->affected_rows === 0) {
                json_response(['error' => '삭제할 데이터가 없습니다.'], 404);
                return;
            }

            json_response(['ok' => true]);
        } catch (Throwable $e) {
            error_log('[students_delete] ' . $e->getMessage());
            json_response(['error' => '서버 오류'], 500);
        }
    }
}

?>