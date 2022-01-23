<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_pages
{
	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT id FROM pages where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( 'DELETE from pages where id="' . $id . '"' );
			api::result ( l::t ( 'Страница удалена' ) , true );
		} else {
			api::result ( l::t ( 'Страница не найдена' ) );
		}
	}

	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t ( 'Статистические страницы' ) , '1' );
		db::q ( 'SELECT id FROM pages' );
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
		$sql = db::q ( 'SELECT id,name,url,visits FROM pages order by id desc LIMIT ' . $page . ' ,' . $allpage );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-pages-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::set ( '{link}' , $row[ 'url' ] );
			tpl::set ( '{visits}' , $row[ 'visits' ] );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Статистические страницы" );
		if ( ! tpl::result ( 'data' ) ) {
			api::result ( l::t ( 'У Вас еще нет ни одной статистической страницы.' ) . ' <a href="/admin/pages/add">' . l::t ( 'Создать' ) . '</a>.' );
		} else {
			tpl::load2 ( 'admin-pages-listen' );
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::$result[ 'data' ] = '';
			tpl::set ( '{nav}' , api::pagination ( $all , $allpage , $gpage , '/admin/pages' ) );
			tpl::compile ( 'content' );
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
			$data[ 'url' ] = mb_strtolower ( $data[ 'url' ] );
			$sql = db::q ( 'SELECT id FROM pages where url="' . api::cl ( $data[ 'url' ] ) . '"' );
			if ( db::n ( $sql ) != 0 ) {
				api::result ( l::t ( 'Адрес уже используется' ) );
			} else {
				db::q (
					"INSERT INTO pages set
										name='" . api::cl ( $data[ 'name' ] ) . "',
										keywords='" . api::cl ( $data[ 'key' ] ) . "',
										description='" . api::cl ( $data[ 'des' ] ) . "',
										url='" . api::cl ( $data[ 'url' ] ) . "',
										info='" . base64_encode ( $data[ 'info' ] ) . "',
										etime='" . time () . "'"
				);
				api::result ( l::t ( 'Статистическая страница успешно создана.' ) , true );
			}

		}
		$title = l::t ( "Создание статистической страницы" );
		tpl::load2 ( 'admin-pages-add' );
		tpl::compile ( 'content' );
		api::nav ( '/admin/pages' , l::t ( 'Статистические страницы' ) );
		api::nav ( '' , l::t ( 'Создание' ) , '1' );
	}

	public static function edit ( $id )
	{
		global $title;
		$data = $_POST[ 'data' ];
		db::q ( "SELECT * FROM pages where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$news = db::r ();
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$data[ 'url' ] = mb_strtolower ( $data[ 'url' ] );
				$sql = db::q ( 'SELECT id FROM pages where id != "' . $id . '" and url="' . api::cl ( $data[ 'url' ] ) . '"' );
				if ( db::n ( $sql ) != 0 ) {
					api::result ( l::t ('Адрес уже используется' ));
				} else {
					db::q (
						"UPDATE pages set
											name='" . api::cl ( $data[ 'name' ] ) . "',
											keywords='" . api::cl ( $data[ 'key' ] ) . "',
											description='" . api::cl ( $data[ 'des' ] ) . "',
											url='" . api::cl ( $data[ 'url' ] ) . "',
											info='" . base64_encode ( $data[ 'info' ] ) . "',
											etime='" . time () . "'
											where id='" . $id . "'"
					);
					api::result ( l::t ('Статистическая страница успешно отредактирована.') , true );

				}

			}
			$title = l::t ("Редактирование статистической страницы");
			tpl::load2 ( 'admin-pages-edit' );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{name}' , $news[ 'name' ] );
			tpl::set ( '{key}' , $news[ 'keywords' ] );
			tpl::set ( '{des}' , $news[ 'description' ] );
			tpl::set ( '{link}' , $news[ 'url' ] );
			tpl::set ( '{info}' , base64_decode ( $news[ 'info' ] ) );
			tpl::compile ( 'content' );
			api::nav ( '/admin/pages' , l::t ('Статистические страницы') );
			api::nav ( '' , l::t ('Редактирование #') . $id , '1' );
		} else {
			api::result ( l::t ('Запрашиваемая статистическая страница не найдена' ));
		}
	}

}

?>