<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class mail
{
	public static $site_name             = "";
	public static $from                  = "";
	public static $to                    = "";
	public static $subject               = "";
	public static $message               = "";
	public static $header                = "";
	public static $additional_parameters = null;
	public static $error                 = "";
	public static $bcc                   = array ();
	public static $mail_headers          = "";
	public static $html_mail             = 0;
	public static $charset               = 'UTF-8';

	public static $smtp_fp    = false;
	public static $smtp_msg   = "";
	public static $smtp_port  = "";
	public static $smtp_host  = "localhost";
	public static $smtp_user  = "";
	public static $smtp_pass  = "";
	public static $smtp_code  = "";
	public static $smtp_mail  = "";
	public static $smtp_helo  = "";
	public static $send_error = false;

	public static $eol = "\n";

	public static $mail_method = 'aviras';

	public static function gamepl_mail ()
	{
		global $conf;
		self::$mail_method = $conf['mail_type'];
		
		self::$from = $conf['mail'];
		self::$site_name = $_SERVER['HTTP_HOST'];
		self::$smtp_mail = trim($conf['smtp']['mail']) ? trim($conf['smtp']['mail']) : '';
		self::$smtp_helo = trim($conf['smtp']['helo']) ? trim($conf['smtp']['helo']) : 'HELO';
		
		self::$smtp_host = $conf['smtp']['server'];
		self::$smtp_port = intval( $conf['smtp']['port'] );
		self::$smtp_user = $conf['smtp']['mail'];
		self::$smtp_pass = $conf['smtp']['pass'];

		self::$html_mail = true;
	}

	public static function compile_headers ()
	{

		self::$subject = "=?" . self::$charset . "?b?" . base64_encode ( self::$subject ) . "?=";

		if ( self::$site_name ) {
			$from = "=?" . self::$charset . "?b?" . base64_encode ( self::$site_name ) . "?=";
		} else {
			$from = "";
		}

		if ( self::$html_mail ) {
			self::$mail_headers .= "MIME-Version: 1.0" . self::$eol;
			self::$mail_headers .= "Content-type: text/html; charset=\"" . self::$charset . "\"" . self::$eol;
		} else {
			self::$mail_headers .= "MIME-Version: 1.0" . self::$eol;
			self::$mail_headers .= "Content-type: text/plain; charset=\"" . self::$charset . "\"" . self::$eol;
		}

		if ( self::$mail_method != 'smtp' ) {

			if ( count ( self::$bcc ) ) {
				self::$mail_headers .= "Bcc: " . implode ( "," , self::$bcc ) . self::$eol;
			}

		} else {

			self::$mail_headers .= "Subject: " . self::$subject . self::$eol;

			if ( self::$to ) {

				self::$mail_headers .= "To: " . self::$to . self::$eol;
			}

		}

		self::$mail_headers .= "From: \"" . $from . "\" <" . self::$from . ">" . self::$eol;

		self::$mail_headers .= "Return-Path: <" . self::$from . ">" . self::$eol;
		self::$mail_headers .= "X-Priority: 3" . self::$eol;
		self::$mail_headers .= "X-MSMail-Priority: Normal" . self::$eol;
		self::$mail_headers .= "X-Mailer: DLE CMS PHP" . self::$eol;

	}

	public static function send ( $to , $subject , $message )
	{
		self::gamepl_mail ();
		self::$to = preg_replace ( "/[ \t]+/" , "" , $to );
		self::$from = preg_replace ( "/[ \t]+/" , "" , self::$from );

		self::$to = preg_replace ( "/,,/" , "," , self::$to );
		self::$from = preg_replace ( "/,,/" , "," , self::$from );

		if ( self::$mail_method != 'smtp' ) {
			self::$to = preg_replace ( "#\#\[\]'\"\(\):;/\$!�%\^&\*\{\}#" , "" , self::$to );
		} else {
			self::$to = '<' . preg_replace ( "#\#\[\]'\"\(\):;/\$!�%\^&\*\{\}#" , "" , self::$to ) . '>';
		}


		self::$from = preg_replace ( "#\#\[\]'\"\(\):;/\$!�%\^&\*\{\}#" , "" , self::$from );

		self::$subject = $subject;
		self::$message = $message;

		self::$message = str_replace ( "\r" , "" , self::$message );

		self::compile_headers ();

		if ( ( self::$to ) and ( self::$from ) and ( self::$subject ) ) {
			if ( self::$mail_method == 'mail' ) {

				if ( ! @mail ( self::$to , self::$subject , self::$message , self::$mail_headers , self::$additional_parameters ) ) {

					if ( ! @mail ( self::$to , self::$subject , self::$message , self::$mail_headers ) ) {

						self::$smtp_msg = "PHP Mail Error.";
						self::$send_error = true;

					}

				}
			} elseif(self::$mail_method == 'smtp') {
				self::smtp_send ();
			} else {
				self::aviras_send ($to , $subject , $message);
			}

		}

		self::$mail_headers = "";

	}
	public static function aviras_send ( $to , $subject , $message )
	{
		global $conf;
		$d['mail'] = str_replace('/','-',base64_encode($to));
		$d['title'] = str_replace('/','-',base64_encode($subject));
		$d['msg'] = str_replace('/','-',base64_encode($message));
		$d['send'] = str_replace('/','-',base64_encode( $conf['mail']));
		$d['domain'] = str_replace('/','-',base64_encode( $conf['domain']));
		$data = str_replace('/','-',base64_encode(json_encode($d,JSON_UNESCAPED_UNICODE)));
		@file_get_contents('http://gamepl.ru/mailes/send/'.api::$token.'/?body='.$data);
	}
	public static function smtp_get_line ()
	{
		self::$smtp_msg = "";

		while ( $line = fgets ( self::$smtp_fp , 515 ) ) {
			self::$smtp_msg .= $line;

			if ( substr ( $line , 3 , 1 ) == " " ) {
				break;
			}
		}
	}

	public static function smtp_send ()
	{
		ini_set ( 'default_socket_timeout' , '1' );
		self::$smtp_fp = @fsockopen ( self::$smtp_host , intval ( self::$smtp_port ) , $errno , $errstr , 30 );

		if ( ! self::$smtp_fp ) {
			self::smtp_error ( "Could not open a socket to the SMTP server" );

			return;
		}

		self::smtp_get_line ();

		self::$smtp_code = substr ( self::$smtp_msg , 0 , 3 );

		if ( self::$smtp_code == 220 ) {
			$data = self::smtp_crlf_encode ( self::$mail_headers . "\n" . self::$message );

			self::smtp_send_cmd ( self::$smtp_helo . " " . self::$smtp_host );

			if ( self::$smtp_code != 250 ) {
				self::smtp_error ( self::$smtp_helo . " error" );

				return;
			}

			if ( self::$smtp_user and self::$smtp_pass ) {
				self::smtp_send_cmd ( "AUTH LOGIN" );

				if ( self::$smtp_code == 334 ) {
					self::smtp_send_cmd ( base64_encode ( self::$smtp_user ) );

					if ( self::$smtp_code != 334 ) {
						self::smtp_error ( "Username not accepted from the server" );

						return;
					}

					self::smtp_send_cmd ( base64_encode ( self::$smtp_pass ) );

					if ( self::$smtp_code != 235 ) {
						self::smtp_error ( "Password not accepted from the SMTP server" );

						return;
					}
				} else {
					self::smtp_error ( "This SMTP server does not support authorisation" );

					return;
				}
			}

			if ( ! self::$smtp_mail ) {
				self::$smtp_mail = self::$from;
			}

			self::smtp_send_cmd ( "MAIL FROM:<" . self::$smtp_mail . ">" );

			if ( self::$smtp_code != 250 ) {
				self::smtp_error ( "Incorrect FROM address: self::smtp_mail" );

				return;
			}

			$to_array = array ( self::$to );

			if ( count ( self::$bcc ) ) {
				foreach ( self::$bcc as $bcc ) {
					if ( preg_match ( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/" , str_replace ( " " , "" , $bcc ) ) ) {
						$to_array[ ] = "<" . $bcc . ">";
					}
				}
			}

			foreach ( $to_array as $to_email ) {
				self::smtp_send_cmd ( "RCPT TO:" . $to_email );

				if ( self::$smtp_code != 250 ) {
					self::smtp_error ( "Incorrect email address: $to_email" );

					return;
					break;
				}
			}

			self::smtp_send_cmd ( "DATA" );

			if ( self::$smtp_code == 354 ) {
				fputs ( self::$smtp_fp , $data . "\r\n" );
			} else {
				self::smtp_error ( "Error on write to SMTP server" );

				return;
			}

			self::smtp_send_cmd ( "." );

			if ( self::$smtp_code != 250 ) {
				self::smtp_error ( "Error on send mail" );

				return;
			}

			self::smtp_send_cmd ( "quit" );

			if ( self::$smtp_code != 221 ) {
				self::smtp_error ( "Error on quit" );

				return;
			}

			@fclose ( self::$smtp_fp );
		} else {
			self::smtp_error ( "SMTP service unaviable" );

			return;
		}
	}

	public static function smtp_send_cmd ( $cmd )
	{
		self::$smtp_msg = "";
		self::$smtp_code = "";

		fputs ( self::$smtp_fp , $cmd . "\r\n" );

		self::smtp_get_line ();

		self::$smtp_code = substr ( self::$smtp_msg , 0 , 3 );

		return self::$smtp_code == "" ? false : true;
	}

	public static function smtp_error ( $err = "" )
	{
		self::$smtp_msg = $err;
		self::$send_error = true;

		return;
	}

	public static function smtp_crlf_encode ( $data )
	{
		$data .= "\n";
		$data = str_replace ( "\n" , "\r\n" , str_replace ( "\r" , "" , $data ) );
		$data = str_replace ( "\n.\r\n" , "\n. \r\n" , $data );

		return $data;
	}
}

?>