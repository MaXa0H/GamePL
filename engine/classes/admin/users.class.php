<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_users
{
	public static function listen ()
	{
		global $conf , $title;
		$add = '';
		if ( $_GET[ 'search' ] ) {
			$add .= ' where mail LIKE  "%' . api::cl ( $_GET[ 'search' ] ) . '%"';
		}
		db::q ( 'SELECT id FROM users ' . $add );
		$all = db::n ();
		$num = 20;
		$pages = (int) r::g ( 3 );
		if ( $pages ) {
			if ( ( $all / $num ) > $pages ) {
				$page = $num * $pages;
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		if ( $_GET[ 'page' ] == 'all' ) {
			$sql = db::q ( 'SELECT * FROM users ' . $add . ' order by id desc ' );
			$nav = true;
		} else {
			$sql = db::q ( 'SELECT * FROM users ' . $add . ' order by id desc LIMIT ' . $page . ' ,' . $num );
		}
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-users-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{mail}' , $row[ 'mail' ] );
			tpl::set ( '{balance}' , $row[ 'balance' ] );
			tpl::set ( '{signup}' , api::langdate ( "d.m.Y" , $row[ 'time' ] ) );
			if ( $row[ 'ugroup' ] == 1 ) {
				tpl::set ( '{group}' , l::t ( 'Администратор' ) );
			} else {
				tpl::set ( '{group}' , l::t ( 'Клиент' ) );
			}
			db::q ( 'SELECT id FROM gh_servers where user="' . $row[ 'id' ] . '"' );
			tpl::set ( '{servers}' , db::n () );
			db::q ( 'SELECT id FROM mysql where user="' . $row[ 'id' ] . '"' );
			tpl::set ( '{mysql}' , db::n () );
			db::q ( 'SELECT id FROM isp where user="' . $row[ 'id' ] . '"' );
			tpl::set ( '{web}' , db::n () );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Список пользователей" );
		tpl::load2 ( 'admin-users-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		if ( $nav ) {
			tpl::set_block ( "'\\[nav\\](.*?)\\[/nav\\]'si" , "" );
		} else {
			tpl::set_block ( "'\\[nav\\](.*?)\\[/nav\\]'si" , "\\1" );
			tpl::set ( '{nav}' , api::pagination ( $all , $num , $pages , '/admin/users' ) );
		}
		tpl::compile ( 'content' );
		api::nav ( "" , l::t ( 'Пользователи' ) , '1' );
	}

	public static function edit ( $id )
	{
		global $lang;
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( 'SELECT * FROM users where id="' . $id . '"' );
			$row = db::r ();
			if ( db::n () != "1" ) {
				api::result ( l::t ( 'Пользователь не найден' ) );
			} else {
				if ( $data[ 'group' ] != 1 and $data[ 'group' ] != 3 ) {
					api::result ( l::t ( 'Группа не найдена' ) );
				} else {
					db::q (
						"UPDATE users set
						balance='" . api::price ( $data[ 'balance' ] ) . "',
						mail='" . api::cl ( $data[ 'mail' ] ) . "',
						name='" . api::cl ( $data[ 'name' ] ) . "',
						lastname='" . api::cl ( $data[ 'lastname' ] ) . "',
						phone='" . api::cl ( $data[ 'phone' ] ) . "',
						ugroup='" . (int) $data[ 'group' ] . "'
					where id='" . $id . "'"
					);
					api::result ( l::t ( 'Сохранено' ) , true );
				}
			}
		}
		db::q ( 'SELECT * FROM users where id="' . $id . '"' );
		if ( db::n () == "1" ) {
			$row = db::r ();
			tpl::load2 ( 'admin-users-edit' );
			tpl::set ( '{email}' , $row[ 'mail' ] );
			tpl::set ( '{phone}' , $row[ 'phone' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::set ( '{lastname}' , $row[ 'lastname' ] );
			tpl::set ( '{balance}' , $row[ 'balance' ] );
			$group = '';
			if ( $row[ 'ugroup' ] == 1 ) {
				$group .= '<option value="1" selected="selected">' . l::t ( 'Администратор' ) . '</option>';
				$group .= '<option value="3">' . l::t ( 'Пользователь' ) . '</option>';
			} else {
				$group .= '<option value="1">' . l::t ( 'Администратор' ) . '</option>';
				$group .= '<option value="3" selected="selected">' . l::t ( 'Пользователь' ) . '</option>';
			}
			tpl::set ( '{group}' , $group );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( "/admin/users" , l::t ( "Пользователи" ) );
				api::nav ( '' , l::t ( 'Редактирование' ) , '1' );
			}
		} else {
			api::result ( l::t ( 'Пользователь не найден' ) );
		}
	}

	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( 'SELECT * FROM users where id="' . $id . '"' );
		if ( db::n () != "1" ) {
			api::result ( l::t ( 'Пользователь не найден' ) );
		} else {
			db::q ( 'DELETE from users where id="' . $id . '"' );
			api::result ( l::t ( 'Удален' ) , true );
		}
	}
}

?>