<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class admin_mysql
{
	public static function on_off ( $id ){
		global $title,$conf;
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT * FROM mysql_boxes where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$rate = db::r ();
			if($rate['power']){
				$power = 0;
			}else{
				$power = 1;
			}
			db::q('update mysql_boxes set power="' . $power . '" where id="' . $id . '"');
			if($rate['power']){
				api::result ( l::t ('Сервер выключен'),1 );
			}else{
				api::result ( l::t ('Сервер включен'),1 );
			}
		} else {
			api::result ( l::t ('Сервер не найден') );
		}
	}
	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT * FROM mysql_boxes where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( "SELECT * FROM mysql where boxes='" . $id . "'" );
			if ( db::n () != "0" ) {
				api::result ( l::t ('Для начала удалите все оказанные услуги') );
			} else {
				db::q ( 'DELETE from mysql_boxes where id="' . $id . '"' );
				api::result ( l::t ('Удалено') , true );
			}
		} else {
			api::result ( l::t ('Тариф не найден') );
		}
	}

	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t ('Серверы MySQL') , '1' );
		$sql = db::q ( 'SELECT * FROM mysql_boxes order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-mysql-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row[ 'loc' ] . '"' );
			$row2 = db::r ( $sql2 );
			tpl::set ( '{loc}' , $row2[ 'name' ] );
			tpl::set ( '{adress}' , $row[ 'ip' ] . ':' . $row[ 'port' ] );
			if($row['power']){
				tpl::set('{color}','blue');
				tpl::set('{icon}','fa fa-check-circle-o');
				tpl::set('{status}','1');
			}else{
				tpl::set('{icon}','fa fa-circle-o');
				tpl::set('{color}','');
				tpl::set('{status}','0');
			}
			tpl::compile ( 'data' );
		};
		$title = l::t ("Серверы MySQL");
		tpl::load2 ( 'admin-mysql-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		tpl::compile ( 'content' );
	}

	public static function edit ( $id )
	{
		global $title;
		db::q ( "SELECT * FROM mysql_boxes where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$box = db::r ();
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( ! preg_match ( "/^[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}$/i" , $data[ 'ip' ] ) ) {
					api::result ( l::t ('IP адрес MySQL введен неверно') );
				} else {
					if ( $data[ 'port' ] < 10 || $data[ 'port' ] > 65000 ) {
						api::result ( l::t ('Порт MySQL разрешено задавать в диапазоне от 10 до 65000') );
					} else {
						ini_set ( 'mysql.connect_timeout' , '3' );
						$dbftp = @mysql_connect ( $data[ 'ip' ] . ':' . $data[ 'port' ] , 'root' , $data[ 'pass' ] );
						if ( ! $dbftp ) {
							api::result ( l::t ('Не удалось установить соединение с сервером MySQL') );
						} else {
							db::q (
								"UPDATE mysql_boxes set
									ip='" . api::cl ( $data[ 'ip' ] ) . "',
									port='" . (int) $data[ 'port' ] . "',
									login='root',
									pass='" . api::cl ( $data[ 'pass' ] ) . "',
									maxdb='" . (int) $data[ 'max' ] . "',
									link='" . api::cl ( $data[ 'link' ] ). "'
									where id='" . $id . "'
								"
							);
							api::result ( l::t ('Подключено') , 1 );
						}
					}
				}
			}
			$title = l::t ("Редактирование MySQL сервера");
			tpl::load2 ( 'admin-mysql-edit' );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{ip}' , $box[ 'ip' ] );
			tpl::set ( '{port}' , $box[ 'port' ] );
			tpl::set ( '{pass}' , $box[ 'pass' ] );
			tpl::set ( '{link}' , $box[ 'link' ] );
			tpl::set ( '{max}' , $box[ 'maxdb' ] );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '/admin/mysql' , l::t ('MySQL серверы') );
				api::nav ( '' , l::t ('Редактирование') , '1' );
			}
		} else {
			api::result ( l::t ('Сервер не найден') );
		}
	}


	public static function add ()
	{
		global $title;
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( ! preg_match ( "/^[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}$/i" , $data[ 'ip' ] ) ) {
				api::result ( l::t ('IP адрес MySQL введен неверно'));
			} else {
				if ( $data[ 'port' ] < 10 || $data[ 'port' ] > 65000 ) {
					api::result ( l::t ('Порт MySQL разрешено задавать в диапазоне от 10 до 65000') );
				} else {
					ini_set ( 'mysql.connect_timeout' , '3' );
					$dbftp = @mysql_connect ( $data[ 'ip' ] . ':' . $data[ 'port' ] , 'root' , $data[ 'pass' ] );
					if ( ! $dbftp ) {
						api::result (l::t ( 'Не удалось установить соединение с сервером MySQL') );
					} else {
						db::q ( 'SELECT * FROM gh_location where id="' . (int) $data[ 'loc' ] . '"' );
						if ( db::n () != 1 ) {
							api::result (l::t ( 'Локация не найдена') );
						} else {
							db::q (
								"INSERT INTO mysql_boxes set
								ip='" . api::cl ( $data[ 'ip' ] ) . "',
								port='" . (int) $data[ 'port' ] . "',
								login='root',
								pass='" . api::cl ( $data[ 'pass' ] ) . "',
								loc='" . (int) $data[ 'loc' ] . "',
								maxdb='" . (int) $data[ 'max' ] . "',
								link='" . api::cl ( $data[ 'link' ] ). "'

							"
							);
							api::result ( l::t ('Подключено') , 1 );
						}
					}
				}
			}
		}
		$title = l::t ("Новый сервер MySQL");
		tpl::load2 ( 'admin-mysql-add' );
		$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
		$loc = '';
		while ( $row2 = db::r ( $sql ) ) {
			$loc .= '<option value="' . $row2[ 'id' ] . '">' . $row2[ 'name' ] . '</option>';
		}
		tpl::set ( '{loc}' , $loc );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		} else {
			api::nav ( '/admin/mysql' , l::t ('Серверы MySQL') );
			api::nav ( '' , l::t ('Новый' ), '1' );
		}
	}
}

?>