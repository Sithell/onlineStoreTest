<?php

function httpPost($url, $data) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function sendMessage($message) {
    $url = "https://api.telegram.org/".CONFIG['telegram']['bot_id'].':'.CONFIG['telegram']['token'].'/sendMessage';
    $data = array(
        'chat_id' => CONFIG['chat_id'],
        'text' => $message
    );
    httpPost($url, $data);
}

function isTokenValid($conn, $token) {
    $stmt = $conn->prepare('SELECT id FROM user WHERE token = :token');
    $stmt->execute([
        'token' => $token
    ]);
    return $stmt->rowCount() > 0;
}

function use_default($args, $defaults) {
    for ($i = 0; $i < count($defaults); $i++) {
        if (is_null($args[$i])) {
            $args[$i] = $defaults[$i];
        }
    }
    return $args;
}

function generate_token($conn, $length = 40) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    while (true) {
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        $stmt = $conn->prepare('
            SELECT id FROM user
            WHERE token = :token;
        ');
        $stmt->execute(array(
            'token' => $string
        ));
        if ($stmt->rowCount() == 0) {
            return $string;
        }
    }
}