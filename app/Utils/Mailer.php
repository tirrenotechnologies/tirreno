<?php

/**
 * tirreno ~ open-source security framework
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.tirreno.com Tirreno(tm)
 */

declare(strict_types=1);

namespace Tirreno\Utils;

class Mailer {
    public static function send(?string $toName, string $toAddress, string $subj, string $msg, bool $html = false): array {
        $f3 = \Base::instance();
        $canSendEmail = $f3->get('SEND_EMAIL');
        if (!$canSendEmail) {
            return [
                'success' => true,
                'message' => 'Email will not be sent in development mode',
            ];
        }

        $toName = $toName ?? '';
        $data = null;
        if (\Tirreno\Utils\Variables::getMailPassword()) {
            $data = self::sendByMailgun($toAddress, $toName, $subj, $msg, $html);
        }

        if ($data === null || !$data['success']) {
            $data = self::sendByNativeMail($toAddress, $toName, $subj, $msg);
        }

        return $data;
    }

    private static function sendByMailgun(string $toAddress, string $toName, string $subj, string $msg, bool $html): array {
        $f3 = \Base::instance();

        $fromName = \Tirreno\Utils\Constants::get()->MAIL_FROM_NAME;
        $smtpDebug = $f3->get('SMTP_DEBUG');
        $fromAddress = \Tirreno\Utils\Variables::getMailLogin();
        $mailLogin = \Tirreno\Utils\Variables::getMailLogin();
        $mailPassword = \Tirreno\Utils\Variables::getMailPassword();

        if ($fromAddress === null) {
            return [
                'success' => false,
                'message' => 'Admin email is not set.',
            ];
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = $smtpDebug;                                              //Enable verbose debug output
            $mail->isSMTP();                                                            //Send using SMTP
            $mail->Host = \Tirreno\Utils\Constants::get()->MAIL_HOST;                   //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                                     //Enable SMTP authentication
            $mail->Username = $mailLogin;                                               //SMTP username
            $mail->Password = $mailPassword;                                            //SMTP password
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;    //Enable implicit TLS encryption
            $mail->Port = 587;                                                          //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($toAddress, $toName);                                     //Add a recipient
            $mail->addReplyTo($fromAddress, $fromName);

            //Content
            $mail->isHTML($html);                                                       //Set email format to HTML
            $mail->Subject = $subj;
            $mail->Body = $msg;

            $mail->send();

            $success = true;
            $msg = 'Message has been sent';
        } catch (\Exception $e) {
            $success = false;
            $msg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return [
            'success' => $success,
            'message' => $msg,
        ];
    }

    private static function sendByNativeMail(string $toAddress, string $toName, string $subj, string $msg): array {
        $sendMailPath = \Tirreno\Utils\Constants::get()->MAIL_SEND_BIN;

        if (!file_exists($sendMailPath) || !is_executable($sendMailPath)) {
            return [
                'success' => false,
                'message' => 'Sendmail is not installed. Cannot send email.',
            ];
        }

        $fromName = \Tirreno\Utils\Constants::get()->MAIL_FROM_NAME;
        $fromAddress = \Tirreno\Utils\Variables::getMailLogin();

        if ($fromAddress === null) {
            return [
                'success' => false,
                'message' => 'Admin email is not set.',
            ];
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            sprintf('From: %s <%s>', $fromName, $fromAddress),
            sprintf('Reply-To: %s', $fromAddress),
            sprintf('X-Mailer: PHP/%s', phpversion()),
        ];

        $headers = implode("\r\n", $headers);

        $success = mail($toAddress, $subj, $msg, $headers);
        $msg = $success ? 'Message sent' : 'Error occurred';

        return [
            'success' => $success,
            'message' => $msg,
        ];
    }
}
