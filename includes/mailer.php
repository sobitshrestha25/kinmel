<?php
/*
    Mail Helper
    -------------------------
    Sends the password-reset OTP email.

    IMPORTANT (XAMPP note):
    PHP's built-in mail() function does NOT actually deliver email on
    XAMPP out of the box - XAMPP has no configured mail server. For this
    to really send emails, install PHPMailer and use Gmail SMTP (or any
    SMTP provider) instead. Steps:

    1. Install Composer: https://getcomposer.org/download/
    2. In your kinmel/ folder, run:
         composer require phpmailer/phpmailer
    3. Replace the body of sendOtpEmail() below with the PHPMailer
       version commented at the bottom of this file, and fill in your
       SMTP credentials (e.g. a Gmail address + App Password, NOT your
       normal Gmail password: https://myaccount.google.com/apppasswords).

    Until you do that, this function will just return true without
    actually delivering the email - useful for local testing, since the
    OTP is also written to error_log() so you can find it during dev.
*/

function sendOtpEmail($to_email, $to_name, $otp) {
    $subject = "Your KinMel password reset code";
    $message = "Hi $to_name,\n\nYour password reset code is: $otp\n\nThis code expires in 10 minutes. If you didn't request this, you can ignore this email.\n\n- KinMel";
    $headers = "From: no-reply@kinmel.local\r\n";

    // Log it locally so you can see the OTP while testing without real SMTP set up.
    error_log("KinMel OTP for $to_email: $otp");

    return @mail($to_email, $subject, $message, $headers);

    /* ---------------- PHPMailer version (uncomment after composer install) ----------------

    require __DIR__ . '/../vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com';
        $mail->Password   = 'your_16_char_app_password';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('your_email@gmail.com', 'KinMel');
        $mail->addAddress($to_email, $to_name);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer error: " . $mail->ErrorInfo);
        return false;
    }
    */
}
?>
