<?php

function encode($data)
{
    $encoded = strtr(base64_encode($data), '+/', '-_');
    return rtrim($encoded, '=');
}

/********* JWT을 사용하여 Client_secret 암호화 **********/

function generateJWT($keyFileId, $teamId, $clientId)
{
    $header = [
        'alg' => 'ES256',
        'kid' => $keyFileId
    ];
    $body = [
        'iss' => $teamId,
        'iat' => time(),
        'exp' => time() + 3600,
        'aud' => 'https://appleid.apple.com',
        'sub' => $clientId
    ];

    $privKey = openssl_pkey_get_private(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/interview/20/apple/AuthKey_Alphagong.pem"));

    if (!$privKey) {
        return false;
    }

    $payload = encode(json_encode($header)) . '.' . encode(json_encode($body));

    $signature = '';
    $success = openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
    if (!$success) return false;

    $raw_signature = fromDER($signature, 64);

    return $payload . '.' . encode($raw_signature);
}

/********* 데이터 암호화 함수 **********/

/**
 * @param string $der
 * @param int    $partLength
 *
 * @return string
 */
function fromDER(string $der, int $partLength)
{
    $hex = unpack('H*', $der)[1];
    if ('30' !== mb_substr($hex, 0, 2, '8bit')) { // SEQUENCE
        throw new \RuntimeException();
    }
    if ('81' === mb_substr($hex, 2, 2, '8bit')) { // LENGTH > 128
        $hex = mb_substr($hex, 6, null, '8bit');
    } else {
        $hex = mb_substr($hex, 4, null, '8bit');
    }
    if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
        throw new \RuntimeException();
    }
    $Rl = hexdec(mb_substr($hex, 2, 2, '8bit'));
    $R = retrievePositiveInteger(mb_substr($hex, 4, $Rl * 2, '8bit'));
    $R = str_pad($R, $partLength, '0', STR_PAD_LEFT);
    $hex = mb_substr($hex, 4 + $Rl * 2, null, '8bit');
    if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
        throw new \RuntimeException();
    }
    $Sl = hexdec(mb_substr($hex, 2, 2, '8bit'));
    $S = retrievePositiveInteger(mb_substr($hex, 4, $Sl * 2, '8bit'));
    $S = str_pad($S, $partLength, '0', STR_PAD_LEFT);
    return pack('H*', $R . $S);
}

/**
 * @param string $data
 *
 * @return string
 */
function retrievePositiveInteger(string $data)
{
    while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') > '7f') {
        $data = mb_substr($data, 2, null, '8bit');
    }
    return $data;
}
