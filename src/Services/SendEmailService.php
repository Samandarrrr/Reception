<?php

namespace Src\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class SendMailService
{
    const SENDER_EMAIL = 'saman035dar@gmail.com';

    const EMAIL_PASS = 'jpsenpuqxqntknlk';

    public function send($email, $subject, $message)
    {

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = self::SENDER_EMAIL;
        $mail->Password = self::EMAIL_PASS;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;


        $mail->setFrom(self::SENDER_EMAIL);


        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $message;
        try {

            $mail->send();
            $info = "\nEmail sended\n\n";
        } catch
        (Exception $e) {
            $info =  $e->getMessage();
        }

        return $info;
    }
}
