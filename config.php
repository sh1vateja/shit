<?php

$biswa_config =  array(
    "bot_token" => "PASTE_YOUR_BOT_TOKEN",
    "creator" => "Biswa"
);
$bot_token = $biswa_config['bot_token'];

function send_message($chatId, $message, $mode="HTML")
{
    $website = "https://api.telegram.org/bot" . $GLOBALS['bot_token'];
    $url = $website . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message)."&parse_mode=$mode";
    url_get_contents($url);
}

function url_get_contents($Url)
{
    if (!function_exists('curl_init')) {
        die('CURL not found');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
