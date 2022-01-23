<?php

$true = true;

class cron
{
	public static $data = array ();

	public static function go ( $id )
	{
		global $conf;
		if(!$conf['dell']){
			$conf['dell'] = 3;
		}
		$sql = db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n ( $sql ) != 0 ) {
			$server = db::r ( $sql );
			self::w ( '	->game ' . $server[ 'game' ] );
			$sql = db::q ( 'SELECT id,td,ip FROM gh_boxes where id="' . $server[ 'box' ] . '"' );
			$box = db::r ( $sql );
			if ( ssh::gh_box ( $box[ 'id' ] ) ) {
				self::w ( '	->box online' );
				$sql233u = db::q ( 'SELECT * FROM users where id="' . $server[ 'user' ] . '"' );
				$user = db::r ( $sql233u );
				if ( $server[ 'time' ] < time () ) {
					if ( $server[ 'status' ] == "1" ) {
						self::w ( "	->offtime" );
						servers_act::off ( $server[ 'id' ] );
						$pm = false;
						$msg = $conf[ 'domain' ] . " ".l::t ('У одной из ваших услуг закончился срок оплаты. Она приостановлена.');
						if ( $user[ 'phone' ] && $conf[ 'sms_time_end' ] ) {
							if ( sms::send ( $user[ 'phone' ] , $msg ) ) {
								$pm = true;
							}
						}
						if ( $pm == false ) {
							tpl::$result['mail'] = "";
							tpl::load('mail-body');
							tpl::set('{title}',$conf['title']);
							$msg = "<h4>".l::t ('Здравствуйте')." ".$user['name']." ".$user['lastname'].",</h4>";
							$msg .= "<p>".l::t ('У одной из ваших услуг закончился срок оплаты. Она приостановлена.')."</p>";
							tpl::set('{content}',$msg);
							tpl::compile('mail');
							mail::send ( $user[ 'mail' ] , l::t ('Отключение услуги') , tpl::result('mail') );
						}
					} elseif ( $server[ 'status' ] == "2" ) {
						if ( ( $server[ 'time' ] + 3600 * 24 * $conf['dell'] ) < time () ) {
							self::w ( "	->del" );
							servers::full_del ( $server[ 'sid' ] , $server[ 'id' ] , $server[ 'user' ] );
							$pm = false;
							$msg = $conf[ 'domain' ] . " ".l::t ('Одна из ваших услуг удалена.');
							if ( $user[ 'phone' ] && $conf[ 'sms_time_del' ] ) {
								if ( sms::send ( $user[ 'phone' ] , $msg ) ) {
									$pm = true;
								}
							}
							if ( $pm == false ) {
								tpl::$result['mail'] = "";
								tpl::load('mail-body');
								tpl::set('{title}',$conf['title']);
								$msg = "<h4>".l::t ('Здравствуйте')." ".$user['name']." ".$user['lastname'].",</h4>";
								$msg .= "<p>".l::t ('Одна из ваших услуг удалена.')."</p>";
								tpl::set('{content}',$msg);
								tpl::compile('mail');
								mail::send ( $user[ 'mail' ] , l::t ('Удаление услуги') , tpl::result('mail') );
							}
						} else {
							self::w ( "	->predel" );
						}
					} else {
						$pm = false;
						$msg = $conf[ 'domain' ] . " ".l::t ('У одной из ваших услуг закончился оплаченный период.');
						if ( $user[ 'phone' ] && $conf[ 'sms_time_pre' ] ) {
							if ( sms::send ( $user[ 'phone' ] , $msg ) ) {
								$pm = true;
							}
						}
						if ( $pm == false ) {
							tpl::$result['mail'] = "";
							tpl::load('mail-body');
							tpl::set('{title}',$conf['title']);
							$msg = "<h4>".l::t ('Здравствуйте')." ".$user['name']." ".$user['lastname'].",</h4>";
							$msg .= "<p>".l::t ('У одной из ваших услуг закончился оплаченный период.')."</p>";
							tpl::set('{content}',$msg);
							tpl::compile('mail');
							mail::send ( $user[ 'mail' ] , l::t ('Напоминание об удалении') , tpl::result('mail') );
						}
						db::q ( "UPDATE gh_servers set status='2' where id='" . $server[ 'id' ] . "'" );
						self::w ( "	->upd-del" );
					}
				} else {
					if ( $server[ 'game' ] == "ts3" ) {
						self::w ( "	->ts3" );

						return false;
					}
					if ( $server[ 'status' ] == "1" ) {
						$pid = '';
						$class = servers::game_class ( $server[ 'game' ] );
						$pid = $class::get_pid ( $server[ 'sid' ] );
						if ( ! $pid ) {
							self::w ( "	->off mon" );
							servers_act::off ( $server[ 'id' ] );
							self::w ( "	->on mon" );
							servers_act::on ( $server[ 'id' ] );
						} else {
							self::w ( "	->mon" );

							if ( ! servers::get_cpu ( $server[ 'sid' ] ) ) {
								servers::set_cpu ( $server[ 'sid' ] , $server[ 'slots' ] , $pid , $server[ 'box' ] , $server[ 'game' ] );
							}
							$cmd = "ps -o \"rss\" -u s" . $server[ 'sid' ];
							ssh::exec_cmd ( $cmd );
							$data = trim ( ssh::get_output () );
							$data = explode ( "\n" , $data );

							unset( $data[ '0' ] );
							$d[ 'cpu' ] = 0;
							$d[ 'mem' ] = 0;
							foreach ( $data as $key => $value ) {
								$d[ 'mem' ] += (int) $value;
							}
							self::w ( "	mem: " . (int) ( $d[ 'mem' ] / 1024 ) . "mb" );
							$cmd = "top  -n 1 -b  | grep s" . $server[ 'sid' ] . " | awk '{ print \$9}';";
							ssh::exec_cmd ( $cmd );
							$data = trim ( ssh::get_output () );
							$data = explode ( "\n" , $data );
							foreach ( $data as $key => $value ) {
								$d[ 'cpu' ] += (int) round ( $value );
							}
							self::w ( "	cpu: " . $d[ 'cpu' ] . "%" );
							$cmd = 'cd /host/' . $server[ 'user' ] . '/' . $server[ 'sid' ] . $class::info ( 'ftp_root' ) . ';du -sk | awk \'{print $1}\';';
							ssh::exec_cmd ( $cmd );
							$data = ssh::get_output ();
							$data = explode ( "\n" , $data );
							$d[ 'hdd' ] = (int) $data[ '0' ];
							self::w ( "	hdd: " . (int) ( $d[ 'hdd' ] / 1024 ) . "mb" );
							servers::configure ( $d , $server[ 'id' ] );
							$class::mon ( $server );
							self::w ( "	->end mon" );
						}
					} else {
						m::d ( 'mon_server_' . $server[ 'id' ] );
						if ( $server[ 'status' ] != "2" ) {
							if ( $server[ 'status' ] == "3" ) {
								$pidn = "install";
							}
							if ( $server[ 'status' ] == "4" ) {
								$pidn = "update";
							}
							if ( $server[ 'status' ] == "5" ) {
								$pidn = "reinstall";
							}
							$pid = "ps -ef | grep SCREEN | grep -v grep | grep " . $pidn . "_" . $server[ 'sid' ] . " | awk '{ print $2}'";
							ssh::exec_cmd ( $pid );
							$pid = ssh::get_output ();
							$pid = explode ( "\n" , $pid );
							if ( $pid[ '0' ] == "" ) {
								self::w ( "	->end " . $pidn );
								ssh::exec_cmd ( "cd /host/" . $server[ 'user' ] . "/;chown -R s" . $server[ 'sid' ] . ":s" . $server[ 'sid' ] . " " . $server[ 'sid' ] . ";chmod -R 755 " . $server[ 'sid' ] . ";" );
								db::q ( "UPDATE gh_servers set status='2' where id='" . $server[ 'id' ] . "'" );
							} else {
								self::w ( "	->wait " . $pidn );
							}
						} else {
							self::w ( "	->offline" );
						}
					}
				}
			} else {
				self::w ( 'box ofline' );
			}
		}
	}

	public static function go_isp ( $id )
	{
		global $conf;
		if(!$conf['dell']){
			$conf['dell'] = 3;
		}
		$sql23223 = db::q ( 'SELECT * FROM isp where boxes="' . $id . '"' );
		while ( $isp = db::r ( $sql23223 ) ) {
			self::w ( '	->isp ' . $isp[ 'id' ] );
			$isp_rate = db::q ( 'SELECT free FROM isp_rates where id="' . $isp[ 'rate' ] . '"' );
			$isp_rate = db::r ( $isp_rate );
			if ( $isp_rate[ 'free' ] == "1" ) {
				$isp_u = db::q ( 'SELECT id FROM gh_servers where user="' . $isp[ 'user' ] . '"' );
				if ( db::n ( $isp_u ) == 0 ) {
					servers_isp::dell ( $isp[ 'id' ] );
					$sql233u = db::q ( 'SELECT * FROM users where id="' . $isp[ 'user' ] . '"' );
					$user = db::r ( $sql233u );
					$pm = false;
					$msg = $conf[ 'domain' ] . " ".l::t ('Одна из ваших услуг удалена.');
					if ( $user[ 'phone' ] && $conf[ 'sms_time_del' ] ) {
						if ( sms::send ( $user[ 'phone' ] , $msg ) ) {
							$pm = true;
						}
					}
					if ( $pm == false ) {
						tpl::$result['mail'] = "";
						tpl::load('mail-body');
						tpl::set('{title}',$conf['title']);
						$msg = "<h4>".l::t ('Здравствуйте')." ".$user['name']." ".$user['lastname'].",</h4>";
						$msg .= "<p>".l::t ('Одна из ваших услуг удалена.')."</p>";
						tpl::set('{content}',$msg);
						tpl::compile('mail');
						mail::send ( $user[ 'mail' ] , l::t ('Удаление услуги') , tpl::result('mail') );
					}
					self::w ( '		->del' );
				}
			} else {
				if ( ( $isp[ 'time' ] + 3600 * 24 * $conf['dell'] ) < time () ) {
					servers_isp::dell ( $isp[ 'id' ] );
					$sql233u = db::q ( 'SELECT * FROM users where id="' . $isp[ 'user' ] . '"' );
					$user = db::r ( $sql233u );
					$pm = false;
					$msg = $conf[ 'domain' ] . " ".l::t ('Одна из ваших услуг удалена.');
					if ( $user[ 'phone' ] && $conf[ 'sms_time_del' ] ) {
						if ( sms::send ( $user[ 'phone' ] , $msg ) ) {
							$pm = true;
						}
					}
					if ( $pm == false ) {
						tpl::$result['mail'] = "";
						tpl::load('mail-body');
						tpl::set('{title}',$conf['title']);
						$msg = "<h4>".l::t ('Здравствуйте')." ".$user['name']." ".$user['lastname'].",</h4>";
						$msg .= "<p>".l::t ('Одна из ваших услуг удалена.')."</p>";
						tpl::set('{content}',$msg);
						tpl::compile('mail');
						mail::send ( $user[ 'mail' ] , l::t ('Удаление услуги') , tpl::result('mail') );
					}
					self::w ( '		->del' );
				}
			}
		}
	}

	public static function go_mysql ( $id )
	{
		global $conf;
		if(!$conf['dell']){
			$conf['dell'] = 3;
		}
		$sql23223 = db::q ( 'SELECT * FROM mysql where boxes="' . $id . '"' );
		while ( $isp = db::r ( $sql23223 ) ) {
			self::w ( '	->mysql ' . $isp[ 'id' ] );
			if ( ( $isp[ 'time' ] + 3600 * 24 * $conf['dell'] ) < time () ) {
				servers_mysql::dell ( $isp[ 'id' ] );
				$sql233u = db::q ( 'SELECT * FROM users where id="' . $isp[ 'user' ] . '"' );
				$user = db::r ( $sql233u );
				$pm = false;
				$msg = $conf[ 'domain' ] . " ".l::t ('Одна из ваших услуг удалена.');
				if ( $user[ 'phone' ] && $conf[ 'sms_time_del' ] ) {
					if ( sms::send ( $user[ 'phone' ] , $msg ) ) {
						$pm = true;
					}
				}
				if ( $pm == false ) {
					tpl::$result['mail'] = "";
					tpl::load('mail-body');
					tpl::set('{title}',$conf['title']);
					$msg = "<h4>".l::t ('Здравствуйте')." ".$user['name']." ".$user['lastname'].",</h4>";
					$msg .= "<p>".l::t ('Одна из ваших услуг удалена.')."</p>";
					tpl::set('{content}',$msg);
					tpl::compile('mail');
					mail::send ( $user[ 'mail' ] , l::t ('Удаление услуги') , tpl::result('mail') );
				}
				self::w ( '		->del' );
			}
		}
	}

	public static function go_ts3 ( $id )
	{
		global $conf;
		if(!$conf['dell']){
			$conf['dell'] = 3;
		}
		$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="'.$id.'"' );
		$box = db::r ( $sql );
		if(ts3::connect ( $box[ 'ip' ] , $box[ 'port' ], $box[ 'login' ], $box[ 'pass' ] )){
			$cmd = "serverlist";
			if ( $data = ts3::cmd ( $cmd ) ) {
				foreach ( $data as $key => $value ) {
					$data2 = explode ( " " , $value );
					$data3 = array ();
					$data4 = array ();
					foreach ( $data2 as $key => $value ) {
						$data4 = explode ( "=" , $value );
						$data3[ $data4[ 0 ] ] = $data4[ 1 ];
					}
					if($data3[ 'virtualserver_id' ]){
						$sql233 = db::q ( 'SELECT * FROM gh_servers where sid="' . $data3[ 'virtualserver_id' ] . '"' );
						if ( db::n ( $sql233 ) == 1 ) {
							$rowjd = db::r ( $sql233 );
							if ( $rowjd[ 'status' ] == "1" ) {
								if ( $rowjd[ 'time' ] < time () ) {
									servers_act::off ( $rowjd[ 'id' ] );
								} else {

								}
								db::q ( 'SELECT * FROM gh_monitoring where sid="' . $rowjd[ "id" ] . '"' );
								if ( db::n () == "1" ) {
									db::q ( "UPDATE gh_monitoring set online='" . api::cl ( $data3[ 'virtualserver_clientsonline' ] ) . "' where sid='" . $rowjd[ "id" ] . "'" );
								} else {
									db::q ( "INSERT INTO gh_monitoring set online='" . api::cl ( $data3[ 'virtualserver_clientsonline' ] ) . "',sid='" . $rowjd[ "id" ] . "'" );
								}
								$ttt = api::gettime();
								$sql123 = db::q ( 'SELECT online FROM gh_monitoring_time where sid="' . $rowjd[ 'id' ] . '" and time="' . $ttt . '"' );
								if ( db::n ( $sql123 ) == "1" ) {
									$time_row = db::r ();
									if ( $time_row[ "online" ] <  api::cl ( $data3[ 'virtualserver_clientsonline' ] )  ) {
										db::q ( 'UPDATE gh_monitoring_time set online="' .  api::cl ( $data3[ 'virtualserver_clientsonline' ] )  . '" where time="' . $ttt . '" and sid="' . $rowjd[ 'id' ] . '"' );
									}
								} elseif ( db::n ( $sql123 ) == "0" ) {
									db::q ( 'INSERT INTO gh_monitoring_time set online="' .  api::cl ( $data3[ 'virtualserver_clientsonline' ] )  . '",time="' . $ttt . '",sid="' . $rowjd[ 'id' ] . '"' );
								}
							}else{
								if ( ( $rowjd[ 'time' ] + 3600 * 24 * $conf['dell'] ) < time () ) {
									$cmd = "serverstop sid=" . $rowjd[ 'sid' ];
									telnet::exec ( $cmd );
									$cmd = "serverdelete sid=" . $rowjd[ 'sid' ];
									telnet::exec ( $cmd );
									if($rowjd['domain']){
										$class = servers::game_class ( $rowjd[ 'game' ] );
										$class::remove_domain( $rowjd[ 'ip' ],$rowjd[ 'port' ],$rowjd[ 'domain' ]);
									}
									db::q ( "delete from gh_servers where id='" . $rowjd[ 'id' ] . "'" );
								}
							}
						}
					}

				}
			}
		}
	}

	public static function start ( $type , $params )
	{
		global $conf;
		if ( ! $params[ 'server' ] && ! $params[ 'isp' ] && ! $params[ 'ts3' ]  && ! $params[ 'mysql' ] ) {
			if ( $type == 0 ) {
				cl::w ( 'RUN MON' );
				$start = 0;
				$sql = db::q ( 'SELECT id FROM gh_servers where game!="ts3"' );
				while ( $server = db::r ( $sql ) ) {
					if ( $start == 10 ) {
						$start = 0;
						self::run ();
						sleep ( 1 );
					} else {
						++ $start;
					}
					self::add (
						array (
							'server' => $server[ 'id' ]
						)
					);
				}
				self::run ();
				sleep ( 1 );
				if ( $conf[ 'key_isp' ] ) {
					$start = 0;
					$sql = db::q ( 'SELECT id FROM  isp_boxes order by id' );
					while ( $box = db::r ( $sql ) ) {
						if ( $start == 4 ) {
							$start = 0;
							self::run ();
							sleep ( 1 );
						} else {
							++ $start;
						}
						self::add (
							array (
								'isp' => $box[ 'id' ]
							)
						);
					}
					self::run ();
					sleep ( 1 );
				}
				sleep ( 1 );
				if ( $conf[ 'key_mysql' ] ) {
					$start = 0;
					$sql = db::q ( 'SELECT id FROM  mysql_boxes order by id' );
					while ( $box = db::r ( $sql ) ) {
						if ( $start == 4 ) {
							$start = 0;
							self::run ();
							sleep ( 1 );
						} else {
							++ $start;
						}
						self::add (
							array (
								'mysql' => $box[ 'id' ]
							)
						);
					}
					self::run ();
					sleep ( 1 );
				}


				$sql123 = db::q ( 'SELECT * FROM gh_boxes_ts3' );
				if ( db::n ( $sql123 ) != 0 ) {
					$start = 0;
					while ( $box = db::r ( $sql123 ) ) {
						if ( $start == 4 ) {
							$start = 0;
							self::run ();
							sleep ( 1 );
						} else {
							++ $start;
						}
						self::add (
							array (
								'ts3' => $box[ 'id' ]
							)
						);

					}
					self::run ();
					sleep ( 1 );
				}
				$sql233 = db::q ( 'SELECT id FROM gh_servers where status="1"' );
				while ( $row = db::r ( $sql233 ) ) {
					$sql2 = db::q ( 'SELECT online FROM gh_monitoring where sid="' . $row[ 'id' ] . '"' );
					$row2 = db::r ( $sql2 );
					db::q ( "UPDATE gh_servers set online='" . $row2[ 'online' ] . "' where id='" . $row[ 'id' ] . "'" );
				}
				cl::w ( 'END MON' );
			} else {
				cl::w ( 'RUN MON' );
				$start = 0;
				$sql = db::q ( 'SELECT id FROM gh_servers  where game!="ts3"' );
				while ( $server = db::r ( $sql ) ) {
					if ( $start == 10 ) {
						$start = 0;
						self::run ();
						sleep ( 1 );
					} else {
						++ $start;
					}
					self::add (
						array (
							'server' => $server[ 'id' ]
						)
					);
				}
				self::run ();
				sleep ( 1 );
				if ( $conf[ 'key_isp' ] ) {
					$start = 0;
					$sql = db::q ( 'SELECT id FROM  isp_boxes order by id' );
					while ( $box = db::r ( $sql ) ) {
						if ( $start == 4 ) {
							$start = 0;
							self::run ();
							sleep ( 1 );
						} else {
							++ $start;
						}
						self::add (
							array (
								'isp' => $box[ 'id' ]
							)
						);
					}
					self::run ();
					sleep ( 1 );
				}
				sleep ( 1 );
				if ( $conf[ 'key_mysql' ] ) {
					$start = 0;
					$sql = db::q ( 'SELECT id FROM  mysql_boxes order by id' );
					while ( $box = db::r ( $sql ) ) {
						if ( $start == 4 ) {
							$start = 0;
							self::run ();
							sleep ( 1 );
						} else {
							++ $start;
						}
						self::add (
							array (
								'mysql' => $box[ 'id' ]
							)
						);
					}
					self::run ();
					sleep ( 1 );
				}

				$sql123 = db::q ( 'SELECT * FROM gh_boxes_ts3' );
				if ( db::n ( $sql123 ) != 0 ) {
					$start = 0;
					while ( $box = db::r ( $sql123 ) ) {
						if ( $start == 4 ) {
							$start = 0;
							self::run ();
							sleep ( 1 );
						} else {
							++ $start;
						}
						self::add (
							array (
								'ts3' => $box[ 'id' ]
							)
						);

					}
					self::run ();
					sleep ( 1 );
				}
				$sql233 = db::q ( 'SELECT id FROM gh_servers where status="1"' );
				while ( $row = db::r ( $sql233 ) ) {
					$sql2 = db::q ( 'SELECT online FROM gh_monitoring where sid="' . $row[ 'id' ] . '"' );
					$row2 = db::r ( $sql2 );
					db::q ( "UPDATE gh_servers set online='" . $row2[ 'online' ] . "' where id='" . $row[ 'id' ] . "'" );
				}
				cl::w ( 'END MON' );
				cl::w ( 'sleep 10s' );
				sleep ( '10' );
				self::start ( 1 , array () );
			}

			return true;
		}
		if ( $params[ 'server' ] ) {
			self::e ();
			self::w ( 'start mon server id ' . $params[ 'server' ] );
			api::inc ( 'MinecraftQuery' );
			api::inc ( 'lgsl' );
			api::inc ( 'mail' );
			api::inc ( 'ssh2' );
			api::inc ( 'servers' );
			api::inc ( 'servers/act' );
			api::inc ( 'telnet' );
			api::inc ( 'sms' );
			servers::$cron = true;
			self::go ( $params[ 'server' ] );
			die( base64_encode ( json_encode ( self::$data , JSON_UNESCAPED_UNICODE ) ) );
		}
		if ( $params[ 'isp' ] ) {
			self::e ();
			self::w ( 'start mon box isp id ' . $params[ 'isp' ] );
			api::inc ( 'mail' );
			api::inc ( 'ssh2' );
			api::inc ( 'sms' );
			api::inc ( 'servers' );
			api::inc ( 'servers/isp' );
			servers_isp::$cron = true;
			servers::$cron = true;
			self::go_isp ( $params[ 'isp' ] );
			die( base64_encode ( json_encode ( self::$data , JSON_UNESCAPED_UNICODE ) ) );
		}
		if ( $params[ 'mysql' ] ) {
			self::e ();
			self::w ( 'start mon box mysql id ' . $params[ 'mysql' ] );
			api::inc ( 'mail' );
			api::inc ( 'sms' );
			api::inc ( 'servers' );
			api::inc ( 'servers/mysql' );
			servers_mysql::$cron = true;
			servers::$cron = true;
			self::go_mysql ( $params[ 'mysql' ] );
			die( base64_encode ( json_encode ( self::$data , JSON_UNESCAPED_UNICODE ) ) );
		}
		if ( $params[ 'ts3' ] ) {
			self::e ();
			self::w ( 'start mon box ts3 id ' . $params[ 'ts3' ] );
			api::inc ( 'servers' );
			api::inc ( 'servers/act' );
			api::inc ( 'mail' );
			api::inc ( 'sms' );
			api::inc ( 'ssh2' );
			api::inc ( 'telnet' );
			servers::$cron = true;
			self::go_ts3 ( $params[ 'ts3' ] );
			die( base64_encode ( json_encode ( self::$data , JSON_UNESCAPED_UNICODE ) ) );
		}
	}

	public static function add ( $params )
	{
		Threads::newThread ( ROOT . '/cron.php' , $params );
	}

	public static function run ()
	{
		while ( false !== ( $result = Threads::iteration () ) ) {
			if ( ! empty( $result ) ) {
				$data = self::parse ( base64_decode ( $result ) );
				foreach ( $data[ 'logs' ] as $var => $key ) {
					if ( count ( $key ) == 1 ) {
						echo $key . "\n";
					} else {
						echo $key[ 'time' ] . "	" . $key[ 'data' ] . "\n";
					}

				}
			}
		}
	}

	public static function w ( $d )
	{
		self::$data[ 'logs' ][ ] = array ( 'time' => date ( "H:i:s" , time () ) , 'data' => $d );
	}

	public static function e ()
	{
		self::$data[ 'logs' ][ ] = "- - - - - - - - - - - - - - - - - - - - -";
	}

	public static function parse ( $data )
	{
		$data2 = json_decode ( $data , true );
		switch ( json_last_error () ) {
			case JSON_ERROR_NONE:
				return $data2;
				break;
			case JSON_ERROR_DEPTH:
				$error = 'Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				$error = 'Неопознанная ошибка';
				break;
		}
		cl::w ( 'Ошибка парсинга ответа: ' . $error );

		return false;
	}
}

?>