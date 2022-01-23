<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_faq
{
	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT id FROM faq where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( 'DELETE from faq where id="' . $id . '"' );
			api::result ( l::t ( 'Удалено' ) , true );
		} else {
			api::result ( l::t ( 'Вопрос не найден' ) );
		}
	}

	public static function edit ( $id )
	{
		global $title;
		db::q ( "SELECT * FROM faq where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$row = db::r ();
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$f = trim ( $data[ 'f' ] );
				$q = trim ( $data[ 'q' ] );
				db::q ( "UPDATE faq set f='" . base64_encode ( $f ) . "',q='" . base64_encode ( $q ) . "' where id='" . $id . "'" );
				api::result ( l::t ( 'Сохранено' ) , true );
			}
			$title = l::t ( "Редактирование" );
			tpl::load2 ( 'admin-faq-edit' );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{f}' , base64_decode ( $row[ 'f' ] ) );
			tpl::set ( '{q}' , base64_decode ( $row[ 'q' ] ) );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '/admin/faq' , l::t ( 'FAQ' ) );
				api::nav ( '' , l::t ( 'Редактирование' ) , '1' );
			}
		} else {
			api::result ( l::t ( 'Вопрос на ответ не найден' ) );
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
			$f = trim ( $data[ 'f' ] );
			$q = trim ( $data[ 'q' ] );
			db::q ( "INSERT INTO faq set f='" . base64_encode ( $f ) . "',q='" . base64_encode ( $q ) . "'" );
			api::result ( l::t ( 'Ответ на вопрос добавлен' ) , true );
		}
		$title = l::t ( "Добавление ответа на вопрос" );
		tpl::load2 ( 'admin-faq-add' );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		} else {
			api::nav ( '/admin/faq' , l::t ( 'FAQ' ) );
			api::nav ( '' , l::t ( 'Создание' ) , '1' );
		}
	}

	public static function listen ()
	{
		global $title;
		api::nav ( '/admin/faq' , l::t ( 'FAQ' ) , 1 );
		$sql = db::q ( 'SELECT id,f FROM faq order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-faq-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , base64_decode ( $row[ 'f' ] ) );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "FAQ" );
		tpl::load2 ( 'admin-faq-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		tpl::compile ( 'content' );
	}
}

?>