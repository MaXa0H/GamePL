<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_maps_cat
{
	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( 'SELECT * FROM gh_maps_cat where id="' . $id . '"' );
		if ( db::n () != "1" ) {
			api::result ( l::t ( 'Категория не найдена' ) );
		} else {
			db::q ( 'SELECT * FROM gh_maps where cat="' . $id . '"' );
			if ( db::n () == 0 ) {
				db::q ( 'DELETE from gh_maps_cat where id="' . $id . '"' );
				api::result ( l::t ( 'Категория удалена' ) , true );
			} else {
				api::result ( l::t ( 'Для начала удалите все дополнения из категории' ) );
			}
		}
	}

	public static function edit ( $id )
	{
		db::q ( 'SELECT * FROM gh_maps_cat where id="' . $id . '"' );
		if ( db::n () != "1" ) {
			api::result ( l::t ( 'Категория не найдена' ) );
		} else {
			$row = db::r ();
			api::inc ( 'servers' );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( $data[ 'name' ] == "" ) {
					api::result ( l::t ( 'Укажите название для категории' ) );
				} else {
					db::q ( "UPDATE gh_maps_cat set name='" . api::cl ( $data[ 'name' ] ) . "' where id='" . $id . "'" );
					api::result ( l::t ( 'Категория сохранена' ) , true );
				}
			}
			tpl::load2 ( 'admin-maps-cat-edit' );
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::set ( '{id}' , $id );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( "/admin/maps" , l::t ( "Карты" ) );
				api::nav ( "/admin/maps-cat" , l::t ( "Категории" ) );
				api::nav ( '' , l::t ( 'Редактирование' ) , '1' );
			}
		}
	}

	public static function add ()
	{
		api::inc ( 'servers' );
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( $data[ 'name' ] == "" ) {
				api::result ( l::t ( 'Укажите название для категории' ) );
			} else {

				if ( ! servers::$games[ $data[ 'game' ] ] ) {
					api::result ( l::t ( 'Игра не найдена' ) );
				} else {
					db::q ( "INSERT INTO gh_maps_cat set name='" . api::cl ( $data[ 'name' ] ) . "',game='" . api::cl ( $data[ 'game' ] ) . "'" );
					api::result ( l::t ( 'Категория создана' ) , true );
				}
			}
		}
		tpl::load2 ( 'admin-maps-cat-add' );
		$games = '';
		foreach ( servers::$games as $key => $value ) {
			$games .= '<option value="' . $key . '">' . $value . '</option>';
		}
		tpl::set ( '{games}' , $games );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		} else {
			api::nav ( "/admin/maps" , l::t ( "Карты" ) );
			api::nav ( "/admin/maps-cat" , l::t ( "Категории" ) );
			api::nav ( '' , l::t ( 'Новая' ) , '1' );
		}
	}

	public static function listen ()
	{
		global $title;
		db::q ( 'SELECT id FROM gh_maps_cat' );
		$all = db::n ();
		$num = 10;
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
		$sql = db::q ( 'SELECT * FROM gh_maps_cat order by id desc LIMIT ' . $page . ' ,' . $num );
		api::inc ( 'servers' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-maps-cat-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::set ( '{game}' , servers::$games[ $row[ 'game' ] ] );
			$sql2 = db::q ( 'SELECT * FROM gh_maps where cat="' . $row[ 'id' ] . '"' );
			tpl::set ( '{install}' , db::n ( $sql2 ) );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Категории карт" );
		tpl::load2 ( 'admin-maps-cat-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		tpl::set ( '{nav}' , api::pagination ( $all , $num , $pages , '/admin/maps-cat' ) );
		tpl::compile ( 'content' );
		api::nav ( "/admin/maps" , l::t ( "Карты" ) );
		api::nav ( "" , l::t ( "Категории" ) , "1" );
	}
}

?>