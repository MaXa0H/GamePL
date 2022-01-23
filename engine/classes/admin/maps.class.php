<?php
	class admin_maps
	{
		public static function del ( $id )
		{
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( 'SELECT * FROM gh_maps where id="' . $id . '"' );
			if ( db::n () != "1" ) {
				api::result ( l::t ( 'Дополнение не найдено' ) );
			} else {
				db::q ( 'DELETE FROM gh_maps where id="' . $id . '"' );
				db::q ( 'DELETE FROM gh_maps_install where maps="' . $id . '"' );
				$uploaddir = ROOT . '/maps/';
				@unlink ( $uploaddir . $id . ".zip" );
				@unlink ( $uploaddir . $id . ".sh" );
				@unlink ( $uploaddir . $id . ".png" );
				api::result ( l::t ( 'Удалено' ) , true );
			}
		}

		public static function listen ()
		{
			global $title;
			api::inc ( 'servers' );
			api::nav ( "" , l::t ( "Карты" ) , '1' );
			db::q ( 'SELECT id FROM gh_maps' );
			$all = db::n ();
			$allpage = 10;
			$gpage = (int) r::g ( 3 );
			if ( $gpage ) {
				if ( ( $all / $allpage ) > $gpage ) {
					$page = $allpage * $gpage;
				} else {
					$page = 0;
				}
			} else {
				$page = 0;
			}
			$sql = db::q ( 'SELECT * FROM gh_maps order by id desc LIMIT ' . $page . ' ,' . $allpage );
			while ( $row = db::r ( $sql ) ) {
				tpl::load2 ( 'admin-maps-listen-get' );
				tpl::set ( '{id}' , $row[ 'id' ] );
				tpl::set ( '{name}' , $row[ 'name' ] );
				db::q ( 'SELECT * FROM gh_maps_cat where id="' . $row[ 'cat' ] . '"' );
				$row2 = db::r ();
				tpl::set ( '{game}' , servers::$games[ $row2[ 'game' ] ] );
				tpl::set ( '{cat}' , $row2[ 'name' ] );
				tpl::compile ( 'data' );
			};
			$title = l::t ( "Дополнения" );
			tpl::load2 ( 'admin-maps-listen' );
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::set ( '{nav}' , api::pagination ( $all , $allpage , $gpage , '/admin/maps' ) );
			tpl::compile ( 'content' );
		}

		public static function add ()
		{
			global $title;
			api::inc ( 'servers' );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$sql = db::q ( 'SELECT * FROM gh_maps where name="' . api::cl ( $data[ 'name' ] ) . '" and cat="' . api::cl ( $data[ 'cat' ] ) . '"' );
				if ( db::n ( $sql ) == 1 ) {
					api::result ( l::t ( 'Уже есть в базе' ) );
				} else {
					db::q ( "INSERT INTO gh_maps set name='" . api::cl ( $data[ 'name' ] ) . "',cat='" . api::cl ( $data[ 'cat' ] ) . "'" );
					$id = db::i ();
					$uploaddir = ROOT . '/maps/';
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
					copy ( $_FILES[ 'uploadfile2' ][ 'tmp_name' ] , $uploaddir . $id . ".png" );
					api::result ( l::t ( 'Карта добавлена' ) , 1 );
				}
			}
			$title = l::t ( "Загрузка карты" );
			tpl::load2 ( 'admin-maps-add' );

			$sql = db::q ( 'SELECT * FROM gh_maps_cat order by id desc' );
			$addons_cat = '';
			while ( $row = db::r ( $sql ) ) {
				$addons_cat .= "<option value='" . $row[ 'id' ] . "'>[" . $row[ 'game' ] . "] " . $row[ 'name' ] . "</option>";
			}
			tpl::set ( '{cats}' , $addons_cat );
			tpl::compile ( 'content' );
			api::nav ( '/admin/maps' , l::t ( 'Карты' ) );
			api::nav ( '' , l::t ( 'Загрузка' ) , '1' );
		}

	}
?>