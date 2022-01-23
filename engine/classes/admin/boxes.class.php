<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_boxes
{
	public static $os = array (
		"1" => "Ubuntu" ,
		"2" => "Debian" ,
		"3" => "CentOS"
	);

	public static function on_off ( $id )
	{
		global $title , $conf;
		if ( api::$demo ) {
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );

			return false;
		}
		db::q ( "SELECT * FROM gh_boxes where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$rate = db::r ();
			if ( $rate[ 'power' ] ) {
				$power = 0;
			} else {
				$power = 1;
			}
			db::q ( 'update gh_boxes set power="' . $power . '" where id="' . $id . '"' );
			if ( $rate[ 'power' ] ) {
				api::result ( l::t ( 'Сервер выключен' ) , 1 );
			} else {
				api::result ( l::t ( 'Сервер включен' ) , 1 );
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function del ( $id )
	{
		if ( api::$demo ) {
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );

			return false;
		}
		db::q ( "SELECT * FROM gh_boxes where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( "SELECT * FROM gh_servers where box='" . $id . "'" );
			if ( db::n () != "0" ) {
				api::result ( l::t ( 'Для начала удалите все игровые серверы' ) );
			} else {
				db::q ( 'DELETE from gh_boxes where id="' . $id . '"' );
				db::q ( 'DELETE from gh_boxes_games where box="' . $id . '"' );
				api::result ( l::t ( 'Удалено' ) , true );
			}
		} else {
			api::result ( l::t ( 'Физический сервер не найден' ) );
		}
	}

	public static function edit ( $id )
	{
		global $title;
		api::inc ( 'ssh2' );
		db::q ( "SELECT * FROM gh_boxes where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$box = db::r ();
			api::inc ( 'servers' );
			api::inc ( 'servers/ftp' );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if ( api::$demo ) {
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );

					return false;
				}
				$get_sql = "UPDATE gh_boxes set ";
				if ( ! preg_match ( "/^[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}$/i" , $data[ 'rip' ] ) ) {
					api::result ( l::t ( 'Ip адрес введен неверно' ) );
				} else {
					$get_sql .= "ip='" . $data[ 'ip' ] . "',";
					$get_sql .= "rip='" . $data[ 'rip' ] . "',";
					if ( $data[ 'port' ] < 10 || $data[ 'port' ] > 65000 ) {
						api::result ( l::t ( 'Порт разрешено задавать в диапазоне от 10 до 65000' ) );
					} else {
						$get_sql .= "port='" . (int) $data[ 'port' ] . "',";
						$get_sql .= "login='root',";
						if ( ! ssh::connect ( $data[ 'rip' ] , $data[ 'port' ] ) ) {
							api::result ( l::t ( 'Не удалось установить соединение с ' ) . $data[ 'ip' ] . ':' . $data[ 'port' ] );
						} else {
							if ( ! $data[ 'pass' ] ) {
								$data[ 'pass' ] = $box[ 'pass' ];
							}
							if ( ! ssh::auth_pwd ( 'root' , $data[ 'pass' ] ) ) {
								api::result ( l::t ( 'Пароль введен неверно' ) );
							} else {
								$get_sql .= "pass='" . $data[ 'pass' ] . "',";
								if ( $data[ 'cores' ] < 100 || $data[ 'cores' ] > 100000 ) {
									api::result ( l::t ( 'CPU должно быть в диапазоне от 50 до 100000' ) );
								} else {
									$get_sql .= "cpu='" . (int) $data[ 'cores' ] . "',";
									if ( ! in_array ( $data[ 'fastdl' ] , array ( 0 , 1 , 2 , 3 ) ) ) {
										api::result ( l::t ( 'Критическая ошибка FastDl' ) );
										return false;
									} else {
										$get_sql .= "fastdl='" . (int) $data[ 'fastdl' ] . "',";
										if ( (int) $data[ 'ftp' ] != "1" ) {
											if ( ! preg_match ( "/^[0-9A-Za-z_=]{3,45}$/i" , $data[ 'login_db' ] ) ) {
												api::result ( l::t ( 'Логин MySQL должен быть от 3 до 45 символов' ) );
												return false;
											} else {
												$get_sql .= "db_login='" . $data[ 'login_db' ] . "',";
												if ( ! preg_match ( "/^[0-9A-Za-z_=-]{3,45}$/i" , $data[ 'db' ] ) ) {
													api::result ( l::t ( 'Название базы данных MySQL должно быть от 3 до 45 символов' ) );
													return false;
												} else {
													if ( ! $data[ 'pass_db' ] ) {
														$data[ 'pass_db' ] = $box[ 'db_pass' ];
													}
													$get_sql .= "db_pass='" . $data[ 'pass_db' ] . "',";
													$get_sql .= "db_ip='" . $data[ 'ip_db' ] . "',";
													if ( $data[ 'port_db' ] < 10 || $data[ 'port_db' ] > 65000 ) {
														api::result ( l::t ( 'Порт MySQL разрешено задавать в диапазоне от 10 до 65000' ) );
														return false;
													} else {
														$get_sql .= "db_port='" . $data[ 'port_db' ] . "',";
														if ( !$dbftp = servers_ftp::ftp_db_conn ( $data[ 'ip_db' ] , $data[ 'port_db' ] , $data[ 'login_db' ] , $data[ 'pass_db' ] , $data[ 'db' ] ) ) {
															return false;
														}
													}
												}
											}
										}

										$error = 0;
										foreach ( servers::$games as $key => $value ) {
											if ( $key != "ts3" ) {
												if ( $data[ 'game_' . $key ] == "1" ) {
													if ( $data[ 'game_cpu_' . $key ] < 1 || $data[ 'game_cpu_' . $key ] > 100 ) {
														$error = 1;
														api::result ( l::t ( 'CPU на игровой слот должно быть от 1 до 100' ) );
													}
													if ( $data[ 'game_ram_' . $key ] < 10 || $data[ 'game_ram_' . $key ] > 10000 ) {
														$error = 1;
														api::result ( l::t ( 'RAM на игровой слот должно быть от 10 до 10000' ) );
													}
												}
											}
										}
										if ( $error == 0 ) {
											$get_sql .= "os='" . (int) $data[ 'os' ] . "',";
											$get_sql .= "eac='" . (int) $data[ 'eac' ] . "',";
											$get_sql .= "ftp='" . (int) $data[ 'ftp' ] . "',";
											$get_sql .= "eac_price='" . (int) $data[ 'eac_price' ] . "',";
											$get_sql .= "eac_dir='" . api::cl ( $data[ 'eac_dir' ] ) . "',";
											$get_sql .= "db_name='" . $data[ 'db' ] . "' where id='" . $box[ 'id' ] . "'";
											db::q ( $get_sql );
											db::q ( 'DELETE from gh_boxes_games where box="' . $box[ 'id' ] . '"' );
											foreach ( servers::$games as $key => $value ) {
												if ( $data[ 'game_' . $key ] == "1" ) {
													if ( $key != "ts3" ) {
														db::q ( "INSERT INTO gh_boxes_games set box='" . $box[ 'id' ] . "',ram='" . (int) $data[ 'game_ram_' . $key ] . "',game='" . $key . "',cpu='" . (int) $data[ 'game_cpu_' . $key ] . "'" );
													}
												}
											}
											api::result ( l::t ( 'Физический сервер сохранен' ) , true );
										}

									}
								}
							}
						}
					}
				}
			}
			$title = l::t ( "Редактирование физического сервера" );
			foreach ( servers::$games as $key => $value ) {
				if ( $key != "ts3" ) {
					tpl::load2 ( 'admin-boxes-add-game' );
					tpl::set ( '{game}' , $key );
					tpl::set ( '{name}' , $value );
					db::q ( 'SELECT * FROM gh_boxes_games where box="' . $box[ 'id' ] . '" and game="' . $key . '" order by id desc' );
					if ( db::n () == 1 ) {
						$row = db::r ();
						tpl::set ( '{cpu}' , $row[ 'cpu' ] );
						tpl::set ( '{ram}' , $row[ 'ram' ] );
						tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "\\1" );
						tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
					} else {
						tpl::set ( '{cpu}' , '' );
						tpl::set ( '{ram}' , '' );
						tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
						tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "\\1" );
					}
				}
				tpl::compile ( 'games' );
			}
			$os = '';
			foreach ( self::$os as $key => $value ) {
				if ( $box[ 'os' ] == $key ) {
					$os .= '<option value="' . $key . '" selected>' . $value . '</option>';
				} else {
					$os .= '<option value="' . $key . '">' . $value . '</option>';
				}

			}
			tpl::load2 ( 'admin-boxes-edit' );
			tpl::set ( '{os}' , $os );
			tpl::set ( '{games}' , tpl::result ( 'games' ) );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{ip}' , $box[ 'ip' ] );
			tpl::set ( '{rip}' , $box[ 'rip' ] );
			tpl::set ( '{port}' , $box[ 'port' ] );
			tpl::set ( '{login}' , $box[ 'login' ] );
			tpl::set ( '{cores}' , $box[ 'cpu' ] );
			tpl::set ( '{login_db}' , $box[ 'db_login' ] );
			tpl::set ( '{ip_db}' , $box[ 'db_ip' ] );
			tpl::set ( '{port_db}' , $box[ 'db_port' ] );
			tpl::set ( '{db}' , $box[ 'db_name' ] );
			tpl::set ( '{eac_dir}' , $box[ 'eac_dir' ] );
			tpl::set ( '{eac_price}' , $box[ 'eac_price' ] );
			if ( $box[ 'eac' ] == "1" ) {
				$eac = '<option value="0">' . l::t ( 'Отключено' ) . '</option><option value="1"selected="selected">' . l::t ( 'Доступно' ) . '</option>';
			} else {
				$eac = '<option value="0" selected="selected">' . l::t ( 'Отключено' ) . '</option><option value="1">' . l::t ( 'Доступно' ) . '</option>';
			}
			tpl::set ( '{eac}' , $eac );
			if ( $box[ 'ftp' ] == "1" ) {
				$eac = '<option value="1" selected="selected">Pure-FTPD</option><option value="0">Pure-FTPD-MySQL</option>';
			} else {
				$eac = '<option value="1">Pure-FTPD</option><option value="0" selected="selected">Pure-FTPD-MySQL</option>';
			}
			tpl::set ( '{ftp}' , $eac );
			$acc = array (
				'0' => l::t ( 'Отключено' ) ,
				'1' => 'Apache2.2' ,
				'3' => 'Apache2.4' ,
				'2' => 'Nginx'
			);
			$fastdl = '';
			foreach ( $acc as $key => $value ) {
				if ( $box[ 'fastdl' ] == $key ) {
					$fastdl .= '<option value="' . $key . '" selected="selected">' . $value . '</option>';
				} else {
					$fastdl .= '<option value="' . $key . '">' . $value . '</option>';
				}
			}
			tpl::set ( '{fastdl}' , $fastdl );
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '/admin/boxes' , l::t ( 'Физические серверы' ) );
				api::nav ( '' , l::t ( 'Редактирование' ) , '1' );
			}
		} else {
			api::result ( l::t ( 'Физический сервер не найден' ) );
		}
	}

	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t ( 'Физические серверы' ) , '1' );
		$sql = db::q ( 'SELECT * FROM gh_boxes order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-boxes-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row[ 'loc' ] . '"' );
			$row2 = db::r ( $sql2 );
			tpl::set ( '{loc}' , $row2[ 'name' ] );
			tpl::set ( '{adress}' , $row[ 'ip' ] . ':' . $row[ 'port' ] );
			if ( $row[ 'power' ] ) {
				tpl::set ( '{color}' , 'blue' );
				tpl::set ( '{icon}' , 'fa fa-check-circle-o' );
				tpl::set ( '{status}' , '1' );
			} else {
				tpl::set ( '{icon}' , 'fa fa-circle-o' );
				tpl::set ( '{color}' , '' );
				tpl::set ( '{status}' , '0' );
			}
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Физические серверы" );
		tpl::load2 ( 'admin-boxes-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		tpl::compile ( 'content' );
	}

	public static function add ()
	{
		global $title;
		api::inc ( 'servers/ftp' );
		api::inc ( 'servers' );
		api::inc ( 'ssh2' );
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if ( api::$demo ) {
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );

				return false;
			}
			$get_sql = "INSERT INTO gh_boxes set ";
			db::q ( 'SELECT * FROM gh_location where id="' . (int) $data[ 'loc' ] . '"' );
			if ( db::n () != 1 ) {
				api::result ( l::t ( 'Локация не найдена' ) );
			} else {
				$get_sql .= "loc='" . (int) $data[ 'loc' ] . "',";
				if ( ! preg_match ( "/^[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}$/i" , $data[ 'rip' ] ) ) {
					api::result ( l::t ( 'Ip адрес введен неверно' ) );
				} else {
					$get_sql .= "ip='" . $data[ 'ip' ] . "',";
					$get_sql .= "rip='" . $data[ 'rip' ] . "',";
					if ( $data[ 'port' ] < 10 || $data[ 'port' ] > 65000 ) {
						api::result ( l::t ( 'Порт разрешено задавать в диапазоне от 10 до 65000' ) );
					} else {
						$get_sql .= "port='" . (int) $data[ 'port' ] . "',";
						$get_sql .= "login='root',";
						if ( ! ssh::connect ( $data[ 'rip' ] , $data[ 'port' ] ) ) {
							api::result ( l::t ( 'Не удалось установить соединение с ' ) . $data[ 'ip' ] . ':' . $data[ 'port' ] );
						} else {
							if ( ! ssh::auth_pwd ( 'root' , $data[ 'pass' ] ) ) {
								api::result ( l::t ( 'Пароль введен неверно' ) );
							} else {
								$get_sql .= "pass='" . $data[ 'pass' ] . "',";
								if ( $data[ 'cores' ] < 100 || $data[ 'cores' ] > 100000 ) {
									api::result ( l::t ( 'CPU должно быть в диапазоне от 50 до 100000' ) );
								} else {
									$get_sql .= "cpu='" . (int) $data[ 'cores' ] . "',";
									if ( ! in_array ( $data[ 'fastdl' ] , array ( 0 , 1 , 2 , 3 ) ) ) {
										api::result ( l::t ( 'Критическая ошибка FastDl' ) );
									} else {
										$get_sql .= "fastdl='" . (int) $data[ 'fastdl' ] . "',";
										$get_sql .= "ftp='" . (int) $data[ 'ftp' ] . "',";
										if ( (int) $data[ 'ftp' ] != "1" ) {
											if ( ! preg_match ( "/^[0-9A-Za-z_=]{3,45}$/i" , $data[ 'login_db' ] ) ) {
												api::result ( l::t ( 'Логин MySQL должен быть от 3 до 45 символов' ) );

												return false;
											} else {
												$get_sql .= "db_login='" . $data[ 'login_db' ] . "',";
												$get_sql .= "db_pass='" . $data[ 'pass_db' ] . "',";
												if ( ! preg_match ( "/^[0-9A-Za-z_=-]{3,45}$/i" , $data[ 'db' ] ) ) {
													api::result ( l::t ( 'Название базы данных MySQL должено быть от 3 до 45 символов' ) );

													return false;
												} else {
													if ( ! preg_match ( "/^[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}$/i" , $data[ 'ip_db' ] ) ) {
														api::result ( l::t ( 'IP адрес MySQL введен не верно' ) );

														return false;
													} else {
														$get_sql .= "db_ip='" . $data[ 'ip_db' ] . "',";
														if ( $data[ 'port_db' ] < 10 || $data[ 'port_db' ] > 65000 ) {
															api::result ( l::t ( 'Порт MySQL разрешено задавать в диапазоне от 10 до 65000' ) );

															return false;
														} else {
															$get_sql .= "db_port='" . $data[ 'port_db' ] . "',";
															if ( ! $dbftp = servers_ftp::ftp_db_conn ( $data[ 'ip_db' ] , $data[ 'port_db' ] , $data[ 'login_db' ] , $data[ 'pass_db' ] , $data[ 'db' ] ) ) {
																return false;
															}
														}
													}
												}
											}
										}
										$error = 0;
										foreach ( servers::$games as $key => $value ) {
											if ( $key != "ts3" ) {
												if ( $data[ 'game_' . $key ] == "1" ) {
													if ( $data[ 'game_cpu_' . $key ] < 1 || $data[ 'game_cpu_' . $key ] > 100 ) {
														$error = 1;
														api::result ( l::t ( 'CPU на игровой слот должно быть от 1 до 100' ) );

														return false;
													}
													if ( $data[ 'game_ram_' . $key ] < 10 || $data[ 'game_ram_' . $key ] > 10000 ) {
														$error = 1;
														api::result ( l::t ( 'RAM на игровой слот должно быть от 10 до 10000' ) );

														return false;
													}
												}
											}
										}
										if ( $error == 0 ) {
											$get_sql .= "os='" . (int) $data[ 'os' ] . "',";
											$get_sql .= "eac='" . (int) $data[ 'eac' ] . "',";
											$get_sql .= "eac_price='" . (int) $data[ 'eac_price' ] . "',";
											$get_sql .= "eac_dir='" . api::cl ( $data[ 'eac_dir' ] ) . "',";
											$get_sql .= "db_name='" . $data[ 'db' ] . "'";
											db::q ( $get_sql );
											$id_b = db::i ();
											foreach ( servers::$games as $key => $value ) {
												if ( $data[ 'game_' . $key ] == "1" ) {
													if ( $key != "ts3" ) {
														db::q ( "INSERT INTO gh_boxes_games set box='" . $id_b . "',ram='" . (int) $data[ 'game_ram_' . $key ] . "',game='" . $key . "',cpu='" . (int) $data[ 'game_cpu_' . $key ] . "'" );
													}
												}
											}
											api::result ( l::t ( 'Физический сервер подключен' ) , true );
										}

									}
								}
							}
						}
					}
				}
			}
		}
		$loc = "";
		$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
		while ( $row2 = db::r ( $sql ) ) {
			$loc .= '<option value="' . $row2[ 'id' ] . '">' . $row2[ 'name' ] . '</option>';
		}
		$title = l::t ( "Новый физический сервер" );
		foreach ( servers::$games as $key => $value ) {
			if ( $key == "ts3" ) {
				continue;
			} else {
				tpl::load2 ( 'admin-boxes-add-game' );
				tpl::set ( '{game}' , $key );
				tpl::set ( '{name}' , $value );
				tpl::set ( '{cpu}' , '' );
				tpl::set ( '{ram}' , '' );
				tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
				tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
				tpl::compile ( 'games' );
			}
		}
		$os = '';
		foreach ( self::$os as $key => $value ) {
			$os .= '<option value="' . $key . '">' . $value . '</option>';
		}
		tpl::load2 ( 'admin-boxes-add' );
		tpl::set ( '{os}' , $os );
		tpl::set ( '{loc}' , $loc );
		tpl::set ( '{games}' , tpl::result ( 'games' ) );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		} else {
			api::nav ( '/admin/boxes' , l::t ( 'Физические серверы' ) );
			api::nav ( '' , l::t ( "Новый физический сервер" ) , '1' );
		}
	}
}

?>