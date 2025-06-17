<?php
// Twilio configuration
$twilio_account_sid = 'ACCOUNT_ID';
$twilio_auth_token = 'AUTH_TOKEN'; 
$twilio_phone_number = 'TWILIO_PHONE_NUMBER'; 

// Initialize Twilio client
require_once __DIR__ . '/../vendor/autoload.php';
use Twilio\Rest\Client;

function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (substr($phone, 0, 1) === '0') {
        $phone = substr($phone, 1);
    }
    if (substr($phone, 0, 2) !== '92') {
        $phone = '92' . $phone;
    }
    return '+' . $phone;
}

function sendSMS($to, $message) {
    global $twilio_account_sid, $twilio_auth_token, $twilio_phone_number;
    
    try {
        $formatted_number = formatPhoneNumber($to);
        
        $client = new Client($twilio_account_sid, $twilio_auth_token);
        
        $message = $client->messages->create(
            $formatted_number, // To
            [
                'from' => $twilio_phone_number,
                'body' => $message
            ]
        );
        
        return true;
    } catch (Exception $e) {
        error_log("Twilio SMS Error: " . $e->getMessage());
        return false;
    }
}
?> 