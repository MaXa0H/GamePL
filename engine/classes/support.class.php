<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class faq
{
	public static function base ()
	{
		global $title;
		api::nav ( '' , 'FAQ' , '1' );
		$sql = db::q ( 'SELECT * FROM faq order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load ( 'users-faq-get' );
			tpl::set ( '{f}' , base64_decode ( $row[ 'f' ] ) );
			tpl::set ( '{q}' , str_replace ( "\n" , "<br/>" , base64_decode ( $row[ 'q' ] ) ) );
			tpl::compile ( 'faq' );
		};
		tpl::load ( 'users-faq' );
		tpl::set ( '{data}' , tpl::result ( 'faq' ) );
		tpl::compile ( 'content' );
		$title = l::t("Ответы на вопросы");
	}
}

class support
{
	public static function feedback ()
	{
		global $conf;
		$data = $_POST[ 'data' ];
		if($data){
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( ! $data[ 'sub' ] ) {
				api::result ( l::t('Укажите тему') );
			} else {
				if ( ! preg_match ( "/^.{2,3000}$/si" , $data[ 'mes' ] ) ) {
					if ( ! $data[ 'msg' ] ) {
						api::result ( l::t('Введите сообщение') );
					} else {
						if ( mb_strlen ( $data[ 'msg' ] , "utf-8" ) < 10 ) {
							api::result ( l::t('Сообщение слишком короткое') );
						} else {
							if ( mb_strlen ( $data[ 'msg' ] , "utf-8" ) > 3000 ) {
								api::result ( l::t('Сообщение слишком длинное') );
							} else {
								api::result ( l::t('Сообщение содержит недопустимые символы') );
							}
						}
					}
				} else {
					if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $data[ 'mail' ] ) ) {
						api::result ( l::t("E-mail указан неверно") );
					} else {
						if ( ! preg_match ( "/^[0-9a-zA-Zа-яА-ЯЁё]{4,20}$/iu" , $data[ 'name' ] ) ) {
							api::result ( l::t("Укажите Ваше Имя") );
						} else {
							$mail = $conf[ 'mail' ];
							$conf[ 'mail' ] = $data[ 'mail' ];
							api::inc ( 'mail' );
							mail::send ($mail , $data[ 'sub' ] , api::cl ( $data[ 'mes' ] ) );
							api::result ( l::t('Отправлено') , true );
						}
					}
				}
			}
		}
		api::nav ( '' , l::t('Обратная связь') , '1' );
		tpl::load ( 'users-feedback' );
		tpl::compile ( 'content' );
	}
	public static function feedback_end(){
		api::nav ( '' , l::t('Обратная связь') , '1' );
		tpl::load ( 'users-feedback-end' );
		tpl::compile ( 'content' );
	}
	public static function ajax ()
	{
		if ( api::admin () ) {
			$sql = db::q ( 'SELECT time FROM support where status="s" order by id desc LIMIT 0,1' );
		} else {
			$sql = db::q ( 'SELECT time FROM support where status="' . api::info ( 'id' ) . '" and user="' . api::info ( 'id' ) . '" order by id desc LIMIT 0,1' );
		}
		if ( db::n () != 0 ) {
			api::result ( '1' , true );
		} else {
			api::result ( '1' );
		}
	}

	public static function ticket ( $id )
	{
		global $title , $conf;
		if ( api::admin ( 'support' ) ) {
			$sql = db::q ( 'SELECT * FROM support where id="' . $id . '"' );
		} else {
			$sql = db::q ( 'SELECT * FROM support where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		$title = l::t("Центр поддержки");
		if ( db::n () == 1 ) {
			$row = db::r ();
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if($row['locked']==1){
					api::result ( l::t('Обращение закрыто') );
				}else {
					if ( ! preg_match ( "/^.{2,3000}$/si" , $data[ 'text' ] ) ) {
						if ( ! $data[ 'text' ] ) {
							api::result ( l::t('Введите сообщение') );
						} else {
							if ( mb_strlen ( $data[ 'text' ] , "utf-8" ) < 10 ) {
								api::result ( l::t('Сообщение слишком короткое') );
							} else {
								if ( mb_strlen ( $data[ 'text' ] , "utf-8" ) > 3000 ) {
									api::result ( l::t('Сообщение слишком длинное') );
								} else {
									api::result ( l::t('Сообщение содержит недопустимые символы') );
								}
							}
						}
					} else {
						db::q (
							"INSERT INTO support_mes set
							user='" . api::info ( 'id' ) . "',
							time='" . time () . "',
							mes='" . base64_encode ( api::cl ( $data[ 'text' ],1 ) ) . "',
							tid='" . $id . "'
						"
						);
						if ( $row[ 'user' ] == api::info ( 'id' ) ) {
							db::q ( "UPDATE support set status='s', time='" . time () . "' where id='" . $id . "'" );
						} else {
							$sql233u = db::q ( 'SELECT * FROM users where id="' . $row[ 'user' ] . '"' );
							$user = db::r ( $sql233u );
							$msg = $conf[ 'domain' ] ." ". l::t("Новое сообщение в центре поддержки.");
							$pm = false;
							if ( $user[ 'phone' ] && $conf[ 'sms_support' ] ) {
								api::inc ( 'sms' );
								if ( sms::send ( $user[ 'phone' ], $msg ) ) {
									$pm = true;
								}
							}
							if ( $pm == false ) {
								api::inc ( 'mail' );
								mail::send ( $user[ 'mail' ] , l::t('Ответ в центре поддержки') , $msg );
							}
							db::q ( "UPDATE support set status='" . $row[ 'user' ] . "', time='" . time () . "' where id='" . $id . "'" );
						}
						api::result ( l::t('Отправлено') , true );
					}
				}
			}
			if ( api::admin ( 'support' ) ) {
				if ( $row[ 'status' ] == "s" ) {
					db::q ( "UPDATE support set status='0' where id='" . $id . "'" );
				}
			} else {
				if ( $row[ 'status' ] == api::info ( 'id' ) ) {
					db::q ( "UPDATE support set status='0' where id='" . $id . "'" );
				}
			}
			$news = '10';
			db::q ( 'SELECT id FROM support_mes where tid="' . $id . '" order by id desc' );
			$all = db::n ();
			$_GET[ 'page' ] = (int) r::g ( 4 );
			if ( (int) $_GET[ 'page' ] ) {
				if ( ( $all / $news ) > (int) $_GET[ 'page' ] ) {
					$page = $news * (int) $_GET[ 'page' ];
				} else {
					$page = 0;
				}
			} else {
				$page = 0;
			}
			$sql = db::q ( 'SELECT * FROM support_mes where tid="' . $id . '" order by id desc LIMIT ' . $page . ',' . $news );
			while ( $row2 = db::r ( $sql ) ) {
				tpl::load ( 'users-support-ticket-get' );
				db::q ( 'SELECT name,lastname,photo,id FROM users where id="' . $row2[ 'user' ] . '"' );
				$row4 = db::r ();
				$u1 = $row4[ 'name' ] . ' ' . $row4[ 'lastname' ];
				$u2 = api::info ( 'name' ) . ' ' . api::info ( 'lastname' );
				if ( api::admin ( 'support' ) ) {
					if ( $row2[ 'user' ] == api::info ( 'id' ) ) {
						tpl::set ( '{name}' , $u2 );
					} else {
						tpl::set ( '{name}' , $u1 );
					}
				} else {
					if ( $row2[ 'user' ] == api::info ( 'id' ) ) {
						tpl::set ( '{name}' , $u2 );
					} else {
						tpl::set ( '{name}' , $u1 );
					}
				}
				if($row4['photo']){
					tpl::set('{photo}','/files/photo/'.$row4['id'].'.png');
				}else{
					tpl::set('{photo}','/img/noavatar.png');
				}
				tpl::set ( '{user}' , $row2[ 'user' ] );
				tpl::set ( '{mes}' , str_replace ( '\n' , '<br />' , str_replace ( "\n" , '<br />' , base64_decode ( $row2[ 'mes' ] ) ) ) );
				tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $row2[ 'time' ] ) );
				tpl::compile ( 'data' );
			}
			api::nav ( "/support" , l::t("Центр поддержки") );
			api::nav ( "" , l::t("Тикет #") . $id . ' - ' . base64_decode ( $row[ 'name' ] ) , 1 );
			tpl::load ( 'users-support-ticket' );
			tpl::set ( '{id}' , $id );
			tpl::set('{name}',base64_decode ( $row[ 'name' ] ));
			if(api::info('photo')){
				tpl::set('{photo}','/files/photo/'.api::info('id').'.png');
			}else{
				tpl::set('{photo}','/img/noavatar.png');
			}
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::set ( '{nav}' , api::pagination ( $all , $news , (int) $_GET[ 'page' ] , '/support/ticket/' . $id ) );
			if($row['locked']==1){
				tpl::set_block ( "'\\[form\\](.*?)\\[/form\\]'si" , "" );
				tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "\\1" );
				tpl::set ( '{error}' , l::t('Обращение закрыто') );
			}else{
				tpl::set_block ( "'\\[form\\](.*?)\\[/form\\]'si" , "\\1" );
				tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "" );
			}
			tpl::compile ( 'content' );
		} else {
			api::result ( l::t('Тикет не найден') );
			api::nav ( "" , l::t("Центр поддержки") , '1' );
		}
	}
	public static function listen_locked ()
	{
		global $title;
		$pages = (int) r::g ( 3 );
		if ( api::admin ( 'support' ) ) {
			db::q ( 'SELECT id FROM support where locked="1" order by id desc' );
		} else {
			db::q ( 'SELECT id FROM support where locked="1" and user="' . api::info ( 'id' ) . '" order by id desc' );
		}
		$all = db::n ();
		$num = 10;
		if ( $pages ) {
			if ( ( $all / $num ) > $pages ) {
				$page = $num * $pages;
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		if ( api::admin ( 'support' ) ) {
			$sql = db::q ( 'SELECT * FROM support where locked="1" order by time desc LIMIT ' . $page . ' ,' . $num );
		} else {
			$sql = db::q ( 'SELECT * FROM support where locked="1" and user="' . api::info ( 'id' ) . '" order by time desc LIMIT ' . $page . ' ,' . $num );
		}
		while ( $row = db::r ( $sql ) ) {
			tpl::load ( 'users-support-locked-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			if ( api::admin ( 'support' ) ) {
				if ( $row[ 'status' ] == 's' ) {
					$stats = '<i class="glyphicon glyphicon-envelope"></i>';
				} else {
					$stats = '';
				}
			} else {
				if ( $row[ 'status' ] == $row[ 'user' ] ) {
					$stats = '<i class="glyphicon glyphicon-envelope"></i>';
				} else {
					$stats = '';
				}
			}
			db::q ( 'SELECT * FROM support_mes where tid="' . $row[ 'id' ] . '" order by id desc' );
			$row3 = db::r ();
			tpl::set ( '{name}' , base64_decode ( $row[ 'name' ] ) );
			tpl::set ( '{user}' , $row[ 'user' ] );
			db::q ( 'SELECT name,lastname,photo,id FROM users where id="' . $row[ 'user' ] . '"' );
			$row4 = db::r ();
			tpl::set ( '{login}' , $row4[ 'name' ] . ' ' . $row4[ 'lastname' ] );
			tpl::set ( '{stats}' , $stats );
			tpl::set ( '{date}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
			if($row4['photo']){
				tpl::set('{photo}','/files/photo/'.$row4['id'].'.png');
			}else{
				tpl::set('{photo}','/img/noavatar.png');
			}
			tpl::compile ( 'tickets' );
		}
		tpl::load ( 'users-support-locked-listen' );
		if ( tpl::result ( 'tickets' ) ) {
			tpl::set ( '{tickets}' , tpl::result ( 'tickets' ) );
			tpl::set_block ( "'\\[tickets\\](.*?)\\[/tickets\\]'si" , "\\1" );
			tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "" );
		} else {
			tpl::set_block ( "'\\[tickets\\](.*?)\\[/tickets\\]'si" , "" );
			tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "\\1" );
			tpl::set ( '{error}' , l::t('Нет закрытых обращений') );
		}
		tpl::set ( '{nav}' , api::pagination ( $all , $num , $pages , '/support/locked' ) );
		tpl::compile ( 'content' );
		api::nav ( "/support" , l::t("Центр поддержки") );
		api::nav ( "" , l::t("Закрытые обращения") , '1' );

		$title = l::t("Центр поддержки");
	}
	public static function listen ()
	{
		global $title;
		if ( api::admin ( 'support' ) ) {
			db::q ( 'SELECT id FROM support where locked="0" order by id desc' );
		} else {
			db::q ( 'SELECT id FROM support where locked="0" and user="' . api::info ( 'id' ) . '" order by id desc' );
		}
		$all = db::n ();
		$num = 10;
		$pages = (int) r::g ( 2 );
		if ( $pages ) {
			if ( ( $all / $num ) > $pages ) {
				$page = $num * $pages;
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		if ( api::admin ( 'support' ) ) {
			$sql = db::q ( 'SELECT * FROM support where locked="0" order by time asc LIMIT ' . $page . ' ,' . $num );
		} else {
			$sql = db::q ( 'SELECT * FROM support where locked="0" and user="' . api::info ( 'id' ) . '" order by time asc LIMIT ' . $page . ' ,' . $num );
		}
		while ( $row = db::r ( $sql ) ) {
			tpl::load ( 'users-support-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			if ( api::admin ( 'support' ) ) {
				if ( $row[ 'status' ] == 's' ) {
					$stats = '<i class="glyphicon glyphicon-envelope"></i>';
				} else {
					$stats = '';
				}
			} else {
				if ( $row[ 'status' ] == $row[ 'user' ] ) {
					$stats = '<i class="glyphicon glyphicon-envelope"></i>';
				} else {
					$stats = '';
				}
			}
			db::q ( 'SELECT * FROM support_mes where tid="' . $row[ 'id' ] . '" order by id desc' );
			$row3 = db::r ();
			tpl::set ( '{name}' , base64_decode ( $row[ 'name' ] ) );
			tpl::set ( '{user}' , $row[ 'user' ] );
			db::q ( 'SELECT name,lastname FROM users where id="' . $row[ 'user' ] . '"' );
			$row4 = db::r ();
			tpl::set ( '{login}' , $row4[ 'name' ] . ' ' . $row4[ 'lastname' ] );
			tpl::set ( '{stats}' , $stats );
			tpl::set ( '{date}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
			tpl::set_block ( "'\\[warning\\](.*?)\\[/warning\\]'si" , "" );
			tpl::set_block ( "'\\[link\\](.*?)\\[/link\\]'si" , "" );
			if($row['service']){
				if ( preg_match ( "/^s[0-9]{1,10}$/i" , $row['service'] ) ) {
					$sql1 = db::q ( 'SELECT support FROM gh_servers where id="' . str_replace('s','',$row['service']) . '"' );
					if(db::n($sql1)==1){
						tpl::set_block ( "'\\[link\\](.*?)\\[/link\\]'si" , "\\1" );
						$link="/servers/base/".str_replace('s','',$row['service']);
						tpl::set ( '{link}' , $link );
						$row4 = db::r ($sql1);
						if($row4['support']>time()){
							tpl::set_block ( "'\\[warning\\](.*?)\\[/warning\\]'si" , "\\1" );
						}
					}

				}
				if ( preg_match ( "/^w[0-9]{1,10}$/i" , $row['service'] ) ) {
					if(db::n($sql1)==1){
						tpl::set_block ( "'\\[link\\](.*?)\\[/link\\]'si" , "\\1" );
						$link="/web/base/".str_replace('w','',$row['service']);
						tpl::set ( '{link}' , $link );
					}
				}
				
			}
			tpl::compile ( 'tickets' );
		}
		tpl::load ( 'users-support-listen' );
		if ( tpl::result ( 'tickets' ) ) {
			tpl::set ( '{tickets}' , tpl::result ( 'tickets' ) );
			tpl::set_block ( "'\\[tickets\\](.*?)\\[/tickets\\]'si" , "\\1" );
			tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "" );
		} else {
			tpl::set_block ( "'\\[tickets\\](.*?)\\[/tickets\\]'si" , "" );
			tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "\\1" );
			tpl::set ( '{error}' , l::t('Нет открытых обращений') );
		}
		tpl::set ( '{nav}' , api::pagination ( $all , $num , $pages , '/support' ) );
		tpl::compile ( 'content' );
		api::nav ( "/support" , l::t("Центр поддержки") );
		api::nav ( "" , l::t("Открытые обращения") , '1' );
		$title = l::t("Центр поддержки");
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
			if ( ! preg_match ( "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ^\.,!? \:\-\_]{2,100}$/i" , $data[ 'title' ] ) ) {
				if ( ! $data[ 'title' ] ) {
					api::result ( l::t('Укажите тему') );
				} else {
					if ( mb_strlen ( $data[ 'title' ] , "utf-8" ) < 2 ) {
						api::result ( l::t('Тема слишком короткая') );
					} else {
						if ( mb_strlen ( $data[ 'title' ] , "utf-8" ) > 100 ) {
							api::result ( l::t('Тема слишком длинная') );
						} else {
							api::result ( l::t('Тема содержит недопустимые символы') );
						}
					}
				}
			} else {
				if ( ! preg_match ( "/^.{2,3000}$/si" , $data[ 'text' ] ) ) {
					if ( ! $data[ 'text' ] ) {
						api::result ( l::t('Введите сообщение' ));
					} else {
						if ( mb_strlen ( $data[ 'text' ] , "utf-8" ) < 10 ) {
							api::result (l::t( 'Сообщение слишком короткое') );
						} else {
							if ( mb_strlen ( $data[ 'text' ] , "utf-8" ) > 3000 ) {
								api::result (l::t( 'Сообщение слишком длинное') );
							} else {
								api::result ( l::t('Сообщение содержит недопустимые символы') );
							}
						}
					}
				} else {
					$service2 = 0;
					$service = api::cl($data['service']);
					if ( preg_match ( "/^s[0-9]{1,10}$/i" , $service ) ) {
						$id = str_replace('s','',$service);
						db::q ( 'SELECT id FROM gh_servers where user="' . api::info ( 'id' ) . '" and id="' . $id . '"' );
						if(db::n()==1){
							$service2=$service;
						}
					}
					if ( preg_match ( "/^w[0-9]{1,10}$/i" , $service ) ) {
						$id = str_replace('w','',$service);
						db::q ( 'SELECT id FROM isp where user="' . api::info ( 'id' ) . '" and id="' . $id . '"' );
						if(db::n()==1){
							$service2=$service;
						}
					}
					db::q (
						"INSERT INTO support set
							user='" . api::info ( 'id' ) . "',
							status='s',
							service='".$service2."',
							time='" . time () . "',
							name='" . base64_encode ( api::cl ( $data[ 'title' ],1 ) ) . "'"
					);
					$id = db::i ();
					db::q (
						"INSERT INTO support_mes set
							user='" . api::info ( 'id' ) . "',
							time='" . time () . "',
							mes='" . base64_encode ( api::cl ( $data[ 'text' ],1 ) ) . "',
							tid='" . $id . "'
						"
					);
					api::result ( l::t('Отправлено') , true );
				}
			}
		}
		tpl::load ( 'users-support-add' );
		$service = null;
		$sql = db::q ( 'SELECT id,ip,port FROM gh_servers where user="' . api::info('id'). '" order by id desc');
		while ( $row = db::r ( $sql ) ) {
			$service .= '<option value="s'.$row['id'].'">Сервер '.$row['ip'].':'.$row['port'].'</option>';
		}
		$sql = db::q ( 'SELECT id,sid FROM isp where user="' . api::info('id'). '" order by id desc');
		while ( $row = db::r ( $sql ) ) {
			$service .= '<option value="w'.$row['id'].'">Web: s'.$row['sid'].'</option>';
		}
		tpl::set('{service}',$service);
		if(api::info('photo')){
			tpl::set('{photo}','/files/photo/'.api::info('id').'.png');
		}else{
			tpl::set('{photo}','/img/noavatar.png');
		}
		tpl::compile ( 'content' );
		api::nav ( "/support" , l::t("Центр поддержки") );
		api::nav ( "" , l::t("Новое обращение") , '1' );
		$title = l::t( "Центр поддержки");
	}

	public static function locked ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( api::admin ( 'support' ) ) {
			db::q ( 'SELECT * FROM support where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM support where user="' . api::info ( 'id' ) . '" and id="' . $id . '"' );
		}
		if ( db::n () == 1 ) {
			db::q ( "UPDATE support set locked='1',status='0' where id='" . $id . "'" );
			api::result ( l::t('Закрыто') , true );
		} else {
			api::result ( l::t('Нет прав') );
		}
	}




	public static function ajax_mobile ()
	{
		$time = (int) $_POST[ 'data' ][ 'support' ];
		if ( api::admin () ) {
			$sql = db::q ( 'SELECT time FROM support where status="s" and time>"' . $time . '" order by id desc LIMIT 0,1' );
		} else {
			$sql = db::q ( 'SELECT time FROM support where status="' . api::info ( 'id' ) . '" and user="' . api::info ( 'id' ) . '"  and time>"' . $time . '" order by id desc LIMIT 0,1' );
		}
		if ( db::n () != 0 ) {
			$row = db::r ();
			mobile::$data[ 'time' ] = $row[ 'time' ];
		} else {
			mobile::$data[ 'time' ] = '0';
		}
		$time = (int) $_POST[ 'data' ][ 'news' ];
		$sql = db::q ( 'SELECT time,name FROM news where time>"' . $time . '" order by id desc LIMIT 0,1' );
		if ( db::n () != 0 ) {
			$row = db::r ();
			mobile::$data[ 'news' ] = $row[ 'time' ];
			mobile::$data[ 'title' ] = $row[ 'name' ];
		} else {
			mobile::$data[ 'news' ] = '0';
		}
	}

	public static function ticket_mobile ( $id , $pages )
	{
		if ( api::admin ( 'support' ) ) {
			$sql = db::q ( 'SELECT * FROM support where id="' . $id . '"' );
		} else {
			$sql = db::q ( 'SELECT * FROM support where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			$data = array ();
			if ( api::admin ( 'support' ) ) {
				if ( $row[ 'status' ] == "s" ) {
					db::q ( "UPDATE support set status='0' where id='" . $id . "'" );
				}
			} else {
				if ( $row[ 'status' ] == api::info ( 'id' ) ) {
					db::q ( "UPDATE support set status='0' where id='" . $id . "'" );
				}
			}
			$news = '10';
			db::q ( 'SELECT id FROM support_mes where tid="' . $id . '" order by id desc' );
			$all = db::n ();
			if ( $pages ) {
				if ( ( $all / $news ) > $pages ) {
					$page = $news * $pages;
				} else {
					$page = 0;
				}
			} else {
				$page = 0;
			}
			$sql = db::q ( 'SELECT * FROM support_mes where tid="' . $id . '" order by id desc LIMIT ' . $page . ',' . $news );
			while ( $row2 = db::r ( $sql ) ) {
				$data2 = array ();
				db::q ( 'SELECT name,lastname FROM users where id="' . $row2[ 'user' ] . '"' );
				$row4 = db::r ();
				$u1 = $row4[ 'name' ] . ' ' . $row4[ 'lastname' ];
				$u2 = api::info ( 'name' ) . ' ' . api::info ( 'lastname' );
				if ( api::admin ( 'support' ) ) {
					if ( $row2[ 'user' ] == api::info ( 'id' ) ) {
						$name = $u2;
					} else {
						$name = $u1;
					}
				} else {
					if ( $row2[ 'user' ] == api::info ( 'id' ) ) {
						$name = $u2;
					} else {
						$name = $u1;
					}
				}
				$data2[ 'user' ] = $name;
				$data2[ 'mes' ] = base64_encode ( str_replace ( '\n' , '<br />' , str_replace ( "\n" , '<br />' , base64_decode ( $row2[ 'mes' ] ) ) ) );
				$data2[ 'date' ] = api::langdate ( "j F Y - H:i" , $row2[ 'time' ] );
				$data[ ] = $data2;
			}
			mobile::$data[ 'name' ] = $row[ 'name' ];
			mobile::$data[ 'id2' ] = $id;
			mobile::$data[ 'pag' ] = base64_encode ( api::pagination2 ( $all , $news , $pages , 'support/ticket/' . $id ) );
			if ( $row[ 'locked' ] == 1 ) {
				mobile::$data[ 'lock' ] = 1;
			} else {
				mobile::$data[ 'lock' ] = 0;
			}
			mobile::$data[ 'data' ] = $data;
		} else {
			mobile::error ( 'Тикет не найден' );
		}
	}

	public static function listen_mobile ( $pages , $st )
	{
		global $title;
		if ( api::admin ( 'support' ) ) {
			db::q ( 'SELECT id FROM support where locked="' . $st . '" order by id desc' );
		} else {
			db::q ( 'SELECT id FROM support where locked="' . $st . '" and user="' . api::info ( 'id' ) . '" order by id desc' );
		}
		$all = db::n ();
		$num = 10;
		if ( $pages ) {
			if ( ( $all / $num ) > $pages ) {
				$page = $num * $pages;
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		if ( $st == 0 ) {
			$f = "asc";
		} else {
			$f = "desc";
		}

		if ( api::admin ( 'support' ) ) {
			$sql = db::q ( 'SELECT * FROM support where locked="' . $st . '" order by time ' . $f . ' LIMIT ' . $page . ' ,' . $num );
		} else {
			$sql = db::q ( 'SELECT * FROM support where locked="' . $st . '" and user="' . api::info ( 'id' ) . '" order by time ' . $f . ' LIMIT ' . $page . ' ,' . $num );
		}
		$servers = array ();
		while ( $row = db::r ( $sql ) ) {
			$server = array ();
			$server[ 'id' ] = $row[ 'id' ];
			if ( api::admin ( 'support' ) ) {
				if ( $row[ 'status' ] == 's' ) {
					$stats = '1';
				} else {
					$stats = '0';
				}
			} else {
				if ( $row[ 'status' ] == $row[ 'user' ] ) {
					$stats = '1';
				} else {
					$stats = '0';
				}
			}
			$server[ 'status' ] = $stats;
			db::q ( 'SELECT * FROM support_mes where tid="' . $row[ 'id' ] . '" order by id desc' );
			$row3 = db::r ();
			$server[ 'name' ] = $row[ 'name' ];
			$server[ 'user' ] = $row[ 'user' ];
			db::q ( 'SELECT name,lastname FROM users where id="' . $row[ 'user' ] . '"' );
			$row4 = db::r ();
			$server[ 'login' ] = $row4[ 'name' ] . ' ' . $row4[ 'lastname' ];
			$server[ 'date' ] = api::langdate ( "j F Y - H:i" , $row[ 'time' ] );
			$servers[ ] = $server;
		}
		if ( $st == 0 ) {
			$p = "support";
		} else {
			$p = "support/locked";
		}
		mobile::$data[ 'pag' ] = base64_encode ( api::pagination2 ( $all , $num , $pages , $p ) );
		mobile::$data[ 'servers' ] = $servers;
	}

	public static function mes_mobile ( $id )
	{
		global $title , $conf;
		if ( api::admin ( 'support' ) ) {
			$sql = db::q ( 'SELECT * FROM support where id="' . $id . '"' );
		} else {
			$sql = db::q ( 'SELECT * FROM support where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			$data = $_POST[ 'data' ];
			if ( $row[ 'locked' ] == 1 ) {
				mobile::error (l::t( 'Обращение закрыто') );
			} else {
				if ( ! preg_match ( "/^.{2,3000}$/si" , $data[ 'text' ] ) ) {
					if ( ! $data[ 'text' ] ) {
						mobile::error ( l::t('Введите сообщение') );
					} else {
						if ( mb_strlen ( $data[ 'text' ] , "utf-8" ) < 10 ) {
							mobile::error (l::t( 'Сообщение слишком короткое') );
						} else {
							if ( mb_strlen ( $data[ 'text' ] , "utf-8" ) > 3000 ) {
								mobile::error ( l::t('Сообщение слишком длинное') );
							} else {
								mobile::error ( l::t('Сообщение содержит недопустимые символы') );
							}
						}
					}
				} else {
					if ( ! api::admin () ) {
						$msg = base64_encode ( api::cl ( $data[ 'text' ] ) );
					} else {
						$msg = base64_encode ( $data[ 'text' ] );
					}
					db::q (
						"INSERT INTO support_mes set
							user='" . api::info ( 'id' ) . "',
							time='" . time () . "',
							mes='" . $msg . "',
							tid='" . $id . "'
						"
					);
					if ( $row[ 'user' ] == api::info ( 'id' ) ) {
						db::q ( "UPDATE support set status='s', time='" . time () . "' where id='" . $id . "'" );
					} else {
						$sql233u = db::q ( 'SELECT * FROM users where id="' . $row[ 'user' ] . '"' );
						$user = db::r ( $sql233u );
						$msg = $conf[ 'domain' ] ." ". l::t("Новое сообщение в центре поддержки.");
						$pm = false;
						if ( $user[ 'phone' ] && $conf[ 'sms_support' ] ) {
							api::inc ( 'sms' );
							if ( sms::send ( (int) ( $user[ 'phone' ] ) , $msg ) ) {
								$pm = true;
							}
						}
						if ( $pm == false ) {
							api::inc ( 'mail' );
							mail::send ( $user[ 'mail' ] , l::t('Ответ в центре поддержки') , $msg );
						}
						db::q ( "UPDATE support set status='" . $row[ 'user' ] . "', time='" . time () . "' where id='" . $id . "'" );
					}

					return true;
				}
			}
		} else {
			mobile::error ( l::t('Тикет не найден') );
		}
	}

	public static function service_mobile ()
	{
		$service = null;
		$sql = db::q ( 'SELECT id,ip,port FROM gh_servers where user="' . api::info ( 'id' ) . '" order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			$service2[ 'id' ] = "s" . $row[ 'id' ];
			$service2[ 'name' ] = $row[ 'ip' ] . ':' . $row[ 'port' ];
			$service[ ] = $service2;
		}
		$sql = db::q ( 'SELECT id,sid FROM isp where user="' . api::info ( 'id' ) . '" order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			$service2[ 'id' ] = "s" . $row[ 'id' ];
			$service2[ 'name' ] = 'Web: s' . $row[ 'sid' ];
			$service[ ] = $service2;
		}
		mobile::$data[ 'service' ] = $service;
	}

	public static function add_mobile ()
	{
		$data = $_POST[ 'data' ];
		if ( ! preg_match ( "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ^\.,!? \:\-\_]{2,40}$/i" , $data[ 'title' ] ) ) {
			if ( ! $data[ 'title' ] ) {
				mobile::error ( l::t('Укажите тему') );
			} else {
				if ( mb_strlen ( $data[ 'title' ] , "utf-8" ) < 2 ) {
					mobile::error ( l::t('Тема слишком короткая') );
				} else {
					if ( mb_strlen ( $data[ 'title' ] , "utf-8" ) > 40 ) {
						mobile::error ( l::t('Тема слишком длинная') );
					} else {
						mobile::error ( l::t('Тема содержит недопустимые символы' ));
					}
				}
			}
		} else {
			if ( ! preg_match ( "/^.{2,400}$/si" , $data[ 'text' ] ) ) {
				if ( ! $data[ 'text' ] ) {
					mobile::error ( l::t('Введите сообщение') );
				} else {
					if ( mb_strlen ( $data[ 'text' ] , "utf-8" ) < 10 ) {
						mobile::error ( l::t('Сообщение слишком короткое') );
					} else {
						if ( mb_strlen ( $data[ 'text' ] , "utf-8" ) > 400 ) {
							mobile::error ( l::t('Сообщение слишком длинное') );
						} else {
							mobile::error ( l::t('Сообщение содержит недопустимые символы') );
						}
					}
				}
			} else {
				$service2 = 0;
				$service = api::cl ( $data[ 'service' ] );
				if ( preg_match ( "/^s[0-9]{1,10}$/i" , $service ) ) {
					$id = str_replace ( 's' , '' , $service );
					db::q ( 'SELECT id FROM gh_servers where user="' . api::info ( 'id' ) . '" and id="' . $id . '"' );
					if ( db::n () == 1 ) {
						$service2 = $service;
					}
				}
				if ( preg_match ( "/^w[0-9]{1,10}$/i" , $service ) ) {
					$id = str_replace ( 'w' , '' , $service );
					db::q ( 'SELECT id FROM isp where user="' . api::info ( 'id' ) . '" and id="' . $id . '"' );
					if ( db::n () == 1 ) {
						$service2 = $service;
					}
				}
				db::q (
					"INSERT INTO support set
							user='" . api::info ( 'id' ) . "',
							status='s',
							service='" . $service2 . "',
							time='" . time () . "',
							name='" . base64_encode ( api::cl ( $data[ 'title' ] ) ) . "'"
				);
				$id = db::i ();
				db::q (
					"INSERT INTO support_mes set
							user='" . api::info ( 'id' ) . "',
							time='" . time () . "',
							mes='" . base64_encode ( $data[ 'text' ] ) . "',
							tid='" . $id . "'
						"
				);
				return true;
			}
		}
	}
}

?>