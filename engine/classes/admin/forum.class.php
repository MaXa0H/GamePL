<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class admin_forum
{
	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t('Форумы') , '1' );
		$sql = db::q ( 'SELECT * FROM forum_cat order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-forum-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			if( $row[ 'subcat' ]){
				$sql2 = db::q ( 'SELECT name FROM forum_cat where id="'.$row['subcat'].'"' );
				$row2 = db::r ( $sql2 );
				tpl::set ( '{name2}' , $row2['name'] );
			}else{
				tpl::set ( '{name2}' , 'Нет' );
			}

			tpl::compile ( 'data' );
		};
		$title = l::t("Управление форумами");
		tpl::load2 ( 'admin-forum-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
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
				db::q ( "INSERT INTO forum_cat set
						name='" . api::cl($data[ 'name' ]) . "',
						img='".db::s($data['img'])."',
						tname='".api::cl(api::totranslit($data['name']))."',
						des='".api::cl($data['des'])."',
						subcat='".(int)$data['cat']."'
					" );
				api::result ( l::t('Форум создан') , true );
			} else {
				api::result ( l::t('Укажите название форума') );
			}
		}
		$title = l::t("Новый форум");
		tpl::load2 ( 'admin-forum-add' );
		$sql4 = db::q ( 'SELECT * FROM forum_cat where subcat="0" order by id asc' );
		$cats = '';
		while ( $row4 = db::r ( $sql4 ) ) {
			$cats .= '<option value="' . $row4[ 'id' ] . '">' . $row4[ 'name' ] . '</option>';
		}
		tpl::set ( '{cat}' , $cats );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		} else {
			api::nav ( '/admin/forum' , l::t('Форум') );
			api::nav ( '' , l::t('Новый') , '1' );
		}
	}

	public static function edit ( $id )
	{
		global $title;
		db::q ( "SELECT * FROM forum_cat where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$cat = db::r ();
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( $data[ 'name' ] != "" ) {
					db::q ( "UPDATE forum_cat set
						name='" . api::cl($data[ 'name' ]) . "',
						tname='".api::totranslit($data['name'])."',
						img='".db::s($data['img'])."',
						des='".api::cl($data['des'])."'
						where id='" . $cat[ 'id' ] . "'" );
					api::result ( l::t('Форум сохранен') , true );
				} else {
					api::result ( l::t('Укажите название форума') );
				}
			}
			$title = l::t("Редактирование");
			tpl::load2 ( 'admin-forum-edit' );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{name}' , $cat[ 'name' ] );
			tpl::set ( '{des}' , $cat[ 'des' ] );
			tpl::set ( '{img}' , $cat[ 'img' ] );

			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '/admin/forum' , l::t('Форумы') );
				api::nav ( '' , l::t('Редактирование') , '1' );
			}
		} else {
			api::result (l::t( 'Форум не найден') );
		}
	}

	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT id FROM forum_cat where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( 'SELECT id FROM forum_cat where subcat="' . $id . '"' );
			if ( db::n () == "0" ) {
				db::q ( 'DELETE from forum_cat where id="' . $id . '"' );
				db::q ( 'DELETE from forum_mes where cat="' . $id . '"' );
				db::q ( 'DELETE from forum_post where cat="' . $id . '"' );

				api::result ( l::t('Форум удален') , true );
			} else {
				api::result ( l::t('Сначала удалите подфорумы') );
			}
		} else {
			api::result ( l::t('Форум не найден') );
		}
	}
}

?>