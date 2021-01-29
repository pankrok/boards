<?php

namespace Application\Core\Modules\Mailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

 /**
 * Mailer controller
 * @package BOARDS Forum
 */

class MailCore
{
    protected $mailer;
    protected $twig;
    
    public function __construct($twig)
    {
        $config = json_decode(file_get_contents(MAIN_DIR . '/environment/Config/mail.json'), true);
        $this->twig = $twig;
        $this->mailer = new PHPMailer(true);
        try {
            if ($config['type'] == 'SMTP') {
                //Server settings
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                $this->mailer->isSMTP();                                            // Send using SMTP
                $this->mailer->Host       = $config['host'];           	     	    // Set the SMTP server to send through
                $this->mailer->SMTPAuth   = $config['auth'];                    	// Enable SMTP authentication
                $this->mailer->Username   = $config['username'];                    // SMTP username
                $this->mailer->Password   = $config['password'];                    // SMTP password
                if ($config['tls']) {
                    $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                $this->mailer->Port       = $config['port'];                         // TCP port to connect to
                $this->mailer->setFrom($config['username'], $config['name']);
            } elseif ($config['type'] == 'MAIL') {
                $this->mailer->setFrom($config['username'], $config['name']);
            }
            return $this->mailer;
        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
        }
    }

    public function getMailer()
    {
        return $this->mailer;
    }
    
    public function send(string $addres, string $username, string $subject,  string $template, array $variables) : bool
    {
        try {
            $html = file_get_contents(MAIN_DIR."/public/mails/html_$template.twig");
            $txt =  file_get_contents(MAIN_DIR."/public/mails/txt_$template.twig");
            $this->mailer->addAddress($addres, $username);
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $this->twig->fetchFromString($html, $variables);
            $this->mailer->AltBody = $this->twig->fetchFromString($txt, $variables);

            $this->mailer->send();
            return true;
        } catch (\Exception $e) {
            return false;
        }   
    }
}
