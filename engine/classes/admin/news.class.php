<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_news
{
	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT id FROM news where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( 'DELETE from news where id="' . $id . '"' );
			api::result ( l::t ( 'Новость удалена' ) , true );
		} else {
			api::result ( l::t ( 'Новость не найдена' ) );
		}
	}

	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t ( 'Новости' ) , '1' );
		db::q ( 'SELECT id FROM news' );
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
		$sql = db::q ( 'SELECT id,name,visits,cat FROM news order by id desc LIMIT ' . $page . ' ,' . $allpage );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-news-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::set ( '{visits}' , $row[ 'visits' ] );
			if ( $row[ 'cat' ] == 0 ) {
				$cat_name = l::t ( "Не выбрана" );
			} else {
				$sql2 = db::q ( 'SELECT name FROM news_cat where id="' . $row[ 'cat' ] . '"' );
				$cat = db::r ( $sql2 );
				$cat_name = $cat[ 'name' ];
			}
			tpl::set ( '{cat}' , $cat_name );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Новости" );
		if ( ! tpl::result ( 'data' ) ) {
			api::result ( l::t ( 'У Вас еще нет ни одной новостей.' ) . ' <a href="/admin/news/add">' . l::t ( 'Создать новость' ) . '</a>.' );
		} else {
			tpl::load2 ( 'admin-news-listen' );
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::$result[ 'data' ] = '';
			tpl::set ( '{nav}' , api::pagination ( $all , $allpage , $gpage , '/admin/news' ) );
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
			if ( ! preg_match ( "/^[0-9a-zA-Z_]{2,200}$/i" , $data[ 'url' ] ) ) {
				if ( mb_strlen ( $data[ 'url' ] , "utf-8" ) < 2 ) {
					api::result ( l::t ( 'Адрес слишком короткий' ) );
				} else {
					if ( mb_strlen ( $data[ 'url' ] , "utf-8" ) > 200 ) {
						api::result ( l::t ( 'Адрес слишком длинный' ) );
					} else {
						api::result ( l::t ( 'Адрес содержит недопустимые символы' ) );
					}
				}
			} else {
				$sql = db::q ( 'SELECT id FROM news where url="' . api::cl ( $data[ 'url' ] ) . '"' );
				if ( db::n ( $sql ) != 0 ) {
					api::result ( l::t ( 'Адрес уже используется' ) );
				} else {
					if ( $data[ 'cat' ] == "0" ) {
						db::q (
							"INSERT INTO news set
												name='" . api::cl ( $data[ 'name' ] ) . "',
												keywords='" . api::cl ( $data[ 'key' ] ) . "',
												description='" . api::cl ( $data[ 'des' ] ) . "',
												url='" . api::cl ( $data[ 'url' ] ) . "',
												info='" . base64_encode ( $data[ 'info' ] ) . "',
												info2='" . base64_encode ( $data[ 'info2' ] ) . "',
												cat='0',
												time='" . time () . "',
												etime='" . time () . "'"
						);
					} else {
						$sql4 = db::q ( 'SELECT * FROM news_cat where id="' . (int) $data[ 'cat' ] . '"' );
						if ( db::n ( $sql4 ) == 1 ) {
							db::q (
								"INSERT INTO news set
													name='" . api::cl ( $data[ 'name' ] ) . "',
													keywords='" . api::cl ( $data[ 'key' ] ) . "',
													description='" . api::cl ( $data[ 'des' ] ) . "',
													url='" . api::cl ( $data[ 'url' ] ) . "',
													info='" . base64_encode ( $data[ 'info' ] ) . "',
													info2='" . base64_encode ( $data[ 'info2' ] ) . "',
													cat='" . (int) $data[ 'cat' ] . "',
													time='" . time () . "',
													etime='" . time () . "'"
							);
						} else {
							api::result ( l::t ( 'Категория не найдена' ) );
						}
					}
					api::result ( l::t ( 'Новость успешно добавлена.' ) , true );
				}
			}
		}
		$title = l::t ( "Создание новости" );
		tpl::load2 ( 'admin-news-add' );
		$sql4 = db::q ( 'SELECT * FROM news_cat order by name asc' );
		$cats = '';
		while ( $row4 = db::r ( $sql4 ) ) {
			$cats .= '<option value="' . $row4[ 'id' ] . '">' . $row4[ 'name' ] . '</option>';
		}
		tpl::set ( '{cats}' , $cats );
		tpl::compile ( 'content' );
		api::nav ( '/admin/news' , l::t ( 'Новости' ) );
		api::nav ( '' , l::t ( 'Создание' ) , '1' );
	}

	public static function edit ( $id )
	{
		global $title;
		$data = $_POST[ 'data' ];
		db::q ( "SELECT * FROM news where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$news = db::r ();
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$data[ 'url' ] = mb_strtolower ( $data[ 'url' ] );

				$sql = db::q ( 'SELECT id FROM news where id != "' . $id . '" and url="' . api::cl ( $data[ 'url' ] ) . '"' );
				if ( db::n ( $sql ) != 0 ) {
					api::result ( l::t ('Адрес уже используется') );
				} else {
					if ( $data[ 'cat' ] == "0" ) {
						db::q (
							"UPDATE news set
													name='" . api::cl ( $data[ 'name' ] ) . "',
													keywords='" . api::cl ( $data[ 'key' ] ) . "',
													description='" . api::cl ( $data[ 'des' ] ) . "',
													url='" . api::cl ( $data[ 'url' ] ) . "',
													info='" . base64_encode ( $data[ 'info' ] ) . "',
													info2='" . base64_encode ( $data[ 'info2' ] ) . "',
													cat='0',
													etime='" . time () . "'
													where id='" . $id . "'"
						);
					} else {
						$sql4 = db::q ( 'SELECT * FROM news_cat where id="' . (int) $data[ 'cat' ] . '"' );
						if ( db::n ( $sql4 ) == 1 ) {
							db::q (
								"UPDATE news set
														name='" . api::cl ( $data[ 'name' ] ) . "',
														keywords='" . api::cl ( $data[ 'key' ] ) . "',
														description='" . api::cl ( $data[ 'des' ] ) . "',
														url='" . api::cl ( $data[ 'url' ] ) . "',
														info='" . base64_encode ( $data[ 'info' ] ) . "',
														info2='" . base64_encode ( $data[ 'info2' ] ) . "',
														cat='" . (int) $data[ 'cat' ] . "',
														etime='" . time () . "'
														where id='" . $id . "'"
							);
						} else {
							api::result ( l::t ('Категория не найдена') );
						}
					}
					api::result ( l::t ('Новость успешно отредактирована.') , true );
				}
			}
			$title = l::t ("Редактирование новости");
			tpl::load2 ( 'admin-news-edit' );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{name}' , $news[ 'name' ] );
			tpl::set ( '{key}' , $news[ 'keywords' ] );
			tpl::set ( '{des}' , $news[ 'description' ] );
			tpl::set ( '{link}' , $news[ 'url' ] );
			tpl::set ( '{info}' , base64_decode ( $news[ 'info' ] ) );
			tpl::set ( '{info2}' , base64_decode ( $news[ 'info2' ] ) );
			$sql4 = db::q ( 'SELECT * FROM news_cat order by name asc' );
			$cats = '';
			while ( $row4 = db::r ( $sql4 ) ) {
				if ( $news[ 'cat' ] == $row4[ 'id' ] ) {
					$cats .= '<option value="' . $row4[ 'id' ] . '" selected>' . $row4[ 'name' ] . '</option>';
				} else {
					$cats .= '<option value="' . $row4[ 'id' ] . '">' . $row4[ 'name' ] . '</option>';
				}
			}

			tpl::set ( '{cats}' , $cats );
			tpl::compile ( 'content' );
			api::nav ( '/admin/news' , l::t ('Новости') );
			api::nav ( '' , l::t ('Редактирование #') . $id , '1' );
		} else {
			api::result ( l::t ('Новость не найдена') );
		}
	}
}

?>