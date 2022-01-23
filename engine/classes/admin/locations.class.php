<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_locations
{
	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t ( 'Локации' ) , '1' );
		$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-location-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Управление локациями" );
		tpl::load2 ( 'admin-location-listen' );
		tpl::set ( '{loc}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		tpl::compile ( 'content' );
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
			if ( $data[ 'name' ] != "" ) {
				db::q ( "INSERT INTO gh_location set name='" . api::cl ( $data[ 'name' ] ) . "'" );
				api::result ( l::t ( 'Локация создана' ) , true );
			} else {
				api::result ( l::t ( 'Укажите название локации' ) );
			}
		}
		$title = l::t ( "Новая локация" );
		tpl::load2 ( 'admin-location-add' );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		} else {
			api::nav ( '/admin/location' , l::t ( 'Локации' ) );
			api::nav ( '' , l::t ( 'Новая' ) , '1' );
		}
	}

	public static function edit ( $id )
	{
		global $title;
		db::q ( "SELECT * FROM gh_location where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$location = db::r ();
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( $data[ 'name' ] != "" ) {
					db::q ( "UPDATE gh_location set name='" . api::cl ( $data[ 'name' ] ) . "' where id='" . $location[ 'id' ] . "'" );
					api::result ( l::t ( 'Локация сохранена' ) , true );
				} else {
					api::result ( l::t ( 'Укажите название локации' ) );
				}
			}
			$title = l::t ( "Редактирование локации" );
			tpl::load2 ( 'admin-location-edit' );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{name}' , $location[ 'name' ] );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '/admin/location' , l::t ( 'Локации' ) );
				api::nav ( '' , l::t ( 'Редактирование' ) , '1' );
			}
		} else {
			api::result ( l::t ( 'Локация не найдена' ) );
		}
	}

	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT id FROM gh_location where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( 'SELECT id FROM gh_rates where loc="' . $id . '"' );
			if ( db::n () == "0" ) {
				db::q ( 'DELETE from gh_location where id="' . $id . '"' );
				api::result ( l::t ( 'Локация удалена' ) , true );
			} else {
				api::result ( l::t ( 'Сначала удалите все тарифы' ) );
			}
		} else {
			api::result ( l::t ( 'Локация не найдена' ) );
		}
	}
}

?>