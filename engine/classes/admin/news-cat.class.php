<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_news_cat
{
	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT id FROM news_cat where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$sql = db::q ( "SELECT id FROM news where cat='" . $id . "'" );
			if ( db::n ( $sql ) != 0 ) {
				api::result ( l::t ( 'Удалите все новости из категории' ) );
			} else {
				db::q ( 'DELETE from news_cat where id="' . $id . '"' );
				api::result ( l::t ( 'Категория удалена' ) , true );
			}
		} else {
			api::result ( l::t ( 'Категория не найдена' ) );
		}
	}

	public static function listen ()
	{
		global $title;
		api::nav ( '/admin/news' , l::t ( 'Новости' ));
		api::nav ( '' , l::t ( 'Категории' ) , '1' );
		db::q ( 'SELECT id FROM news_cat' );
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
		$sql = db::q ( 'SELECT id,name FROM news_cat order by id desc LIMIT ' . $page . ' ,' . $allpage );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-news-cat-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Категории новостей" );
		if ( ! tpl::result ( 'data' ) ) {
			api::result ( l::t ( 'У Вас еще нет ни одной категории новостей.' ) . ' <a href="/admin/news-cat/add">' . l::t ( 'Создать' ) . '</a>.' );
		} else {
			tpl::load2 ( 'admin-news-cat-listen' );
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::$result[ 'data' ] = '';
			tpl::set ( '{nav}' , api::pagination ( $all , $allpage , $gpage , '/admin/news-cat' ) );
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
			$sql = db::q ( 'SELECT id FROM news_cat where url="' . api::cl ( $data[ 'url' ] ) . '"' );
			if ( db::n ( $sql ) != 0 ) {
				api::result ( l::t ( 'Адрес уже используется' ) );
			} else {

				db::q (
					"INSERT INTO news_cat set
									name='" . api::cl ( $data[ 'name' ] ) . "',
									keywords='" . api::cl ( $data[ 'key' ] ) . "',
									description='" . api::cl ( $data[ 'des' ] ) . "',
									url='" . api::cl ( $data[ 'url' ] ) . "'"
				);
				api::result ( l::t ( 'Категория новостей успешно создана.' ) , true );
			}
		}
		$title = l::t ( "Создание категории новостей" );
		tpl::load2 ( 'admin-news-cat-add' );
		tpl::compile ( 'content' );
		api::nav ( '/admin/news' , l::t ( 'Новости' ) );
		api::nav ( '/admin/news-cat' , l::t ( 'Категории' ) );
		api::nav ( '' , l::t ( 'Создание' ) , '1' );
	}

	public static function edit ( $id )
	{
		global $title;
		$data = $_POST[ 'data' ];
		db::q ( "SELECT * FROM news_cat where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$news = db::r ();
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$data[ 'url' ] = mb_strtolower ( $data[ 'url' ] );
				$sql = db::q ( 'SELECT id FROM news_cat where id != "' . $id . '" and url="' . api::cl ( $data[ 'url' ] ) . '"' );
				if ( db::n ( $sql ) != 0 ) {
					api::result (  l::t ('Адрес уже используется') );
				} else {
					db::q (
						"UPDATE news_cat set
										name='" . api::cl ( $data[ 'name' ] ) . "',
										keywords='" . api::cl ( $data[ 'key' ] ) . "',
										description='" . api::cl ( $data[ 'des' ] ) . "',
										url='" . api::cl ( $data[ 'url' ] ) . "'
										where id='" . $id . "'"
					);
					api::result (  l::t ('Категория новостей успешно отредактирована.') , true );
				}

			}
			$title =  l::t ("Редактирование категории новостей");
			tpl::load2 ( 'admin-news-cat-edit' );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{name}' , $news[ 'name' ] );
			tpl::set ( '{key}' , $news[ 'keywords' ] );
			tpl::set ( '{des}' , $news[ 'description' ] );
			tpl::set ( '{link}' , $news[ 'url' ] );
			tpl::compile ( 'content' );
			api::nav ( '/admin/news' ,  l::t ('Новости') );
			api::nav ( '/admin/news-cat' ,  l::t ('Категории' ));
			api::nav ( '' ,  l::t ('Редактирование #') . $id , '1' );
		} else {
			api::result (  l::t ('Категория новостей не найдена') );
		}
	}
}

?>