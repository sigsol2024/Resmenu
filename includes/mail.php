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

if (!defined('MAIL_PHP_FALLBACK_ENABLED')) {
    define('MAIL_PHP_FALLBACK_ENABLED', true);
}

require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/zeptomail.php';

if (!function_exists('sendEmailRecipientHash')) {
    function sendEmailRecipientHash(string $email): string
    {
        $email = strtolower(trim($email));
        return substr(hash('sha256', $email), 0, 12);
    }
}

if (!function_exists('sendEmailLogTransport')) {
    function sendEmailLogTransport(string $transport, string $status, string $toEmail, array $extra = []): void
    {
        $parts = [];
        $parts[] = 'transport=' . $transport;
        $parts[] = 'status=' . $status;
        $parts[] = 'to_hash=' . sendEmailRecipientHash($toEmail);
        foreach ($extra as $k => $v) {
            if ($v === null || $v === '') continue;
            $parts[] = $k . '=' . $v;
        }
        error_log('sendEmail: ' . implode(' ', $parts));
    }
}

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

    $replyTo = $options['reply_to'] ?? (defined('ZEPTOMAIL_REPLY_TO') ? ZEPTOMAIL_REPLY_TO : '');
    $replyTo = is_string($replyTo) ? trim($replyTo) : '';
    $replyToName = $options['reply_to_name'] ?? '';

    // Primary: ZeptoMail API (transactional)
    if (function_exists('zeptoMailIsEnabled') && zeptoMailIsEnabled()) {
        $zeptoOptions = [
            'reply_to' => $replyTo,
            'reply_to_name' => $replyToName,
        ];
        // Only override ZeptoMail defaults if explicitly provided by the caller.
        if (array_key_exists('from_email', $options)) $zeptoOptions['from_email'] = $fromEmail;
        if (array_key_exists('from_name', $options)) $zeptoOptions['from_name'] = $fromName;

        $zeptoResult = zeptoMailSendHtml((string)$to, (string)($toName ?? ''), (string)$subject, (string)$htmlBody, $zeptoOptions);

        if (!empty($zeptoResult['ok'])) {
            sendEmailLogTransport('zeptomail', 'success', (string)$to, [
                'http' => (string)((int)($zeptoResult['http_code'] ?? 0)),
                'request_id' => (string)($zeptoResult['request_id'] ?? ''),
            ]);
            return true;
        }

        $http = isset($zeptoResult['http_code']) ? (int)$zeptoResult['http_code'] : 0;
        $errno = isset($zeptoResult['curl_errno']) ? (int)$zeptoResult['curl_errno'] : 0;
        $emsg = !empty($zeptoResult['error_message']) ? (string)$zeptoResult['error_message'] : 'unknown error';
        sendEmailLogTransport('zeptomail', 'fail', (string)$to, [
            'http' => (string)$http,
            'curl_errno' => (string)$errno,
            'request_id' => (string)($zeptoResult['request_id'] ?? ''),
            'reason' => $emsg,
        ]);
        // fall through to SMTP
    }

    if (MAIL_ENABLED && defined('SMTP_HOST') && SMTP_HOST && SMTP_HOST !== 'smtp.example.com') {
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->isHTML(true);
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to, $toName ?? '');
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            if ($replyTo !== '') {
                $mail->addReplyTo($replyTo, $replyToName);
            }

            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = !empty(SMTP_USERNAME);
            $mail->Username = SMTP_USERNAME ?? '';
            $mail->Password = SMTP_PASSWORD ?? '';
            $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
            $mail->Port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
            $mail->send();
            sendEmailLogTransport('smtp', 'success', (string)$to, [
                'host' => (string)SMTP_HOST,
            ]);
            return true;
        } catch (PHPMailerException $e) {
            sendEmailLogTransport('smtp', 'fail', (string)$to, [
                'host' => (string)(defined('SMTP_HOST') ? SMTP_HOST : ''),
                'reason' => $e->getMessage(),
            ]);
            // fall through to PHP mail (if enabled)
        }
    }

    if (!MAIL_PHP_FALLBACK_ENABLED) {
        error_log("sendEmail: PHP mail fallback disabled; failing for {$to}");
        return false;
    }

    // Fallback: PHP mail()
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        $replyTo !== '' ? ('Reply-To: ' . $replyTo) : null,
        'X-Mailer: PHP/' . phpversion(),
    ];
    $headers = array_values(array_filter($headers, static fn($h) => is_string($h) && $h !== ''));
    $sent = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    if (!$sent) {
        sendEmailLogTransport('php_mail', 'fail', (string)$to, []);
    } else {
        sendEmailLogTransport('php_mail', 'success', (string)$to, []);
    }
    return $sent;
}
