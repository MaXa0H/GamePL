<?php

final class r
{
	public static $data = array ();
	public static $path = "";

	//Парсим REQUEST на части
	public static function run ()
	{
		//Удаляем GET параметры
		self::$path = parse_url ( $_SERVER[ 'REQUEST_URI' ] , PHP_URL_PATH );
		//Разбиваем на части
		self::$data = explode ( "/" , trim ( self::$path , ' /' ) );
	}

	public static function g ( $id )
	{
		//Получаем значение параметра по id
		if ( ! empty( self::$data[ $id ] ) ) {
			return self::$data[ $id ];
		} else {
			return false;
		}
	}

	//Парсим url по ругулякам возвращая его в массив
	public static function p ( $preg , $vars )
	{
		if ( preg_match ( $preg , self::$path , $matches , PREG_OFFSET_CAPTURE ) ) {
			if ( $vars ) {
				//если передан массив с названиями значений, то присваиваем их
				$data = array ();
				foreach ( $vars as $key => $value ) {
					$data[ $value ] = $matches[ $key ][ 0 ];
				}

				return $data;
			}

			//массив не пришел, выводим все данные, не рекомендую так делать.
			return $matches;
		} else {
			return false;
		}
	}
}

final class db
{
	public static $db_id         = false;
	public static $connected     = false;
	public static $query_num     = 0;
	public static $query_list    = array ();
	public static $mysql_version = '';
	public static $mysql_extend  = "MySQL";
	public static $query_id      = false;
	public static $time          = 0;

	public static function c ( $db_user , $db_pass , $db_name , $db_location = 'localhost' )
	{
		$time_before = api::get_real_time ();
		if ( ! self::$db_id = @mysql_connect ( $db_location , $db_user , $db_pass ) ) {
			exit;
		}
		if ( ! @mysql_select_db ( $db_name , self::$db_id ) ) {
			exit;
		}
		self::$mysql_version = mysql_get_server_info ();
		if ( version_compare ( self::$mysql_version , '4.1' , ">=" ) ) {
			mysql_query ( "/*!40101 SET NAMES 'utf8' */" );
		}
		self::$connected = true;
		self::$time += api::get_real_time () - $time_before;

		return true;
	}

	public static function q ( $query , $show_error = true )
	{
		global $conf;
		$time_before = api::get_real_time ();
		if ( ! self::$connected ) {
			db::c ( $conf[ 'db_users_user' ] , $conf[ 'db_users_pass' ] , $conf[ 'db_users_name' ] , $conf[ 'db_users_host' ] );
		}
		if ( ! ( self::$query_id = mysql_query ( $query , self::$db_id ) ) ) {
			die( mysql_error () );
		}
		self::$query_num ++;
		self::$time += api::get_real_time () - $time_before;

		return self::$query_id;
	}

	public static function r ( $query_id = '' )
	{
		if ( $query_id == '' ) {
			$query_id = self::$query_id;
		}

		return @mysql_fetch_assoc ( $query_id );
	}

	public static function n ( $query_id = '' )
	{
		if ( $query_id == '' ) {
			$query_id = self::$query_id;
		}

		return mysql_num_rows ( $query_id );
	}


	public static function i ()
	{
		return mysql_insert_id ( self::$db_id );
	}

	public static function s ( $source )
	{
		global $conf;
		if ( ! self::$db_id ) {
			db::c ( $conf[ 'db_users_user' ] , $conf[ 'db_users_pass' ] , $conf[ 'db_users_name' ] , $conf[ 'db_users_host' ] );
		}
		if ( self::$db_id ) {
			return mysql_real_escape_string ( $source , self::$db_id );
		} else {
			return addslashes ( $source );
		}
	}

	public static function e ()
	{
		@mysql_close ( self::$db_id );
	}
}

final class tpl
{
	public static $template   = array ();
	public static $tpl        = array ();
	public static $data       = array ();
	public static $block_data = array ();
	public static $result     = array ();
	public static $id         = 0;
	public static $time       = 0;

	public static function set ( $name , $var )
	{
		if ( is_array ( $var ) && count ( $var ) ) {
			foreach ( $var as $key => $key_var ) {
				tpl::set ( $key , $key_var );
			}
		} else {
			self::$data[ self::$id ][ $name ] = $var;
		}
	}

	public static function set_block ( $name , $var )
	{
		if ( is_array ( $var ) && count ( $var ) ) {
			foreach ( $var as $key => $key_var ) {
				tpl::set_block ( $key , $key_var );
			}
		} else {
			self::$block_data[ self::$id ][ $name ] = $var;
		}
	}
	public static function load2 ( $name )
	{
		global $conf;
		$time_before = api::get_real_time ();
		self::$id ++;
		if ( empty( self::$tpl[ $name ] ) ) {
			self::$template[ self::$id ] = base64_decode ( m::g ( l::$lang.'_tpla_' . $name ) );
			if ( empty( self::$template[ self::$id ] ) ) {
				db::q ( 'SELECT * FROM admin_tpl where name="' . $name . '"' );
				if ( db::n () != "1" ) {
					die( '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "Невозможно загрузить шаблон: " . $name );
				}
				$data = db::r ();
				self::$template[ self::$id ] = base64_decode ( $data[ 'tpl' ] );
				self::$template[ self::$id ] = str_replace ( "{url}" , 'http://' . $conf[ 'domain' ] . '/' , self::$template[ self::$id ] );
				self::$template[ self::$id ] = str_replace ( "{lang}" , l::$lang , self::$template[ self::$id ] );
				self::$template[ self::$id ] = str_replace ( "{domain}" , $conf[ 'domain' ] , self::$template[ self::$id ] );
				self::$template[ self::$id ] = str_replace ( "{curs-name}" , $conf[ 'curs-name' ] , self::$template[ self::$id ] );
				self::$template[ self::$id ] = preg_replace_callback( "#\\{lang=(.+?)\\}#i", function($matches) {
					return l::t($matches['1']);
				}, self::$template[ self::$id ] );

				m::s ( l::$lang.'_tpla_' . $name , base64_encode ( self::$template[ self::$id ] ) , 3600 );
			}
		} else {
			self::$template[ self::$id ] = self::$tpl[ $name ];
		}
		if ( api::$go ) {
			self::$template[ self::$id ] = preg_replace ( "'\\[login\\](.*?)\\[/login\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[nologin\\](.*?)\\[/nologin\\]'si" , "" , self::$template[ self::$id ] );
		} else {
			self::$template[ self::$id ] = preg_replace ( "'\\[login\\](.*?)\\[/login\\]'si" , "" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[nologin\\](.*?)\\[/nologin\\]'si" , "\\1" , self::$template[ self::$id ] );
		}
		if ( api::admin () ) {
			self::$template[ self::$id ] = preg_replace ( "'\\[admin\\](.*?)\\[/admin\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[noadmin\\](.*?)\\[/noadmin\\]'si" , "" , self::$template[ self::$id ] );
		} else {
			self::$template[ self::$id ] = preg_replace ( "'\\[noadmin\\](.*?)\\[/noadmin\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[admin\\](.*?)\\[/admin\\]'si" , "" , self::$template[ self::$id ] );
		}
		if ( api::ajax () ) {
			self::$template[ self::$id ] = preg_replace ( "'\\[ajax\\](.*?)\\[/ajax\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[noajax\\](.*?)\\[/noajax\\]'si" , "" , self::$template[ self::$id ] );
		} else {
			self::$template[ self::$id ] = preg_replace ( "'\\[ajax\\](.*?)\\[/ajax\\]'si" , "" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[noajax\\](.*?)\\[/noajax\\]'si" , "\\1" , self::$template[ self::$id ] );
		}
		if ( api::modal () ) {
			self::$template[ self::$id ] = preg_replace ( "'\\[modal\\](.*?)\\[/modal\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[nomodal\\](.*?)\\[/nomodal\\]'si" , "" , self::$template[ self::$id ] );
		} else {
			self::$template[ self::$id ] = preg_replace ( "'\\[modal\\](.*?)\\[/modal\\]'si" , "" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[nomodal\\](.*?)\\[/nomodal\\]'si" , "\\1" , self::$template[ self::$id ] );
		}
		self::$template[ self::$id ] = preg_replace_callback( "#\\{lang=(.+?)\\}#i", function($matches) {
			return l::t($matches['1']);
		}, self::$template[ self::$id ] );
		self::$time += api::get_real_time () - $time_before;

		return true;
	}
	public static function load ( $name )
	{
		global $conf;
		$time_before = api::get_real_time ();
		self::$id ++;
		if ( empty( self::$tpl[ $name ] ) ) {
			self::$template[ self::$id ] = base64_decode ( m::g ( l::$lang.'_tpl_' . $name ) );
			if ( empty( self::$template[ self::$id ] ) ) {
				if($conf['tpl']==1){
					db::q ( 'SELECT * FROM tpl2 where name="' . $name . '"' );
				}elseif($conf['tpl']==2){
					db::q ( 'SELECT * FROM tpl3 where name="' . $name . '"' );
				}else{
					db::q ( 'SELECT * FROM tpl where name="' . $name . '"' );
				}
				if ( db::n () != "1" ) {
					die( '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "Невозможно загрузить шаблон: " . $name );
				}
				$data = db::r ();
				self::$template[ self::$id ] = base64_decode ( $data[ 'tpl' ] );
				self::$template[ self::$id ] = str_replace ( "{url}" , 'http://' . $conf[ 'domain' ] . '/' , self::$template[ self::$id ] );
				self::$template[ self::$id ] = str_replace ( "{lang}" , l::$lang , self::$template[ self::$id ] );
				self::$template[ self::$id ] = str_replace ( "{domain}" , $conf[ 'domain' ] , self::$template[ self::$id ] );
				self::$template[ self::$id ] = str_replace ( "{curs-name}" , $conf[ 'curs-name' ] , self::$template[ self::$id ] );
				self::$template[ self::$id ] = preg_replace_callback( "#\\{lang=(.+?)\\}#i", function($matches) {
					return l::t($matches['1']);
				}, self::$template[ self::$id ] );

				m::s ( l::$lang.'_tpl_' . $name , base64_encode ( self::$template[ self::$id ] ) , 3600 );
			}
		} else {
			self::$template[ self::$id ] = self::$tpl[ $name ];
		}
		if ( api::$go ) {
			self::$template[ self::$id ] = preg_replace ( "'\\[login\\](.*?)\\[/login\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[nologin\\](.*?)\\[/nologin\\]'si" , "" , self::$template[ self::$id ] );
		} else {
			self::$template[ self::$id ] = preg_replace ( "'\\[login\\](.*?)\\[/login\\]'si" , "" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[nologin\\](.*?)\\[/nologin\\]'si" , "\\1" , self::$template[ self::$id ] );
		}
		if ( api::admin () ) {
			self::$template[ self::$id ] = preg_replace ( "'\\[admin\\](.*?)\\[/admin\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[noadmin\\](.*?)\\[/noadmin\\]'si" , "" , self::$template[ self::$id ] );
		} else {
			self::$template[ self::$id ] = preg_replace ( "'\\[noadmin\\](.*?)\\[/noadmin\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[admin\\](.*?)\\[/admin\\]'si" , "" , self::$template[ self::$id ] );
		}
		if ( api::ajax () ) {
			self::$template[ self::$id ] = preg_replace ( "'\\[ajax\\](.*?)\\[/ajax\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[noajax\\](.*?)\\[/noajax\\]'si" , "" , self::$template[ self::$id ] );
		} else {
			self::$template[ self::$id ] = preg_replace ( "'\\[ajax\\](.*?)\\[/ajax\\]'si" , "" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[noajax\\](.*?)\\[/noajax\\]'si" , "\\1" , self::$template[ self::$id ] );
		}
		if ( api::modal () ) {
			self::$template[ self::$id ] = preg_replace ( "'\\[modal\\](.*?)\\[/modal\\]'si" , "\\1" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[nomodal\\](.*?)\\[/nomodal\\]'si" , "" , self::$template[ self::$id ] );
		} else {
			self::$template[ self::$id ] = preg_replace ( "'\\[modal\\](.*?)\\[/modal\\]'si" , "" , self::$template[ self::$id ] );
			self::$template[ self::$id ] = preg_replace ( "'\\[nomodal\\](.*?)\\[/nomodal\\]'si" , "\\1" , self::$template[ self::$id ] );
		}
		self::$template[ self::$id ] = preg_replace_callback( "#\\{lang=(.+?)\\}#i", function($matches) {
			return l::t($matches['1']);
		}, self::$template[ self::$id ] );
		self::$time += api::get_real_time () - $time_before;

		return true;
	}
	public static function _clear ()
	{
		self::$block_data[ self::$id ] = array ();
		self::$data[ self::$id ] = array ();
	}

	public static function compile ( $tpl )
	{
		$time_before = api::get_real_time ();
		if ( count ( self::$block_data[ self::$id ] ) ) {
			foreach ( self::$block_data[ self::$id ] as $key_find => $key_replace ) {
				$find_preg[ ] = $key_find;
				$replace_preg[ ] = $key_replace;
			}
			self::$template[ self::$id ] = preg_replace ( $find_preg , $replace_preg , self::$template[ self::$id ] );
		}
		foreach ( self::$data[ self::$id ] as $key_find => $key_replace ) {
			$find[ ] = $key_find;
			$replace[ ] = $key_replace;
		}
		self::$template[ self::$id ] = str_replace ( $find , $replace , self::$template[ self::$id ] );

		if ( isset( self::$result[ $tpl ] ) ) {
			self::$result[ $tpl ] .= self::$template[ self::$id ];
		} else {
			self::$result[ $tpl ] = self::$template[ self::$id ];
		}
		self::$template[ self::$id ] = null;
		self::_clear ();
		self::$id = self::$id - 1;
		self::$time += api::get_real_time () - $time_before;
	}

	public static function result ( $name )
	{
		return self::$result[ $name ];
	}

	public static function clear ( $name )
	{
		unset( self::$result[ $name ] );
	}
}

final class m
{
	public static $time  = 0;
	public static $cache = array ();

	public static function connect ()
	{
		global $conf , $m;
		$time_before = api::get_real_time ();
		if(!$m->connect ( $conf[ 'm_ip' ] , $conf[ 'm_port' ] )){
			echo 'Не удалось установить связь с Memcached';
			die;
		}
		self::$time += api::get_real_time () - $time_before;
	}

	public static function g ( $name )
	{
		global $m;
		$name = base64_encode($_SERVER[ 'HTTP_HOST' ]).'_'.$name;
		$time_before = api::get_real_time ();
		if ( self::$cache[ $name ] ) {
			$data = self::$cache[ $name ];
		} else {
			$data = $m->get ( $name );
			self::$cache[ $name ] = $data;
		}
		self::$time += api::get_real_time () - $time_before;

		return $data;
	}

	public static function s ( $name , $data , $time )
	{
		global $m;
		$name = base64_encode($_SERVER[ 'HTTP_HOST' ]).'_'.$name;
		$time_before = api::get_real_time ();
		$m->set ( $name , $data , false , $time );
		self::$time += api::get_real_time () - $time_before;
	}

	public static function d ( $name )
	{
		global $m;
		$name = base64_encode($_SERVER[ 'HTTP_HOST' ]).'_'.$name;
		$time_before = api::get_real_time ();
		$m->delete ( $name );
		unset( self::$cache[ $name ] );
		self::$time += api::get_real_time () - $time_before;
	}
}

final class api
{
	public static $langdate     = array ( 'January' => "января" , 'February' => "февраля" , 'March' => "марта" , 'April' => "апреля" , 'May' => "мая" , 'June' => "июня" , 'July' => "июля" , 'August' => "августа" , 'September' => "сентября" , 'October' => "октября" , 'November' => "ноября" , 'December' => "декабря" , 'Jan' => "янв" , 'Feb' => "фев" , 'Mar' => "мар" , 'Apr' => "апр" , 'Jun' => "июн" , 'Jul' => "июл" , 'Aug' => "авг" , 'Sep' => "сен" , 'Oct' => "окт" , 'Nov' => "ноя" , 'Dec' => "дек" , 'Sunday' => "Воскресенье" , 'Monday' => "Понедельник" , 'Tuesday' => "Вторник" , 'Wednesday' => "Среда" , 'Thursday' => "Четверг" , 'Friday' => "Пятница" , 'Saturday' => "Суббота" , 'Sun' => "Вс" , 'Mon' => "Пн" , 'Tue' => "Вт" , 'Wed' => "Ср" , 'Thu' => "Чт" , 'Fri' => "Пт" , 'Sat' => "Сб" , );
	public static $langtranslit = array ( 'а' => 'a' , 'б' => 'b' , 'в' => 'v' , 'г' => 'g' , 'д' => 'd' , 'е' => 'e' , 'ё' => 'e' , 'ж' => 'zh' , 'з' => 'z' , 'и' => 'i' , 'й' => 'y' , 'к' => 'k' , 'л' => 'l' , 'м' => 'm' , 'н' => 'n' , 'о' => 'o' , 'п' => 'p' , 'р' => 'r' , 'с' => 's' , 'т' => 't' , 'у' => 'u' , 'ф' => 'f' , 'х' => 'h' , 'ц' => 'c' , 'ч' => 'ch' , 'ш' => 'sh' , 'щ' => 'sch' , 'ь' => '' , 'ы' => 'y' , 'ъ' => '' , 'э' => 'e' , 'ю' => 'yu' , 'я' => 'ya' , "ї" => "yi" , "є" => "ye" , 'А' => 'A' , 'Б' => 'B' , 'В' => 'V' , 'Г' => 'G' , 'Д' => 'D' , 'Е' => 'E' , 'Ё' => 'E' , 'Ж' => 'Zh' , 'З' => 'Z' , 'И' => 'I' , 'Й' => 'Y' , 'К' => 'K' , 'Л' => 'L' , 'М' => 'M' , 'Н' => 'N' , 'О' => 'O' , 'П' => 'P' , 'Р' => 'R' , 'С' => 'S' , 'Т' => 'T' , 'У' => 'U' , 'Ф' => 'F' , 'Х' => 'H' , 'Ц' => 'C' , 'Ч' => 'Ch' , 'Ш' => 'Sh' , 'Щ' => 'Sch' , 'Ь' => '' , 'Ы' => 'Y' , 'Ъ' => '' , 'Э' => 'E' , 'Ю' => 'Yu' , 'Я' => 'Ya' , "Ї" => "yi" , "Є" => "ye" , );
	public static $logget       = array ();
	public static $go           = false;
	public static $rules        = array ();
	public static $speedbar     = false;
	public static $time         = 0;
	public static $token         = false;
	public static $admin         = false;
	public static $demo         = false;
	public static $inc         = array();
	public static function inc ( $data )
	{
		global $conf,$cron,$install;
		if ( ! self::$inc[$data] ) {
			try {
				$true = false;
				$file = ROOT . '/engine/classes/' . $data . '.class.php';
				if ( ! @include_once ( $file ) ) {
					throw new Exception ( l::t ('Не удалось загрузить модуль ') . $data );
				}
				self::$inc[$data] = 1;
				return true;
			} catch ( Exception $e ) {
				api::e404 ( $e->getMessage () );

				return false;
			}
		}
	}

	public static function price ( $data )
	{
		return round ( (float) $data , 2 );
	}

	public static function getNumEnding ( $number , $endingArray )
	{
		$number = $number % 100;
		if ( $number >= 11 && $number <= 19 ) {
			$ending = $endingArray[ 2 ];
		} else {
			$i = $number % 10;
			switch ( $i ) {
				case ( 1 ):
					$ending = $endingArray[ 0 ];
					break;
				case ( 2 ):
				case ( 3 ):
				case ( 4 ):
					$ending = $endingArray[ 1 ];
					break;
				default:
					$ending = $endingArray[ 2 ];
			}
		}

		return $ending;
	}

	public static function totranslit ( $var )
	{
		$var = trim ( strip_tags ( $var ) );
		$var = preg_replace ( "/\s+/ms" , "-" , $var );
		$var = str_replace ( "/" , "-" , $var );
		$var = preg_replace ( '#[\-]+#i' , '-' , $var );
		$var = strtr ( $var , self::$langtranslit );
		$var = preg_replace ( "/[^a-zA-ZА-Яа-я0-9-\s]/" , "" , $var );

		return $var;
	}

	public static function log_balance ( $user , $mes , $tip , $sum )
	{
		db::q (
			"INSERT INTO logs_balance set
			user='" . $user . "',
			mes='" . $mes . "',
			tip='" . $tip . "',
			time='" . time () . "',
			sum='" . $sum . "'"
		);
	}

	public static function captcha_create ()
	{
		$rand_k1 = mt_rand ( 10 , 50 );
		$rand_k2 = mt_rand ( 10 , 50 );
		$rand_ks = $rand_k1 + $rand_k2;
		$_SESSION[ 'captcha' ] = $rand_ks;
		tpl::set ( '{captcha}' , $rand_k1 . '+' . $rand_k2 );
	}

	public static function captcha_chek ()
	{
		if ( self::admin () ) {

			return true;
		}
		if ( $_SESSION[ 'captcha' ] != $_POST[ 'captcha' ] ) {
			api::result ( l::t ('Ошибка проверки капчи') );

			return false;
		} else {
			return true;
		}
	}

	public static function admin ( $cfg = "0" )
	{
		global $cron;
		if ( $cron ) {
			return true;
		}
		if ( api::info ( 'ugroup' ) == "1" ) {
			return true;
		} else {
			if ( self::$rules ) {
				if ( $cfg != "0" ) {
					if ( self::$rules[ $cfg ] == 1 ) {
						return true;
					} else {
						return false;
					}
				} else {
					return true;
				}
			} else {
				$sql = db::q ( 'SELECT * FROM admins where user="' . api::info ( 'id' ) . '"' );
				if ( db::n ( $sql ) == 1 ) {
					$row = db::r ( $sql );
					$data = json_decode ( $row[ 'data' ] , true );
					self::$rules = $data;
					if ( $cfg != "0" ) {
						if ( $data[ $cfg ] == 1 ) {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				} else {
					return false;
				}
			}
		}
	}

	public static function gettime ()
	{
		$t = time ();
		$d = date ( "d" , $t );
		$m = date ( "m" , $t );
		$Y = date ( "Y" , $t );
		$H = date ( "H" , $t );
		$i = 0;
		$i2 = date ( "i" , $t );
		$i2 = (int)$i2;
		if($i2>=0){$i = 0;}
		if($i2>=10){$i = 10;}
		if($i2>=20){$i = 20;}
		if($i2>=30){$i = 30;}
		if($i2>=40){$i = 40;}
		if($i2>=50){$i = 50;}

		$time = mktime ( $H , $i , 0 , $m , $d , $Y );
		return $time;
	}

	public static function cl ( $data,$l=3 )
	{
		if($l==3){
			$data = db::s ( htmlspecialchars ( trim ( $data ) , null , '' ) );
		}
		if($l==2){
			$data = db::s ( trim ( $data ) );
		}
		if($l==1){
			$data = htmlspecialchars ( trim ( $data ) , null , '' );
		}
		return $data;
	}

	public static function parseInt ( $string )
	{
		if ( preg_match ( '/(\d+)/' , $string , $array ) ) {
			return $array[ 1 ];
		} else {
			return 0;
		}
	}
	public static function authOpenAPIMember() {
		global $conf;
		if(!$conf['vk_id']){
			return false;
		}
		$session = array();
		$member = false;
		$valid_keys = array('expire', 'mid', 'secret', 'sid', 'sig');
		$app_cookie = $_COOKIE['vk_app_'.$conf['vk_id']];
		if ($app_cookie) {
			$session_data = explode ('&', $app_cookie, 10);
			foreach ($session_data as $pair) {
				list($key, $value) = explode('=', $pair, 2);
				if (empty($key) || empty($value) || !in_array($key, $valid_keys)) {
					continue;
				}
				$session[$key] = $value;
			}
			foreach ($valid_keys as $key) {
				if (!isset($session[$key])) {return $member;}
			}
			ksort($session);

			$sign = '';
			foreach ($session as $key => $value) {

				if ($key != 'sig') {
					$sign .= ($key.'='.$value);
				}
			}
			$sign .= $conf['vk_key'];
			$sign = md5($sign);
			if ($session['sig'] == $sign && $session['expire'] > time()) {
				$member = array(
					'id' => intval($session['mid']),
					'secret' => $session['secret'],
					'sid' => $session['sid']
				);
			}
		}
		return $member;
	}

	public static function logget ()
	{
		global $logget_key,$conf;
			$key = api::cl ( @$_COOKIE[ 'key' ] );
			$key2 = api::cl ( @$_COOKIE[ 'key2' ] );
			if ( $key != "" and $key2 != "" ) {
				if ( ! preg_match ( "/^[0-9a-zA-Z]{64}$/i" , $key ) or ! preg_match ( "/^[0-9a-zA-Z]{64}$/i" , $key2 ) ) {
					api::set_cookie ( "key" , null , null );
					api::set_cookie ( "key2" , null , null );
				} else {
					db::q ( 'SELECT * FROM login_key where key1="' . $key . '"' );
					if ( db::n () == "1" ) {
						$row = db::r ();
						$rtime = time ();
						$time = round ( $rtime , - 3 );
						$ip = base64_encode ( $_SERVER[ 'REMOTE_ADDR' ] );
						$agent = base64_encode ( $_SERVER[ 'HTTP_USER_AGENT' ] );
						db::q ( 'SELECT * FROM users where id="' . $row[ 'user' ] . '"' );
						self::$logget = db::r ();
						if ( $key2 != ( md5 ( $key . round ( $row[ 'time' ] , - 3 ) ) . md5 ( $ip . round ( $row[ 'time' ] , - 3 ) ) ) ) {
							api::set_cookie ( "key" , null , null );
							api::set_cookie ( "key2" , null , null );
							return false;
						}
						self::$go = true;
					} else {
						api::set_cookie ( "key" , null , null );
						api::set_cookie ( "key2" , null , null );
					}
				}
			}
	}

	public static function info ( $data )
	{
		if ( self::$go ) {
			return self::$logget[ $data ];
		} else {
			return false;
		}
	}

	public static function nav ( $url , $t , $e = "0" )
	{
		if(self::$admin){
			tpl::load2 ( 'base-nav-get' );
		}else{
			tpl::load ( 'base-nav-get' );
		}

		tpl::set ( '{link}' , $url );
		tpl::set ( '{title}' , $t );
		if ( $e == "1" ) {
			tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "" );
			tpl::set_block ( "'\\[no\\](.*?)\\[/no\\]'si" , "\\1" );
		} else {
			tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
			tpl::set_block ( "'\\[no\\](.*?)\\[/no\\]'si" , "" );
		}
		tpl::compile ( 'nav_get' );
	}

	public static function nav_base ()
	{
		if(self::$admin) {
			tpl::load2 ( 'base-nav' );
		}else{
			tpl::load ( 'base-nav' );
		}
		tpl::set ( '{data}' , tpl::result ( 'nav_get' ) );
		tpl::compile ( 'nav' );
	}

	public static function phone_e ( $data )
	{
		mobile::error($data);
	}

	public static function phone_r ( $data )
	{
		mobile::result($data);
	}

	public static function ajax_e ( $data )
	{
		die( '{"e":"' . $data . '"}' );
	}

	public static function ajax_r ( $data )
	{
		die( '{"r":"' . $data . '"}' );
	}

	public static function ajax_d ( $data )
	{
		die( '{"d":"' . base64_encode ( $data ) . '"}' );
	}

	public static function mem ()
	{
		return api::size ( memory_get_usage () );
	}

	public static function size ( $data )
	{
		$units = array ( '' , 'K' , 'M' , 'G' , 'T' );
		foreach ( $units as $unit ) {
			if ( $data < 1024 ) {
				break;
			}
			$data /= 1024;
		}

		return sprintf ( '%.1f %s' , $data , $unit );
	}

	public static function get_real_time ()
	{
		return microtime ( true );
	}

	public static function speedbar ( $a )
	{
		global $conf;

	}

	public static function set_cookie ( $name , $value , $expires )
	{
		if ( $expires ) {
			$expires = time () + ( $expires * 86400 );
		} else {
			$expires = false;
		}
		if ( PHP_VERSION < 5.2 ) {
			setcookie ( $name , $value , $expires , "/" , $_SERVER[ 'HTTP_HOST' ] . "; HttpOnly" );
		} else {
			setcookie ( $name , $value , $expires , "/" , '' , null , true );
		}
	}

	public static function langdate ( $format , $stamp )
	{
		return strtr ( @date ( $format , $stamp ) , self::$langdate );
	}

	public static function ajax ()
	{
		if ( $_POST[ 'ajax' ] ) {
			return true;
		} else {
			return false;
		}
	}

	public static function mobile ()
	{
		if ( $_POST[ 'mobile' ] ) {
			return true;
		} else {
			return false;
		}
	}

	public static function modal ()
	{
		if ( $_POST[ 'modal' ] ) {
			return true;
		} else {
			return false;
		}
	}

	public static function phone ()
	{
		if ( $_POST[ 'phone' ] ) {
			return true;
		} else {
			return false;
		}
	}

	public static function ajax_f ()
	{
		if ( $_POST[ 'ajax_f' ] ) {
			return true;
		} else {
			return false;
		}
	}

	public static function error ( $data )
	{
		if(self::$admin) {
			tpl::load2 ( 'base-error' );
		}else{
			tpl::load ( 'base-error' );
		}
		tpl::set ( '{error}' , $data );
		tpl::compile ( 'error' );
	}

	public static function success ( $data )
	{
		if(self::$admin) {
			tpl::load2 ( 'base-success' );
		}else{
			tpl::load ( 'base-success' );
		}
		tpl::set ( '{data}' , $data );
		tpl::compile ( 'error' );
	}

	public static function pagination ( $all , $page , $active , $link )
	{
		$go = ( $all / $page );
		if ( $go < $active ) {
			return false;
		}
		if($_GET['search']){
			$search = "?search=".api::cl($_GET['search']);
		}else{
			$search = "";
		}
		if ( $go > 1 ) {
			$x = 0;
			if ( $go < 11 ) {
				while ( $x < ( $all / $page ) ) {
					if(self::$admin) {
						tpl::load2 ( 'base-pagination-get' );
					}else{
						tpl::load ( 'base-pagination-get' );
					}
					if ( $x == 0 ) {
						tpl::set ( '{link}' , $link.$search );
					} else {
						tpl::set ( '{link}' , $link . '/page/' . $x.$search );
					}
					tpl::set ( '{page}' , $x + 1 );
					if ( $active == $x ) {
						tpl::set ( '{active}' , "active" );
						tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "" );
						tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "\\1" );
					} else {
						tpl::set ( '{active}' , "" );
						tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
						tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "" );
					}
					tpl::compile ( 'pagination_list' );
					$x ++;
					$end = $x;
				}
			} else {
				if ( $active > 5 ) {
					$x = 0;
					$x2 = 0;
					if ( ( $go - $active ) < 5 ) {
						$x2 = 10 - ( $go - $active );
					} else {
						$x2 = 5;
					}
					while ( $x ++ < $x2 ) {
						if(self::$admin) {
							tpl::load2 ( 'base-pagination-get' );
						}else{
							tpl::load ( 'base-pagination-get' );
						}
						tpl::set ( '{page}' , (int) ( $active - $x2 + $x ) );
						tpl::set ( '{active}' , "" );
						tpl::set ( '{link}' , $link . '/page/' . (int) ( $active - $x2 + $x - 1 ).$search );
						tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
						tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "" );
						tpl::compile ( 'pagination_list' );
					}

					$x = 0;
					$x2 = 0;
					if ( ( $go - $active ) < 6 ) {
						$x2 = ( $go - $active );
					} else {
						$x2 = 6;
					}
					while ( $x ++ < $x2 ) {
						$end = ( $active + $x );
						if(self::$admin) {
							tpl::load2 ( 'base-pagination-get' );
						}else{
							tpl::load ( 'base-pagination-get' );
						}
						tpl::set ( '{page}' , $active + $x );
						if ( ( $active + 1 ) == $end ) {
							tpl::set ( '{link}' , '' );
							tpl::set ( '{active}' , "active" );
							tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "" );
							tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "\\1" );
						} else {
							tpl::set ( '{link}' , $link . '/page/' . ( $active + $x - 1 ).$search );
							tpl::set ( '{active}' , "" );
							tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "" );
							tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
						}
						tpl::compile ( 'pagination_list' );
					}
				} else {
					$x = 0;
					while ( $x < 11 ) {
						if(self::$admin) {
							tpl::load2 ( 'base-pagination-get' );
						}else{
							tpl::load ( 'base-pagination-get' );
						}
						if ( $x == 0 ) {
							tpl::set ( '{link}' , $link.$search );
						} else {
							tpl::set ( '{link}' , $link . '/page/' . $x.$search );
						}
						tpl::set ( '{page}' , $x + 1 );
						if ( $active == $x ) {
							tpl::set ( '{active}' , "active" );
							tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "" );
							tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "\\1" );
						} else {
							tpl::set ( '{active}' , "" );
							tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
							tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "" );
						}
						tpl::compile ( 'pagination_list' );
						$x ++;
						$end = $x;
					}
				}
			}
			if(self::$admin) {
				tpl::load2 ( 'base-pagination' );
			}else{
				tpl::load ( 'base-pagination' );
			}
			if ( $active == 0 ) {
				tpl::set ( '{prev_diss}' , "disabled" );
				tpl::set ( '{prev}' , '' );
				tpl::set_block ( "'\\[prev\\](.*?)\\[/prev\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[prev\\](.*?)\\[/prev\\]'si" , "\\1" );
				tpl::set ( '{prev_diss}' , "" );
				if ( ( $active - 1 ) == 0 ) {
					tpl::set ( '{prev}' , $link.$search );
				} else {
					tpl::set ( '{prev}' , $link . '/page/' . ( $active - 1 ).$search );
				}
			}
			tpl::set ( '{all}' , $link );
			if ( ( $active + 1 ) == $end ) {
				tpl::set ( '{next_diss}' , "disabled" );
				tpl::set ( '{next}' , '' );
				tpl::set_block ( "'\\[next\\](.*?)\\[/next\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[next\\](.*?)\\[/next\\]'si" , "\\1" );
				tpl::set ( '{next_diss}' , "" );
				tpl::set ( '{next}' , $link . '/page/' . ( $active + 1 ).$search );
			}
			tpl::set ( '{list}' , tpl::result ( 'pagination_list' ) );
			tpl::compile ( 'pagination' );
			$return = tpl::$result[ 'pagination' ];
			tpl::$result[ 'pagination' ] = '';
			tpl::$result[ 'pagination_list' ] = '';

			return $return;
		}
	}

	public static function pagination2 ( $all , $page , $active , $link,$linkend )
	{
		$go = ( $all / $page );
		if ( $go < $active ) {
			return false;
		}
		if ( $go > 1 ) {
			$x = 0;
			if ( $go < 11 ) {
				while ( $x < ( $all / $page ) ) {
					tpl::load ( 'mobile-pagination-get' );
					if ( $x == 0 ) {
						tpl::set ( '{link}' , $link );
					} else {
						tpl::set ( '{link}' , $link . '/page/' . $x.$linkend );
					}
					tpl::set ( '{page}' , $x + 1 );
					if ( $active == $x ) {
						tpl::set ( '{active}' , "active" );
						tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "" );
						tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "\\1" );
					} else {
						tpl::set ( '{active}' , "" );
						tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
						tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "" );
					}
					tpl::compile ( 'pagination_list' );
					$x ++;
					$end = $x;
				}
			} else {
				if ( $active > 5 ) {
					$x = 0;
					$x2 = 0;
					if ( ( $go - $active ) < 5 ) {
						$x2 = 10 - ( $go - $active );
					} else {
						$x2 = 5;
					}
					while ( $x ++ < $x2 ) {
						tpl::load ( 'mobile-pagination-get' );
						tpl::set ( '{page}' , (int) ( $active - $x2 + $x ) );
						tpl::set ( '{active}' , "" );
						tpl::set ( '{link}' , $link . '/page/' . (int) ( $active - $x2 + $x - 1 ).$linkend);
						tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
						tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "" );
						tpl::compile ( 'pagination_list' );
					}

					$x = 0;
					$x2 = 0;
					if ( ( $go - $active ) < 6 ) {
						$x2 = ( $go - $active );
					} else {
						$x2 = 6;
					}
					while ( $x ++ < $x2 ) {
						$end = ( $active + $x );
						tpl::load ( 'mobile-pagination-get' );
						tpl::set ( '{page}' , $active + $x );
						if ( ( $active + 1 ) == $end ) {
							tpl::set ( '{link}' , '' );
							tpl::set ( '{active}' , "active" );
							tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "" );
							tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "\\1" );
						} else {
							tpl::set ( '{link}' , $link . '/page/' . ( $active + $x - 1 ).$linkend );
							tpl::set ( '{active}' , "" );
							tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "" );
							tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
						}
						tpl::compile ( 'pagination_list' );
					}
				} else {
					$x = 0;
					while ( $x < 11 ) {
						tpl::load ( 'mobile-pagination-get' );
						if ( $x == 0 ) {
							tpl::set ( '{link}' , $link );
						} else {
							tpl::set ( '{link}' , $link . '/page/' . $x );
						}
						tpl::set ( '{page}' , $x + 1 );
						if ( $active == $x ) {
							tpl::set ( '{active}' , "active" );
							tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "" );
							tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "\\1" );
						} else {
							tpl::set ( '{active}' , "" );
							tpl::set_block ( "'\\[active\\](.*?)\\[/active\\]'si" , "\\1" );
							tpl::set_block ( "'\\[noactive\\](.*?)\\[/noactive\\]'si" , "" );
						}
						tpl::compile ( 'pagination_list' );
						$x ++;
						$end = $x;
					}
				}
			}
			tpl::load ( 'mobile-pagination' );
			if ( $active == 0 ) {
				tpl::set ( '{prev_diss}' , "disabled" );
				tpl::set ( '{prev}' , '' );
				tpl::set_block ( "'\\[prev\\](.*?)\\[/prev\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[prev\\](.*?)\\[/prev\\]'si" , "\\1" );
				tpl::set ( '{prev_diss}' , "" );
				if ( ( $active - 1 ) == 0 ) {
					tpl::set ( '{prev}' , $link );
				} else {
					tpl::set ( '{prev}' , $link . '/page/' . ( $active - 1 ).$linkend );
				}
			}
			tpl::set ( '{all}' , $link );
			if ( ( $active + 1 ) == $end ) {
				tpl::set ( '{next_diss}' , "disabled" );
				tpl::set ( '{next}' , '' );
				tpl::set_block ( "'\\[next\\](.*?)\\[/next\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[next\\](.*?)\\[/next\\]'si" , "\\1" );
				tpl::set ( '{next_diss}' , "" );
				tpl::set ( '{next}' , $link . '/page/' . ( $active + 1 ).$linkend );
			}
			tpl::set ( '{list}' , tpl::result ( 'pagination_list' ) );
			tpl::compile ( 'pagination' );
			$return = tpl::$result[ 'pagination' ];
			tpl::$result[ 'pagination' ] = '';
			tpl::$result[ 'pagination_list' ] = '';

			return $return;
		}
	}
	public static function e404 ( $r = "1" )
	{
		if($r=="1"){
			$r = l::t ('Запрашиваемая страница не найдена');
		}
		if ( ! api::ajax_f () and ! api::modal () and ! api::ajax () ) {
			header ( "HTTP/1.0 404 Not Found" );
		}
		api::result ( $r );
	}

	public static function result ( $data , $t = false )
	{
		if ( $t ) {
			if ( api::phone() ) {
				api::phone_r ( $data );
			}elseif ( api::ajax_f () ) {
				api::ajax_r ( $data );
			} elseif ( api::modal () ) {
				api::perror ( $data );
			} else {
				api::success ( $data );
			}
		} else {
			if ( api::phone() ) {
				api::phone_e( $data );
			}elseif ( api::ajax_f () ) {
				api::ajax_e ( $data );
			} elseif ( api::modal () ) {
				api::perror ( $data );
			} else {
				api::error ( $data );
			}
		}
	}

	public static function perror ( $data )
	{
		if(self::$admin) {
			tpl::load2 ( 'base-modal-error' );
		}else{
			tpl::load ( 'base-modal-error' );
		}

		tpl::set ( '{error}' , $data );
		tpl::compile ( 'error' );
		die( tpl::result ( 'error' ) );
	}

	public static function generate_password ( $number )
	{
		$chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
		$size = StrLen ( $chars ) - 1;
		$password = null;
		while ( $number -- )
			$password .= $chars[ rand ( 0 , $size ) ];

		return $password;
	}

	public static function RemoveDir ( $path )
	{
		if ( file_exists ( $path ) && is_dir ( $path ) ) {
			$dirHandle = opendir ( $path );
			while ( false !== ( $file = readdir ( $dirHandle ) ) ) {
				if ( $file != '.' && $file != '..' ) {
					$tmpPath = $path . '/' . $file;
					chmod ( $tmpPath , 0777 );
					if ( is_dir ( $tmpPath ) ) {
						api::RemoveDir ( $tmpPath );
					} else {
						if ( file_exists ( $tmpPath ) ) {
							unlink ( $tmpPath );
						}
					}
				}
			}
			closedir ( $dirHandle );
			if ( file_exists ( $path ) ) {
				rmdir ( $path );
			}
		}
	}

	public static function rdir ( $path2dir )
	{
		$d = dir ( $path2dir );
		while ( false !== ( $entry = $d->read () ) ) {
			if ( $entry != '.' && $entry != '..' && $entry != '' ) {
				$all_path = $path2dir . $entry;
				$new_path = api::go ( $all_path , is_file ( $all_path ) );
				if ( ! is_file ( $all_path ) ) {
					if ( ! api::rdir ( $new_path ) ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	public static function go ( $path2file , $is_file = true )
	{
		if ( $is_file ) {
			echo $path2file , "\n";
		} else {
			$path2file = $path2file . '/';
		}

		return $path2file;
	}

}

class cl{
	public static function w($d){
		global $cron;
		if ( $cron ) {
			echo date ( "H:i:s" , time () ) . "	" . $d . "\n";
		}
	}
	public static function e(){
		global $cron;
		if ( $cron ) {
			echo "- - - - - - - - - - - - - - - - - - - - -\n";
		}
	}
}

	if ( $conf ) {
		if ( ! $cron ) {
			api::logget ();

		}
		$m = new Memcache;
		m::connect ();
	} else {
		echo 'Не удалось открыть настройки панели';
		exit;
	}
	$cfg = $conf;
?>