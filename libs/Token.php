<?php


namespace libs;

use Firebase\JWT\JWT;


class Token {

    protected static $secretKey = 'secret_key';
    protected static $expiry = 86400;
    // protected static $expiry = 10;
    protected static $alg = 'HS256';

    public static function createToken ($userId, $expiry = null) {
        $expiry = isset($expiry) ? time() + $expiry : time() + self::$expiry;

        $data = [
            'exp'  => $expiry,           // Expire
            'userId' => $userId,     // User name
        ];

        return JWT::encode($data, self::$secretKey, self::$alg);

    }

    public static function extractToken ($token) {
        return JWT::decode($token, self::$secretKey, array(self::$alg));
    }

}