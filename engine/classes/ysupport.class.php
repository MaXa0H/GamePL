<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class ysupport
{
	public static $return = [ ];
	public static function check($key,$key2,$ip){
		db::q ( 'SELECT * FROM login_key where key1="' . $key . '"' );
		if ( db::n () == "1" ) {
			$row = db::r ();
			$rtime = time ();
			$time = round ( $rtime , - 3 );
			$ip = base64_encode ( $ip );
			$agent = base64_encode ('GamePL центр поддержки' );
			db::q ( 'SELECT * FROM users where id="' . $row[ 'user' ] . '"' );
			api::$logget = db::r ();
			if ( $key2 != ( md5 ( $key . round ( $row[ 'time' ] , - 3 ) ) . md5 ( $ip . round ( $row[ 'time' ] , - 3 ) ) ) ) {
				self::error('1');
				return false;
			}
			api::$go = true;
			if ( !api::admin ('support') ) {
				self::error ("У вас нет доступа к данной функции");
				return false;
			}else{
				return true;
			}
		}
		self::error('1');
		return false;
	}
	public static function login($mail,$pass,$ip){
		$login = api::cl ( $mail );
		$pass = $pass;
		if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $login ) ) {
			self::error ('E-mail указан неверно');
		} else {
			if ( ! preg_match ( "/^[0-9a-zA-Z]{6,40}$/i" , $pass ) ) {
				self::error ("Пароль указан неверно" );
			} else {
				db::q ( 'SELECT * FROM users where mail="' . $login . '" and pass="' . $pass . '"' );
				if ( db::n () != 1 ) {
					self::error ("E-mail или пароль указан неверно");
				} else {
					api::$logget = db::r ();
					$row = api::$logget;
					api::$go = true;
					if ( !api::admin ('support') ) {
						self::error ("У вас нет доступа к данной функции");
					}else{
						$rtime = time ();
						$time = round ( $rtime , - 3 );
						$ip = base64_encode ( $ip );
						$agent = base64_encode ( 'GamePL центр поддержки' );
						$key = md5 ( $login . $pass . $time . $ip ) . md5 ( $agent . $time );
						$key2 = md5 ( $key . $time ) . md5 ( $ip . $time );
						db::q ( 'DELETE from login_key where key1="' . $key . '"' );
						$sql12 = db::q ( 'SELECT id FROM login_key where user="' . $row[ 'id' ] . '" order by id desc LIMIT 4,1' );
						if ( db::n ( $sql12 ) != 0 ) {
							$row12 = db::r ( $sql12 );
							db::q ( 'DELETE from login_key where  user="' . $row[ 'id' ] . '" and id<="' . $row12[ 'id' ] . '"' );
						}
						db::q (
							"INSERT INTO login_key set
									user='" . $row[ 'id' ] . "',
									time='" . $rtime . "',
									ip='" . $ip . "',
									key1='" . $key . "',
									agent='" . $agent . "'"
						);
						self::$return['key'] = $key;
						self::$return['key2'] = $key2;
					}
				}
			}
		}
		die( json_encode ( self::$return ) );
	}
	public static function load ()
	{
		self::$return[ 'e' ] = "";
		if(self::check($_POST['key'],$_POST['key2'],$_POST['ip'])){
			$post = $_POST;
			if ( $post[ 'act' ] == "tickets" ) {
				self::$return[ 'tickets' ] = self::tickets ();
			}
			if ( $post[ 'act' ] == "ticket" ) {
				self::$return[ 'ticket' ] = self::ticket ( (int) $post[ 'id' ] );
			}
			if ( $post[ 'act' ] == "cur" ) {
				self::$return[ 'cur' ] = self::ticket_cur ( (int) $post[ 'id' ] );
			}
			if ( $post[ 'act' ] == "send" ) {
				self::mess_add( (int) $post[ 'id' ] ,$post[ 'txt' ] );
				list(self::$return[ 'mes' ],self::$return[ 'last' ])= self::ticket_mess ( (int) $post[ 'id' ],(int) $post[ 'last' ] );
			}
			if ( $post[ 'act' ] == "auto" ) {
				if($post[ 'act2' ]=="ticket"){
					list(self::$return['ticket'][ 'mes' ],self::$return['ticket'][ 'last' ])= self::ticket_mess ( (int) $post[ 'id' ],(int) $post[ 'last' ] );
				}
			}
		}
		die( json_encode ( self::$return ) );
	}

	public static function error ( $error )
	{
		self::$return[ 'e' ] = $error;
	}

	public static function tickets ()
	{
		$sql = db::q ( 'SELECT * FROM support where locked="0" order by time desc ' );
		$tickets = array ();
		while ( $row = db::r ( $sql ) ) {
			db::q ( 'SELECT * FROM support_mes where tid="' . $row[ 'id' ] . '" order by id desc' );
			$row3 = db::r ();
			db::q ( 'SELECT name,lastname FROM users where id="' . $row[ 'user' ] . '"' );
			$row4 = db::r ();
			$ticket = array ();
			$ticket[ 'id' ] = $row[ 'id' ];
			$ticket[ 'name2' ] = base64_decode($row[ 'name' ]);
			$ticket[ 'user_name' ] = $row4[ 'name' ];
			$ticket[ 'user_lname' ] = $row4[ 'lastname' ];
			$ticket[ 'time' ] = api::langdate ( "j F Y - H:i" , $row[ 'time' ] );
			if ( $row[ 'status' ] == 's' ) {
				$stats = '1';
			} else {
				$stats = '0';
			}
			$ticket[ 'status' ] = $stats;
			if ( $row[ 'cur' ] ) {
				db::q ( 'SELECT name,lastname FROM users where id="' . $row[ 'cur' ] . '"' );
				$row4 = db::r ();
				$ticket[ 'cur' ] = $row4[ 'name' ] . ' ' . $row4[ 'lastname' ];
			} else {
				$ticket[ 'cur' ] = "Нет";
			}
			$tickets[ ] = $ticket;
		}

		return $tickets;
	}

	public static function ticket_cur ( $id )
	{
		global $conf;
		$ticket = array ();
		$sql = db::q ( 'SELECT * FROM support where id="' . $id . '"' );
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'cur' ] == 0 ) {
				db::q ( "UPDATE support set cur='" . api::info ( 'id' ) . "' where id='" . $id . "'" );
				$cur[ 'id' ] = api::info ( 'id' );
				db::q ( 'SELECT name,lastname,photo,id FROM users where id="' . api::info ( 'id' ) . '"' );
				$row4 = db::r ();
				if ( $row4[ 'photo' ] ) {
					$cur[ 'photo' ] = '/files/photo/' . $row4[ 'id' ] . '.png';
				} else {
					$cur[ 'photo' ] = '/img/noavatar.png';
				}
				$cur[ 'name' ] = $row4[ 'name' ];
				$cur[ 'lname' ] = $row4[ 'lastname' ];
			} else {
				$cur[ 'id' ] = $row[ 'cur' ];
				db::q ( 'SELECT name,lastname,photo,id FROM users where id="' . $row[ 'cur' ] . '"' );
				$row4 = db::r ();
				if ( $row4[ 'photo' ] ) {
					$cur[ 'photo' ] = '/files/photo/' . $row4[ 'id' ] . '.png';
				} else {
					$cur[ 'photo' ] = '/img/noavatar.png';
				}
				$cur[ 'name' ] = $row4[ 'name' ];
				$cur[ 'lname' ] = $row4[ 'lastname' ];
				self::error ( 'У тикета уже есть оператор' );
			}

			return $cur;
		} else {
			self::error ( 'Тикет не найден' );
		}
	}

	public static function mess_add ( $id , $text )
	{
		$sql = db::q ( 'SELECT * FROM support where id="' . $id . '"' );
		if ( db::n () == 1 ) {
			$row = db::r ();
			db::q ( "INSERT INTO support_mes set
							user='" . api::info ( 'id' ) . "',
							time='" . time () . "',
							mes='" . base64_encode ( api::cl ( $text,1 ) ) . "',
							tid='" . $id . "'");
			db::q ( "UPDATE support set status='" . $row[ 'user' ] . "', time='" . time () . "' where id='" . $id . "'" );
		} else {
			self::error ( 'Тикет не найден' );
		}
	}
	public static function ticket_mess ( $id ,$last=0){
		$sql = db::q ( 'SELECT user FROM support where id="' . $id . '"' );
		$row = db::r ();
		$mess = array ();
		$sql = db::q ( 'SELECT * FROM support_mes where tid="' . $id . '" and id>"'.$last.'" order by id asc' );
		while ( $row2 = db::r ( $sql ) ) {
			$mes = array ();
			$mes[ 'id' ] = $row2[ 'id' ];
			$mes[ 'mes' ] = str_replace ( '\n' , '<br />' , str_replace ( "\n" , '<br />' , base64_decode ( $row2[ 'mes' ] ) ) );
			$mes[ 'time' ] = api::langdate ( "j F Y - H:i" , $row2[ 'time' ] );
			if ( $row[ 'user' ] == $row2[ 'user' ] ) {
				$mes[ 'user' ] = '1';
			} else {
				$mes[ 'user' ] = '0';
			}
			$mess[ ] = $mes;
			$last = $row2[ 'id' ];
		}
		return array($mess,$last);
	}
	public static function ticket ( $id )
	{
		global $conf;
		$ticket = array ();
		$sql = db::q ( 'SELECT * FROM support where id="' . $id . '"' );
		if ( db::n () == 1 ) {
			$row = db::r ();
			db::q ( 'SELECT * FROM users where id="' . $row[ 'user' ] . '"' );
			$row4 = db::r ();
			$ticket[ 'user' ] = $row[ 'user' ];
			$ticket[ 'user_name' ] = $row4[ 'name' ];
			$ticket[ 'user_lname' ] = $row4[ 'lastname' ];
			$ticket[ 'user_mail' ] = $row4[ 'mail' ];
			$ticket[ 'user_phone' ] = $row4[ 'phone' ];
			$ticket[ 'user_balance' ] = $row4[ 'balance' ] . ' ' . $conf[ 'curs-name' ];
			if ( $row[ 'cur' ] ) {
				$ticket[ 'cur' ][ 'id' ] = $row[ 'cur' ];
				db::q ( 'SELECT name,lastname,photo,id FROM users where id="' . $row[ 'cur' ] . '"' );
				$row4 = db::r ();
				if ( $row4[ 'photo' ] ) {
					$ticket[ 'cur' ][ 'photo' ] = '/files/photo/' . $row4[ 'id' ] . '.png';
				} else {
					$ticket[ 'cur' ][ 'photo' ] = '/img/noavatar.png';
				}
				$ticket[ 'cur' ][ 'uname' ] = $row4[ 'name' ];
				$ticket[ 'cur' ][ 'lname' ] = $row4[ 'lastname' ];
			} else {
				$ticket[ 'cur' ][ 'id' ] = 0;
			}
			list($ticket[ 'mes' ],$ticket[ 'last' ])= self::ticket_mess($id);
		}

		return $ticket;
	}
	public static function fast($id){
		tpl::load2('ysupport-base2');
		if ( api::$go ) {
			tpl::set ( '{userid}' , api::$logget[ 'id' ] );
		} else {
			tpl::set ( '{userid}' , '0' );
		}
		tpl::compile('content');
		echo tpl::result ( 'content' );
		die;
	}
}

?>