<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Include Twilio configuration
require_once __DIR__ . '/../config/twilio.php';

// Email configuration
function sendRentalEmail($user_email, $user_name, $car_details, $rental_details) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'EMAIL';
        $mail->Password   = 'PASSWORD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('raqeebextraid1@gmail.com', 'AR Rentals');
        $mail->addAddress($user_email, $user_name);
        $mail->addReplyTo('raqeebextraid1@gmail.com', 'AR Rentals');

        $mail->isHTML(true);
        $mail->Subject = "Car Rental Confirmation - AR Rentals";
        
        $message = "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif;
                    color: #333333;
                    line-height: 1.6;
                }
                p {
                    color: #333333;
                }
                .container { 
                    padding: 20px;
                    max-width: 600px;
                    margin: 0 auto;
                }
                .header { 
                    background-color: #f8f9fa; 
                    padding: 20px; 
                    text-align: center;
                    border-radius: 5px;
                }
                .details { 
                    margin: 20px 0;
                }
                .rental-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                    background-color: #ffffff;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .rental-table th {
                    background-color: #f8f9fa;
                    padding: 12px;
                    text-align: left;
                    border-bottom: 2px solid #dee2e6;
                }
                .rental-table td {
                    padding: 12px;
                    border-bottom: 1px solid #dee2e6;
                }
                .rental-table tr:last-child td {
                    border-bottom: none;
                }
                .footer { 
                    text-align: center; 
                    margin-top: 20px; 
                    padding-top: 20px;
                    border-top: 1px solid #dee2e6;
                    color: #666666;
                }
                .total-row {
                    font-weight: bold;
                    background-color: #f8f9fa;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Car Rental Confirmation</h2>
                </div>
                <div class='details'>
                    <p>Dear " . htmlspecialchars($user_name) . ",</p>
                    <p>Thank you for choosing AR Rentals. Your car rental has been confirmed.</p>
                    
                    <table class='rental-table'>
                        <tr>
                            <th>Rental Details</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td>Car</td>
                            <td>" . htmlspecialchars($car_details['brand'] . ' ' . $car_details['model']) . "</td>
                        </tr>
                        <tr>
                            <td>Start Date</td>
                            <td>" . date('F d, Y', strtotime($rental_details['start_date'])) . "</td>
                        </tr>
                        <tr>
                            <td>End Date</td>
                            <td>" . date('F d, Y', strtotime($rental_details['end_date'])) . "</td>
                        </tr>
                        <tr class='total-row'>
                            <td>Total Cost</td>
                            <td>$" . number_format($rental_details['total_cost'], 2) . "</td>
                        </tr>
                    </table>
                    
                    <p>Please enjoy your ride and Don't break the car.</p>
                </div>
                <div class='footer'>
                    <p>AR Rentals<br>
                    Email: raqeebextraid1@gmail.com<br>
                    Phone: +92 311 5368378</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message); // Plain text version for non-HTML mail clients

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send rental confirmation email to {$user_email}: " . $mail->ErrorInfo);
        return false;
    }
}

function sendRentalSMS($phone_number, $car_details, $rental_details) {
    $message = "AR Rentals - Rental Confirmation\n\n" .
               "Car: " . $car_details['brand'] . " " . $car_details['model'] . "\n" .
               "Start Date: " . date('M d, Y', strtotime($rental_details['start_date'])) . "\n" .
               "End Date: " . date('M d, Y', strtotime($rental_details['end_date'])) . "\n" .
               "Total Cost: $" . number_format($rental_details['total_cost'], 2) . "\n\n" .
               "Thank you for choosing AR Rentals!";
    
    return sendSMS($phone_number, $message);
}
?> 