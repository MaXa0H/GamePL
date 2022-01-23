<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class ts3
{
	public static $connect = false;
	public static $ok      = "error id=0 msg=ok";
	public static $error = false;
	public static function connect ( $ip ,$port, $login , $pass )
	{
		telnet::connect ( $ip , $port );
		$cmd = "login " . $login . " " . $pass;
		$data = self::rr ( telnet::exec ( $cmd ) );
		if ( ! self::find_error ( $data ) ) {
			return false;
		} else {
			return true;
		}
	}

	public static function rr ( $data )
	{
		$data = explode ( "\n" , trim ( $data ) );

		return $data;
	}

	public static function parse ( $data , $p )
	{
		$data = explode ( $p , trim ( $data ) );

		return $data;
	}

	public static function cmd ( $cmd , $error )
	{
		$data = self::rr ( telnet::exec ( $cmd ) );
		if ( ! $error ) {
			if ( self::find_error ( $data ) ) {
				if(!$data){
					return true;
				}
				return $data;
			} else {
				return false;
			}
		}
		return false;
	}

	public static function find_error ( $data )
	{
		foreach ( $data as $key => $value ) {
			if ( strpos ( $value , "error id=" ) !== false ) {
				$d = explode ( " " , $value );
				$id_error = str_replace ( 'id=' , '' , $d[ '1' ] );
				if ( $id_error != "0" ) {
					$error = str_replace ( '\s' , ' ' , $d[ '2' ] );
					$error = str_replace ( 'msg=' , '' , $error );
					if ( ! servers::$cron ) {
						api::result ( api::cl ( $error ) );
					}
					ts3::$error .= $error;
					return false;
					break;
				}
			}
		}

		return true;
	}

}

class telnet
{
	public static $host;
	public static $port;
	public static $timeout;
	public static $stream_timeout_sec;
	public static $stream_timeout_usec;

	public static $socket       = null;
	public static $buffer       = null;
	public static $prompt;
	public static $errno;
	public static $errstr;
	public static $strip_prompt = true;

	public static $NULL;
	public static $DC1;
	public static $WILL;
	public static $WONT;
	public static $DO;
	public static $DONT;
	public static $IAC;

	public static $global_buffer = '';

	const TELNET_ERROR = false;
	const TELNET_OK    = true;

	public static function connect ( $host = '127.0.0.1' , $port = '23' , $timeout = 10 , $prompt , $stream_timeout = 1 )
	{
		self::$host = $host;
		self::$port = $port;
		self::$timeout = $timeout;
		self::setPrompt ( $prompt );
		self::setStreamTimeout ( $stream_timeout );

		self::$NULL = chr ( 0 );
		self::$DC1 = chr ( 17 );
		self::$WILL = chr ( 251 );
		self::$WONT = chr ( 252 );
		self::$DO = chr ( 253 );
		self::$DONT = chr ( 254 );
		self::$IAC = chr ( 255 );
		if ( ! preg_match ( '/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/' , self::$host ) ) {
			$ip = gethostbyname ( self::$host );
			if ( self::$host == $ip ) {
				api::result ( "Cannot resolve " . self::$host );
			} else {
				self::$host = $ip;
			}
		}
		self::$socket = @fsockopen ( self::$host , self::$port , self::$errno , self::$errstr , self::$timeout );

		if ( ! self::$socket ) {
			api::result ( "Cannot connect to " . self::$host . " on port " . self::$port );
		}

		if ( ! empty( self::$prompt ) ) {
			self::waitPrompt ();
		}

		return self::TELNET_OK;
	}

	public static function disconnect ()
	{
		if ( self::$socket ) {
			if ( ! fclose ( self::$socket ) ) {
				api::result ( "Error while closing telnet socket" );
			}
			self::$socket = null;
		}

		return self::TELNET_OK;
	}

	public static function exec ( $command , $add_newline = true )
	{
		self::write ( $command , $add_newline );
		self::waitPrompt ();

		return trim(self::getBuffer ());
	}

	public static function login ( $username , $password )
	{
		try {
			self::setPrompt ( 'login:' );
			self::waitPrompt ();
			self::write ( $username );
			self::setPrompt ( 'Password:' );
			self::waitPrompt ();
			self::write ( $password );
			self::setPrompt ();
			self::waitPrompt ();
		} catch ( Exception $e ) {
			api::result ( "Login failed." );
		}

		return self::TELNET_OK;
	}

	public static function setPrompt ( $str = '$' )
	{
		return self::setRegexPrompt ( preg_quote ( $str , '/' ) );
	}

	public static function setRegexPrompt ( $str = '\$' )
	{
		self::$prompt = $str;

		return self::TELNET_OK;
	}


	public static function setStreamTimeout ( $timeout )
	{
		self::$stream_timeout_usec = (int) ( fmod ( $timeout , 1 ) * 1000000 );
		self::$stream_timeout_sec = (int) $timeout;
	}

	public static function stripPromptFromBuffer ( $strip )
	{
		self::$strip_prompt = $strip;
	}

	public static function getc ()
	{
		stream_set_timeout ( self::$socket , self::$stream_timeout_sec , self::$stream_timeout_usec );
		$c = fgetc ( self::$socket );
		self::$global_buffer .= $c;

		return $c;
	}

	public static function clearBuffer ()
	{
		self::$buffer = '';
	}

	public static function readTo ( $prompt )
	{
		if ( ! self::$socket ) {
			api::result ( "Telnet connection closed" );
		}
		self::clearBuffer ();
		$until_t = time () + self::$timeout;
		do {
			if ( time () > $until_t ) {
				api::result ( "Couldn't find the requested : '$prompt' within " . self::$timeout . " seconds" );
			}
			$c = self::getc ();

			if ( $c === false ) {
				if ( empty( $prompt ) ) {
					return self::TELNET_OK;
				}
				api::result ( "Couldn't find the requested : '" . $prompt . "', it was not in the data returned from server: " . self::$buffer );
			}

			if ( $c == self::$IAC ) {
				if ( self::negotiateTelnetOptions () ) {
					continue;
				}
			}
			self::$buffer .= $c;
			if ( ! empty( $prompt ) && preg_match ( "/{$prompt}$/" , self::$buffer ) ) {
				return self::TELNET_OK;
			}

		} while ( $c != self::$NULL || $c != self::$DC1 );
	}

	public static function write ( $buffer , $add_newline = true )
	{
		if ( ! self::$socket ) {
			api::result ( "Telnet connection closed" );
		}

		self::clearBuffer ();

		if ( $add_newline == true ) {
			$buffer .= "\n";
		}

		self::$global_buffer .= $buffer;
		if ( ! fwrite ( self::$socket , $buffer ) < 0 ) {
			api::result ( "Error writing to socket" );
		}

		return self::TELNET_OK;
	}

	public static function getBuffer ()
	{
		$buf = preg_replace ( '/\r\n|\r/' , "\n" , self::$buffer );
		if ( self::$strip_prompt ) {
			$buf = explode ( "\n" , $buf );
			unset( $buf[ count ( $buf ) - 1 ] );
			$buf = implode ( "\n" , $buf );
		}

		return trim ( $buf );
	}

	public static function getGlobalBuffer ()
	{
		return self::$global_buffer;
	}

	public static function negotiateTelnetOptions ()
	{
		$c = self::getc ();

		if ( $c != self::$IAC ) {
			if ( ( $c == self::$DO ) || ( $c == self::$DONT ) ) {
				$opt = self::getc ();
				fwrite ( self::$socket , self::$IAC . self::$WONT . $opt );
			} else {
				if ( ( $c == self::$WILL ) || ( $c == self::$WONT ) ) {
					$opt = self::getc ();
					fwrite ( self::$socket , self::$IAC . self::$DONT . $opt );
				} else {
					api::result ( 'Error: unknown control character ' . ord ( $c ) );
				}
			}
		} else {
			api::result ( 'Error: Something Wicked Happened' );
		}

		return self::TELNET_OK;
	}

	public static function waitPrompt ()
	{
		return self::readTo ( self::$prompt );
	}
}

?>