<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class MinecraftQuery
{


	const STATISTIC = 0x00;
	const HANDSHAKE = 0x09;

	public static $Socket  = false;
	public static $Players = array ();
	public static $Info    = array ();

	public static function Connect ( $Ip , $Port = 25565 , $Timeout = 3 )
	{
		ini_set ( 'default_socket_timeout' , '1' );
		self::$Socket = @FSockOpen ( 'udp://' . $Ip , (int) $Port , $ErrNo , $ErrStr , $Timeout );
		if ( ! self::$Socket ) {
			return false;
		}
		Stream_Set_Timeout ( self::$Socket , $Timeout );
		Stream_Set_Blocking ( self::$Socket , true );
		$Challenge = self::GetChallenge ();
		self::GetStatus ( $Challenge );
		fclose ( self::$Socket );

		return true;
	}

	public static function GetInfo ()
	{
		return isset( self::$Info ) ? self::$Info : false;
	}

	public static function GetPlayers ()
	{
		return isset( self::$Players ) ? self::$Players : false;
	}

	public static function GetChallenge ()
	{
		$Data = self::WriteData ( self :: HANDSHAKE );
		if ( $Data === false ) {
			return false;
		}

		return Pack ( 'N' , $Data );
	}

	public static function GetStatus ( $Challenge )
	{
		$Data = self::WriteData ( self :: STATISTIC , $Challenge . Pack ( 'c*' , 0x00 , 0x00 , 0x00 , 0x00 ) );
		if ( ! $Data ) {
			return false;
		}
		$Last = "";
		$Info = Array ();
		$Data = SubStr ( $Data , 11 );
		$Data = Explode ( "\x00\x00\x01player_\x00\x00" , $Data );
		$Players = SubStr ( $Data[ 1 ] , 0 , - 2 );
		$Data = Explode ( "\x00" , $Data[ 0 ] );
		$Keys = Array (
			'hostname'   => 'HostName' ,
			'gametype'   => 'GameType' ,
			'version'    => 'Version' ,
			'plugins'    => 'Plugins' ,
			'map'        => 'Map' ,
			'numplayers' => 'Players' ,
			'maxplayers' => 'MaxPlayers' ,
			'hostport'   => 'HostPort' ,
			'hostip'     => 'HostIp'
		);
		foreach ( $Data as $Key => $Value ) {
			if ( ~$Key & 1 ) {
				if ( ! Array_Key_Exists ( $Value , $Keys ) ) {
					$Last = false;
					continue;
				}

				$Last = $Keys[ $Value ];
				$Info[ $Last ] = "";
			} else {
				if ( $Last != false ) {
					$Info[ $Last ] = $Value;
				}
			}
		}
		$Info[ 'Players' ] = IntVal ( $Info[ 'Players' ] );
		$Info[ 'MaxPlayers' ] = IntVal ( $Info[ 'MaxPlayers' ] );
		$Info[ 'HostPort' ] = IntVal ( $Info[ 'HostPort' ] );
		if ( $Info[ 'Plugins' ] ) {
			$Data = Explode ( ": " , $Info[ 'Plugins' ] , 2 );

			$Info[ 'RawPlugins' ] = $Info[ 'Plugins' ];
			$Info[ 'Software' ] = $Data[ 0 ];

			if ( Count ( $Data ) == 2 ) {
				$Info[ 'Plugins' ] = Explode ( "; " , $Data[ 1 ] );
			}
		} else {
			$Info[ 'Software' ] = 'Vanilla';
		}

		self::$Info = $Info;

		if ( $Players ) {
			self::$Players = Explode ( "\x00" , $Players );
		}
	}

	public static function WriteData ( $Command , $Append = "" )
	{
		$Command = Pack ( 'c*' , 0xFE , 0xFD , $Command , 0x01 , 0x02 , 0x03 , 0x04 ) . $Append;
		$Length = StrLen ( $Command );
		if ( $Length !== FWrite ( self::$Socket , $Command , $Length ) ) {
			return false;
		}
		$Data = FRead ( self::$Socket , 2048 );
		if ( $Data === false ) {
			return false;
		}
		if ( StrLen ( $Data ) < 5 || $Data[ 0 ] != $Command[ 2 ] ) {
			return false;
		}

		return SubStr ( $Data , 5 );
	}
}
