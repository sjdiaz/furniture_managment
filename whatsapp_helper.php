<?php
function sendWhatsAppOrderNotification($phone, $name, $orderId, $totalAmount) {
    $url = 'http://localhost:3000/send-order-message';

    // Admin WhatsApp Number (Sri Lanka: 94776446513)
    $adminPhone = '94776446513';

    $data = array(
        'phone' => $adminPhone, // Customer number-ukku pathila Admin-ukku message pogum
        'name' => $name,
        'orderId' => $orderId,
        'totalAmount' => $totalAmount
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>