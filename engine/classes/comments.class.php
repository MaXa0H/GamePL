<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class comments
{
	public static function base ( $data )
	{
		global $array;
		$sql = db::q ( 'SELECT id FROM ' . $data[ 'table' ] . ' where id2="' . $data[ 'id' ] . '"' );
		$all = db::n ( $sql );
		if ( (int) $array[ 'page' ] ) {
			if ( ( $all / 10 ) > (int) $array[ 'page' ] ) {
				$page = 10 * (int) $array[ 'page' ];
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		$sql = db::q ( 'SELECT * FROM ' . $data[ 'table' ] . ' where id2="' . $data[ 'id' ] . '" order by id desc LIMIT ' . $page . ' ,10' );
		if ( db::n ( $sql ) != 0 ) {
			while ( $row = db::r ( $sql ) ) {
				tpl::load ( 'base-comments-get' );
				$sql4 = db::q ( 'SELECT name,lastname,photo,id FROM users where id="' . $row[ 'user' ] . '"' );
				$row4 = db::r ( $sql4 );
				if ( $row4[ 'photo' ] ) {
					tpl::set ( '{photo}' , '/files/photo/' . $row4[ 'id' ] . '.png' );
				} else {
					tpl::set ( '{photo}' , '/img/noavatar.png' );
				}
				tpl::set ( '{name}' , $row4[ 'name' ] . ' ' . $row4[ 'lastname' ] );
				tpl::set ( '{user_id}' , $row[ 'user' ] );
				tpl::set ( '{user}' , $row[ 'user' ] );
				tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );

				tpl::set ( '{comment_id}' , $row[ 'id' ] );
				tpl::set ( '{comment}' , $row[ 'mess' ] );
				tpl::compile ( 'data' );
			}
		}
		tpl::load ( 'base-comments-main' );
		if ( api::info ( 'photo' ) ) {
			tpl::set ( '{photo}' , '/files/photo/' . api::info ( 'id' ) . '.png' );
		} else {
			tpl::set ( '{photo}' , '/img/noavatar.png' );
		}
		if ( db::n ( $sql ) == 0 ) {
			tpl::set_block ( "'\\[comments\\](.*?)\\[/comments\\]'si" , "" );
		} else {
			tpl::set ( '{comments}' , tpl::result ( 'data' ) );
			tpl::set_block ( "'\\[comments\\](.*?)\\[/comments\\]'si" , "\\1" );
		}
		if ( api::$go ) {
			tpl::set_block ( "'\\[comments_add\\](.*?)\\[/comments_add\\]'si" , "\\1" );
		} else {
			tpl::set_block ( "'\\[comments_add\\](.*?)\\[/comments_add\\]'si" , "" );
		}
		tpl::set ( '{link}' , $data[ 'link' ] );
		tpl::set ( '{nav}' , api::pagination ( $all , 10 , (int) $array[ 'page' ] , $data[ 'link' ] ) );
		tpl::compile ( 'comments' );

		return tpl::result ( 'comments' );
	}

	public static function num ( $data )
	{
		$sql = db::q ( 'SELECT * FROM ' . $data[ 'table' ] . ' where id2="' . $data[ 'id' ] . '"' );

		return db::n ( $sql );
	}

	public static function add ( $data )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( ! api::$go ) {
			api::result ( l::t ( 'Для доступа к данной странице нужно авторизоваться на сайте' ) );

			return false;
		}
		if ( ! preg_match ( "/^.{2,3000}$/si" , $data[ 'comment' ] ) ) {
			if ( ! $data[ 'comment' ] ) {
				api::result ( l::t ( 'Введите текст комментария' ) );
			} else {
				if ( mb_strlen ( $data[ 'comment' ] , "utf-8" ) < 2 ) {
					api::result ( l::t ( 'Текст комментария слишком короткий' ) );
				} else {
					if ( mb_strlen ( $data[ 'comment' ] , "utf-8" ) > 3000 ) {
						api::result ( l::t ( 'Текст комментария слишком длинный' ) );
					} else {
						api::result ( l::t ( 'Текст комментария содержит недопустимые символы' ) );
					}
				}
			}
		} else {
			db::q (
				"INSERT INTO " . $data[ 'table' ] . " set
				user='" . api::info ( 'id' ) . "',
				time='" . time () . "',
				mess='" . api::cl ( $data[ 'comment' ] ) . "',
				id2='" . $data[ 'id' ] . "'"
			);
			api::result ( l::t ( 'Комментарий успешно добавлен' ) , true );
		}
	}

	public static function dell ( $data )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( ! api::$go ) {
			api::result ( l::t ( 'Для доступа к данной странице нужно авторизоваться на сайте' ) );

			return false;
		}
		if ( api::admin ( 'comments' ) ) {
			db::q ( "SELECT * FROM " . $data[ 'table' ] . " where id='" . (int) $data[ 'id' ] . "'" );
			if ( db::n () != "1" ) {
				api::result ( l::t ( 'Не найдено' ) );
			} else {
				db::q ( "DELETE FROM " . $data[ 'table' ] . " where id='" . (int) $data[ 'id' ] . "'" );
				api::result ( l::t ( 'Комментарий успешно удален' ) , true );
			}
		} else {
			api::result ( l::t ( 'Нет доступа' ) );
		}
	}

	public static function dell_all ( $data )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "DELETE FROM " . $data[ 'table' ] . " where id2='" . (int) $data[ 'id' ] . "'" );
	}
}

?>