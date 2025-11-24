<?php

namespace App\Controllers;

use App\Services\ImageService;
use Throwable;

require_once __DIR__.'/../db.php';
require_once __DIR__.'/../http.php';

class SalonController {

    // ===============================
    // 'GET' -> Salon 정보 조회
    // ===============================
    public function index():void
    {

        try {
            $db = get_db(); // DB 연결

            // Salon 테이블에서 전체 데이터 조회 
            $stmt = $db->prepare("SELECT * FROM Salon");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            json_response([
                'success' => true,
                'data' => ['salon' => $row]
            ]);

        } catch (Throwable $e) {
              error_log('[salon_index]'.$e->getMessage());
                json_response([
                    "success" => false,
                    "error"   => [
                        'code'    => 'INTERNAL_SERVER_ERROR', 
                        'message' => '서버 오류가 발생했습니다.'
                    ]
                ],500);
            return;
        } 
    }   


    // ==========================================
    // 'PUT' -> Salon 정보(소개글, 안내문 등) 수정
    // ==========================================
    public function update():void{
        
        // json형태로 데이터를 받음
        $data = read_json_body();

        // JSON 형식이 아닐 경우
        if (!is_array($data)) {
            json_response([
                'success' => false,
                    'error'   => [
                        'code'    => 'INVALID_REQUEST_BODY',
                        'message' => 'JSON 형식의 요청 본문이 필요합니다.',
                    ],
             ], 400);
            return;
        }

        // UPDATE SET 구성용 
        $filds  = []; // "col = ?" 문자열을 담는 배열
        $params = []; // 바인딩할 값들
        $types  = ''; // bind_param 타입 문자열

        // 수정 가능하는 항목
        $allowed = ['introduction', 'information', 'map', 'traffic'];

        // update 
        foreach ($allowed as $fild) {
            if (array_key_exists($fild, $data)) {

                // 문자열 변환 + 공백 제거
                $value = trim((string)$data[$fild]);

                // 검증
                if ($value === '') {
                    json_response([
                        'success' => false,
                        'error'   => [
                            'code'    => 'VALIDATION_ERROR',
                            'message' => '요청 데이터의 형식이 올바르지 않습니다.',
                        ],
                    ], 422);
                    return;
                }

                $filds[]  = $fild .' = ?';
                $params[] = $value;
                $types   .= 's';
            }
        }
         
        // 수정할 항목이 없을 경우
        if (empty($filds)) {
            json_response([
                'success' => false,
                'error'   => [
                    'code'    => 'NO_FIELDS_TO_UPDATE',
                    'message' => '수정할 필드가 없습니다.']
            ], 400);
            return;
        }
       

        try {

            $db = get_db(); // DB접속

            // UPDATE 문 동적 생성
            $stmt = $db->prepare("UPDATE Salon SET "
                    .implode(",",$filds));
            $stmt->bind_param($types, $params);
            $stmt->execute();

            // 변경된 내용이 없는 경우
            if ($stmt->affected_rows === 0){
                json_response([
                    'success' => false,
                    'error'   => [
                        'code'    => 'NO_CHANGES_APPLIED',
                        'message' => '수정된 내용이 없습니다.'
                        ]
                ], 404);
            }

            json_response([
                'success' => true
            ], 201);


        } catch (Throwable $e) {
              error_log('[salon_update]'.$e->getMessage());
                json_response([
                    "success" => false,
                    "error" => [
                        'code'    => 'INTERNAL_SERVER_ERROR', 
                        'message' => '서버 오류가 발생했습니다.'
            ]],500);
            return;
        }    
    }



    // ==========================================
    // 'PUT' -> Salon 이미지 변경 (image, image_key)
    // ==========================================
    public function updateImage():void
    {
        // 파일이 정달됐는지 확인
        if ($_FILES['image']) {
            json_response([
                'success' => false,
                'error'   => [
                    'code'     => 'NO_FILE',
                    'message'  => 'image 파일이 전달되지 않았습니다.']
            ], 400);
        }

        $file = $_FILES['image'];

        try {
            // db 접속
            $db = get_db();

            // DB에 있는 기존의 image를 삭제 하기 위해 데이터 조회
            $stmt = $db->prepare("SELECT * FROM Salon");
            $stmt->execute();
            $result = $stmt->get_result();
            $current = $result->fetch_assoc();

            if (!$current) {
                json_response([
                    'success' => false,
                    'error'   => [
                        'code'    => 'RESOURCE_NOT_FOUND',
                        'message' => '수정할 데이터를 찾을 수 없습니다.'
                    ] 
                ], 404);
                return;
            }

            // MIME 타입 검사 (이미지인지 확인)
            $mime = mime_content_type($_FILES['image']) ?: '';
            if (strpos($mime, 'image/') !== 0) {
                json_response([
                    'success' => false,
                    'error'   => [
                        'code'    => 'INVALID_MIME',
                        'message' => '이미지 파일만 업로드할 수 있습니다.' 
                    ]
                ], 400);
                return;
            }

            // 이미지 업로드 처리
            $imageService = new ImageService();
            $uploadResult = $imageService->upload($file, 'salon');
            $newKey       = $uploadResult['key'];
            $newUrl       = $uploadResult['url'];

            // 기존 이미지 삭제 (실패하더라도 서비스 자체는 계속) 
            try {
                if (!empty($current['image_key'])) {
                    $imageService->delete($current['image_key']);
                }
            } catch (Throwable $e) {
                error_log('[salon_updateImage_delete_old] ' . $e->getMessage());
            }

            // DB 업데이트
            $stmt2 = $db->prepare("UPDATE Salon SET image = ?, image_key = ?");
            $stmt2->bind_param('ss', $newUrl, $newKey);
            $stmt2->execute();

            json_response([
                'success' => true
            ], 201);

        } catch (Throwable $e) {
            error_log('[salon_updateImage] ' . $e->getMessage());
            json_response([
                'success' => false,
                'error'   => [
                    'code'    => 'INTERNAL_SERVER_ERROR',
                    'message' => '서버 오류가 발생했습니다.',
                ],
            ], 500);
        }
    }

}














?>