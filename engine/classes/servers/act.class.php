<?php

class servers_act
{
	public static function on ( $id )
	{
		if ( servers::$cron ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			if ( db::n () == 1 ) {
				$row = db::r ();
				if ( $row[ 'time' ] > time () ) {
					if ( $row[ 'status' ] == "2" ) {
						db::q ( "UPDATE gh_servers set status='1' where id='" . $id . "'" );
						sleep ( '5' );
						$class = servers::game_class ( $row[ 'game' ] );
						$class::on ( $id );
					}
				}
			}
		} else {
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'on' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				if ( $row[ 'time' ] < time () ) {
					api::result ( l::t ( 'Срок аренды сервера истек' ) );
				} else {
					$class = servers::game_class ( $row[ 'game' ] );
					if ( $row[ 'status' ] != "2" ) {
						api::result ( l::t ( 'Выполняется другая операция' ) );
					} else {
						if(api::$demo){
							api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
							return false;
						}
						if($row['game']=="ts3"){
							if($class::on ( $id )){
								db::q ( 'DELETE FROM gh_monitoring where sid="' . $id . '"' );
								db::q ( "UPDATE gh_servers set status='1' where id='" . $id . "'" );
								api::result ( l::t ( 'Сервер включен' ) , true );
							} else {
								api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
							}
						}else{
							api::inc ( 'ssh2' );
							if ( ssh::gh_box ( $row[ 'box' ] ) ) {
								ssh::exec_cmd ( 'cd /host/;./kill.sh;' );
								sleep ( 2 );
								$class::on ( $id );
								ssh::disconnect ();
								db::q ( 'DELETE FROM gh_monitoring where sid="' . $id . '"' );
								db::q ( "UPDATE gh_servers set status='1' where id='" . $id . "'" );
								api::result ( l::t ( 'Сервер включен' ) , true );
							} else {
								api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
							}
						}

					}
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}
	}

	public static function off ( $id )
	{
		if ( servers::$cron ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			if ( db::n () == 1 ) {
				$row = db::r ();
				if ( $row[ 'status' ] == "1" ) {
					if ( $row[ 'game' ] == "ts3" ) {
						$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
						$box = db::r ();
						if ( ts3::connect ( $box[ 'ip' ] , $box[ 'port' ] , $box[ 'login' ] , $box[ 'pass' ] ) ) {
							$cmd = "serverstop sid=" . $row[ 'sid' ];
							ts3::cmd ( $cmd );
							db::q ( "UPDATE gh_servers set status='2' where id='" . $id . "'" );
						}
					} else {
						servers::kill_pid_d ();
						servers::kill_pid_all ( $row[ 'sid' ] );
						servers::kill_pid ( servers::get_pid_screen ( $row[ 'sid' ] ) );
						db::q ( "UPDATE gh_servers set status='2' where id='" . $id . "'" );
					}
				}
			}
		} else {
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'off' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				if ( $row[ 'time' ] < time () ) {
					api::result ( l::t ( 'Срок аренды сервера истек' ) );
				} else {
					$class = servers::game_class ( $row[ 'game' ] );
					if ( $row[ 'status' ] != "1" ) {
						api::result ( l::t ( 'Выполняется другая операция' ) );
					} else {
						if(api::$demo){
							api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
							return false;
						}
						if ( $row[ 'game' ] == "ts3" ) {
							api::inc ( 'telnet' );
							$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
							$box = db::r ();
							if ( ts3::connect ( $box[ 'ip' ] , $box[ 'port' ] , $box[ 'login' ] , $box[ 'pass' ] ) ) {
								$cmd = "serverstop sid=" . $row[ 'sid' ];
								if ( ts3::cmd ( $cmd ) ) {
									db::q ( "UPDATE gh_servers set status='2' where id='" . $id . "'" );
									api::result ( l::t ( 'Сервер выключен' ) , true );
								}
							}
						} else {
							api::inc ( 'ssh2' );
							if ( ssh::gh_box ( $row[ 'box' ] ) ) {
								servers::kill_pid_d ();
								servers::kill_pid_all ( $row[ 'sid' ] );
								servers::kill_pid ( servers::get_pid_screen ( $row[ 'sid' ] ) );
								ssh::disconnect ();
								db::q ( "UPDATE gh_servers set status='2' where id='" . $id . "'" );
								api::result ( l::t ( 'Сервер выключен' ) , true );
							} else {
								api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
							}
						}
					}
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}
	}

	public static function restart ( $id )
	{

		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'restart' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $row[ 'status' ] != "1" ) {
					api::result ( l::t ( 'Выполняется другая операция' ) );
				} else {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					if($row['game']=="ts3"){
						api::inc ( 'telnet' );
						$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
						$box = db::r ();
						if ( ts3::connect ( $box[ 'ip' ] , $box[ 'port' ] , $box[ 'login' ] , $box[ 'pass' ] ) ) {
							$cmd = "serverstop sid=" . $row[ 'sid' ];
							ts3::cmd ( $cmd );
							db::q ( 'DELETE FROM gh_monitoring where sid="' . $id . '"' );
						}
						$class::on ( $id );
						api::result ( l::t ( 'Сервер перезапущен' ) , true );
					}else{
						api::inc ( 'ssh2' );
						if ( ssh::gh_box ( $row[ 'box' ] ) ) {
							servers::kill_pid_d ();
							servers::kill_pid_all ( $row[ 'sid' ] );
							servers::kill_pid ( servers::get_pid_screen ( $row[ 'sid' ] ) );
							db::q ( 'DELETE FROM gh_monitoring where sid="' . $id . '"' );
							$class::on ( $id );
							ssh::disconnect ();
							api::result ( l::t ( 'Сервер перезапущен' ) , true );
						} else {
							api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
						}
					}
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function reinstall ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'reinstall' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $row[ 'status' ] != "2" ) {
					api::result ( l::t ( 'Выполняется другая операция' ) );
				} else {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					api::inc ( 'ssh2' );
					if ( ssh::gh_box ( $row[ 'box' ] ) ) {
						if ( $row[ 'game' ] == "ts3" ) {
							api::inc ( 'telnet' );
							$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
							$box = db::r ();
							if ( ts3::connect ( $box[ 'ip' ] , $box[ 'port' ] , $box[ 'login' ] , $box[ 'pass' ] ) ) {
								$cmd = "serverstop sid=" . $row[ 'sid' ];
								ts3::cmd ( $cmd , true );
								$cmd = "serverdelete sid=" . $row[ 'sid' ];
								ts3::cmd ( $cmd , true );
								telnet::disconnect ();
								$ssid = $class::install ( $row[ 'box' ] , $row[ 'rate' ] , $row[ 'slots' ] , $row[ 'port' ] , $row[ 'id' ] );
								db::q ( 'UPDATE gh_servers set sid="' . $ssid . '" where id="' . $row[ 'id' ] . '"' );
								$cmd = "serverstop sid=" . $ssid;
								ts3::cmd ( $cmd , true );
								telnet::disconnect ();
								api::result ( l::t ( 'Сервер переустанавливается' ) , true );
							} else {
								api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
							}
						} else {
							db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
							$rate = db::r ();
							db::q ( 'SELECT os FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
							$box = db::r ();
							if ( $rate[ 'dir' ] == "1" ) {
								$versionsa = json_decode ( $rate[ 'versions' ] , true );
								if ( ! $versionsa[ $_POST[ 'data' ][ 'ver' ] ] ) {
									api::result ( l::t ( 'Версия игры не найдена' ) );

									return false;
								} else {
									db::q ( "DELETE FROM gh_servers_cfg where server='" . $id . "'" );
									$data12[ 'bild' ] = $_POST[ 'data' ][ 'ver' ];
									servers::configure ( $data12 , $id );
									self::reinstall_go ( $row[ 'user' ] , $row[ 'sid' ] , $versionsa[ $_POST[ 'data' ][ 'ver' ] ][ 'dir' ] , $box[ 'os' ] , $row[ 'game' ] );
								}
							} else {
								db::q ( "DELETE FROM gh_servers_cfg where server='" . $id . "'" );
								self::reinstall_go ( $row[ 'user' ] , $row[ 'sid' ] , $rate[ 'dir' ] , $box[ 'os' ] );
							}
							ssh::disconnect ();
							db::q ( "DELETE FROM gh_addons_install where server='" . $id . "'" );
							db::q ( "DELETE FROM maps_install where server='" . $id . "'" );
							db::q ( "UPDATE gh_servers set status='5' where id='" . $id . "'" );

							db::q ( 'SELECT * FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
							$box = db::r ();
							$ftp_ip = ( $box[ 'db_ip' ] != "" ) ? $box[ 'db_ip' ] : $box[ 'ip' ];
							if(!$box['ftp']){
								api::inc ( 'servers/ftp' );
								if ( $dbftp = servers_ftp::ftp_db_conn ( $ftp_ip , $box[ 'db_port' ] , $box[ 'db_login' ] , $box[ 'db_pass' ] , $box[ 'db_name' ] ) ) {
									mysql_query ( "delete from ftpd where user='s" . $row[ 'sid' ] . "'" , $dbftp );

								}
							}
							$class::install ( $row[ 'id' ] );
							api::result ( l::t ( 'Переустановка сервера запущена' ) , true );
						}
					} else {
						api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
					}
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function reinstall_go ( $user , $id , $dir , $os , $game )
	{
		$exec = "cd /host/;mkdir " . $user . ";";
		$exec .= "cd /host/" . $user . "/;";
		if ( $game == "rust" ) {
			$exec .= "cd " . $id . "/;";
			$exec .= "rm -Rf server;";
			$exec .= "cd /host/;";
			$exec .= "screen -dmS reinstall_" . $id . " cp -rv " . $dir . " " . $user . "/" . $id . "/server";
			if ( $os == "1" || $os == "2" ) {
				$exec .= "/;";
			}
		} else {
			$exec .= "rm -Rf " . $id . ";";
			$exec .= "cd /host/;";
			$exec .= "screen -dmS reinstall_" . $id . " cp -rv " . $dir . " " . $user . "/" . $id;
			if ( $os == "1" || $os == "2" ) {
				$exec .= "/;";
			}
		}

		ssh::exec_cmd ( $exec );

	}

	public static function update ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'update' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $row[ 'status' ] != "2" ) {
					api::result ( l::t ( 'Выполняется другая операция' ) );
				} else {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					api::inc ( 'ssh2' );
					if ( $class::info ( 'update' ) ) {
						if ( ssh::gh_box ( $row[ 'box' ] ) ) {
							$class::update ( $row );
							db::q ( "UPDATE gh_servers set status='4' where id='" . $id . "'" );
							api::result ( l::t ( 'Обновление сервера запущено' ) , true );
						} else {
							api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
						}
					} else {
						api::result ( l::t ( 'Данная функция отключена' ) );
					}
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function on_mobile ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				mobile::error ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $row[ 'status' ] != "2" ) {
					mobile::error ( l::t ( 'Выполняется другая операция' ) );
				} else {
					api::inc ( 'ssh2' );
					if ( ssh::gh_box ( $row[ 'box' ] ) ) {
						$class::on ( $id );
						ssh::disconnect ();
						db::q ( 'DELETE FROM gh_monitoring where sid="' . $id . '"' );
						db::q ( "UPDATE gh_servers set status='1' where id='" . $id . "'" );
						mobile::$data[ 'act' ] = l::t ( "Север запущен" );
					} else {
						mobile::error ( l::t ( 'Не удалось установить соединение с сервером' ) );
					}
				}
			}
		} else {
			mobile::error ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function off_mobile ( $id )
	{

		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				mobile::error ( l::t ( 'Срок аренды сервера истек' ) );
			} else {

				$class = servers::game_class ( $row[ 'game' ] );
				if ( $row[ 'status' ] != "1" ) {
					mobile::error ( l::t ( 'Выполняется другая операция' ) );
				} else {
					api::inc ( 'ssh2' );
					if ( ssh::gh_box ( $row[ 'box' ] ) ) {
						servers::kill_pid_d ();
						servers::kill_pid_all ( $row[ 'sid' ] );
						servers::kill_pid ( servers::get_pid_screen ( $row[ 'sid' ] ) );
						ssh::exec_cmd ( "cd /host/" . $row[ 'user' ] . "/;chown -R s" . $row[ 'sid' ] . ":s" . $row[ 'sid' ] . " " . $row[ 'sid' ] . ";chmod -R 755 " . $row[ 'sid' ] . ";" );
						ssh::disconnect ();
						db::q ( "UPDATE gh_servers set status='2' where id='" . $id . "'" );
						mobile::$data[ 'act' ] = l::t ( 'Сервер выключен' );
					} else {
						mobile::error ( l::t ( 'Не удалось установить соединение с сервером' ) );
					}
				}
			}
		} else {
			mobile::error ( l::t ( 'Сервер не найден' ) );
		}
	}

	public static function restart_mobile ( $id )
	{

		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				mobile::error ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $row[ 'status' ] != "1" ) {
					mobile::error ( l::t ( 'Выполняется другая операция' ) );
				} else {
					api::inc ( 'ssh2' );
					if ( ssh::gh_box ( $row[ 'box' ] ) ) {
						if ( $row[ 'game' ] == "ts3" ) {
							api::inc ( 'telnet' );
							$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
							$box = db::r ();
							if ( ts3::connect ( $box[ 'ip' ] , $box[ 'port' ] , $box[ 'login' ] , $box[ 'pass' ] ) ) {
								$cmd = "serverstop sid=" . $row[ 'sid' ];
								ts3::cmd ( $cmd );
								db::q ( 'DELETE FROM gh_monitoring where sid="' . $id . '"' );
							}
						} else {
							servers::kill_pid_d ();
							servers::kill_pid_all ( $row[ 'sid' ] );
							servers::kill_pid ( servers::get_pid_screen ( $row[ 'sid' ] ) );
							db::q ( 'DELETE FROM gh_monitoring where sid="' . $id . '"' );
						}
						$class::on ( $id );
						ssh::disconnect ();
						mobile::$data[ 'act' ] = l::t ( 'Сервер перезапущен' );
					} else {
						mobile::error ( l::t ( 'Не удалось установить соединение с сервером' ) );
					}
				}
			}
		} else {
			mobile::error ( l::t ( 'Сервер не найден' ) );
		}
	}

}

?>