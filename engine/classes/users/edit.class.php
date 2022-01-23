<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class users_edit
{
	public static function edit_phone ()
	{
		global $conf;
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( $data[ 'code' ] ) {
				$key = $_SESSION[ 'users_key_1' ];
				$key2 = $_SESSION[ 'users_key_2' ];
				if ( $key != $data[ 'code1' ] ) {
					api::result ( l::t ('Код подтверждения №1 указан неверно.') );
				} else {
					if ( $key2 != $data[ 'code2' ] ) {
						api::result ( l::t ('Код подтверждения №2 указан неверно.') );
					} else {
						db::q ( "UPDATE users set phone='" . $_SESSION[ 'users_key_3' ] . "' where id='" . api::info ( 'id' ) . "'" );
						$_SESSION[ 'users_key_1' ] = time ();
						$_SESSION[ 'users_key_2' ] = time ();
						$_SESSION[ 'users_key_3' ] = time ();
						api::result ( l::t ('Номер телефона изменен') , true );
					}
				}
				api::result ( l::t ("Коды подтверждения указаны неверно") );
			} else {
				if ( ! preg_match ( "/^[0-9]{11,13}$/i" , $data[ 'new' ] ) ) {
					api::result ( l::t ("Телефон указан неверно") );
				} else {
					if ( $conf[ 'sms_signup' ] == 1 ) {
						$key = mt_rand ( 1000 , 9999 );
						$key2 = mt_rand ( 1000 , 9999 );
						api::inc ( 'sms' );
						$phone = api::info ( 'phone' );
						$phone2 = api::cl($data[ 'new' ]);
						if ( $phone == $phone2 ) {
							api::result ( l::t ('Номер телефона совпадает со старым!') );
						} else {
							if ( $d = sms::send ( $phone , l::t ('код №1:').' ' . $key ) ) {
								$skey = true;
							} else {
								$skey = false;
							}
							if ( $d2 = sms::send ( $phone2 , l::t ('код №2:').' ' . $key2 ) ) {
								$skey2 = true;
							} else {
								$skey2 = false;
							}

							if ( ! $skey || ! $skey2 ) {
								api::result ( l::t ('Не удалось отправить СМС на один из ваших номеров.') );
							} else {
								$_SESSION[ 'users_key_1' ] = $key;
								$_SESSION[ 'users_key_2' ] = $key2;
								$_SESSION[ 'users_key_3' ] = $phone2;
								api::result ( '1' , true );
							}
						}
					} else {
						api::result ( l::t ('На сайте отключена СМС регистрация') );
					}
				}
			}
		}
		tpl::load ( 'users-edit-phone' );
		tpl::set ( '{id}' , api::info ( 'id' ) );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		}
	}

	public static function edit_mail ()
	{
		global $conf;

		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( $data[ 'code' ] ) {
				$key = $_SESSION[ 'users_key_1' ];
				$key2 = $_SESSION[ 'users_key_2' ];
				if ( $key != $data[ 'code1' ] ) {
					api::result ( l::t ('Код подтверждения №1 указан неверно.') );
				} else {
					if ( $key2 != $data[ 'code2' ] ) {
						api::result ( l::t ('Код подтверждения №2 указан неверно.') );
					} else {
						db::q ( "UPDATE users set mail='" . $_SESSION[ 'users_key_3' ] . "' where id='" . api::info ( 'id' ) . "'" );
						$_SESSION[ 'users_key_1' ] = time ();
						$_SESSION[ 'users_key_2' ] = time ();
						$_SESSION[ 'users_key_3' ] = time ();
						api::result ( l::t ('E-mail изменен') , true );
					}
				}
				api::result ( l::t ("Коды подтверждения указаны неверно") );
			} else {
				$data[ 'new' ] = api::cl ( $data[ 'new' ] );
				if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $data[ 'new' ] ) ) {
					api::result ( l::t ("E-mail указан неверно") );
				} else {
					db::q ( 'SELECT id FROM users where mail="' . $data[ 'new' ] . '"' );
					if ( db::n () == "1" ) {
						api::result ( l::t ("E-mail занят") );
					} else {
						$row = db::r();
						$key = mt_rand ( 1000 , 9999 );
						$key2 = mt_rand ( 1000 , 9999 );
						api::inc ( 'mail' );
						$mail = api::info ( 'mail' );
						$mail2 = $data[ 'new' ];
						if ( $mail == $mail2 ) {
							api::result ( l::t ('Почтовый адрес совпадает со старым!') );
						} else {
							api::inc ( 'mail' );
							tpl::load('mail-body');
							tpl::set('{title}',$conf['title']);
							$msg = "<h4>".l::t ('Здравствуйте')." ".$row['name']." ".$row['lastname'].",</h4>";
							$msg .= "<p>".l::t ('Вы сделали запрос на изменение e-mail.')."</p>";
							$msg .= '<div style="padding: 10px;background: #ECF8FF;border: 0;">';
							$msg .= l::t ("Код №1:")." " . $key;
							$msg .= '</div>';
							$msg .= "<p>".l::t ('Если вы не делали запроса для изменения e-mail, то просто удалите данное письмо.')."</p>";
							tpl::set('{content}',$msg);
							tpl::compile('mail');
							mail::send ( $mail , l::t ('Подтверждение e-mail адреса') , tpl::result('mail') );

							tpl::load('mail-body');
							tpl::set('{title}',$conf['title']);
							$msg = "<h4>".l::t ('Здравствуйте')." ".$row['name']." ".$row['lastname'].",</h4>";
							$msg .= "<p>".l::t ('Вы сделали запрос на изменение e-mail.')."</p>";
							$msg .= '<div style="padding: 10px;background: #ECF8FF;border: 0;">';
							$msg .= l::t ("Код №2:")." " . $key2;
							$msg .= '</div>';
							$msg .= "<p>".l::t ('Если вы не делали запроса для изменения e-mail, то просто удалите данное письмо.')."</p>";
							tpl::set('{content}',$msg);
							tpl::compile('mail2');
							mail::send ( $mail2 , l::t ('Подтверждение e-mail адреса') , tpl::result('mail2') );

							$_SESSION[ 'users_key_1' ] = $key;
							$_SESSION[ 'users_key_2' ] = $key2;
							$_SESSION[ 'users_key_3' ] = $mail2;
							api::result ( '1' , true );
						}
					}
				}
			}
		}
		tpl::load ( 'users-edit-mail' );
		tpl::set ( '{id}' , api::info ( 'id' ) );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		}
	}
}
?>