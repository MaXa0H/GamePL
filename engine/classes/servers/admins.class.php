<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class servers_admins
{
	public static function base ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'admins' )){
				api::result('Недостаточно привилегий!');
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();

			$adress = $row[ 'ip' ] . ':' . $row[ 'port' ];
			api::nav ( "/servers" , "Серверы" );
			api::nav ( "/servers/base/" . $id , $adress );
			api::nav ( "" , 'Администраторы' , '1' );
			if ( $row[ 'time' ] < time () ) {
				api::result ( 'Срок аренды сервера истек' );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'admins' ) ) {
					servers::$speedbar = $id;
					$class::admins ( $row );
				} else {
					api::result ( 'Данная функция отключена' );
				}
			}
		} else {
			api::result ( 'Сервер не найден' );
		}
	}
}

class source_parser_admins
{
	public static $fhand;
	public static $fend           = false;
	public static $comment        = false;
	public static $turnoffcomment = false;
	public static $level          = 0;
	public static $keyname        = array ();
	public static $keyset         = array ();
	public static $mykey          = array ();

	public static function GetArray ( $file )
	{
		self::OpenFile ( $file );
		while ( ! self::$fend ) {
			$line = self::ReadLine ();
			$pos = 0;
			$len = strlen ( $line );
			while ( $pos < $len ) {
				if ( self::$turnoffcomment == true ) {
					self::$comment = false;
					self::$turnoffcomment = false;
				}
				$char = substr ( $line , $pos , 1 );
				if ( $char == " " || $char == "\t" || $char == "\r" || $char == "\n" ) {
					$pos ++;
					continue;
				}
				switch ( $char ) {
					case "/":
						$char2 = substr ( $line , $pos , 2 );
						if ( $char2 == "/*" ) {
							self::$comment = true;
							break;
						}
						$char2 = substr ( $line , $pos - 1 , 2 );
						if ( $char2 == "*/" && self::$comment == true ) {
							self::$turnoffcomment = true;
							break;
						}
				}
				if ( self::$comment ) {
					$pos ++;
					continue;
				}
				switch ( $char ) {
					case "{":
						self::$level ++;
						self::$keyset[ self::$level ] = false;
						break;
					case "}":
						self::$level --;
						self::$keyset[ self::$level ] = false;
						break;
					case "\"":
						$pos2 = strpos ( $line , "\"" , $pos + 1 );
						$val = substr ( $line , $pos + 1 , ( ( $pos2 - 1 ) - ( $pos ) ) );
						$pos = $pos2;

						if ( self::$keyset[ self::$level ] == false ) {
							self::$keyname[ self::$level ] = $val;
							self::$keyset[ self::$level ] = true;
						} else {
							self::SetKeyVal ( $val , self::$level );
							self::$keyset[ self::$level ] = false;
						}
				}
				$pos ++;
			}
		}
		self::CloseFile ();

		return self::$mykey;
	}

	public static function SetKeyVal ( $val , $lvl )
	{
		$arr = array ();
		$arr = self::RecSet ( $val , $lvl , $arr );
		self::$mykey = array_merge_recursive ( self::$mykey , $arr );
	}

	public static function RecSet ( $val , $lvl , $array , $my = - 1 )
	{
		$my ++;
		if ( $my == $lvl ) {
			$array[ self::$keyname[ $my ] ] = $val;
		} else {
			$array[ self::$keyname[ $my ] ] = self::RecSet ( $val , $lvl , $array , $my );
		}

		return $array;
	}

	public static function ReadLine ()
	{
		if ( self::$fend == true ) {
			return;
		}
		if ( ( $buf = fgets ( self::$fhand ) ) === false ) {
			self::$fend = true;
		} else {
			if ( feof ( self::$fhand ) ) {
				self::$fend = true;
			} else {
				return $buf;
			}
		}
	}

	public static function OpenFile ( $file )
	{
		self::$comment = false;
		self::$turnoffcomment = false;
		self::$keyname = array ();
		self::$keyset = array ();
		self::$mykey = array ();
		self::$keyname[ 0 ] = "Admins";
		self::$level = 0;
		self::$fhand = @fopen ( $file , "r" );
		if ( self::$fhand == false ) {
			$fend = true;
		}
	}

	public static function CloseFile ()
	{
		if ( self::$fhand != false ) {
			fclose ( self::$fhand );
		}
	}
}

?>