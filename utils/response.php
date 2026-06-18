<?php
// ===========================================
// API Response Helper
// সব API একই format এ response দেবে
// ===========================================

class Response {

    public static function success($data = [], $message = 'Success', $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }

    public static function error($message = 'Error', $code = 400, $errors = []) {
        http_response_code($code);
        $res = [
            'success' => false,
            'message' => $message
        ];
        if (!empty($errors)) {
            $res['errors'] = $errors;
        }
        echo json_encode($res);
        exit;
    }
}
