<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_addons
{
	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( 'SELECT * FROM gh_addons where id="' . $id . '"' );
		if ( db::n () != "1" ) {
			api::result ( l::t ( 'Дополнение не найдено' ) );
		} else {
			db::q ( 'DELETE FROM gh_addons where id="' . $id . '"' );
			db::q ( 'DELETE FROM gh_addons_cfg where addon="' . $id . '"' );
			db::q ( 'DELETE FROM gh_addons_cfg_add where addon="' . $id . '"' );
			db::q ( 'DELETE FROM gh_addons_install where addon="' . $id . '"' );
			$uploaddir = ROOT . '/addons/';
			@unlink ( $uploaddir . $id . ".zip" );
			@unlink ( $uploaddir . $id . ".sh" );
			api::result ( l::t ( 'Удалено' ) , true );
		}
	}

	public static function listen ()
	{
		global $title;
		api::inc ( 'servers' );
		api::nav ( "" , l::t ( "Дополнения" ) , '1' );
		db::q ( 'SELECT id FROM gh_addons' );
		$all = db::n ();
		$pages = (int) r::g ( 3 );
		if ( $pages ) {
			if ( ( $all / 10 ) > $pages ) {
				$page = 10 * $pages;
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		$sql = db::q ( 'SELECT * FROM gh_addons order by id desc LIMIT ' . $page . ' ,10' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-addons-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			db::q ( 'SELECT * FROM gh_addons_cat where id="' . $row[ 'cat' ] . '"' );
			$row2 = db::r ();
			tpl::set ( '{game}' , servers::$games[ $row2[ 'game' ] ] );
			tpl::set ( '{cat}' , $row2[ 'name' ] );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Дополнения" );
		tpl::load2 ( 'admin-addons-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::set ( '{nav}' , api::pagination ( $all , 10 , $pages , '/admin/addons' ) );
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
			$sql = db::q ( 'SELECT * FROM gh_addons where name="' . api::cl ( $data[ 'name' ] ) . '" and cat="' . api::cl ( $data[ 'cat' ] ) . '"' );
			if ( db::n ( $sql ) == 1 ) {
				api::result ( l::t ( 'Уже есть в базе' ) );
			} else {
				$d1 = $_POST[ 'data_1' ];
				$d2 = $_POST[ 'data_2' ];
				$d3 = $_POST[ 'data_3' ];
				$d4 = $_POST[ 'data_4' ];
				db::q ( "INSERT INTO gh_addons set name='" . api::cl ( $data[ 'name' ] ) . "',info='" . db::s ( $data[ 'info' ] ) . "',cat='" . api::cl ( $data[ 'cat' ] ) . "',dir='" . api::cl ( $data[ 'dir' ] ) . "'" );
				$id = db::i ();
				$uploaddir = ROOT . '/addons/';
				copy ( $_FILES[ 'uploadfile' ][ 'tmp_name' ] , $uploaddir . $id . ".zip" );
				$zip = new ZipArchive;
				$zip->open ( $uploaddir . $id . ".zip" );
				$zip->extractTo ( $uploaddir . $id . "/" );
				$zip->close ();
				$folder = $uploaddir . $id . "/";
				ob_start ();
				api::rdir ( $folder );
				api::RemoveDir ( $uploaddir . $id );
				$get = ob_get_contents ();
				$get = str_replace ( $uploaddir . $id . "/" , "rm " , $get );
				$file = fopen ( $uploaddir . $id . '.sh' , "w" );
				if ( $file ) {
					fputs ( $file , $get );
				}
				fclose ( $file );
				ob_end_clean ();
				foreach ( $d1 as $key => $val ) {
					db::q ( "INSERT INTO gh_addons_cfg set addon='" . $id . "',file='" . $d1[ $key ] . "',value='" . $d2[ $key ] . "'" );
				}
				foreach ( $d3 as $key => $val ) {
					db::q ( "INSERT INTO gh_addons_cfg_add set addon='" . $id . "',dir='" . $d3[ $key ] . "',file='" . $d4[ $key ] . "'" );
				}
				api::result ( l::t ( 'Модуль добавлен' ) , 1 );
			}
		}
		$title = l::t ( "Загрузка" );
		tpl::load2 ( 'admin-addons-add' );
		$sql = db::q ( 'SELECT * FROM gh_addons_cat where cat="0" order by id desc' );
		$addons_cat = '';
		while ( $row = db::r ( $sql ) ) {
			$addons_cat .= "<option value='" . $row[ 'id' ] . "'>[" . $row[ 'game' ] . "]" . $row[ 'name' ] . "</option>";
			$sql2 = db::q ( 'SELECT * FROM gh_addons_cat where cat="' . $row[ 'id' ] . '"' );
			while ( $row2 = db::r ( $sql2 ) ) {
				$addons_cat .= "<option value='" . $row2[ 'id' ] . "'>[" . $row2[ 'game' ] . "]-" . $row2[ 'name' ] . "</option>";
				$sql3 = db::q ( 'SELECT * FROM gh_addons_cat where cat="' . $row2[ 'id' ] . '"' );
			}
		}
		tpl::set ( '{addons_cats}' , $addons_cat );
		tpl::compile ( 'content' );
		api::nav ( '/admin/addons' , l::t ( 'Дополнения' ) );
		api::nav ( '' , l::t ( 'Загрузка' ) , '1' );
	}
}

?>