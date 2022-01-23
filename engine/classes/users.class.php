<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class users
{
	public static function signup_get ()
	{
		api::nav ( '' , l::t ( 'Регистрация' ) , '1' );
		tpl::load ( 'users-signup-get' );
		tpl::compile ( 'content' );
	}

	public static function recovery_get ()
	{
		api::nav ( '' , l::t ( 'Восстановление пароля' ) , '1' );
		tpl::load ( 'users-recovery-get' );
		tpl::compile ( 'content' );
	}

	public static function invite ( $id )
	{
		db::q ( 'SELECT id FROM users where id="' . $id . '"' );
		if ( db::n () != 0 ) {
			api::set_cookie ( "invite" , $id , 7 );
		}
		header ( 'location:/' );
		exit;
	}

	public static function profile ( $id )
	{
		global $title , $conf;
		if ( $id != api::info ( 'id' ) ) {
			if ( ! api::admin ( 'profile' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий для доступа к данной странице' ) );

				return false;
			}
		}
		db::q ( 'SELECT * FROM users where id="' . $id . '"' );
		if ( db::n () == 0 ) {
			api::result ( l::t ( 'Пользователь не найден' ) );

			return false;
		}
		$user = db::r ();
		db::q ( 'SELECT id FROM logs_balance where user="' . $id . '" order by id desc' );
		$all = db::n ();
		$_GET[ 'page' ] = (int) r::g ( 4 );
		if ( (int) $_GET[ 'page' ] ) {
			if ( ( $all / 20 ) > (int) $_GET[ 'page' ] ) {
				$page = 20 * (int) $_GET[ 'page' ];
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		$sql = db::q ( 'SELECT * FROM logs_balance where user="' . $id . '" order by id desc LIMIT ' . $page . ' ,20' );
		$data = "";
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-money-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{com}' , $row[ 'mes' ] );
			tpl::set ( '{sum}' , $row[ 'sum' ] );
			tpl::set ( '{time}' , api::langdate ( "d.m.Y - H:i" , $row[ 'time' ] ) );
			if ( $row[ 'tip' ] == 0 ) {
				tpl::set_block ( "'\\[act-0\\](.*?)\\[/act-0\\]'si" , "\\1" );
				tpl::set_block ( "'\\[act-1\\](.*?)\\[/act-1\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[act-1\\](.*?)\\[/act-1\\]'si" , "\\1" );
				tpl::set_block ( "'\\[act-0\\](.*?)\\[/act-0\\]'si" , "" );
			}
			tpl::compile ( 'logs' );
		}
		$sql = db::q ( 'SELECT * FROM  login_key where user="' . $id . '" order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load ( 'users-profile-login' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{ip}' , base64_decode ( $row[ 'ip' ] ) );
			tpl::set ( '{agent}' , base64_decode ( $row[ 'agent' ] ) );
			tpl::set ( '{time}' , api::langdate ( "d.m.Y <br> H:i" , $row[ 'time' ] ) );
			tpl::compile ( 'logs2' );
		}
		tpl::load ( 'users-profile' );
		if ( $id != api::info ( 'id' ) ) {
			if ( ! api::admin ( 'profile' ) ) {
				tpl::set_block ( "'\\[link\\](.*?)\\[/link\\]'si" , "" );
				tpl::set_block ( "'\\[link2\\](.*?)\\[/link2\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[link\\](.*?)\\[/link\\]'si" , "" );
				tpl::set_block ( "'\\[link2\\](.*?)\\[/link2\\]'si" , "\\1" );
			}
			tpl::set_block ( "'\\[link3\\](.*?)\\[/link3\\]'si" , "\\1" );
		} else {
			tpl::set_block ( "'\\[link\\](.*?)\\[/link\\]'si" , "\\1" );
			tpl::set_block ( "'\\[link2\\](.*?)\\[/link2\\]'si" , "\\1" );
			tpl::set_block ( "'\\[link3\\](.*?)\\[/link3\\]'si" , "" );
		}
		tpl::set ( '{logs_balance}' , tpl::result ( 'logs' ) );
		tpl::set ( '{logs_auth}' , tpl::result ( 'logs2' ) );
		tpl::set ( '{mail}' , $user[ 'mail' ] );
		tpl::set ( '{vk_id}' , $conf[ 'vk_id' ] );
		tpl::set ( '{vk}' , $user[ 'vk_id' ] );

		tpl::set ( '{phone}' , $user[ 'phone' ] );
		tpl::set ( '{name}' , $user[ 'name' ] . ' ' . $user[ 'lastname' ] );
		tpl::set ( '{invite}' , "http://" . $conf[ 'domain' ] . '/users/invite/' . $id );
		db::q ( 'SELECT id FROM users where invite="' . $id . '" and signup="0" order by id desc' );
		$priced = db::n ();
		tpl::set ( '{invited}' , $priced );
		tpl::set ( '{invite_money}' , $user[ 'invite_money' ] );
		$pr = $conf[ 'invite' ];
		krsort ( $pr );
		foreach ( $pr as $key => $val ) {
			if ( $priced >= $key ) {
				$price = $val;
				break;
			}
		}
		tpl::set ( '{invited_price}' , $price );
		tpl::set ( '{balance}' , $user[ 'balance' ] );
		tpl::set ( '{id}' , $id );
		db::q ( '(SELECT id FROM gh_servers WHERE user="' . $id . '") UNION (SELECT t1.id FROM gh_servers as t1, gh_servers_friends as t2 WHERE t1.id = t2.server and t2.user="' . $id . '") order by id desc' );
		tpl::set ( '{servers}' , db::n () );
		db::q ( 'SELECT id FROM mysql where user="' . $id . '"' );
		tpl::set ( '{mysql}' , db::n () );
		db::q ( 'SELECT id FROM isp where user="' . $id . '"' );
		tpl::set ( '{web}' , db::n () );
		tpl::set ( '{nav}' , api::pagination ( $all , 20 , (int) $_GET[ 'page' ] , '/users/profile/' . $id ) );
		if ( $user[ 'photo' ] ) {
			tpl::set ( '{photo}' , '/files/photo/' . $user[ 'id' ] . '.png' );
		} else {
			tpl::set ( '{photo}' , '/img/noavatar.png' );
		}
		if ( $user[ 'ugroup' ] == 1 ) {
			tpl::set ( '{group}' , l::t ( 'Администратор' ) );
		} else {
			$sql = db::q ( 'SELECT * FROM admins where user="' . $user[ 'id' ] . '"' );
			if ( db::n ( $sql ) == 1 ) {
				tpl::set ( '{group}' , l::t ( 'Команда проекта' ));
			} else {
				tpl::set ( '{group}' , l::t ( 'Клиент' ));
			}
		}
		tpl::compile ( 'content' );
		api::nav ( '' , l::t ( 'Профиль' ) , '1' );
		$title = l::t ( "Профиль" );
	}

	public static function settings ( $data )
	{
		global $title;
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			$passold = $data[ 'oldpass' ];
			$passnew = $data[ 'newpass' ];
			$passnew2 = $data[ 'newpass2' ];
			if ( md5 ( $passold ) != api::info ( 'pass' ) ) {
				api::result ( l::t ( 'Старый пароль указан неверно' ) );
			} else {
				if ( $passnew != $passnew2 ) {
					api::result ( l::t ( 'Пароли не совпадают' ) );
				} else {
					if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $passnew ) ) {
						api::result ( l::t ( "Новый пароль указан неверно" ) );
					} else {
						db::q ( "UPDATE users set pass='" . md5 ( $passnew ) . "' where id='" . api::info ( 'id' ) . "'" );
						api::result ( l::t ( 'Пароль изменен' ) , true );
					}
				}
			}
		}
		tpl::load ( 'users-settings' );
		if ( api::modal () ) {
			tpl::compile ( 'content' );
			die( tpl::result ( 'content' ) );
		} else {
			tpl::compile ( 'content2' );
			api::result ( l::t ( 'Данную страницу можно открывать только в модальном окне' ) );
		}
	}

	public static function avatar ()
	{
		global $title;
		$imageinfo = getimagesize ( $_FILES[ 'img' ][ 'tmp_name' ] );
		if ( $_POST[ 'data' ] ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( $imageinfo ) {
				if ( $imageinfo[ "mime" ] != "image/jpeg" && $imageinfo[ "mime" ] != "image/png" ) {
					api::result ( l::t("Формат должен быть jpeg, png.") );

					return false;
				}
				if ( self::big_res ( $_FILES[ 'img' ][ 'tmp_name' ] , ROOT . '/files/photo/' . api::info ( 'id' ) . '.png' ) ) {
					if ( filesize ( ROOT . '/files/photo/' . api::info ( 'id' ) . '.png' ) > 5000 ) {
						db::q ( "UPDATE users set photo='1' where id='" . api::info ( 'id' ) . "'" );
						api::result ( l::t('Изображение загружено') , true );
					} else {
						api::result ( l::t('Слишком маленькое изображение') );
					}
				} else {
					return false;
				}
			} else {
				api::result ( l::t('Выберите изображение') );
			}
		}
		tpl::load ( 'users-profile-avatar' );
		tpl::set ( '{id}' , api::info ( 'id' ) );
		if ( api::modal () ) {
			tpl::compile ( 'content' );
			die( tpl::result ( 'content' ) );
		} else {
			tpl::compile ( 'content2' );
			api::result ( l::t('Данную страницу можно открывать только в модальном окне') );
		}
	}

	public static function vk_auth ()
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( $return = api::authOpenAPIMember () ) {
			if ( api::$go ) {
				db::q ( 'SELECT * FROM users where id!="' . api::info ( 'id' ) . '" and vk_id="' . $return[ 'id' ] . '"' );
				if ( db::n () == 1 ) {
					api::result ( l::t('Страница уже привязана') );
				} else {
					db::q ( "UPDATE users set vk_id='" . $return[ 'id' ] . "' where id='" . api::info ( 'id' ) . "'" );
					api::result ( l::t('Страница прикреплена') , 1 );
				}
				die;
			} else {
				db::q ( 'SELECT * FROM users where vk_id="' . $return[ 'id' ] . '"' );
				if ( db::n () == 1 ) {
					$row = db::r ();
					$rtime = time ();
					$time = round ( $rtime , - 3 );
					$ip = base64_encode ( $_SERVER[ 'REMOTE_ADDR' ] );
					$agent = base64_encode ( $_SERVER[ 'HTTP_USER_AGENT' ] );
					$key = md5 ( $row[ 'mail' ] . $row[ 'pass' ] . $time . $ip ) . md5 ( $agent . $time );
					$key2 = md5 ( $key . $time ) . md5 ( $ip . $time );
					db::q ( 'DELETE from login_key where key1="' . $key . '"' );
					$sql12 = db::q ( 'SELECT id FROM login_key where user="' . $row[ 'id' ] . '" order by id desc LIMIT 4,1' );
					if ( db::n ( $sql12 ) != 0 ) {
						$row12 = db::r ( $sql12 );
						db::q ( 'DELETE from login_key where  user="' . $row[ 'id' ] . '" and id<="' . $row12[ 'id' ] . '"' );
					}
					db::q (
						"INSERT INTO login_key set
									user='" . $row[ 'id' ] . "',
									time='" . $rtime . "',
									ip='" . $ip . "',
									key1='" . $key . "',
									agent='" . $agent . "'"
					);
					api::set_cookie ( "key" , $key , 7 );
					api::set_cookie ( "key2" , $key2 , 7 );
					api::result (l::t('Успешно авторизованы') , 1 );
				} else {
					api::result ( l::t('Учетная запись не найдена') );
				}
			}
		} else {
			if ( api::$go ) {
				db::q ( "UPDATE users set vk_id='0' where id='" . api::info ( 'id' ) . "'" );
				api::result ( l::t('Страница откреплена') , 1 );
			} else {
				api::result (l::t( 'Учетная запись не найдена') );
			}
		}

	}

	public static function big_res ( $vvname , $newname )
	{
		$max_size = 250; //максимальный размер большей стороны
		if ( file_exists ( $vvname ) ) {
			$infoimg = getimagesize ( $vvname );
			switch ( $infoimg[ 2 ] ) {
				case 1:
					$source = imagecreatefromgif ( $vvname );
					$formatimg = ".gif";
					break;
				case 2:
					$source = imagecreatefromjpeg ( $vvname );
					$formatimg = ".jpg";
					break;
				case 3:
					$source = imagecreatefrompng ( $vvname );
					$formatimg = ".png";
					break;
				default:
					exit;
			}
			if ( ( $infoimg[ 0 ] <= $max_size ) && ( $infoimg[ 1 ] <= $max_size ) ) {
				// если не меняем разрешение то сохраняем
				$resource = imagecreatetruecolor ( $infoimg[ 0 ] , $infoimg[ 1 ] );
				imagecopyresampled (
					$resource , $source , 0 , 0 , 0 , 0 , $infoimg[ 0 ] , $infoimg[ 1 ] ,
					$infoimg[ 0 ] , $infoimg[ 1 ]
				);
				imagepng ( $resource , $newname );
				imagedestroy ( $resource );
				imagedestroy ( $source );
			} else {
				// если нужно ресайзитьs
				$x_vr = $infoimg[ 0 ];
				$y_vr = $infoimg[ 1 ];
				if ( $x_vr > $y_vr ) {
					$resource = imagecreatetruecolor ( $max_size , floor ( ( $max_size / $x_vr ) * $y_vr ) );
					imagecopyresampled (
						$resource , $source , 0 , 0 , 0 , 0 , $x_vr * ( $max_size / $x_vr ) ,
						$y_vr * ( $max_size / $x_vr ) , $infoimg[ 0 ] , $infoimg[ 1 ]
					);
				}
				if ( $y_vr > $x_vr ) {
					$resource = imagecreatetruecolor ( floor ( ( $max_size / $y_vr ) * $x_vr ) , $max_size );
					imagecopyresampled (
						$resource , $source , 0 , 0 , 0 , 0 , $x_vr * ( $max_size / $y_vr ) ,
						$y_vr * ( $max_size / $y_vr ) , $infoimg[ 0 ] , $infoimg[ 1 ]
					);
				}
				if ( $y_vr == $x_vr ) {
					$resource = imagecreatetruecolor ( floor ( ( $max_size / $y_vr ) * $x_vr ) , $max_size );
					imagecopyresampled (
						$resource , $source , 0 , 0 , 0 , 0 , $x_vr * ( $max_size / $y_vr ) ,
						$y_vr * ( $max_size / $y_vr ) , $infoimg[ 0 ] , $infoimg[ 1 ]
					);
				}
				imagepng ( $resource , $newname );
				imagedestroy ( $resource );
				imagedestroy ( $source );
			}
		}

		return true;
	}

	public static function logout ()
	{
		global $conf;
		api::set_cookie ( "key" , "" , 0 );
		api::set_cookie ( "key2" , "" , 0 );
		api::set_cookie ( "vk_app_" . $conf[ 'vk_id' ] , "" , 0 );
		header ( 'location:/' );
		exit;
	}

	public static function login ( $data )
	{
		global $title , $conf;
		if ( api::$go ) {
			api::result ( l::t("Выйдите из аккаунта") );
		} else {
			$login = api::cl ( $data[ 'email' ] );
			$pass = md5 ( $data[ 'password' ] );
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $login ) ) {
					api::result ( l::t("E-mail указан неверно") );
				} else {
					if ( ! preg_match ( "/^[0-9a-zA-Z]{6,40}$/i" , $pass ) ) {
						api::result ( l::t("Пароль указан неверно") );
					} else {
						db::q ( 'SELECT * FROM users where mail="' . $login . '" and pass="' . $pass . '"' );
						if ( db::n () != 1 ) {
							api::result ( l::t("E-mail или пароль указан неверно") );
						} else {
							$row = db::r ();
							if ( $row[ 'signup' ] != '0' ) {
								api::result ( l::t('Подтвердите свой почтовый ящик') );
							} else {
								api::set_cookie ( "key" , null , null );
								api::set_cookie ( "key2" , null , null );
								$rtime = time ();
								$time = round ( $rtime , - 3 );
								$ip = base64_encode ( $_SERVER[ 'REMOTE_ADDR' ] );
								$agent = base64_encode ( $_SERVER[ 'HTTP_USER_AGENT' ] );
								$key = md5 ( $login . $pass . $time . $ip ) . md5 ( $agent . $time );
								$key2 = md5 ( $key . $time ) . md5 ( $ip . $time );
								db::q ( 'DELETE from login_key where key1="' . $key . '"' );
								$sql12 = db::q ( 'SELECT id FROM login_key where user="' . $row[ 'id' ] . '" order by id desc LIMIT 4,1' );
								if ( db::n ( $sql12 ) != 0 ) {
									$row12 = db::r ( $sql12 );
									db::q ( 'DELETE from login_key where  user="' . $row[ 'id' ] . '" and id<="' . $row12[ 'id' ] . '"' );
								}
								db::q (
									"INSERT INTO login_key set
									user='" . $row[ 'id' ] . "',
									time='" . $rtime . "',
									ip='" . $ip . "',
									key1='" . $key . "',
									agent='" . $agent . "'"
								);
								api::set_cookie ( "key" , $key , 7 );
								api::set_cookie ( "key2" , $key2 , 7 );
								api::result ( l::t('Успешно авторизованы') , true );
								header ( "location:/" );
							}
						}
					}
				}
			}
			$title = l::t("Авторизация");
			tpl::load ( 'users-login' );
			tpl::set ( '{vk_id}' , $conf[ 'vk_id' ] );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '' , l::t('Авторизация') , '1' );
			}
		}
	}

	public static function login_check ( $data )
	{
		$login = api::cl ( $data[ 'email' ] );
		$pass = md5 ( $data[ 'password' ] );
		if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $login ) ) {
			return l::t("E-mail указан неверно");
		} else {
			if ( ! preg_match ( "/^[0-9a-zA-Z]{6,40}$/i" , $pass ) ) {
				return l::t("Пароль указан неверно");
			} else {
				db::q ( 'SELECT * FROM users where mail="' . $login . '" and pass="' . $pass . '"' );
				if ( db::n () != 1 ) {
					return l::t("E-mail или пароль указан неверно");
				} else {
					$row = db::r ();
					if ( $row[ 'signup' ] != '0' ) {
						return l::t('Подтвердите свой почтовый ящик');
					} else {
						return false;
					}
				}
			}
		}
	}

	public static function login2 ( $data )
	{
		global $title;
		if ( api::$go ) {
			api::result (l::t("Выйдите из аккаунта"));
		} else {
			$login = api::cl ( $data[ 'email' ] );
			$pass = md5 ( $data[ 'password' ] );
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( $error = self::login_check ( $data ) ) {
					api::result ( $error );
				} else {
					db::q ( 'SELECT * FROM users where mail="' . $login . '" and pass="' . $pass . '"' );
					$row = db::r ();
					$rtime = time ();
					api::set_cookie ( "key" , null , null );
					api::set_cookie ( "key2" , null , null );
					$ip = base64_encode ( $_SERVER[ 'REMOTE_ADDR' ] );
					$agent = base64_encode ( $_SERVER[ 'HTTP_USER_AGENT' ] );
					$key = md5 ( $login . $pass . $ip ) . md5 ( $agent );
					$key2 = md5 ( $key ) . md5 ( $ip );
					db::q ( 'DELETE from login_key where key1="' . $key . '"' );
					$sql12 = db::q ( 'SELECT id FROM login_key where user="' . $row[ 'id' ] . '" order by id desc LIMIT 4,1' );
					if ( db::n ( $sql12 ) != 0 ) {
						$row12 = db::r ( $sql12 );
						db::q ( 'DELETE from login_key where  user="' . $row[ 'id' ] . '" and id<="' . $row12[ 'id' ] . '"' );
					}
					db::q (
						"INSERT INTO login_key set
										user='" . $row[ 'id' ] . "',
										time='" . $rtime . "',
										ip='" . $ip . "',
										key1='" . $key . "',
										agent='" . $agent . "'"
					);
					api::set_cookie ( "key" , $key , 7 );
					api::set_cookie ( "key2" , $key2 , 7 );
					api::result ( l::t('Успешно авторизованы') , true );
					header ( "location:/" );
				}
			}
			$title = l::t("Авторизация");
			tpl::load ( 'users-login' );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '' , l::t('Авторизация') , '1' );
			}
		}
	}

	public static function recovery_end ( $key )
	{
		global $conf;
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( ! preg_match ( "/^[0-9]{4}$/i" , $key ) ) {
			api::result ( l::t("Ключ не найден") );
		} else {
			$sql = db::q ( 'SELECT * FROM users where recovery="' . api::cl ( $key ) . '"' );
			if ( db::n ( $sql ) == "1" ) {
				$row = db::r ( $sql );
				$pass = api::generate_password ( '12' );
				db::q ( "UPDATE users set pass='" . md5 ( $pass ) . "',recovery='0' where recovery='" . api::cl ( $key ) . "'" );
				api::inc ( 'mail' );
				tpl::load ( 'mail-body' );
				tpl::set ( '{title}' , $conf[ 'title' ] );
				$msg = "<h4>".l::t('Здравствуйте')." " . $row[ 'name' ] . " " . $row[ 'lastname' ] . ",</h4>";
				$msg .= "<p>".l::t("Ваш новый пароль:")."</p>";
				$msg .= '<div style="padding: 10px;background: #ECF8FF;border: 0;">';
				$msg .= $pass;
				$msg .= '</div>';
				$msg .= "<p>".l::t("Если вы не делали запроса для получения пароля, то просто удалите данное письмо.")."</p>";
				tpl::set ( '{content}' , $msg );
				tpl::compile ( 'mail' );
				mail::send ( $row[ 'mail' ] , l::t('Новый пароль') , tpl::result ( 'mail' ) );
				tpl::load ( 'users-recovery-end' );
				tpl::compile ( 'content' );
				api::nav ( '' , l::t('Восстановление пароля') , '1' );
			} else {
				api::result ( l::t('Ключ не найден') );
			}
		}
	}

	public static function recovery ( $data )
	{
		global $conf;
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( $data[ 'cphone' ] ) {
				$code = (int) $data[ 'code' ];
				if ( $code ) {
					db::q ( 'SELECT * FROM users where recovery="' . $code . '"' );
					if ( db::n () == "1" ) {
						$row = db::r ();
						if ( $row[ 'phone' ] ) {
							$pass = api::generate_password ( '6' );
							db::q ( "UPDATE users set pass='" . md5 ( $pass ) . "' where recovery='" . $code . "'" );
							api::inc ( 'mail' );
							tpl::load ( 'mail-body' );
							tpl::set ( '{title}' , $conf[ 'title' ] );
							$msg = "<h4>".l::t("Здравствуйте")." " . $row[ 'name' ] . " " . $row[ 'lastname' ] . ",</h4>";
							$msg .= "<p>".l::t("Ваш новый пароль:")."</p>";
							$msg .= '<div style="padding: 10px;background: #ECF8FF;border: 0;">';
							$msg .= $pass;
							$msg .= '</div>';
							$msg .= "<p>".l::t("Если вы не делали запроса для получения пароля, то просто удалите данное письмо.")."</p>";
							tpl::set ( '{content}' , $msg );
							tpl::compile ( 'mail' );
							mail::send ( $row[ 'mail' ] , l::t('Новый пароль') , tpl::result ( 'mail' ) );
							api::inc ( 'sms' );
							if ( sms::send ( $row[ 'phone' ] , l::t('пароль:').' ' . $pass ) ) {
								api::result ( l::t('Новый пароль выслан Вам на телефон') , true );
							}
						} else {
							api::result ( l::t('В Вашем аккаунте на указан номер телефона') );
						}
					} else {
						api::result ( l::t('Код введен неверно') );
					}
				} else {
					api::result ( l::t('Введите код подтверждения!') );
				}
			}
			$mail = api::cl ( $data[ 'mail' ] );
			if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $mail ) ) {
				api::result ( l::t("E-mail указан неверно") );
			} else {
				db::q ( 'SELECT * FROM users where mail="' . $mail . '"' );
				if ( db::n () != "1" ) {
					api::result ( l::t("E-mail указан неверно") );
				} else {
					$row = db::r ();
					if ( $row[ 'signup' ] != '0' ) {
						api::result ( l::t("Подтвердите свой аккаунт!") );
					} else {
						$key = mt_rand ( 1000 , 9999 );
						db::q ( "UPDATE users set recovery='" . $key . "' where id='" . $row[ 'id' ] . "'" );
						if ( $row[ 'phone' ] ) {
							if ( $conf[ 'sms_recovery' ] == 1 ) {
								$pass = $key;
								api::inc ( 'mail' );
								tpl::load ( 'mail-body' );
								tpl::set ( '{title}' , $conf[ 'title' ] );
								$msg = "<h4>".l::t("Здравствуйте")." " . $row[ 'name' ] . " " . $row[ 'lastname' ] . ",</h4>";
								$msg .= "<p>".l::t("Вы сделали запрос на получение забытого пароля.")."<br/>";
								$msg .= l::t("Однако в целях безопасности все пароли хранятся в зашифрованном виде, поэтому мы не можем сообщить вам ваш старый пароль, поэтому если вы хотите сгенерировать новый пароль, зайдите по следующей ссылке: ")."</p>";
								$msg .= '<div style="padding: 10px;background: #ECF8FF;border: 0;">';
								$msg .= '<a style="color: #2ba6cb;" href="http://' . $conf[ 'domain' ] . '/users/recovery/' . $pass . '">http://' . $conf[ 'domain' ] . '/users/recovery/' . $pass . '</a>';
								$msg .= '</div>';
								$msg .= "<p>".l::t("Если вы не делали запроса для получения пароля, то просто удалите данное письмо.")."</p>";
								tpl::set ( '{content}' , $msg );
								tpl::compile ( 'mail' );
								mail::send ( $mail , l::t('Восстановление пароля') , tpl::result ( 'mail' ) );
								api::inc ( 'sms' );
								if ( $d = sms::send ( $row[ 'phone' ] , 'код: ' . $key ) ) {
									api::result ( '1' , true );

									return false;
								}
							}
						}
						$pass = $key;
						api::inc ( 'mail' );
						tpl::load ( 'mail-body' );
						tpl::set ( '{title}' , l::t('Восстановление пароля') );
						$msg = "<h4>".l::t("Здравствуйте")." " . $row[ 'name' ] . " " . $row[ 'lastname' ] . ",</h4>";
						$msg .= "<p>";
						$msg .= l::t("Вы сделали запрос на получение забытого пароля.");
						$msg .= "<br/>";
						$msg .= l::t("Однако в целях безопасности все пароли хранятся в зашифрованном виде, поэтому мы не можем сообщить вам ваш старый пароль, поэтому если вы хотите сгенерировать новый пароль, зайдите по следующей ссылке:");
 						$msg .= "</p>";
						$msg .= '<div style="padding: 10px;background: #ECF8FF;border: 0;">';
						$msg .= '<a style="color: #2ba6cb;" href="http://' . $conf[ 'domain' ] . '/users/recovery/' . $pass . '">http://' . $conf[ 'domain' ] . '/users/recovery/' . $pass . '</a>';
						$msg .= '</div>';
						$msg .= "<p>";
						$msg .= l::t("Если вы не делали запроса для получения пароля, то просто удалите данное письмо.");
						$msg .= "</p>";
						tpl::set ( '{content}' , $msg );
						tpl::compile ( 'mail' );
						mail::send ( $mail , l::t('Восстановление пароля') , tpl::result ( 'mail' ) );
						api::result ( l::t("Инструкции выславы на указанный e-mail") , true );
					}
				}
			}
		} else {
			tpl::load ( 'users-recovery' );
			tpl::set ( '{mail}' , api::cl ( $data[ 'mail' ] ) );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '' , l::t('Восстановление пароля') , '1' );
			}
		}
	}

	public static function signup_end ( $key )
	{
		global $conf;
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( ! preg_match ( "/^[0-9]{4}$/i" , $key ) ) {
			api::result ( l::t("Ключ не найден") );
		} else {
			db::q ( 'SELECT * FROM users where signup="' . api::cl ( $key ) . '"' );
			if ( db::n () == "1" ) {
				db::q ( "UPDATE users set signup='0' where signup='" . api::cl ( $key ) . "'" );
				api::result ( l::t('Аккаунт подтвержден') , true );
			} else {
				api::result ( l::t('Ключ не найден') );
			}
		}
	}


	public static function signup ( $data )
	{
		global $title , $conf;
		if ( api::$go ) {
			api::result ( l::t("Выйдите из аккаунта") );
		} else {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( $data[ 'cphone' ] ) {
				$code = (int) $data[ 'code' ];
				if ( $code ) {
					db::q ( 'SELECT * FROM users where signup="' . $code . '"' );
					if ( db::n () == "1" ) {
						db::q ( "UPDATE users set signup='0' where signup='" . $code . "'" );

						api::result ( l::t('Аккаунт подтвержден') , true );
					} else {
						api::result ( l::t('Код введен неверно') );
					}
				} else {
					api::result ( l::t('Введите код подтверждения!') );
				}
			}
			$mail = api::cl ( $data[ 'mail' ] );
			$pass = api::cl ( $data[ 'password' ] );
			$pass2 = api::cl ( $data[ 'password2' ] );
			$name = api::cl ( $data[ 'name' ] );
			$phone = api::cl ( $data[ 'phone' ] );
			$lastname = api::cl ( $data[ 'lastname' ] );
			if ( $data ) {
				if ( $conf[ 'tpl' ] == 1 ) {
					if ( ! api::captcha_chek () ) {
						return false;
					}
				}
				if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $mail ) ) {
					api::result ( l::t("E-mail указан неверно") );
				} else {

					db::q ( 'SELECT id FROM users where mail="' . $mail . '"' );
					if ( db::n () == "1" ) {
						api::result ( l::t("E-mail занят") );
					} else {
						if ( $pass != $pass2 ) {
							api::result ( l::t("Пароли не совпадают") );
						} else {
							if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $pass ) ) {
								api::result ( l::t("Пароль указан неверно") );
							} else {
								if ( ! preg_match ( "/^[0-9a-zA-Zа-яА-ЯЁё]{4,20}$/iu" , $name ) ) {
									api::result ( l::t("Укажите Ваше Имя") );
								} else {
									if ( $conf[ 'sphone' ] ) {
										if ( ! preg_match ( "/^[0-9]{11,13}$/i" , $phone ) ) {
											api::result ( l::t("Телефон указан неверно") );

											return false;
										}
									}
									if ( ! preg_match ( "/^[0-9a-zA-Zа-яА-ЯЁё]{4,20}$/iu" , $lastname ) ) {
										api::result ( l::t("Укажите Вашу Фамилию") );
									} else {
										if ( $conf[ 'signup' ] == 1 ) {
											$key = mt_rand ( 1000 , 9999 );
										} else {
											$key = 0;
										}
										$invite = (int) ( @$_COOKIE[ 'invite' ] );
										if ( $invite != 0 ) {
											db::q ( 'SELECT id FROM users where id="' . $invite . '"' );
											if ( db::n () == 0 ) {
												$invite = 0;
											}
										}
										db::q (
											"INSERT INTO users set
															mail='" . $mail . "',
															pass='" . md5 ( $pass ) . "',
															ugroup='3',
															balance='0',
															name='" . $name . "',
															lastname='" . $lastname . "',
															signup='" . $key . "',
															invite='" . $invite . "',
															phone='" . $phone . "',
															time='" . time () . "'"
										);
										if ( $conf[ 'signup' ] == 1 ) {
											if ( $conf[ 'sms_signup' ] == 1 ) {
												api::inc ( 'sms' );
												if ( $d = sms::send ( $phone , 'код: ' . $key ) ) {
													api::result ( '1' , true );

													return false;
												}
											}
											api::inc ( 'mail' );
											tpl::load ( 'mail-body' );
											tpl::set ( '{title}' , $conf[ 'title' ] );
											$msg = "<h4>".l::t("Здравствуйте")." " . $name . " " . $lastname . ",</h4>";
											$msg .= "<p>";
											$msg .= l::t("Вы в одном шаге от завершения регистрации на");
											$msg .= " <b>" . $conf[ 'domain' ] . "</b></p>";
											$msg .= "<p>";
											$msg .= l::t("Если Вы действительно желаете зарегистрироваться, пожалуйста, подтвердите свое намерение.");
											$msg .= "</p>";
											$msg .= "<p>";
											$msg .= l::t("Подтверждение требуется для исключения несанкционированного использования Вашего e-mail адреса. Для потверждения достаточно перейти по ссылке, дополнительных писем отправлять не требуется.");
											$msg .= "</p>";
											$msg .= '<div style="padding: 10px;background: #ECF8FF;border: 0;">';
											$msg .= '<a style="color: #2ba6cb;" href="http://' . $conf[ 'domain' ] . '/users/signup/' . $key . '">http://' . $conf[ 'domain' ] . '/users/signup/' . $key . '</a>';
											$msg .= '</div>';
											$msg .= "<p>";
											$msg .= l::t("Если указанная выше ссылка не открывается, скопируйте ее в буфер обмена, вставьте в адресную строку браузера и нажмите ввод.");
											$msg .= "</p>";
											$msg .= "<p>";
											$msg .= l::t("Если Вы считаете, что данное сообщение послано Вам ошибочно, просто проигнорируйте его и все данные будут автоматически удалены.");
											$msg .= "</p>";
											tpl::set ( '{content}' , $msg );
											tpl::compile ( 'mail' );
											mail::send ( $mail , l::t('Подтверждение e-mail адреса') , tpl::result ( 'mail' ) );
										}
										api::result ( l::t('Вы успешно зарегистрированы') , true );
									}
								}
							}
						}
					}
				}
			}
			$title = l::t("Регистрация");
			tpl::load ( 'users-signup' );
			api::captcha_create ();
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '' , l::t('Регистрация') , '1' );
			}
		}
	}
}

?>