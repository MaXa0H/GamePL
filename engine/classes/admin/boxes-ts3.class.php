<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_boxes_ts3
{
	public static function on_off ( $id )
	{
		global $title , $conf;
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT * FROM gh_boxes_ts3 where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$rate = db::r ();
			if ( $rate[ 'power' ] ) {
				$power = 0;
			} else {
				$power = 1;
			}
			db::q ( 'update gh_boxes_ts3 set power="' . $power . '" where id="' . $id . '"' );
			if ( $rate[ 'power' ] ) {
				api::result ( l::t ( 'Сервер выключен' ) , 1 );
			} else {
				api::result ( l::t ( 'Сервер включен' ) , 1 );
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT * FROM gh_boxes_ts3 where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( "SELECT * FROM gh_servers where box='" . $id . "'" );
			if ( db::n () != "0" ) {
				api::result ( l::t ( 'Для начала удалите все игровые серверы' ) );
			} else {
				db::q ( 'DELETE from gh_boxes_ts3 where id="' . $id . '"' );
				api::result ( l::t ( 'Удалено' ) , true );
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t ( 'Серверы TS3' ) , '1' );
		$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-boxes-ts3-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row[ 'loc' ] . '"' );
			$row2 = db::r ( $sql2 );
			tpl::set ( '{loc}' , $row2[ 'name' ] );
			tpl::set ( '{adress}' , $row[ 'ip' ] . ':' . $row[ 'port' ] );
			if ( $row[ 'power' ] ) {
				tpl::set ( '{color}' , 'blue' );
				tpl::set ( '{icon}' , 'fa fa-check-circle-o' );
				tpl::set ( '{status}' , '1' );
			} else {
				tpl::set ( '{icon}' , 'fa fa-circle-o' );
				tpl::set ( '{color}' , '' );
				tpl::set ( '{status}' , '0' );
			}
			$sql2 = db::q ( 'SELECT slots FROM gh_servers where game="ts3" and box="' . $row[ 'id' ] . '"' );
			$slots = 0;
			while ( $row1 = db::r ( $sql2 ) ) {
				$slots += $row1[ 'slots' ];
			}
			tpl::set ( '{slots}' , $slots );
			tpl::set ( '{slots-%}' , ( 100 / $row[ 'slots' ] * $slots ) );
			tpl::set ( '{slots-all}' , $row[ 'slots' ] );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Серверы TS3" );
		tpl::load2 ( 'admin-boxes-ts3-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		tpl::compile ( 'content' );
	}

	public static function add ()
	{

		global $title;
		api::inc ( 'servers' );
		api::inc ( 'telnet' );
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( 'SELECT * FROM gh_location where id="' . (int) $data[ 'loc' ] . '"' );
			if ( db::n () != 1 ) {
				api::result ( l::t ( 'Локация не найдена' ) );
			} else {
				if ( ! preg_match ( "/^[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}$/i" , $data[ 'ip' ] ) ) {
					api::result ( l::t ( 'Ip адрес введен неверно' ) );
				} else {
					if ( $data[ 'port' ] < 100 || $data[ 'port' ] > 65000 ) {
						api::result ( l::t ( 'Порт разрешено задавать в диапазоне от 100 до 65000' ) );
					} else {
						if ( ts3::connect ( api::cl ( $data[ 'ip' ] ) , api::cl ( $data[ 'port' ] ) , api::cl ( $data[ 'login' ] ) , api::cl ( $data[ 'pass' ] ) ) ) {
							db::q (
								"INSERT INTO gh_boxes_ts3 set
								ip='" . api::cl ( $data[ 'ip' ] ) . "',
								port='" . api::cl ( $data[ 'port' ] ) . "',
								login='" . api::cl ( $data[ 'login' ] ) . "',
								pass='" . api::cl ( $data[ 'pass' ] ) . "',
								loc='" . (int) $data[ 'loc' ] . "',
								slots='" . api::cl ( $data[ 'slots' ] ) . "'"
							);
							api::result ( l::t ( 'Сервер подключен' ) , true );
						} else {
							api::result ( l::t ( 'Не удалось установить соединение с сервером.' ) );
						}
					}
				}
			}
		}
		$loc = "";
		$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
		while ( $row2 = db::r ( $sql ) ) {
			$loc .= '<option value="' . $row2[ 'id' ] . '">' . $row2[ 'name' ] . '</option>';
		}
		$title = l::t ( "Новый сервер TS3" );
		tpl::load2 ( 'admin-boxes-ts3-add' );
		tpl::set ( '{loc}' , $loc );
		tpl::compile ( 'content' );
		api::nav ( '/admin/boxes-ts3' , l::t ( 'Cерверы TS3' ) );
		api::nav ( '' , l::t ( 'Новый сервер TS3' ) , '1' );
	}

	public static function edit ( $id )
	{

		global $title;
		db::q ( "SELECT * FROM gh_boxes_ts3 where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$box = db::r ();
			api::inc ( 'servers' );
			api::inc ( 'telnet' );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( ! preg_match ( "/^[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}$/i" , $data[ 'ip' ] ) ) {
					api::result ( l::t ( 'Ip адрес введен неверно' ) );
				} else {
					if ( $data[ 'port' ] < 100 || $data[ 'port' ] > 65000 ) {
						api::result ( l::t ( 'Порт разрешено задавать в диапазоне от 100 до 65000' ) );
					} else {
						if ( ts3::connect ( api::cl ( $data[ 'ip' ] ) , api::cl ( $data[ 'port' ] ) , api::cl ( $data[ 'login' ] ) , api::cl ( $data[ 'pass' ] ) ) ) {
							db::q (
								"UPDATE gh_boxes_ts3 set
								ip='" . api::cl ( $data[ 'ip' ] ) . "',
								port='" . api::cl ( $data[ 'port' ] ) . "',
								login='" . api::cl ( $data[ 'login' ] ) . "',
								pass='" . api::cl ( $data[ 'pass' ] ) . "',
								slots='" . api::cl ( $data[ 'slots' ] ) . "' where id='" . $id . "'"
							);
							api::result ( l::t ( 'Сервер сохранен' ) , true );
						} else {
							api::result ( l::t ( 'Не удалось установить соединение с сервером.' ) );
						}
					}
				}
			}
			$title = l::t ( "Редактирование" );
			tpl::load2 ( 'admin-boxes-ts3-edit' );
			tpl::set ( '{ip}' , $box[ 'ip' ] );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{port}' , $box[ 'port' ] );
			tpl::set ( '{login}' , $box[ 'login' ] );
			tpl::set ( '{slots}' , $box[ 'slots' ] );
			tpl::compile ( 'content' );
			api::nav ( '/admin/boxes-ts3' , l::t ( 'Cерверы TS3' ) );
			api::nav ( '' , l::t ( 'Редактирование' ) , '1' );
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}
}

?>