<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_rules
{
	public static $rules = array (
		'news'           => 'Управление новостями' ,
		'pages'          => 'Управление статистическими страницами' ,
		'location'       => 'Управление локациями' ,
		'rates'          => 'Управление тарифами' ,
		'servers'        => 'Управление игровыми серверами' ,
		'servers_delete' => 'Разрешить удалять серверы' ,
		'servers_edit'   => 'Разрешить редактировать серверы' ,
		'boxes'          => 'Управление физическими серверами' ,
		'tpl'            => 'Управление шаблонами' ,
		'faq'            => 'Управление вопросами на ответы' ,
		'support'        => 'Управление центром поддержки' ,
		'admins'         => 'Управление администраторами сайта' ,
		'logs_puy'       => 'Просмотр истории платежных операций' ,
		'puy_servers'    => 'Бесплатный заказ серверов' ,
		'addons'         => 'Управление репозиторием' ,
		'users'          => 'Управление пользователями' ,
		'isp'            => 'Управление Web хостингом' ,
		'puy_isp'        => 'Бесплатный заказ Web хостинга' ,
		'forum'          => 'Управление форумом' ,
		'mysql'          => 'Управление MySQL' ,
		'mysql_dell'     => 'Удаление MySQL' ,
		'chars'          => 'Просмотр статистики' ,
		'license'        => 'Просмотр лицензий' ,
		'ts3'            => 'Управление TS3' ,
	);

	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t ( 'Администраторы' ) , '1' );
		$sql = db::q ( 'SELECT id,user FROM admins order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-rules-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			$sql2 = db::q ( 'SELECT name,lastname FROM users where id="' . $row[ 'user' ] . '"' );
			$row2 = db::r ( $sql2 );
			tpl::set ( '{name}' , $row2[ 'name' ] . ' ' . $row2[ 'lastname' ] );
			tpl::set ( '{user}' , $row[ 'user' ] );
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Администраторы" );
		tpl::load2 ( 'admin-rules-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		tpl::compile ( 'content' );
	}

	public static function add ()
	{
		global $title;
		if ( api::admin ( 'admins' ) ) {
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( (int) $data[ 'friend' ] == 0 ) {
					api::result ( l::t ( 'Выберите пользователя' ) );
				} else {
					if ( db::n ( db::q ( 'SELECT id FROM users where id="' . (int) $data[ 'friend' ] . '"' ) ) == 1 ) {
						$error = 0;
						$data2 = array ();
						foreach ( self::$rules as $key => $value ) {
							if ( $data[ $key ] == 1 ) {
								$error = 1;
								$data2[ $key ] = 1;
							} else {
								$data2[ $key ] = 0;
							}
						}
						if ( $error == 1 ) {
							db::q ( "INSERT INTO admins set user='" . (int) $data[ 'friend' ] . "',data='" . json_encode ( $data2 ) . "'" );
							api::result ( l::t ( 'Добавлен' ) , true );
						} else {
							api::result ( l::t ( 'Выберите хоть один пункт привилегий' ) );
						}
					} else {
						api::result ( l::t ( 'Пользователь не найден' ) );
					}
				}
			}
			api::nav ( '/admin/rules' , l::t ( 'Администраторы' ) );
			api::nav ( '' , l::t ( 'Новый' ) , '1' );
			$title = l::t ( "Новый администратор" );
			tpl::load2 ( 'admin-rules-add' );
			$id3 = api::info ( 'id' );
			$sql3 = db::q ( 'SELECT name,lastname,id,mail FROM users' );
			$friends = '';
			while ( $row2 = db::r ( $sql3 ) ) {
				if ( db::n ( db::q ( 'SELECT id FROM admins where user="' . $row2[ 'id' ] . '"' ) ) == 0 && api::info ( 'id' ) != $row2[ 'id' ] ) {
					$friends .= "<option value='" . $row2[ 'id' ] . "'>" . $row2[ 'mail' ] . ' - ' . $row2[ 'name' ] . " " . $row2[ 'lastname' ] . "</option>";
				}
			}
			foreach ( self::$rules as $key => $value ) {
				tpl::load2 ( 'admin-rules-add-get' );
				tpl::set ( '{name}' , $key );
				tpl::set ( '{info}' , l::t ( $value ) );
				tpl::compile ( 'rules' );
			}
			tpl::set ( '{rules}' , tpl::$result[ 'rules' ] );
			tpl::set ( '{users}' , $friends );
			tpl::compile ( 'content' );
		} else {
			api::result ( l::t ( 'Запрашиваемая страница не найдена.' ) );
		}
	}

	public static function edit ( $id )
	{
		global $title;
		$sql = db::q ( 'SELECT * FROM admins where id="' . $id . '"' );
		if ( db::n ( $sql ) != 0 ) {
			$row = db::r ( $sql );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( (int) $data[ 'friend' ] == 0 ) {
					api::result ( l::t ( 'Выберите пользователя' ) );
				} else {
					if ( db::n ( db::q ( 'SELECT id FROM users where id="' . (int) $data[ 'friend' ] . '"' ) ) == 1 ) {
						$error = 0;
						$data2 = array ();
						foreach ( self::$rules as $key => $value ) {
							if ( $data[ $key ] == 1 ) {
								$error = 1;
								$data2[ $key ] = 1;
							} else {
								$data2[ $key ] = 0;
							}
						}
						if ( $error == 1 ) {
							db::q ( "UPDATE admins set user='" . (int) $data[ 'friend' ] . "',data='" . json_encode ( $data2 ) . "' where id='" . $id . "'" );
							api::result ( l::t ( 'Сохранено' ) , true );
						} else {
							api::result ( l::t ( 'Выберите хоть один пункт привилегий' ) );
						}
					} else {
						api::result ( l::t ( 'Пользователь не найден' ) );
					}
				}
			}
			$rules = json_decode ( $row[ 'data' ] , true );
			foreach ( self::$rules as $key => $value ) {
				tpl::load2 ( 'admin-rules-edit-get' );
				tpl::set ( '{name}' , $key );
				tpl::set ( '{info}' , l::t ( $value ) );
				if ( $rules[ $key ] == 1 ) {
					tpl::set ( '{checked}' , 'checked' );
				} else {
					tpl::set ( '{checked}' , '' );
				}
				tpl::compile ( 'rules' );
			}
			$id3 = api::info ( 'id' );
			$sql3 = db::q ( 'SELECT * FROM users' );
			$friends = '';
			while ( $row13 = db::r ( $sql3 ) ) {
				if ( api::info ( 'id' ) != $row13[ 'id' ] ) {
					if ( db::n ( db::q ( 'SELECT id FROM admins where user="' . $row13[ 'id' ] . '"' ) ) == 0 or $row13[ 'id' ] == $row[ 'user' ] ) {
						if ( $row13[ 'id' ] == $row[ 'user' ] ) {
							$friends .= "<option value='" . $row13[ 'id' ] . "' selected>" . $row13[ 'name' ] . " " . $row13[ 'lastname' ] . "</option>";
						} else {
							$friends .= "<option value='" . $row13[ 'id' ] . "'>" . $row13[ 'name' ] . " " . $row13[ 'lastname' ] . "</option>";
						}

					}
				}
			}
			tpl::load2 ( 'admin-rules-edit' );
			tpl::set ( '{rules}' , tpl::$result[ 'rules' ] );
			tpl::set ( '{users}' , $friends );
			tpl::set ( '{id}' , $id );
			tpl::compile ( 'content' );
			api::nav ( '/admin/rules' , l::t ( 'Администраторы' ) );
			api::nav ( '' , l::t ( 'Редактирование' ) , '1' );
			$title = l::t ( "Редактирование" );
		} else {
			api::result ( l::t ( 'Администратор не найден.' ) );
		}
	}

	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		$sql = db::q ( 'SELECT * FROM admins where id="' . $id . '"' );
		if ( db::n ( $sql ) != 0 ) {
			$row = db::r ( $sql );
			db::q ( 'DELETE from admins where id="' . $id . '"' );
			api::result ( l::t ( 'Удалено' ) , true );
		} else {
			api::result ( l::t ( 'Администратор не найден.' ) );
		}
	}
}

?>