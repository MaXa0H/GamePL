<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class servers_fastdl
{
	public static function base ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'fastdl' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			servers::$speedbar = $id;
			$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
			api::nav ( "/servers" , l::t ( "Серверы" ) );
			api::nav ( "/servers/base/" . $id , $adress );
			api::nav ( "" , 'Fast DL' , '1' );
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'fastdl' ) ) {
					db::q ( 'SELECT fastdl FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
					$type = db::r ();
					if ( $type[ 'fastdl' ] == 0 ) {
						api::result ( l::t ( 'FastDl отключен на данной машине' ) );
					} else {
						db::q ( 'SELECT fastdl FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
						$row7 = db::r ();
						if ( $row7[ 'fastdl' ] == 1 ) {
							tpl::load ( 'servers-fastdl' );
							tpl::set ( '{id}' , $id );
							$cfg = servers::cfg ( $id );
							if ( $cfg[ 'fastdl' ] == "1" ) {
								tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "\\1" );
								tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
								tpl::set ( '{link}' , 'http://' . $row[ 'ip' ] . '/s' . $row[ 'sid' ] );
							} else {
								tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
								tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "\\1" );
							}
							tpl::compile ( 'content' );
						} else {
							api::result ( l::t ( 'FastDl отключен на данном тарифе' ) );
						}
					}
				} else {
					api::result ( l::t ( 'Данная функция отключена' ) );
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function on ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'fastdl' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			servers::$speedbar = $id;
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'fastdl' ) ) {
					db::q ( 'SELECT fastdl,os FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
					$type = db::r ();
					if ( $type[ 'fastdl' ] == 0 ) {
						api::result ( l::t ( 'FastDl отключен на данной машине' ) );
					} else {
						db::q ( 'SELECT fastdl FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
						$row7 = db::r ();
						if ( $row7[ 'fastdl' ] != 0 ) {
							$cfg = servers::cfg ( $id );
							if ( $cfg[ 'fastdl' ] == "1" ) {
								api::result ( l::t ( 'FastDl уже подключен' ) );
							} else {
								api::inc ( 'ssh2' );
								if ( ssh::gh_box ( $row[ 'box' ] ) ) {
									api::inc ( 'fastdl' );
									fastdl::$dir = '/host/' . $row[ 'user' ] . '/' . $row[ 'sid' ] . '/';
									fastdl::$type = $type[ 'fastdl' ];
									fastdl::$server = $row[ 'sid' ];
									fastdl::$os = $type[ 'os' ];
									$class::fastdl_on ();
									fastdl::on ();
									ssh::disconnect ();
									$d[ 'fastdl' ] = 1;
									servers::configure ( $d , $id );
									api::result ( l::t ( 'FastDl подключен' ) , true );
								} else {
									api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
								}
							}
						} else {
							api::result ( l::t ( 'FastDl оключен на данном тарифе' ) );
						}
					}
				} else {
					api::result ( l::t ( 'Данная функция отключена' ) );
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function off ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'fastdl' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			servers::$speedbar = $id;
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'fastdl' ) ) {
					db::q ( 'SELECT fastdl,os FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
					$type = db::r ();
					if ( $type[ 'fastdl' ] == 0 ) {
						api::result ( l::t ( 'FastDl отключен на данной машине' ) );
					} else {
						db::q ( 'SELECT fastdl FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
						$row7 = db::r ();
						if ( $row7[ 'fastdl' ] != 0 ) {
							$cfg = servers::cfg ( $id );
							if ( $cfg[ 'fastdl' ] == "0" ) {
								api::result ( l::t ( 'FastDl уже отключен' ) );
							} else {
								api::inc ( 'ssh2' );
								if ( ssh::gh_box ( $row[ 'box' ] ) ) {
									if ( $type[ 'fastdl' ] == 1 || $type[ 'fastdl' ] == 3 ) {
										if ( $type[ 'os' ] == 3 ) {
											ssh::exec_cmd ( "cd /etc/httpd/fastdl/;rm " . $row[ 'sid' ] . ".conf;" );
										} else {
											ssh::exec_cmd ( "cd /etc/apache2/fastdl/;rm " . $row[ 'sid' ] . ".conf;" );
										}
									} else {
										ssh::exec_cmd ( "cd /etc/nginx/fastdl/;rm " . $row[ 'sid' ] . ".conf;" );
									}
									ssh::disconnect ();
									$d[ 'fastdl' ] = 0;
									servers::configure ( $d , $id );
									api::result ( l::t ( 'FastDl отключен' ) , true );
								} else {
									api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
								}
							}
						} else {
							api::result ( l::t ( 'FastDl отключен на данном тарифе' ) );
						}
					}
				} else {
					api::result ( l::t ( 'Данная функция отключена' ) );
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}
}

?>