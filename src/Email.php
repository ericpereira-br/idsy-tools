<?php

namespace Idsy\Tools;

use PHPMailer\PHPMailer\{
    PHPMailer,
    SMTP,
    Exception
};

class Email
{
    public string $from = '';
    public string $to = '';
    public string $subject = '';
    public string $message = '';
    public string $host = '';
    public string $username = '';
    public string $password = '';
    public int $port = 465;

    private function validate()
    {
        if ($this->from == '') {
            throw new Exception('Email "from" field is empty.');
        };

        if ($this->to == '') {
            throw new Exception('Email "to" field is empty.');
        };

        if ($this->subject == '') {
            throw new Exception('Email "subject" field is empty.');
        };

        if ($this->message == '') {
            throw new Exception('Email "message" field is empty.');
        };

        if ($this->host == '') {
            throw new Exception('Email "host" field is empty.');
        };

        if ($this->username == '') {
            throw new Exception('Email "username" field is empty.');
        };

        if ($this->password == '') {
            throw new Exception('Email "password" field is empty.');
        };

        if ($this->port == 0) {
            throw new Exception('Email "port" field is empty.');
        };
    }

    public function send()
    {
        $this->validate();
        $mail = new PHPMailer;

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $this->host;                            //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            // $mail->SMTPSecure = 'tls';
            $mail->Username   = $this->username;                        //SMTP username
            $mail->Password   = $this->password;                        //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $this->port;                            //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($this->username, $this->from);
            $mail->addAddress($this->to, $this->to);               //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $this->subject;
            $mail->Body    = $this->message;
            $mail->Debugoutput = 'error_log';
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            if (!$mail->send()) {
                throw new \Exception($mail->ErrorInfo);
            }

        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
