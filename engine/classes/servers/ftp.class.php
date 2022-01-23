<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class servers_ftp
{
	public static function base ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'ftp' ) ) {
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
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'ftp' ) ) {
					$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
					api::nav ( "/servers" , l::t ( "Серверы" ) );
					api::nav ( "/servers/base/" . $id , $adress );
					api::nav ( "" , 'FTP' , '1' );
					tpl::load ( 'servers-ftp' );
					tpl::set ( '{id}' , $id );
					$cfg = servers::cfg ( $id );
					if ( $cfg[ 'ftp' ] ) {
						tpl::set_block ( "'\\[ftp_off\\](.*?)\\[/ftp_off\\]'si" , "" );
						tpl::set_block ( "'\\[ftp_on\\](.*?)\\[/ftp_on\\]'si" , "\\1" );
						tpl::set ( '{host}' ,servers::ip_server($row['box']) . ':21' );
						tpl::set ( '{login}' , 's' . $row[ 'sid' ] );
						tpl::set ( '{pass}' , $cfg[ 'ftp_pass' ] );
						tpl::set ( '{ftp_url}' , 'ftp://s' . $row[ 'sid' ] . ':' . $cfg[ 'ftp_pass' ] . '@' . servers::ip_server($row['box']) . ':21' );
					} else {
						tpl::set_block ( "'\\[ftp_off\\](.*?)\\[/ftp_off\\]'si" , "\\1" );
						tpl::set_block ( "'\\[ftp_on\\](.*?)\\[/ftp_on\\]'si" , "" );
					}
					api::captcha_create ();
					tpl::compile ( 'content' );
				} else {
					api::result ( l::t ( 'Данная функция отключена' ) );
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function online ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'ftp' ) ) {
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
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'ftp' ) ) {
					$cfg = servers::cfg ( $id );
					if ( $cfg[ 'ftp' ] == "0" ) {
						api::result ( l::t ( 'FTP выключен' ) );

						return false;
					} else {
						include_once ( ROOT . '/engine/classes/file_systems.class.php' );
						fs::load_ftp ( servers::ip_server2($row['box']) , $cfg[ 'ftp_pass' ] , 's' . $row[ 'sid' ] );
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
		if ( api::captcha_chek () ) {
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'ftp' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$row = db::r ();
				if ( $row[ 'time' ] < time () ) {
					api::result ( l::t ( 'Срок аренды сервера истек' ) );
				} else {
					$class = servers::game_class ( $row[ 'game' ] );
					if ( $class::info ( 'ftp' ) ) {
						$cfg = servers::cfg ( $id );
						if ( $cfg[ 'ftp' ] == "1" ) {
							api::result ( l::t ( 'FTP уже включен' ) );

							return false;
						}
						db::q ( 'SELECT * FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
						$box = db::r ();
						db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
						$rate = db::r ();
						if(!$box['ftp']){
							$ftp_ip = ( $box[ 'db_ip' ] != "" ) ? $box[ 'db_ip' ] : $box[ 'ip' ];
							if ( $dbftp = self::ftp_db_conn ( $ftp_ip , $box[ 'db_port' ] , $box[ 'db_login' ] , $box[ 'db_pass' ] , $box[ 'db_name' ] ) ) {
								api::inc('ssh2');
								if ( ssh::gh_box ( $row[ 'box' ] ) ) {
									if ( $cfg[ 'ftp_pass' ] == "" ) {
										$password = api::generate_password ( '6' );
										$password2 = md5 ( $password );
										$data[ 'ftp_pass' ] = $password;
										$data[ 'ftp' ] = "1";
									} else {
										$password2 = md5 ( $cfg[ 'ftp_pass' ] );
										$data[ 'ftp' ] = "1";
									}
									servers::configure ( $data , $id );
									$dir = '/host/' . $row[ 'user' ] . "/" . $row[ 'sid' ] . $class::info ( 'ftp_root' );

									mysql_query ( "INSERT INTO ftpd set user='s" . $row[ 'sid' ] . "', status='1',password='" . $password2 . "',uid='" . $row[ 'sid' ] . "',gid='" . $row[ 'sid' ] . "',dir='" . $dir . "',ipaccess='*',quotasize='" . $rate[ 'hard' ] . "'" , $dbftp );
									$exec = 'cd /host/' . $row[ 'user' ] . "/" . $row[ 'sid' ] . '/;ls -R |wc -l';
									ssh::exec_cmd ( $exec );
									$data = ssh::get_output ();
									$data = explode ( "\n" , $data );
									$s = $data[ 0 ];
									$exec = 'cd /host/' . $row[ 'user' ] . "/" . $row[ 'sid' ] . '/;du -sk | awk \'{print $1}\'';
									ssh::exec_cmd ( $exec );
									$data = ssh::get_output ();
									$data = explode ( "\n" , $data );
									$s = $s . ' ' . ( $data[ 0 ] * 1024 );
									$exec = 'echo "' . $s . '" > ' . $dir . '.ftpquota;';
									$exec .= 'chmod -R 600 ' . $dir . '.ftpquota;';
									$exec .= 'chown -R s' . $row[ 'sid' ] . ':s' . $row[ 'sid' ] . ' ' . $dir . '.ftpquota;';
									ssh::exec_cmd ( $exec );
									api::result ( l::t ( 'FTP включен' ) , true );
								} else {
									api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
								}
							} else {
								api::result ( l::t ( 'Не удалось установить соединение c базой данных от FTP' ) );
							}
						}else{
							api::inc ( 'ssh2' );
							if ( ssh::gh_box ( $row[ 'box' ] ) ) {
								if ( $cfg[ 'ftp_pass' ] == "" ) {
									$password2 = api::generate_password ( '6' );
									$data[ 'ftp_pass' ] = $password2;
									$data[ 'ftp' ] = "1";
								} else {
									$password2 = $cfg[ 'ftp_pass' ];
									$data[ 'ftp' ] = "1";
								}
								servers::configure ( $data , $id );
								$dir = '/host/' . $row[ 'user' ] . "/" . $row[ 'sid' ] . $class::info ( 'ftp_root' );
								$exec = "screen -dmS ftp_s".$row[ 'sid' ]." pure-pw useradd s".$row[ 'sid' ]." -u s".$row[ 'sid' ]." -g s".$row[ 'sid' ]." -d ".$dir." -N ".$rate[ 'hard' ]." ;";
								ssh::exec_cmd ( $exec );
								sleep(1);
								$exec = 'screen -S ftp_s'.$row[ 'sid' ].' -p 0 -X stuff \'' . $password2 . '\'$\'\n\';';
								ssh::exec_cmd ( $exec );
								sleep(1);
								ssh::exec_cmd ( $exec );
								sleep(1);
								$exec = 'pure-pw mkdb;';
								ssh::exec_cmd ( $exec );
								api::result ( l::t ( 'FTP включен' ) , true );
							} else {
								api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
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

	public static function off ( $id )
	{
		if ( api::admin ( 'servers' ) or servers::$cron == true ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'ftp' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
		}
		if ( db::n () == 1 ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			$row = db::r ();
			if ( $row[ 'time' ] < time () and servers::$cron == false ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'ftp' ) ) {
					$cfg = servers::cfg ( $id );
					if ( $cfg[ 'ftp' ] == "0" ) {
						if ( ! servers::$cron ) {
							api::result ( l::t ( 'FTP уже выключен' ) );
						}

						return false;
					}
					db::q ( 'SELECT * FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
					$box = db::r ();
					if(!$box['ftp']) {
						$ftp_ip = ( $box[ 'db_ip' ] != "" ) ? $box[ 'db_ip' ] : $box[ 'ip' ];
						if ( $dbftp = self::ftp_db_conn ( $ftp_ip , $box[ 'db_port' ] , $box[ 'db_login' ] , $box[ 'db_pass' ] , $box[ 'db_name' ] ) ) {
							mysql_query ( "delete from ftpd where user='s" . $row[ 'sid' ] . "'" , $dbftp );
							$data[ 'ftp' ] = "0";
							servers::configure ( $data , $id );
							if ( ! servers::$cron ) {
								api::result ( l::t ( 'FTP выключен' ) , true );
							}
						} else {
							if ( ! servers::$cron ) {
								api::result ( l::t ( 'Не удалось установить соединение c базой данных от FTP' ) );
							}
						}
					}else{
						api::inc('ssh2');
						if ( ssh::gh_box ( $row[ 'box' ] ) ) {
							$exec = 'pure-pw userdel s'.$row[ 'sid' ].';';
							$exec .= 'pure-pw mkdb;';
							ssh::exec_cmd ( $exec );
							$data[ 'ftp' ] = "0";
							servers::configure ( $data , $id );
							if ( ! servers::$cron ) {
								api::result ( l::t ( 'FTP выключен' ) , true );
							}
						} else {
							if ( ! servers::$cron ) {
								api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
							}
						}
					}
				} else {
					if ( ! servers::$cron ) {
						api::result ( l::t ( 'Данная функция отключена' ) );
					}
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function password ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'ftp' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'ftp' ) ) {
					$cfg = servers::cfg ( $id );
					if ( $cfg[ 'ftp' ] == "0" ) {
						api::result ( l::t ( 'FTP выключен' ) );

						return false;
					}
					$pass = api::cl($_POST[ 'data' ][ 'password' ]);
					if ( preg_match ( "/^[^a-z,A-Z0-9_]{6,20}$/" , $pass ) ) {
						api::result ( l::t ( 'Пароль содержит недопустимые символы' ) );

						return false;
					}
					db::q ( 'SELECT * FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
					$box = db::r ();
					if(!$box['ftp']) {
						$ftp_ip = ( $box[ 'db_ip' ] != "" ) ? $box[ 'db_ip' ] : $box[ 'ip' ];
						if ( ! $dbftp = self::ftp_db_conn ( $ftp_ip , $box[ 'db_port' ] , $box[ 'db_login' ] , $box[ 'db_pass' ] , $box[ 'db_name' ] ) ) {
							api::result ( l::t ( 'Не удалось установить соединение c базой данных от FTP' ) );
						} else {
							mysql_select_db ( $box[ 'db_name' ] , $dbftp );
							mysql_query ( "update ftpd set password='" . md5 ( $pass ) . "' where user='s" . $row[ 'sid' ] . "'" );
							$data[ 'ftp_pass' ] = $pass;
							servers::configure ( $data , $id );
							api::result ( l::t ( 'Пароль изменен' ) , true );
						}
					}else{
						api::inc('ssh2');
						if ( ssh::gh_box ( $row[ 'box' ] ) ) {
							$exec = "screen -dmS ftp_s".$row[ 'sid' ]." pure-pw passwd s".$row[ 'sid' ].";";
							ssh::exec_cmd ( $exec );
							sleep(1);
							$exec = 'screen -S ftp_s'.$row[ 'sid' ].' -p 0 -X stuff \'' . $pass . '\'$\'\n\';';
							ssh::exec_cmd ( $exec );
							sleep(1);
							ssh::exec_cmd ( $exec );
							sleep(1);
							$exec = 'pure-pw mkdb;';
							ssh::exec_cmd ( $exec );
							$data[ 'ftp_pass' ] = $pass;
							servers::configure ( $data , $id );
							api::result ( l::t ( 'Пароль изменен' ) , true );
						} else {
							api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
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

	public static function ftp_db_conn ( $ip , $port = 3306 , $login , $pass , $db )
	{
		ini_set ( 'mysql.connect_timeout' , '3' );
		$dbftp = @mysql_connect ( $ip . ':' . $port , $login , $pass );
		if ( ! $dbftp ) {
			api::result ( l::t ( 'Не удалось установить соединение c базой данных от FTP' ) );
		} else {
			if ( ! mysql_select_db ( $db , $dbftp ) ) {
				api::result ( l::t ( 'База MySQL не найдена' ) );
			} else {
				return $dbftp;
			}
		}
		return false;
	}
}

?>