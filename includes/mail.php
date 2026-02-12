<?php
/**
 * Central Mail Service
 * Uses PHPMailer with SMTP when configured; falls back to PHP mail() otherwise.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if (!defined('MAIL_ENABLED')) {
    define('MAIL_ENABLED', false);
}

if (!defined('MAIL_FROM_EMAIL')) {
    define('MAIL_FROM_EMAIL', 'noreply@localhost');
}

if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', 'Resmenu');
}

require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';

/**
 * Send an HTML email
 *
 * @param string $to Recipient email address
 * @param string $toName Recipient name (optional)
 * @param string $subject Email subject
 * @param string $htmlBody HTML body
 * @param array $options Optional: from_email, from_name, reply_to
 * @return bool True if sent, false otherwise
 */
function sendEmail($to, $toName, $subject, $htmlBody, $options = []) {
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("sendEmail: Invalid recipient: " . (string)$to);
        return false;
    }

    $fromEmail = $options['from_email'] ?? MAIL_FROM_EMAIL;
    $fromName = $options['from_name'] ?? MAIL_FROM_NAME;

    if (MAIL_ENABLED && defined('SMTP_HOST') && SMTP_HOST && SMTP_HOST !== 'smtp.example.com') {
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->isHTML(true);
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to, $toName ?? '');
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            if (!empty($options['reply_to'])) {
                $mail->addReplyTo($options['reply_to'], $options['reply_to_name'] ?? '');
            }

            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = !empty(SMTP_USERNAME);
            $mail->Username = SMTP_USERNAME ?? '';
            $mail->Password = SMTP_PASSWORD ?? '';
            $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
            $mail->Port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log("sendEmail PHPMailer failed to {$to}: {$subject} - " . $e->getMessage());
            return false;
        }
    }

    // Fallback: PHP mail()
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'X-Mailer: PHP/' . phpversion(),
    ];
    $sent = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    if (!$sent) {
        error_log("sendEmail mail() failed to {$to}: {$subject}");
    }
    return $sent;
}
