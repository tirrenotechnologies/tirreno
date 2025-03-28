<?php

/**
 * Tirreno ~ Open source user analytics
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

namespace Utils;

class Mailer {
    public static function send(?string $toName, string $toAddress, string $subject, string $message): array {
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
        if (\Utils\Variables::getMailPassword()) {
            $data = self::sendByMailgun($toAddress, $toName, $subject, $message);
        }

        if ($data === null || !$data['success']) {
            $data = self::sendByNativeMail($toAddress, $toName, $subject, $message);
        }

        return $data;
    }

    private static function sendByMailgun(string $toAddress, string $toName, string $subject, string $message): array {
        $f3 = \Base::instance();

        $fromName = \Utils\Constants::get('MAIL_FROM_NAME');
        $smtpDebug = $f3->get('SMTP_DEBUG');
        $fromAddress = \Utils\Variables::getMailLogin();
        $mailLogin = \Utils\Variables::getMailLogin();
        $mailPassword = \Utils\Variables::getMailPassword();

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
            $mail->Host = \Utils\Constants::get('MAIL_HOST');                                  //Set the SMTP server to send through
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
            $mail->isHTML(false);                                                       //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();

            $success = true;
            $message = 'Message has been sent';
        } catch (\Exception $e) {
            $success = false;
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    private static function sendByNativeMail(string $toAddress, string $toName, string $subject, string $message): array {
        $sendMailPath = \Utils\Constants::get('MAIL_SEND_BIN');

        if (!file_exists($sendMailPath) || !is_executable($sendMailPath)) {
            return [
                'success' => false,
                'message' => 'Sendmail is not installed. Cannot send email.',
            ];
        }

        $fromName = \Utils\Constants::get('MAIL_FROM_NAME');
        $fromAddress = \Utils\Variables::getMailLogin();

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

        $success = mail($toAddress, $subject, $message, $headers);
        $message = $success ? 'Message sent' : 'Error occurred';

        return [
            'success' => $success,
            'message' => $message,
        ];
    }
}
