<?php

namespace App\Controllers\Mail;

use App\Controllers\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

 /**
 * Mailer controller
 * @package BOARDS Forum
 */

class MailController extends Controller
{
public $mailer;

	public function initMailer($config){
		
		$this->mailer = new PHPMailer(true);
		
		if($config['type'] == 'SMTP'){
			//Server settings
			$this->mailer->isSMTP();                                            // Send using SMTP
			$this->mailer->Host       = $config['host'];           	     	    // Set the SMTP server to send through
			$this->mailer->SMTPAuth   = $config['auth'];                    	// Enable SMTP authentication
			$this->mailer->Username   = $config['username'];                    // SMTP username
			$this->mailer->Password   = $config['password'];                    // SMTP password
			if($config['tls']) $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
			$this->mailer->Port       = $config['port'];                         // TCP port to connect to
		}
		elseif($config['type'] == 'MAIL'){
			
			$this->mailer->setFrom($config['email'], $config['name']);
			
		}
		return $this->mailer;
	}

	public function mailer(){
		return $this->mailer;
	}

}