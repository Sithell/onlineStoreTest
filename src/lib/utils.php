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
    $url = "https://api.telegram.org/bot970690429:AAG0Ydbc8CUIo5p1YJ8CQZu2AhZJXQc4N2o/sendMessage";
    $data = array(
        'chat_id' => 788277446,
        'text' => $message
    );
    httpPost($url, $data);
}
