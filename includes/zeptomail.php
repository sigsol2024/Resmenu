<?php
/**
 * ZeptoMail API helper (transactional email)
 * Uses Send Mail Token via Authorization header.
 */

if (!function_exists('zeptoMailIsEnabled')) {
    function zeptoMailIsEnabled(): bool
    {
        $token = defined('ZEPTOMAIL_SENDMAIL_TOKEN') ? trim((string)ZEPTOMAIL_SENDMAIL_TOKEN) : '';
        if ($token === '') return false;
        // Accept either raw token or a full header value pasted from ZeptoMail UI.
        $token = preg_replace('/^zoho-enczapikey\\s+/i', '', $token);
        return trim((string)$token) !== '';
    }
}

if (!function_exists('zeptoMailSendHtml')) {
    /**
     * @return array{ok:bool,http_code:int,curl_errno:int,response_body:string,request_id:?string,error_message:?string}
     */
    function zeptoMailSendHtml(string $toEmail, string $toName, string $subject, string $htmlBody, array $options = []): array
    {
        if (!function_exists('curl_init')) {
            return [
                'ok' => false,
                'http_code' => 0,
                'curl_errno' => 0,
                'response_body' => '',
                'request_id' => null,
                'error_message' => 'cURL extension not available',
            ];
        }

        $token = defined('ZEPTOMAIL_SENDMAIL_TOKEN') ? trim((string)ZEPTOMAIL_SENDMAIL_TOKEN) : '';
        // Normalize: allow pasting "Zoho-enczapikey <token>" into the config.
        $token = preg_replace('/^zoho-enczapikey\\s+/i', '', $token);
        $token = trim((string)$token);
        if ($token === '') {
            return [
                'ok' => false,
                'http_code' => 0,
                'curl_errno' => 0,
                'response_body' => '',
                'request_id' => null,
                'error_message' => 'ZEPTOMAIL_SENDMAIL_TOKEN not configured',
            ];
        }

        $url = defined('ZEPTOMAIL_URL') && trim((string)ZEPTOMAIL_URL) !== ''
            ? trim((string)ZEPTOMAIL_URL)
            : 'https://api.zeptomail.com/v1.1/email';

        $timeout = defined('ZEPTOMAIL_TIMEOUT_SECONDS') ? max(1, (int)ZEPTOMAIL_TIMEOUT_SECONDS) : 30;

        $fromAddress = trim((string)($options['from_email'] ?? (defined('ZEPTOMAIL_FROM_ADDRESS') ? ZEPTOMAIL_FROM_ADDRESS : '')));
        if ($fromAddress === '') {
            $fromAddress = defined('MAIL_FROM_EMAIL') ? (string)MAIL_FROM_EMAIL : 'noreply@localhost';
        }

        $fromName = trim((string)($options['from_name'] ?? (defined('ZEPTOMAIL_FROM_NAME') ? ZEPTOMAIL_FROM_NAME : '')));
        if ($fromName === '') {
            $fromName = defined('MAIL_FROM_NAME') ? (string)MAIL_FROM_NAME : '';
        }

        $replyTo = trim((string)($options['reply_to'] ?? (defined('ZEPTOMAIL_REPLY_TO') ? ZEPTOMAIL_REPLY_TO : '')));
        $replyToName = trim((string)($options['reply_to_name'] ?? ''));

        $toName = trim($toName) !== '' ? $toName : $toEmail;

        $payload = [
            'from' => array_filter([
                'address' => $fromAddress,
                'name' => $fromName,
            ], static fn($v) => $v !== ''),
            'to' => [[
                'email_address' => array_filter([
                    'address' => $toEmail,
                    'name' => $toName,
                ], static fn($v) => $v !== ''),
            ]],
            'subject' => $subject,
            'htmlbody' => $htmlBody,
        ];

        if ($replyTo !== '') {
            $payload['reply_to'] = [array_filter([
                'address' => $replyTo,
                'name' => $replyToName,
            ], static fn($v) => $v !== '')];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'authorization: Zoho-enczapikey ' . $token,
            ],
        ]);

        $responseBody = (string)curl_exec($curl);
        $curlErrno = (int)curl_errno($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = (string)curl_error($curl);
        curl_close($curl);

        $requestId = null;
        $errorMessage = null;
        $decoded = null;
        if ($responseBody !== '') {
            $decoded = json_decode($responseBody, true);
            if (is_array($decoded)) {
                $requestId = $decoded['request_id'] ?? ($decoded['error']['request_id'] ?? null);
                $errorMessage = $decoded['error']['message'] ?? null;
            }
        }

        if ($curlErrno !== 0) {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'curl_errno' => $curlErrno,
                'response_body' => $responseBody,
                'request_id' => $requestId,
                'error_message' => $curlError !== '' ? $curlError : ($errorMessage ?? 'ZeptoMail curl error'),
            ];
        }

        $ok = ($httpCode >= 200 && $httpCode < 300);
        if (!$ok && $errorMessage === null && is_array($decoded) && isset($decoded['message']) && is_string($decoded['message'])) {
            $errorMessage = $decoded['message'];
        }

        return [
            'ok' => $ok,
            'http_code' => $httpCode,
            'curl_errno' => $curlErrno,
            'response_body' => $responseBody,
            'request_id' => $requestId,
            'error_message' => $ok ? null : ($errorMessage ?? 'ZeptoMail HTTP error'),
        ];
    }
}

