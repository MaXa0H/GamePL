<?php

	class admin_isp_boxes
	{
		public static function on_off ( $id )
		{
			global $title , $conf;
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( "SELECT * FROM isp_boxes where id='" . $id . "'" );
			if ( db::n () == "1" ) {
				$rate = db::r ();
				if ( $rate[ 'power' ] ) {
					$power = 0;
				} else {
					$power = 1;
				}
				db::q ( 'update isp_boxes set power="' . $power . '" where id="' . $id . '"' );
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
			db::q ( "SELECT * FROM isp_boxes where id='" . $id . "'" );
			if ( db::n () == "1" ) {
				db::q ( "SELECT * FROM isp where boxes='" . $id . "'" );
				if ( db::n () != "0" ) {
					api::result ( l::t ( 'Для начала удалите все оказанные услуги' ) );
				} else {
					db::q ( 'DELETE from isp_boxes where id="' . $id . '"' );
					api::result ( l::t ( 'Удалено' ) , true );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

		public static function listen ()
		{
			global $title;
			api::nav ( '' , l::t ( 'Серверы Web хостинга' ) , '1' );
			$sql = db::q ( 'SELECT * FROM isp_boxes order by id desc' );
			while ( $row = db::r ( $sql ) ) {
				tpl::load2 ( 'admin-isp-boxes-listen-get' );
				tpl::set ( '{id}' , $row[ 'id' ] );
				tpl::set ( '{ip}' , $row[ 'ip' ] );
				$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row[ 'loc' ] . '"' );
				$row2 = db::r ( $sql2 );
				tpl::set ( '{loc}' , $row2[ 'name' ] );
				if ( $row[ 'power' ] ) {
					tpl::set ( '{color}' , 'blue' );
					tpl::set ( '{icon}' , 'fa fa-check-circle-o' );
					tpl::set ( '{status}' , '1' );
				} else {
					tpl::set ( '{icon}' , 'fa fa-circle-o' );
					tpl::set ( '{color}' , '' );
					tpl::set ( '{status}' , '0' );
				}
				tpl::compile ( 'data' );
			};
			$title = l::t ( "Серверы Web хостинга" );
			tpl::load2 ( 'admin-isp-boxes-listen' );
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::$result[ 'data' ] = '';
			tpl::compile ( 'content' );
		}

		public static function add ()
		{
			global $title;
			api::nav ( '/admin/isp-boxes' , l::t ( 'Серверы Web хостинга' ) );
			api::nav ( '' , l::t ( 'Новый' ) , 1 );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				db::q ( 'SELECT * FROM gh_location where id="' . (int) $data[ 'loc' ] . '"' );
				if ( db::n () == 1 ) {
					api::inc ( 'isp-api' );
					if ( isp_api::connect ( $data[ 'ip' ] , $data[ 'pass' ] ) ) {
						db::q ( "INSERT INTO isp_boxes set ip='" . api::cl ( $data[ 'ip' ] ) . "',pass='" . api::cl ( $data[ 'pass' ] ) . "',version='" . (int) $data[ 'version' ] . "',disklimit='" . (int) $data[ 'hdd' ] . "',loc='" . (int) $data[ 'loc' ] . "'" );
						api::result ( l::t ( 'Создано' ) , 1 );
					}
				} else {
					api::result ( l::t ( 'Локация не найдена' ) );
				}
			}
			$title = l::t ( "Новый сервер Web хостинга" );
			$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
			$loc = '';
			while ( $row2 = db::r ( $sql ) ) {
				$loc .= '<option value="' . $row2[ 'id' ] . '">' . $row2[ 'name' ] . '</option>';
			}
			tpl::load2 ( 'admin-isp-boxes-add' );
			tpl::set ( '{loc}' , $loc );
			tpl::compile ( 'content' );
		}

		public static function edit ( $id )
		{
			global $title;
			$sql = db::q ( 'SELECT * FROM isp_boxes where id="' . $id . '"' );
			if ( db::n ( $sql ) != 0 ) {
				$box = db::r ();
				api::nav ( '/admin/isp-boxes' , l::t ( 'Серверы Web хостинга' ) );
				api::nav ( '' , l::t ( 'Редактирование' ) , 1 );
				$data = $_POST[ 'data' ];
				if ( $data ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					db::q ( 'SELECT * FROM gh_location where id="' . (int) $data[ 'loc' ] . '"' );
					if ( db::n () == 1 ) {
						api::inc ( 'isp-api' );
						if ( isp_api::connect ( $data[ 'ip' ] , $data[ 'pass' ] ) ) {
							db::q ( "UPDATE isp_boxes set ip='" . api::cl ( $data[ 'ip' ] ) . "',pass='" . api::cl ( $data[ 'pass' ] ) . "',disklimit='" . (int) $data[ 'hdd' ] . "',loc='" . (int) $data[ 'loc' ] . "' where id='" . $id . "'" );
							api::result ( l::t ( 'Сохранено' ) , 1 );
						}
					} else {
						api::result ( l::t ( 'Локация не найдена' ) );
					}
				}
				$title = l::t ( "Редактирование сервера Web хостинга" );
				$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
				$loc = '';
				while ( $row2 = db::r ( $sql ) ) {
					$loc .= '<option value="' . $row2[ 'id' ] . '">' . $row2[ 'name' ] . '</option>';
				}
				tpl::load2 ( 'admin-isp-boxes-edit' );
				tpl::set ( '{loc}' , $loc );
				tpl::set ( '{ip}' , $box[ 'ip' ] );
				tpl::set ( '{disklimit}' , $box[ 'disklimit' ] );
				tpl::set ( '{id}' , $id );
				tpl::compile ( 'content' );
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}
	}

?>