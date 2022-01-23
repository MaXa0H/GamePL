<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class servers_friends
{
	public static $icons = array (
		'friends'   => 'fa fa-male font-blue' ,
		'on'        => 'fa fa-power-off font-green' ,
		'off'       => 'fa fa-power-off font-red' ,
		'restart'   => 'fa fa-refresh font-green' ,
		'settings'  => 'fa fa-gear font-blue' ,
		'reinstall' => 'fa fa-gavel font-blue' ,
		'buy'       => 'fa fa-rouble font-blue' ,
		'ftp'       => 'fa fa-folder font-blue' ,
		'modules'   => 'fa fa-puzzle-piece font-blue' ,
		'maps'      => 'fa fa-road font-blue' ,
		'fastdl'    => 'fa fa-cloud font-blue' ,
		'eac'       => 'fa fa-shield font-blue' ,
		'rise'      => 'fa fa-trophy font-blue' ,
		'console'   => 'fa fa-terminal font-blue' ,
		'slots'     => 'fa fa-group font-blue'
	);

	public static function dell ( $id , $friend )
	{
		global $conf;
		if ( api::admin ( 'servers' ) ) {
			$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n ( $sql ) != 1 ) {
			if ( ! servers::friend ( $id , 'friends' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		$row = db::r ( $sql );
		db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
		$rate = db::r ();
		$class = servers::game_class ( $row[ 'game' ] );
		$data = $_POST[ 'data' ];
		$sqla = db::q ( 'SELECT * FROM gh_servers_friends where id="' . $friend . '" and server="' . $id . '"' );
		if ( db::n ( $sqla ) == 0 ) {
			api::result ( l::t ('Друг не найден!') );

			return false;
		} else {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( 'DELETE from gh_servers_friends where id="' . $friend . '"' );
			api::result ( l::t ('Друг удален!') , 1 );
		}
	}

	public static function edit ( $id , $friend )
	{
		global $conf;
		if ( api::admin ( 'servers' ) ) {
			$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n ( $sql ) != 1 ) {
			if ( ! servers::friend ( $id , 'friends' ) ) {
				api::result (l::t ('Недостаточно привилегий!') );

				return false;
			} else {
				$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		$row = db::r ( $sql );
		db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
		$rate = db::r ();
		$class = servers::game_class ( $row[ 'game' ] );
		$data = $_POST[ 'data' ];
		servers::$speedbar = $id;
		$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
		api::nav ( "/servers" , l::t ("Серверы") );
		api::nav ( "/servers/base/" . $id , $adress );
		api::nav ( "/servers/friends/" . $id , l::t ('Друзья') );
		api::nav ( '' , l::t ('Редактирование') , '1' );
		$sqla = db::q ( 'SELECT * FROM gh_servers_friends where id="' . $friend . '" and server="' . $id . '"' );
		if ( db::n ( $sqla ) == 0 ) {
			api::result ( l::t ('Друг не найден!') );

			return false;
		}
		$friend = db::r ();
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $data[ 'friend' ] ) ) {
				api::result ( l::t ("E-mail указан неверно") );
			} else {
				db::q ( 'SELECT * FROM users where mail="' . api::cl ( $data[ 'friend' ] ) . '"' );
				if ( db::n () != 1 ) {
					api::result ( l::t ('Пользователь не найден') );
				} else {
					$user = db::r ();
					if ( $row[ 'user' ] == $user[ 'id' ] ) {
						api::result (l::t ( 'Вы не можете назначить сами себя другом') );
					} else {
						db::q ( 'SELECT * FROM gh_servers_friends where user="' . $user[ 'id' ] . '" and server="' . $row[ 'id' ] . '" and id!="' . $friend[ 'id' ] . '"' );
						if ( db::n () == 1 ) {
							api::result ( l::t ('Друг уже добавлен!') );
						} else {
							$rules = array ();
							foreach ( $class::$rules as $key => $value ) {
								if ( $key == "fastdl" ) {
									if ( $rate[ 'fastdl' ] != 1 ) {
										continue;
									}
								}
								if ( $key == "modules" ) {
									if ( $rate[ 'modules' ] != 1 ) {
										continue;
									}
								}
								if ( $key == "ftp" ) {
									if ( $rate[ 'ftp' ] == 0 ) {
										continue;
									}
								}
								if ( $key == "maps" ) {



								}
								if ( $key == "rise" ) {
									$sql1111 = db::q ( 'SELECT * FROM gh_rise where game="' . $row[ 'game' ] . '" order by id asc' );
									if ( db::n ( $sql1111 ) == 0 ) {
										continue;
									}
								}
								if ( $key == "eac" ) {

								}
								if ( $data[ $key ] == 1 ) {
									$rules[ $key ] = 1;
								} else {
									$rules[ $key ] = 0;
								}
							}
							db::q ( "UPDATE gh_servers_friends set data='" . base64_encode ( json_encode ( $rules ) ) . "',user='" . $user[ 'id' ] . "' where id='" . $friend[ 'id' ] . "'" );
							api::result (l::t ( 'Сохранено') , 1 );
						}
					}
				}
			}
		}
		if ( $rate[ 'friends' ] != 1 ) {
			api::result ( l::t ('Данная функция отключена!') );
		} else {
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ('Срок аренды сервера истек') );
			} else {
				tpl::load ( 'servers-friends-edit' );
				db::q ( 'SELECT mail FROM users where id="' . $friend[ 'user' ] . '"' );
				$user = db::r ();
				tpl::set ( '{mail}' , $user[ 'mail' ] );
				$rules = json_decode ( base64_decode ( $friend[ 'data' ] ) , true );
				foreach ( $class::$rules as $key => $value ) {
					if ( $key == "fastdl" ) {
						if ( $rate[ 'fastdl' ] != 1 ) {
							continue;
						}
					}
					if ( $key == "modules" ) {
						if ( $rate[ 'modules' ] != 1 ) {
							continue;
						}
					}
					if ( $key == "ftp" ) {
						if ( $rate[ 'ftp' ] == 0 ) {
							continue;
						}
					}

					if ( $key == "rise" ) {
						$sql1111 = db::q ( 'SELECT * FROM gh_rise where game="' . $row[ 'game' ] . '" order by id asc' );
						if ( db::n ( $sql1111 ) == 0 ) {
							continue;
						}
					}

					tpl::load ( 'servers-friends-edit-get' );
					if ( $rules[ $key ] == 1 ) {
						tpl::set ( '{checked}' , 'checked' );
					} else {
						tpl::set ( '{checked}' , '' );
					}
					tpl::set ( '{name}' , $key );
					tpl::set ( '{info}' , l::t ($value) );
					tpl::compile ( 'rules' );
				}
				tpl::set ( '{rules}' , tpl::$result[ 'rules' ] );
				tpl::set ( '{id}' , $id );
				tpl::compile ( 'content' );
			}
		}
	}

	public static function add ( $id )
	{
		global $conf;
		if ( api::admin ( 'servers' ) ) {
			$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n ( $sql ) != 1 ) {
			if ( ! servers::friend ( $id , 'friends' ) ) {
				api::result ( l::t ('Недостаточно привилегий!') );

				return false;
			} else {
				$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		$row = db::r ( $sql );
		db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
		$rate = db::r ();
		$class = servers::game_class ( $row[ 'game' ] );
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( ! preg_match ( "/^[0-9^\.a-z_\-]+@[0-9a-z_^\.]+\.[a-z]{2,3}$/i" , $data[ 'friend' ] ) ) {
				api::result ( l::t ("E-mail указан неверно") );
			} else {
				db::q ( 'SELECT * FROM users where mail="' . api::cl ( $data[ 'friend' ] ) . '"' );
				if ( db::n () != 1 ) {
					api::result ( l::t ('Пользователь не найден!') );
				} else {
					$user = db::r ();
					if ( $row[ 'user' ] == $user[ 'id' ] ) {
						api::result ( l::t ('Вы не можете назначить сами себя другом!') );
					} else {
						db::q ( 'SELECT * FROM gh_servers_friends where user="' . $user[ 'id' ] . '" and server="' . $row[ 'id' ] . '"' );
						if ( db::n () == 1 ) {
							api::result ( l::t ('Друг уже добавлен!') );
						} else {
							$rules = array ();
							foreach ( $class::$rules as $key => $value ) {
								if ( $key == "fastdl" ) {
									if ( $rate[ 'fastdl' ] != 1 ) {
										continue;
									}
								}
								if ( $key == "modules" ) {
									if ( $rate[ 'modules' ] != 1 ) {
										continue;
									}
								}
								if ( $key == "ftp" ) {
									if ( $rate[ 'ftp' ] == 0 ) {
										continue;
									}
								}
								if ( $key == "maps" ) {

								}
								if ( $key == "rise" ) {
									$sql1111 = db::q ( 'SELECT * FROM gh_rise where game="' . $row[ 'game' ] . '" order by id asc' );
									if ( db::n ( $sql1111 ) == 0 ) {
										continue;
									}
								}
								if ( $key == "eac" ) {

								}
								if ( $data[ $key ] == 1 ) {
									$rules[ $key ] = 1;
								} else {
									$rules[ $key ] = 0;
								}
							}
							db::q (
								"INSERT INTO gh_servers_friends set
									user='" . $user[ 'id' ] . "',
									server='" . $row[ 'id' ] . "',
									data='" . base64_encode ( json_encode ( $rules ) ) . "'

								"
							);
							api::result (l::t ('Друг успешно добавлен!') , 1 );
						}
					}
				}
			}
		}
		servers::$speedbar = $id;
		$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
		api::nav ( "/servers" , l::t ("Серверы") );
		api::nav ( "/servers/base/" . $id , $adress );
		api::nav ( "/servers/friends/" . $id , l::t ('Друзья' ));
		api::nav ( '' , l::t ('Добавление') , '1' );
		if ( $rate[ 'friends' ] != 1 ) {
			api::result ( l::t ('Данная функция отключена!') );
		} else {
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ('Срок аренды сервера истек') );
			} else {
				tpl::load ( 'servers-friends-add' );
				foreach ( $class::$rules as $key => $value ) {
					if ( $key == "fastdl" ) {
						if ( $rate[ 'fastdl' ] != 1 ) {
							continue;
						}
					}
					if ( $key == "modules" ) {
						if ( $rate[ 'modules' ] != 1 ) {
							continue;
						}
					}
					if ( $key == "ftp" ) {
						if ( $rate[ 'ftp' ] == 0 ) {
							continue;
						}
					}
					if ( $key == "maps" ) {

					}
					if ( $key == "rise" ) {
						$sql1111 = db::q ( 'SELECT * FROM gh_rise where game="' . $row[ 'game' ] . '" order by id asc' );
						if ( db::n ( $sql1111 ) == 0 ) {
							continue;
						}
					}
					if ( $key == "eac" ) {

					}
					tpl::load ( 'servers-friends-add-get' );
					tpl::set ( '{name}' , $key );
					tpl::set ( '{info}' , l::t ($value) );
					tpl::compile ( 'rules' );
				}
				tpl::set ( '{rules}' , tpl::$result[ 'rules' ] );
				tpl::set ( '{id}' , $id );
				tpl::compile ( 'content' );
			}
		}
	}

	public static function base ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'friends' ) ) {
				api::result ( l::t ('Недостаточно привилегий!') );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		$row = db::r ();
		db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
		$rate = db::r ();
		servers::$speedbar = $id;
		$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
		api::nav ( "/servers" , l::t ("Серверы" ));
		api::nav ( "/servers/base/" . $id , $adress );
		api::nav ( "" , l::t ('Друзья') , '1' );
		if ( $rate[ 'friends' ] != 1 ) {
			api::result ( l::t ('Данная функция отключена!' ));
		} else {
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ('Срок аренды сервера истек' ));
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				$sql = db::q ( 'SELECT * FROM gh_servers_friends where server="' . $id . '"' );
				while ( $row4 = db::r ( $sql ) ) {
					$rules = json_decode ( base64_decode ( $row4[ 'data' ] ) , true );
					tpl::load ( 'servers-friends-base-get' );
					tpl::set ( '{id}' , $row4[ 'id' ] );
					$sql2 = db::q ( 'SELECT name,lastname FROM users where id="' . $row4[ 'user' ] . '"' );
					$row2 = db::r ( $sql2 );
					tpl::set ( '{name}' , $row2[ 'name' ] . ' ' . $row2[ 'lastname' ] );
					tpl::set ( '{user}' , $row4[ 'user' ] );
					tpl::set ( '{server}' , $row[ 'id' ] );
					tpl::$result[ 'rules' ] = "";
					foreach ( $class::$rules as $key => $value ) {
						if ( $rules[ $key ] == "1" ) {
							tpl::load ( 'servers-friends-base-rules' );
							tpl::set ( '{icon}' , self::$icons[ $key ] );
							tpl::set ( '{name}' ,l::t ($value) );
							tpl::compile ( 'rules' );
						}
					}
					tpl::set ( '{rules}' , tpl::result ( 'rules' ) );
					tpl::compile ( 'data' );
				};
				tpl::load ( 'servers-friends-base' );
				tpl::set ( '{data}' , tpl::result ( 'data' ) );
				tpl::set ( '{id}' , $id );
				tpl::compile ( 'content' );
			}
		}
	}
}

?>