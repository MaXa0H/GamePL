<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class forum
{
	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		$sql_base_cat = db::q ( "SELECT * FROM forum_post where id='" . $id . "'" );
		api::nav ( '/forum' , 'Форум' );
		if ( db::n ( $sql_base_cat ) ) {
			$forum = db::r ( $sql_base_cat );
			$num = db::q ( "SELECT * FROM forum_mes where post='" . $id . "'" );
			$num = db::n ( $num );
			db::q ( 'DELETE from forum_mes where post="' . $id . '"' );
			db::q ( 'DELETE from forum_post where id="' . $id . '"' );
			api::result ( l::t ( 'Пост удален' ) , true );
		} else {
			api::result ( l::t ( 'Пост не найден' ) );
		}
	}

	public static function edit ( $id )
	{
		$sql_base_cat = db::q ( "SELECT * FROM forum_mes where id='" . $id . "'" );
		api::nav ( '/forum' , l::t ( 'Форум' ) );
		if ( db::n ( $sql_base_cat ) ) {
			$forum = db::r ( $sql_base_cat );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( ! preg_match ( "/^.{100,3000}$/si" , $data[ 'mes' ] ) ) {
					api::result ( l::t ( 'Сообщение должно быть от 100 до 3000 символов' ) );

					return false;
				}
				db::q ( "UPDATE forum_mes set mes='" . base64_encode ( api::cl ( $data[ 'mes' ] ,1) ) . "' where id='" . $id . "'" );
				api::result ( l::t ( 'Сохранено' ) , true );
			}
			api::nav ( '' , l::t ( 'Редактирование сообщения' ) , 1 );
			tpl::load ( 'forum-mes-edit' );
			tpl::set ( '{mes}' , str_replace ( '\n' , "\n" , base64_decode ( $forum[ 'mes' ] ) ) );
			tpl::compile ( 'content' );
		} else {
			api::result ( l::t ( 'Пост не найден' ) );
		}

	}

	public static function dell ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		$sql_base_cat = db::q ( "SELECT * FROM forum_mes where id='" . $id . "'" );
		api::nav ( '/forum' , l::t ( 'Форум' ) );
		if ( db::n () ) {
			db::q ( 'DELETE from forum_mes where id="' . $id . '"' );
			api::result ( l::t ( 'Сообщение удалено' ) , true );
		} else {
			api::result ( l::t ( 'Пост не найден' ) );
		}

	}

	public static function lock ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		$sql_base_cat = db::q ( "SELECT * FROM forum_post where id='" . $id . "'" );
		api::nav ( '/forum' , l::t ( 'Форум' ) );
		if ( db::n ( $sql_base_cat ) ) {
			$forum = db::r ( $sql_base_cat );
			if ( $forum[ 'locked' ] ) {
				api::result ( l::t ( 'Пост уже закрыт' ) );
			} else {
				db::q ( 'UPDATE forum_post set locked="1" where id="' . $id . '"' );
				api::result ( l::t ( 'Пост закрыт' ) , true );
			}
		} else {
			api::result ( l::t ( 'Пост не найден' ) );
		}

	}

	public static function post_add ( $name )
	{
		global $title;
		$name2 = explode ( '-' , $name );
		$sql_base_cat = db::q ( "SELECT * FROM forum_cat where id='" . api::cl ( $name2[ '0' ] ) . "'" );
		api::nav ( '/forum' , 'Форум' );
		if ( db::n ( $sql_base_cat ) ) {
			$forum = db::r ( $sql_base_cat );
			if ( $forum[ 'tname' ] == ( str_replace ( $name2[ '0' ] . '-' , '' , $name ) ) ) {
				$data = $_POST[ 'data' ];
				if ( $data ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					if ( ! preg_match ( "/^.{10,100}$/i" , $data[ 'name' ] ) ) {
						api::result ( l::t ( 'Название должно быть от 10 до 100 символов' ) );

						return false;
					}
					if ( ! preg_match ( "/^.{10,100}$/i" , $data[ 'des' ] ) ) {
						api::result ( l::t ( 'Описание должно быть от 10 до 100 символов' ) );

						return false;
					}
					if ( ! preg_match ( "/^.{100,3000}$/si" , $data[ 'mes' ] ) ) {
						api::result ( l::t ( 'Сообщение должно быть от 100 до 3000 символов' ) );

						return false;
					}
					db::q (
						"INSERT INTO forum_post set
							time='" . time () . "',
							user='" . api::info ( 'id' ) . "',
							name='" . api::cl ( $data[ 'name' ],1 ) . "',
							des='" . api::cl ( $data[ 'des' ],1 ) . "',
							tname='" . api::totranslit ( api::cl ( $data[ 'name' ] ) ) . "',
							cat='" . $forum[ 'id' ] . "'
						"
					);
					$id = db::i ();
					db::q ( 'UPDATE forum_cat set post="' . ( $forum[ 'post' ] + 1 ) . '" where id="' . $forum[ 'id' ] . '"' );
					self::mes_add ( $data[ 'mes' ] , api::info ( 'id' ) , $id );
					api::result ( l::t ( 'Опубликовано' ) , true );
				}
				$title = $forum[ 'name' ];
				$sql = db::q ( "SELECT * FROM forum_cat where id='" . $forum[ 'subcat' ] . "'" );
				$cat2 = db::r ( $sql );
				api::nav ( '/forum' , $cat2[ 'name' ] );
				api::nav ( '/forum/' . $forum[ 'id' ] . '-' . $forum[ 'tname' ] , $title );
				api::nav ( '' , l::t ( 'Новая тема' ) , 1 );
				tpl::load ( 'forum-post-add' );
				tpl::compile ( 'content' );
			} else {
				$title = l::t ( "Ошибка" );
				api::nav ( '' , l::t ( 'Ошибка' ) , 1 );
				api::e404 ( l::t ( 'Форум не найден' ) );
			}
		} else {
			$title = l::t ( "Ошибка" );
			api::nav ( '' , l::t ( 'Ошибка' ) , 1 );
			api::e404 ( l::t ( 'Форум не найден' ) );
		}

	}

	public static function mes_add ( $mes , $user , $post )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT * FROM forum_post where id='" . $post . "'" );
		$post = db::r ();
		db::q ( 'UPDATE forum_post set mes="' . ( $post[ 'mes' ] + 1 ) . '",time="' . time () . '" where id="' . $post[ 'id' ] . '"' );
		db::q ( "SELECT * FROM forum_cat where id='" . $post[ 'cat' ] . "'" );
		$cat = db::r ();
		db::q ( 'UPDATE forum_cat set mes="' . ( $cat[ 'mes' ] + 1 ) . '" where id="' . $cat[ 'id' ] . '"' );
		db::q ( "SELECT * FROM forum_cat where id='" . $cat[ 'subcat' ] . "'" );
		$cat2 = db::r ();
		db::q ( 'UPDATE forum_cat set mes="' . ( $cat2[ 'mes' ] + 1 ) . '" where id="' . $cat2[ 'id' ] . '"' );
		db::q ( "SELECT id,forum_mes FROM users where id='" . $user . "'" );
		$user = db::r ();
		db::q ( 'UPDATE users set forum_mes="' . ( $user[ 'forum_mes' ] + 1 ) . '" where id="' . $user[ 'id' ] . '"' );
		db::q (
			"INSERT INTO forum_mes set
				time='" . time () . "',
				user='" . $user[ 'id' ] . "',
				mes='" . base64_encode ( api::cl ( $mes ,1) ) . "',
				post='" . $post[ 'id' ] . "',
				cat='" . $post[ 'cat' ] . "'
			"
		);

	}

	public static function post ( $name )
	{
		global $title;
		$name2 = explode ( '-' , $name );
		$sql_posts = db::q ( "SELECT * FROM forum_post where id='" . $name2[ '0' ] . "'" );
		api::nav ( '/forum' , l::t ( 'Форум' ) );
		if ( db::n ( $sql_posts ) ) {
			$post = db::r ( $sql_posts );
			if ( $post[ 'tname' ] == ( str_replace ( $name2[ '0' ] . '-' , '' , $name ) ) ) {
				db::q ( 'UPDATE forum_post set view="' . ( $post[ 'view' ] + 1 ) . '" where id="' . $post[ 'id' ] . '"' );
				$data = $_POST[ 'data' ];
				if ( $data ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					if ( ! api::$go ) {
						api::result ( l::t ( 'Для доступа к данной странице нужно авторизоваться на сайте' ) );
					} else {
						if ( ! $data[ 'mes' ] ) {
							api::result ( l::t ( 'Введите сообщение' ) );

							return false;
						}
						self::mes_add ( $data[ 'mes' ] , api::info ( 'id' ) , $post[ 'id' ] );
						api::result ( l::t ( 'Отправлено' ) , true );

						return true;
					}
				}
				$title = $post[ 'name' ];
				$sql = db::q ( "SELECT * FROM forum_cat where id='" . $post[ 'cat' ] . "'" );
				$cat = db::r ( $sql );
				$sql = db::q ( "SELECT * FROM forum_cat where id='" . $cat[ 'subcat' ] . "'" );
				$cat2 = db::r ( $sql );
				api::nav ( '/forum/' , $cat2[ 'name' ] );
				$link = '/forum/' . $cat[ 'id' ] . '-' . $cat[ 'tname' ];
				api::nav ( $link , $cat[ 'name' ] );
				api::nav ( '' , $post[ 'name' ] , 1 );
				$pages = (int) r::g ( 4 );
				$sql = db::q ( 'SELECT id FROM forum_mes where post="' . $post[ 'id' ] . '"' );
				$all = db::n ( $sql );
				if ( (int) $pages ) {
					if ( ( $all / 10 ) > $pages ) {
						$page = 10 * $pages;
					} else {
						$page = 0;
					}
				} else {
					$page = 0;
				}
				api::inc ( 'bbcode' );
				$sql = db::q ( "SELECT * FROM forum_mes where post='" . $post[ 'id' ] . "' order by time asc  LIMIT " . $page . " ,10" );
				$i = 0;
				while ( $mes = db::r ( $sql ) ) {
					$i ++;
					tpl::load ( 'forum-base-post-data-get' );
					if ( api::admin ( 'forum' ) ) {
						if ( $pages ) {
							tpl::set_block ( "'\\[del\\](.*?)\\[/del\\]'si" , "\\1" );
							tpl::set_block ( "'\\[edit\\](.*?)\\[/edit\\]'si" , "\\1" );
						} else {
							if ( $i != 1 ) {
								tpl::set_block ( "'\\[edit\\](.*?)\\[/edit\\]'si" , "\\1" );
								tpl::set_block ( "'\\[del\\](.*?)\\[/del\\]'si" , "\\1" );
							} else {
								tpl::set_block ( "'\\[edit\\](.*?)\\[/edit\\]'si" , "" );
								tpl::set_block ( "'\\[del\\](.*?)\\[/del\\]'si" , "" );
							}
						}
					} else {
						tpl::set_block ( "'\\[del\\](.*?)\\[/del\\]'si" , "" );
						tpl::set_block ( "'\\[edit\\](.*?)\\[/edit\\]'si" , "" );
					}
					tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $mes[ 'time' ] ) );
					tpl::set ( '{id}' , $mes[ 'id' ] );
					tpl::set ( '{mes}' , bbcode::forum ( base64_decode ( $mes[ 'mes' ] ) ) );
					$user = db::q ( "SELECT id,name,lastname,photo,ugroup,forum_mes,time FROM users where id='" . $mes[ 'user' ] . "'" );
					$user = db::r ( $user );
					tpl::set ( '{user}' , $user[ 'name' ] . ' ' . $user[ 'lastname' ] );
					if ( $user[ 'photo' ] ) {
						$photo = "/files/photo/" . $user[ 'id' ] . ".png";
					} else {
						$photo = "/img/icon_admins.png";
					}
					tpl::set ( '{photo}' , $photo );
					if ( $user[ 'ugroup' ] == 1 ) {
						$group = l::t ( "Администратор" );
					} elseif ( $user[ 'ugroup' ] == 2 ) {
						$group = l::t ( "Поддержка" );
					} else {
						$sql2 = db::q ( 'SELECT id FROM gh_servers where user="' . $user[ 'id' ] . '"' );
						if ( db::n ( $sql2 ) ) {
							$group = l::t ( "Клиент" );
						} else {
							$sql2 = db::q ( 'SELECT id FROM isp where user="' . $user[ 'id' ] . '"' );
							if ( db::n ( $sql2 ) ) {
								$group = l::t ( "Клиент" );
							} else {
								$group = l::t ( "Посетитель" );
							}
						}
					}
					tpl::set ( '{group}' , $group );
					tpl::set ( '{mess}' , $user[ 'forum_mes' ] );
					tpl::set ( '{signup}' , api::langdate ( "j.m.Y" , $user[ 'time' ] ) );
					tpl::compile ( 'post' );
				}
				tpl::load ( 'forum-base-post-data' );
				if ( $post[ 'locked' ] ) {
					tpl::set_block ( "'\\[lock\\](.*?)\\[/lock\\]'si" , "" );
				} else {
					tpl::set_block ( "'\\[lock\\](.*?)\\[/lock\\]'si" , "\\1" );
				}
				tpl::set ( '{date}' , api::langdate ( "j F Y - H:i" , $post[ 'time' ] ) );
				tpl::set ( '{name}' , $post[ 'name' ] );
				tpl::set ( '{id}' , $post[ 'id' ] );
				tpl::set (
					'{posts}' , $all . ' ' . api::getNumEnding (
								  $all , array (
										   l::t ( 'ответ' ) ,
										   l::t ( 'ответа' ) ,
										   l::t ( 'ответов' )
									   )
							  )
				);
				tpl::set ( '{data}' , tpl::result ( 'post' ) );
				$link = $link . '/' . $post[ 'id' ] . '-' . $post[ 'tname' ];
				tpl::set ( '{nav}' , api::pagination ( $all , 10 , $pages , $link , '' ) );
				if ( api::$logget ) {
					if ( $post[ 'locked' ] ) {
						tpl::set ( '{error}' , l::t ( 'Пост закрыт' ) );
						tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "\\1" );
						tpl::set_block ( "'\\[add\\](.*?)\\[/add\\]'si" , "" );
					} else {
						tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "" );
						tpl::set_block ( "'\\[add\\](.*?)\\[/add\\]'si" , "\\1" );
					}
				} else {
					tpl::set ( '{error}' , l::t ( 'Посетители, находящиеся в группе Гости, не могут оставлять комментарии.' ) );
					tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "\\1" );
					tpl::set_block ( "'\\[add\\](.*?)\\[/add\\]'si" , "" );
				}

				tpl::compile ( 'content' );
			} else {
				$title = l::t ( "Ошибка" );
				api::nav ( '' , l::t ( 'Ошибка' ) , 1 );
				api::e404 ( l::t ( 'Пост не найден' ) );
			}
		} else {
			$title = l::t ( "Ошибка" );
			api::nav ( '' , l::t ( 'Ошибка' ) , 1 );
			api::e404 ( l::t ( 'Пост не найден' ) );
		}
	}

	public static function cat ( $name )
	{

		global $title;
		$name2 = explode ( '-' , $name );
		$sql_base_cat = db::q ( "SELECT * FROM forum_cat where id='" . api::cl ( $name2[ '0' ] ) . "'" );
		api::nav ( '/forum' , l::t ( 'Форум' ) );
		if ( db::n ( $sql_base_cat ) ) {
			$forum = db::r ( $sql_base_cat );
			if ( $forum[ 'tname' ] == ( str_replace ( $name2[ '0' ] . '-' , '' , $name ) ) ) {
				$title = $forum[ 'name' ];
				$sql = db::q ( "SELECT * FROM forum_cat where id='" . $forum[ 'subcat' ] . "'" );
				$cat2 = db::r ( $sql );
				api::nav ( '/forum' , $cat2[ 'name' ] );
				api::nav ( '' , $title , 1 );
				tpl::load ( 'forum-base-posts' );
				tpl::set ( '{add}' , '/forum/' . $forum[ 'id' ] . '-' . $forum[ 'tname' ] . '/add' );
				tpl::set ( '{name}' , $forum[ 'name' ] );
				$sql_posts = db::q ( "SELECT * FROM forum_post where cat='" . $forum[ 'id' ] . "' order by time desc" );
				while ( $post = db::r ( $sql_posts ) ) {
					tpl::load ( 'forum-base-posts-sub' );
					tpl::set ( '{link}' , '/forum/' . $forum[ 'id' ] . '-' . $forum[ 'tname' ] . '/' . $post[ 'id' ] . '-' . $post[ 'tname' ] );
					tpl::set ( '{name}' , $post[ 'name' ] );
					tpl::set ( '{des}' , $post[ 'des' ] );
					tpl::set ( '{posts}' , $post[ 'mes' ] );
					tpl::set ( '{view}' , $post[ 'view' ] );
					$user = db::q ( "SELECT name,lastname FROM users where id='" . $post[ 'user' ] . "'" );
					$user = db::r ( $user );
					tpl::set ( '{user}' , $user[ 'name' ] . ' ' . $user[ 'lastname' ] );
					$sql_last_mes = db::q ( "SELECT * FROM forum_mes where post='" . $post[ 'id' ] . "' order by time desc LIMIT 0,1" );
					$last_mes = db::r ();
					$last = '<p style="margin: 0px; font-size: 11px;">' . api::langdate ( "j F Y - H:i" , $last_mes[ 'time' ] ) . '</p>';
					$sql_last_mes_user = db::q ( "SELECT name,lastname FROM users where id='" . $last_mes[ 'user' ] . "'" );
					$last_mes_user = db::r ( $sql_last_mes_user );
					$last .= '<p style="margin: 0px;">Автор: <a href="/users/profile/' . $last_mes[ 'user' ] . '" style=" font-size: 11px;">' . $last_mes_user[ 'name' ] . ' ' . $last_mes_user[ 'lastname' ] . '</a></p>';
					tpl::set ( '{last}' , $last );
					tpl::compile ( 'data' );
				}
				tpl::set ( '{data}' , tpl::result ( 'data' ) );
				tpl::compile ( 'content' );
			} else {
				$title = l::t ( "Ошибка" );
				api::nav ( '' , l::t ( 'Ошибка' ) , 1 );
				api::e404 ( l::t ( 'Форум не найден' ) );
			}
		} else {
			$title = l::t ( "Ошибка" );
			api::nav ( '' , l::t ( 'Ошибка' ) , 1 );
			api::e404 ( l::t ( 'Форум не найден' ) );
		}

	}

	public static function base ()
	{
		global $title;
		$title = l::t ( "Форум" );
		api::nav ( '' , l::t ( 'Форум' ) , 1 );
		$sql_base_cat = db::q ( "SELECT * FROM forum_cat where subcat='0'" );
		while ( $base_cat = db::r ( $sql_base_cat ) ) {
			tpl::$result[ 'forums2' ] = '';
			tpl::load ( 'forum-base-cat' );
			$sql_base_cat_sub = db::q ( "SELECT * FROM forum_cat where subcat='" . $base_cat[ 'id' ] . "'" );
			while ( $base_cat_sub = db::r ( $sql_base_cat_sub ) ) {
				tpl::load ( 'forum-base-cat-sub' );
				tpl::set ( '{link}' , '/forum/' . $base_cat_sub[ 'id' ] . '-' . $base_cat_sub[ 'tname' ] );
				tpl::set ( '{name}' , $base_cat_sub[ 'name' ] );
				tpl::set ( '{img}' , $base_cat_sub[ 'img' ] );
				tpl::set ( '{des}' , $base_cat_sub[ 'des' ] );
				tpl::set ( '{posts}' , $base_cat_sub[ 'post' ] );
				tpl::set ( '{mes}' , $base_cat_sub[ 'mes' ] );
				$sql_last_mes = db::q ( "SELECT * FROM forum_mes where cat='" . $base_cat_sub[ 'id' ] . "' order by time desc LIMIT 0,1" );
				if ( db::n () ) {
					$last_mes = db::r ();
					$sql_last_post = db::q ( "SELECT * FROM forum_post where id='" . $last_mes[ 'post' ] . "'" );
					$last_post = db::r ( $sql_last_post );

					$last = '<p style="margin: 0px; font-size: 11px;">' . api::langdate ( "j F Y - H:i" , $last_mes[ 'time' ] ) . '</p>';
					$last .= '<p style="margin: 0px; font-size: 11px;">Тема: <a href="/forum/' . $base_cat_sub[ 'id' ] . '-' . $base_cat_sub[ 'tname' ] . '/' . $last_post[ 'id' ] . '-' . $last_post[ 'tname' ] . '" style="font-size: 11px;">' . $last_post[ 'name' ] . '</a></p>';
					$sql_last_mes_user = db::q ( "SELECT name,lastname FROM users where id='" . $last_mes[ 'user' ] . "'" );
					$last_mes_user = db::r ( $sql_last_mes_user );
					$last .= '<p style="margin: 0px; font-size: 11px;">Автор: <a  style="font-size: 11px;" href="/users/profile/' . $last_mes[ 'user' ] . '">' . $last_mes_user[ 'name' ] . ' ' . $last_mes_user[ 'lastname' ] . '</a></p>';
					tpl::set ( '{last}' , $last );
				} else {
					tpl::set ( '{last}' , '' );
				}
				tpl::compile ( 'forums2' );
			}
			tpl::set ( '{forums}' , tpl::result ( 'forums2' ) );
			tpl::set ( '{name}' , $base_cat[ 'name' ] );
			tpl::compile ( 'forums' );
		}
		tpl::load ( 'forum-base' );
		tpl::set ( '{forums}' , tpl::result ( 'forums' ) );
		tpl::compile ( 'content' );
	}

}

?>