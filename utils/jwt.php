<?php
// ===========================================
// JWT Token Utility
// Login হলে এই token app এ পাঠানো হবে
// ===========================================

define('JWT_SECRET', 'change_this_to_a_long_random_secret_key_2024');
define('JWT_EXPIRY', 86400); // 24 ঘণ্টা (seconds)

class JWT {

    // Token তৈরি করো
    public static function generate($payload) {
        $header = base64_encode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));

        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRY;

        $payloadEncoded = base64_encode(json_encode($payload));

        $signature = base64_encode(
            hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true)
        );

        return "$header.$payloadEncoded.$signature";
    }

    // Token verify করো
    public static function verify($token) {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        // Signature check
        $expectedSig = base64_encode(
            hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
        );

        if (!hash_equals($expectedSig, $signature)) {
            return false;
        }

        // Decode payload
        $data = json_decode(base64_decode($payload), true);

        // Expiry check
        if (isset($data['exp']) && $data['exp'] < time()) {
            return false; // Token expired
        }

        return $data;
    }
}
