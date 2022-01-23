<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class ssh
{
	public static $conn;
	public static $error;
	public static $stream;

	public static function login ( $user , $pass , $host , $port = 22 )
	{
		if ( self::connect ( $host , $port ) ) {
			if ( self::auth_pwd ( $user , $pass ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function gh_box ( $id )
	{
		db::q ( 'SELECT * FROM gh_boxes where id="' . $id . '"' );
		if ( db::n () != 1 ) {
			return false;
		} else {
			$row = db::r ();
			if ( self::connect ( servers::ip_server2($id) , $row[ 'port' ] ) ) {
				if ( self::auth_pwd ( $row[ 'login' ] , $row[ 'pass' ] ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

	public static function connect ( $host , $port = 22 )
	{
		global $conf;
		ini_set ( 'default_socket_timeout' , '1' );
		if ( self::$conn = ssh2_connect ( $host , $port ) ) {
			ini_set ( 'default_socket_timeout' , '180' );

			return true;
		} else {
			$key = m::g ( 'box_offline_' . $host );
			if ( empty( $key ) ) {
				m::s ( 'box_offline_' . $host , (time()+600) , 3600 );
			} else {
				if ( $key <time() ) {
					api::inc('sms');
					if ( empty(m::g ( 'box_offline_send_' . $host )) ) {
						$msg = l::t("Пропало соединение с машиной: ") . $host;
						if ( $conf[ 'sms_boxes' ] && $conf[ 'sms_phone_admin' ] ) {
							sms::send ( $conf[ 'sms_phone_admin' ] , $msg );
							m::s ( 'box_offline_send_' . $host , $key , 7200 );
						}
					}
				}
				m::s ( 'box_offline_' . $host , $key , 600 );
			}
			self::$error = '[x] Can not connected to ' . $host . ':' . $port;

			return false;
		}
	}

	public static function auth_pwd ( $u , $p )
	{
		if ( ssh2_auth_password ( self::$conn , $u , $p ) ) {
			return true;
		} else {
			self::$error = 'Login Failed';

			return false;
		}
	}

	public static function send_file ( $localFile , $remoteFile , $permision )
	{
		if ( ssh2_scp_send ( self::$conn , $localFile , $remoteFile , $permision ) ) {
			return true;
		} else {
			self::$error = 'Can not transfer file';

			return false;
		}
	}

	public static function get_file ( $remoteFile , $localFile )
	{
		if ( ssh2_scp_recv ( self::$conn , $remoteFile , $localFile ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function exec_cmd ( $cmd )
	{
		self::$stream = ssh2_exec ( self::$conn , $cmd );
		stream_set_blocking ( self::$stream , true );
	}

	public static function file_stat ( $cmd )
	{
		$sftp = ssh2_sftp ( self::$conn );
		$statinfo = ssh2_sftp_stat ( $sftp , $cmd );

		return $statinfo;
	}

	public static function file_unlink ( $cmd )
	{
		$sftp = ssh2_sftp ( self::$conn );
		ssh2_sftp_unlink ( $sftp , $cmd );
	}

	public static function get_output ()
	{
		$line = '';
		while ( $get = fgets ( self::$stream ) ) {
			$line .= $get;
		}

		return $line;
	}

	public static function disconnect ()
	{
		if ( function_exists ( 'ssh2_disconnect' ) ) {
			ssh2_disconnect ( self::$conn );
		} else {
			@fclose ( self::$conn );
			self::$conn = null;
		}

		return null;
	}
}

?>