<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_cs
{
	public static $engine = false;

	public static $rules = array (
		'on'        => 'Включение сервера' ,
		'off'       => 'Выключение сервера' ,
		'restart'   => 'Перезагрузка сервера' ,
		'settings'  => 'Управление настройками' ,
		'reinstall' => 'Переустановка сервера' ,
		'buy'       => 'Продление сервера' ,
		'ftp'       => 'Управление FTP' ,
		'modules'   => 'Управление модулями' ,
		'maps'      => 'Управление картами' ,
		'fastdl'    => 'Управление Fast DL' ,
		'eac'       => 'Управление EAC' ,
		'rise'      => 'Управление раскрутками' ,
		'friends'   => 'Управление друзьями' ,
		'console'   => 'Управление консолью',
		'slots'		=> 'Изменение слотов',
		'sale'		=> 'Управление админами'
	);

	public static function engine ()
	{
		if ( ! self::$engine ) {
			self::$engine = true;
			include_once ( ROOT . '/engine/classes/source-engine.php' );
		}
	}

	public static function info ( $data )
	{
		global $conf;
		$cfg[ 'update' ] = 0;
		$cfg[ 'online' ] = 1;
		$cfg[ 'repository' ] = 1;
		$cfg[ 'fastdl' ] = 1;
		$cfg[ 'fps' ] = 1;
		$cfg[ 'reinstall' ] = 1;
		$cfg[ 'friends' ] = 1;
		$cfg[ 'ftp' ] = 1;
		$cfg[ 'maps2' ] = 'cstrike/maps/';
		$cfg[ 'maps3' ] = 1;
		$cfg[ 'maps' ] = 1;
		$cfg[ 'sale' ] = 1;
		$cfg[ 'rcon_kb' ] = 1;
		$cfg[ 'console' ] = 1;
		$cfg[ 'settings' ] = 1;
		$cfg[ 'settings2' ] = 1;
		$cfg[ 'settings_servercfg' ] = "/cstrike/server.cfg";
		$cfg[ 'settings_motd' ] = "/cstrike/motd.txt";
		$cfg[ 'tv' ] = 1;
		$cfg[ 'eac' ] = 1;
		$cfg[ 'eac_dir' ] = "servers/hl1";
		$cfg[ 'ftp_root' ] = "/cstrike/";
		$cfg[ 'tv_dir' ] = "cstrike/";
		

		return $cfg[ $data ];
	}

	public static function install ( $id )
	{
		$data[ 'map' ] = 'de_dust2';
		$data[ 'rcon' ] = api::generate_password ( '10' );
		$data[ 'tickrate' ] = '66';
		servers::configure ( $data , $id );
	}

	public static function on ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$server = db::r ();
		db::q ( 'SELECT * FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
		$rows = db::r ();
		$cfg = servers::cfg ( $id );
		$sid = $server[ 'sid' ];
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "sudo -u s" . $sid . " echo '10'>steam_appid.txt;";
		$exec .= "cd ..;chown -R s" . $sid . ":s" . $sid . " " . $sid . ";chmod -R 771 " . $sid . ";";
		ssh::exec_cmd ( $exec );
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= 'sed -i "/rcon_password/d" "cstrike/server.cfg";';
		$exec .= 'sed -i "/adminpassword/d" "hltv.cfg";';
		$exec .= "echo \"adminpassword " . $cfg[ 'rcon' ] . "\">>\"hltv.cfg\";";
		$exec .= "chmod 755 hlds_run;";
		$exec .= "screen -dmS server_" . $sid . " ";
		$exec .= "sudo -u s" . $sid . " ./hlds_run ";
		$exec .= "-game cstrike -norestart -nohltv +servercfgfile server.cfg +sv_lan 0 +ip " . servers::ip_server2($server['box']) . " +port " . $server[ 'port' ] . " ";
		$exec .= " +maxplayers " . $server[ 'slots' ] . " +map " . $cfg[ 'map' ] . " +clientport 40000 ";
		if ( $cfg[ 'pass' ] ) {
			$exec .= " +sv_password " . $cfg[ 'pass' ] . "";
		}
		if ( $cfg[ 'vac' ] ) {
			$exec .= " -insecure";
		}
		if ( $cfg[ 'rcon' ] ) {
			$exec .= " +rcon_password " . $cfg[ 'rcon' ] . "";
		}
		if ( $rows[ 'fps' ] ) {
			$exec .= "  +fps_max " . $rows[ 'fps' ] . "";
		}
		if ( $rows[ 'fps' ] ) {
			$exec .= "  +sys_ticrate " . ( $rows[ 'fps' ] + 100 ) . " -sys_ticrate " . ( $rows[ 'fps' ] + 100 ) . "";
		}
		$exec .= " " . $rows[ 'plus' ];
		$exec .= ";";
		ssh::exec_cmd ( $exec );
		sleep ( '2' );
		$pid = self::get_pid ( $sid );
		if ( $pid ) {
			servers::set_cpu ( $sid , $server[ 'slots' ] , $pid , $server[ 'rate' ] , $server[ 'game' ] );
			sleep ( '2' );
			servers::get_pid_screen ( $sid );
		}
		if ( $cfg[ 'tv_time' ] > time () ) {
			if ( $cfg[ 'tv' ] == 1 ) {
				$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
				$exec .= 'echo "#!/bin/sh">"start.sh";';
				$exec .= 'echo "sleep 10;">>"start.sh";';
				$exec .= 'echo "LD_LIBRARY_PATH=/host/' . $server[ 'user' ] . '/' . $sid . '/; export LD_LIBRARY_PATH">>"start.sh";';
				$exec .= 'echo "./hltv +connect ' . servers::ip_server2($server['box']) . ':' . $server[ 'port' ] . ' -port ' . ( $server[ 'port' ] + 10000 ) . ' +maxclients ' . $cfg[ 'tv_slots' ] . ' +exec cstrike/hltv.cfg ">>"start.sh";';
				$exec .= "chmod 755 start.sh;";
				$exec .= "screen -dmS server_tv_" . $sid . " sudo -u s" . $sid . " ./start.sh";
				ssh::exec_cmd ( $exec );
			}
		}
	}

	public static function update ( $data )
	{
		self::engine ();
		source_engine::update ( $data , '90' );
	}

	public static function get_pid ( $id )
	{
		db::q ( 'SELECT rate,id FROM gh_servers where sid="' . $id . '"' );
		$server = db::r ();
		db::q ( 'SELECT tipe,versions FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
		$rows = db::r ();

		if( $rows[ 'versions' ]){
			$cfg = servers::cfg ( $server['id'] );
			$versionsa = json_decode ( $rows[ 'versions' ] , true );
			$rows[ 'tipe' ] = $versionsa[$cfg['bild']]['type'];
		}
		if ( $rows[ 'tipe' ] == 0 ) {
			ssh::exec_cmd ( "ps -ef  | grep s" . $id . " | grep -v sudo | grep -v screen | grep -v hlds_run | grep hlds | awk '{ print $3}';" );
		} else {
			ssh::exec_cmd ( "ps -ef  | grep s" . $id . " | grep -v sudo | grep -v screen | grep -v hlds_run | grep hlds | awk '{ print $3}';" );
		}
		$data = trim ( ssh::get_output () );
		$data = explode ( "\n" , $data );
		if ( count ( $data ) > 1 ) {
			servers::kill_pid ( $data );

			return false;
		} else {
			return $data[ 0 ];
		}
	}

	public static function mon ( $data )
	{
		global $conf;
		self::admins_reload($data['id']);
		api::inc ( 'lgsl' );
		$server = lgsl_query_live ( "halflife" , $data[ "ip" ] , $data[ "port" ] , $data[ "port" ] , $data[ "port" ] , "sep" );
		$server = servers::convert ( $server , servers::detect ( $server ) );
		$status = servers::status ( $server[ 'b' ][ 'status' ] , $server[ 's' ][ 'password' ] , @$server[ 'b' ][ 'pending' ] );
		if ( $status != "0" && trim ( $server[ 's' ][ 'name' ] ) ) {
			$ttt = api::gettime ();
			$cfg = servers::cfg ( $data[ 'id' ] );
			$sql344 = db::q ( 'SELECT * FROM gh_boxes_games where box="' . $data[ 'box' ] . '" and game="' . $data[ 'game' ] . '"' );
			$rows344 = db::r ( $sql344 );

			$pcpu = (int) ( ( 100 / ( $rows344[ 'cpu' ] * $server[ 'slots' ] ) ) * $cfg[ 'cpu' ] );
			$sql123 = db::q ( 'SELECT cpu FROM gh_monitoring_cpu_time where sid="' . $data[ 'id' ] . '" and time="' . $ttt . '"' );
			if ( db::n ( $sql123 ) == "1" ) {
				$time_row = db::r ( $sql123 );
				if ( $time_row[ "cpu" ] < $pcpu ) {
					db::q ( 'UPDATE gh_monitoring_cpu_time set cpu="' . $pcpu . '" where time="' . $ttt . '" and sid="' . $data[ 'id' ] . '"' );
				}
			} elseif ( db::n ( $sql123 ) == "0" ) {
				db::q ( 'INSERT INTO gh_monitoring_cpu_time set cpu="' . $pcpu . '",time="' . $ttt . '",sid="' . $data[ 'id' ] . '"' );
			}

			$pram = (int) ( 100 / $rows344[ 'ram' ] * (int) ( $cfg[ 'mem' ] / 1024 ) );
			$sql123 = db::q ( 'SELECT ram FROM gh_monitoring_ram_time where sid="' . $data[ 'id' ] . '" and time="' . $ttt . '"' );
			if ( db::n ( $sql123 ) == "1" ) {
				$time_row = db::r ( $sql123 );
				if ( $time_row[ "ram" ] < $pram ) {
					db::q ( 'UPDATE gh_monitoring_ram_time set ram="' . $pram . '" where time="' . $ttt . '" and sid="' . $data[ 'id' ] . '"' );
				}
			} elseif ( db::n ( $sql123 ) == "0" ) {
				db::q ( 'INSERT INTO gh_monitoring_ram_time set ram="' . $pram . '",time="' . $ttt . '",sid="' . $data[ 'id' ] . '"' );
			}

			$sqlasd = db::q ( 'SELECT * FROM gh_rates where id="' . $data[ 'rate' ] . '"' );
			$rowsasd = db::r ( $sqlasd );
			$phdd = (int) ( 100 / $rowsasd[ 'hard' ] * (int) ( $cfg[ 'hdd' ] / 1024 ) );
			$sql123 = db::q ( 'SELECT hdd FROM gh_monitoring_hdd_time where sid="' . $data[ 'id' ] . '" and time="' . $ttt . '"' );
			if ( db::n ( $sql123 ) == "1" ) {
				$time_row = db::r ( $sql123 );
				if ( $time_row[ "hdd" ] < $phdd ) {
					db::q ( 'UPDATE gh_monitoring_hdd_time set hdd="' . $phdd . '" where time="' . $ttt . '" and sid="' . $data[ 'id' ] . '"' );
				}
			} elseif ( db::n ( $sql123 ) == "0" ) {
				db::q ( 'INSERT INTO gh_monitoring_hdd_time set hdd="' . $phdd . '",time="' . $ttt . '",sid="' . $data[ 'id' ] . '"' );
			}

			$sql123 = db::q ( 'SELECT online FROM gh_monitoring_time where sid="' . $data[ 'id' ] . '" and time="' . $ttt . '"' );
			if ( db::n ( $sql123 ) == "1" ) {
				$time_row = db::r ();
				if ( $time_row[ "online" ] < $server[ 's' ][ 'players' ] ) {
					db::q ( 'UPDATE gh_monitoring_time set online="' . $server[ 's' ][ 'players' ] . '" where time="' . $ttt . '" and sid="' . $data[ 'id' ] . '"' );
				}
			} elseif ( db::n ( $sql123 ) == "0" ) {
				db::q ( 'INSERT INTO gh_monitoring_time set online="' . $server[ 's' ][ 'players' ] . '",time="' . $ttt . '",sid="' . $data[ 'id' ] . '"' );
			}

				$cfg = servers::cfg ( $data[ 'id' ] );
				try {
					$data1 = '';
					api::inc ( 'SourceQuery/SourceQuery' );
					$r = new SourceQuery();
					$r->Connect($data['ip'], $data['port'],1,0);
					if(!$r->SetRconPassword( $cfg['rcon'])){
						api::result ( 'Не удалось авторизоваться' );
					}
					$data1 = $r->Rcon("status");
					$r->Disconnect();
					define( 'STATUS_PARSE' , '/#([ ])?+([0-9 ]+)[ ]+"(.+)"[ ]+([0-9 ]+)[ ]+(STEAM_[0-9]:[0-9]:[0-9]+)[ ]+([0-9]+)[ ]+([0-9.:]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9.:]+)/' );
					$search = preg_match_all ( STATUS_PARSE , $data1 , $matches , PREG_PATTERN_ORDER );
					$score = array ();
					foreach ( $server[ 'p' ] as $player_key => $player ) {
						if ( $player[ 'name' ] != "" ) {
							$name2 = $player[ 'name' ];
						}else{
							$name2 = 'offline';
						}
						$score[ $name2 ] = $player[ 'score' ];
					}
					$u = array ();
					$u[ 'name' ] = l::t('Имя');
					$u[ 'steam' ] = l::t('Steam ID');
					$u[ 'ip' ] = l::t('IP');
					$u[ 'ping' ] = l::t('Пинг');
					$u[ 'score' ] = l::t('Счет');
					$u[ 'time' ] = l::t('Время');
					$u[ 'title' ] = '1';
					$sp[ ] = $u;

					foreach ( $matches[ 2 ] as $key => $value ) {
						if ( $value ) {
							$u = array ();
							$u[ 'name' ] = api::cl ( $matches[ 3 ][ $key ] );
							$u[ 'steam' ] = $matches[ 5 ][ $key ];
							$u[ 'ip' ] = $matches[ 10 ][ $key ];
							$u[ 'ping' ] = (int) $matches[ 8 ][ $key ];
							$u[ 'score' ] = (int) $score[ $matches[ 3 ][ $key ] ];
							$u[ 'time' ] = $matches[ 7 ][ $key ];
							$sp[ ] = $u;
						}
					}
				} catch ( Exception $e ) {
					$e = $e->getMessage ();
				}

			$sp = base64_encode ( json_encode ( $sp ) );
			if ( $status == "2" ) {
				$stats = 1;
			} else {
				$stats = 0;
			}
			if ( servers::$cron ) {
				cron::w ( "	online: " . (int) $server[ 's' ][ 'players' ] );
			}
			db::q ( 'SELECT * FROM gh_monitoring where sid="' . $data[ "id" ] . '"' );
			if ( db::n () == "1" ) {
				db::q ( "UPDATE gh_monitoring set name='" . api::cl ( $server[ 's' ][ 'name' ] ) . "',map='" . api::cl ( $server[ 's' ][ 'map' ] ) . "',online='" . api::cl ( $server[ 's' ][ 'players' ] ) . "',gamers='" . $sp . "',guard='" . $stats . "' where sid='" . $data[ "id" ] . "'" );
			} else {
				db::q ( "INSERT INTO gh_monitoring set name='" . api::cl ( $server[ 's' ][ 'name' ] ) . "',map='" . api::cl ( $server[ 's' ][ 'map' ] ) . "',online='" . api::cl ( $server[ 's' ][ 'players' ] ) . "',gamers='" . $sp . "',sid='" . $data[ "id" ] . "',guard='" . $stats . "'" );
			}
			db::q ( "UPDATE gh_servers set name='" . api::cl ( $server[ 's' ][ 'name' ] ) . "' where id='" . $data[ "id" ] . "'" );
		}
	}

	public static function settings ( $data )
	{
		self::engine ();

		return source_engine::settings ( $data , "/cstrike/maps/" );
	}

	public static function fastdl_on ()
	{
		fastdl::data ( 'cstrike/sound' , 'sound' );
		fastdl::data ( 'cstrike/models' , 'models' );
		fastdl::data ( 'cstrike/materials' , 'materials' );
		fastdl::data ( 'cstrike/maps' , 'maps' );
		fastdl::data ( 'cstrike/sprites' , 'sprites' );
	}

	public static function rcon_bk ( $data )
	{
		global $conf;
		if ( $_POST[ 'data' ] ) {
			$cfg = servers::cfg ( $data[ 'id' ] );
			try {
				api::inc ( 'SourceQuery/SourceQuery' );
				$r = new SourceQuery();
				$r->Connect($data['ip'], $data['port'],1,0);
				if(!$r->SetRconPassword( $cfg['rcon'])){
					api::result ( l::t('Не удалось авторизоваться') );
					return false;
				}
				$name = $_POST[ 'data' ][ 'name' ];
				if ( $_POST[ 'data' ][ 't' ] == "2" ) {
					$d = $r->Rcon( "removeid " . $name);

					$row_gamers = json_decode ( base64_decode ( $cfg[ 'baneds' ] ) , true );
					foreach ( $row_gamers as $key => $value ) {
						if ( $value[ 'steam' ] == $name ) {
							continue;
						}
						foreach ( $value as $key2 => $value2 ) {
							$ban2[ $key2 ] = $value2;
						}
						$bans[ ] = $ban2;
					}
					$dat[ 'baneds' ] = base64_encode ( json_encode ( $bans ) );
					servers::configure ( $dat , $data[ 'id' ] );
					$r->Disconnect();
					api::result ( l::t('Игрок разбанен') , true );

					return true;
				}
				$data1 = $r->Rcon( "status");
				define( 'STATUS_PARSE' , '/#([ ])?+([0-9 ]+)[ ]+"(.+)"[ ]+([0-9 ]+)[ ]+(STEAM_[0-9]:[0-9]:[0-9]+)[ ]+([0-9]+)[ ]+([0-9.:]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9.:]+)/' );
				$search = preg_match_all ( STATUS_PARSE , $data1 , $matches , PREG_PATTERN_ORDER );
				$i = 0;
				$found = false;
				$index = - 1;
				foreach ( $matches[ 2 ] AS $match ) {
					if ( $match == $name ) {
						$found = true;
						$index = $i;
						break;
					}
					$i ++;
				}
				if ( $found ) {
					$steam = $matches[ 4 ][ $index ];
					$class = servers::game_class ( $data[ 'game' ] );
					if ( $_POST[ 'data' ][ 't' ] == "1" ) {
						$d = $r->Rcon( "banid 60 " . $steam . " kick " );
						self::mon ( $data );
						if ( $cfg[ 'baneds' ] ) {
							$bans = json_decode ( base64_decode ( $cfg[ 'baneds' ] ) , true );
						} else {
							$bans = array ();
						}
						$ban[ 'name' ] = api::cl ( $matches[ 3 ][ $index ] );
						$ban[ 'steam' ] = $matches[ 5 ][ $index ];
						$ban[ 'ip' ] = $matches[ 9 ][ $index ];
						$ban[ 'time' ] = time ();
						$bans[ ] = $ban;
						$dat[ 'baneds' ] = base64_encode ( json_encode ( $bans ) );
						servers::configure ( $dat , $data[ 'id' ] );
						$r->Disconnect();
						api::result ( l::t('Игрок забанен на 60 минут') , true );
					} else {
						$d = $r->Rcon("kick #" . $steam . " kick" );
						$r->Disconnect();
						self::mon ( $data );
						api::result ( l::t('Игрок кикнут') , true );
					}
				} else {
					api::result ( l::t('Игрок не найден') );
				}
			} catch ( Exception $e ) {
				api::ajax_d ( $e->getMessage () );
			}

		}
	}

	public static function maps ( $data )
	{
		self::engine ();

		return source_engine::maps ( $data , "/cstrike/maps/" );
	}

	public static function maps_go ( $map )
	{
		return 'changelevel ' . $map;
	}

	public static function tv ( $data )
	{
		db::q ( 'SELECT * FROM gh_rates where id="' . $data[ 'rate' ] . '"' );
		$rate = db::r ();
		$cfg = servers::cfg ( $data[ 'id' ] );
		$adress = servers::ip_server($data['box']) . ':' . $data[ 'port' ];
		$data2 = $_POST[ 'data' ];
		if ( $data2 ) {
			switch ( $data2[ 'act' ] ) {
				case "1" :
					if ( in_array ( $data2[ 'time' ] , array ( 0 , 1 , 3 , 6 , 12 ) ) ) {
						if ( $data2[ 'slots' ] >= 10 && $data2[ 'slots' ] <= 255 ) {
							if ( ! api::admin ( 'puy_servers' ) ) {
								if ( $data2[ 'time' ] == 0 ) {
									$price = (int) ( ( $data[ 'time' ] - time () ) / 86400 ) * (int) $data2[ 'slots' ] * ( $rate[ 'tv_slots' ] / 30 );
								} else {
									$price = (int) ( $rate[ 'tv_slots' ] * (int) $data2[ 'slots' ] * (int) $data2[ 'time' ] );
								}
								if ( api::info ( 'balance' ) < $price ) {
									api::result ( l::t('Недостаточно средств на счете') );
									break;
								}
							}
							if ( ! api::admin ( 'puy_servers' ) ) {
								$msg = l::t("Приобретение TV для").' ' . $adress;
								api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
								db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
							}
							if ( $data2[ 'time' ] == 0 ) {
								$d[ 'tv_time' ] = $data[ 'time' ];
							} else {
								$d[ 'tv_time' ] = (int) ( time () + 86400 * 30 * $data2[ 'time' ] );
							}
							$d[ 'tv' ] = 1;
							$d[ 'tv_slots' ] = (int) $data2[ 'slots' ];
							servers::configure ( $d , $data[ 'id' ] );
							api::result ( l::t('Оплачено') , true );
						} else {
							api::result ( l::t('Критическая ошибка') );
						}
					} else {
						api::result ( l::t('Критическая ошибка') );
					}
					break;
				case "2" :
					if ( in_array ( $data2[ 'time' ] , array ( 0 , 1 , 3 , 6 , 12 ) ) ) {
						if ( $data2[ 'slots' ] >= 10 && $data2[ 'slots' ] <= 255 ) {
							if ( ! api::admin ( 'puy_servers' ) ) {
								$pd = (int) ( ( $cfg[ 'tv_time' ] - time () ) / 86400 );
								$pd2 = $rate[ 'tv_slots' ] / 30;
								if ( $data2[ 'time' ] == 0 ) {
									if ( $cfg[ 'tv_slots' ] > $data2[ 'slots' ] ) {
										$price = (int) ( ( $pd * $cfg[ 'tv_slots' ] * $pd2 ) - ( $pd * $data2[ 'slots' ] * $pd2 ) );
										$t = 1;
									} else {
										$price = (int) ( ( $pd * $data2[ 'slots' ] * $pd2 ) - ( $pd * $cfg[ 'tv_slots' ] * $pd2 ) );
										if ( api::info ( 'balance' ) < $price ) {
											api::result ( l::t('Недостаточно средств на счете') );
											break;
										}
										$t = 0;
									}
								} else {
									if ( $cfg[ 'tv_slots' ] != $data2[ 'slots' ] ) {
										if ( $cfg[ 'tv_slots' ] > $data2[ 'slots' ] ) {
											$price2 = (int) ( ( $pd * $cfg[ 'tv_slots' ] * $pd2 ) - ( $pd * $data2[ 'slots' ] * $pd2 ) );
											$price = (int) ( $rate[ 'tv_slots' ] * (int) $data2[ 'slots' ] * (int) $data2[ 'time' ] );
											$price = $price - $price2;
											if ( $price < 0 ) {
												$t = 1;
												$price = str_replace ( "-" , "" , $price );
											} else {
												if ( api::info ( 'balance' ) < $price ) {
													api::result ( l::t('Недостаточно средств на счете') );
													break;
												}
												$t = 0;
											}
										} else {
											$price2 = (int) ( ( $pd * $data2[ 'slots' ] * $pd2 ) - ( $pd * $cfg[ 'tv_slots' ] * $pd2 ) );
											$price = (int) ( $rate[ 'tv_slots' ] * (int) $data2[ 'slots' ] * (int) $data2[ 'time' ] );
											$price = $price + $price2;
											if ( api::info ( 'balance' ) < $price ) {
												api::result ( l::t('Недостаточно средств на счете') );
												break;
											}
											$t = 0;
										}
									} else {
										$price = (int) ( $rate[ 'tv_slots' ] * (int) $data2[ 'slots' ] * (int) $data2[ 'time' ] );
										if ( api::info ( 'balance' ) < $price ) {
											api::result ( l::t('Недостаточно средств на счете') );
											break;
										}
										$t = 0;
									}
								}
							}
							if ( ! api::admin ( 'puy_servers' ) ) {
								if ( $t == 1 ) {
									$msg = l::t("Перерасчет TV для ").' ' . $adress;
									api::log_balance ( api::info ( 'id' ) , $msg , '0' , $price );
									db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) + $price ) . '" where id="' . api::info ( 'id' ) . '"' );
								} else {
									$msg = l::t("Продление TV для").' ' . $adress;
									api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
									db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
								}
							}
							if ( $data2[ 'time' ] == 0 ) {
								$d[ 'tv_time' ] = $data[ 'time' ];
							} else {
								$d[ 'tv_time' ] = (int) ( time () + 86400 * 30 * $data2[ 'time' ] );
							}
							$d[ 'tv' ] = 1;
							$d[ 'tv_slots' ] = (int) $data2[ 'slots' ];
							servers::configure ( $d , $data[ 'id' ] );
							api::result ( l::t('Оплачено') , true );
						} else {
							api::result ( l::t('Критическая ошибка') );
						}
					} else {
						api::result ( l::t('Критическая ошибка') );
					}
					break;
				case "3" :
					if ( $data2[ 'on' ] == 1 ) {
						$d[ 'tv' ] = 1;
					} else {
						$d[ 'tv' ] = 0;
					}
					servers::configure ( $d , $data[ 'id' ] );
					api::result ( l::t('Сохранено') , true );
					break;
			}
		}
		tpl::load ( 'servers-game-cs-tv' );
		$adress = servers::ip_server($data['box']) . ':' . ( $data[ 'port' ] + 10000 );
		tpl::set ( '{adress}' , $adress );
		tpl::set ( '{price}' , $rate[ 'tv_slots' ] );
		tpl::set ( '{id}' , $data[ 'id' ] );
		if ( $cfg[ 'tv_time' ] < time () ) {
			tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "\\1" );
			tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
			for ( $i = 10 ; $i <= 255 ; $i ++ ) {
				$slots .= '<option value="' . $i . '">' . $i . '</option>';
			}
			tpl::set ( '{price_d}' , (int) ( ( $data[ 'time' ] - time () ) / 86400 ) );
			tpl::set ( '{price_d2}' , $rate[ 'tv_slots' ] / 30 );
			tpl::set ( '{slots}' , $slots );
		} else {
			tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
			tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "\\1" );
			tpl::set ( '{slot}' , $cfg[ 'tv_slots' ] );
			tpl::set ( '{price_d}' , (int) ( ( $cfg[ 'tv_time' ] - time () ) / 86400 ) );
			tpl::set ( '{price_d2}' , $rate[ 'tv_slots' ] / 30 );
			tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $cfg[ 'tv_time' ] ) );
			for ( $i = 10 ; $i <= 255 ; $i ++ ) {
				if ( $cfg[ 'tv_slots' ] == $i ) {
					$slots .= '<option value="' . $i . '" selected>' . $i . '</option>';
				} else {
					$slots .= '<option value="' . $i . '">' . $i . '</option>';
				}
			}
			if ( $cfg[ 'tv' ] == 1 ) {
				tpl::set ( '{checked}' , 'checked' );
			} else {
				tpl::set ( '{checked}' , '' );
			}
			tpl::set ( '{slots}' , $slots );
		}
		tpl::compile ( 'content' );
	}
	public static function admins_reload($id){
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$row = db::r ();
		api::inc ( 'ssh2' );
		$true = 0;
		if ( !servers::$cron ) {
			if ( ssh::gh_box ( $row[ 'box' ] ) ) {
				$true = 1;
			}
		}else{
			$true = 1;
		}
		if($true){
			$file = time().api::generate_password ( '6' );
			$data = "";
			$sql = db::q ( 'SELECT * FROM gh_servers_admins where server="'.$id.'" order by id desc' );
			if(db::n($sql)!=0){
				$act = 0;
				while ( $row2 = db::r ( $sql ) ) {
					if($row2['time']<time()){
						$act = 1;
						continue;
					}
					$sql2 = db::q ( 'SELECT * FROM gh_servers_admins_rates where id="'.$row2['rate'].'" order by id desc' );
					$row3 = db::r($sql2);
					$data .="\"".$row2['login']."\" \"".$row2['pass']."\" \"".$row3['flags']."\" ";
					if($row2['type']==1){
						$data .= "\"ca\"\n";
					}
					if($row2['type']==2){
						$data .= "\"a\"\n";
					}
					if($row2['type']==3){
						$data .= "\"de\"\n";
					}
				}
				$file2 = fopen ( ROOT . '/conf/'.$file , "w" );
				fputs ( $file2 , $data );
				fclose ( $file2 );
				ssh::send_file ( ROOT . '/conf/'.$file , "/host/" . $row[ 'user' ] . "/" . $row[ 'sid' ] . "/cstrike/addons/amxmodx/configs/users.ini", 0777 );
				unlink(ROOT . '/conf/'.$file);
				$comand = "amx_reloadadmins";
				$exec = 'screen -S server_' . $row[ 'sid' ] . ' -p 0 -X stuff \'' . $comand . '\'$\'\n\';';
				ssh::exec_cmd ( $exec );
			}
		}
	}
	public static $servercfg     = array (
		array (
			'name' => 'Название вашего сервера, это название будет отображаться во вкладке Интернет в игре.' ,
			'var'  => 'hostname' ,
			'default' => 'Counter-Strike',
			'type' => '2'
		) ,
		array (
			'name' => 'Ввести логи сервера.' ,
			'var'  => 'log' ,
			'type' => '1' ,
			'default' => 'on',
			'val'  => array (
				'on'  => 'Да' ,
				'off' => 'Нет'
			)
		) ,
		array (
			'name' => 'Записывать в лог баны.' ,
			'var'  => 'sv_logbans' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Показывать информацию из логов сервера в консоль.' ,
			'var'  => 'sv_logecho' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Записывать логи сервера в файлы.' ,
			'var'  => 'sv_logfile' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Записывать логи сервера в один файл.' ,
			'var'  => 'sv_log_onefile' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Писать в логи чат игроков для последующих разборок.' ,
			'var'  => 'mp_logmessages' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Записывать в лог повреждения.' ,
			'var'  => 'mp_logdetail' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'0'  => 'выключено' ,
				'1'  => 'от противников' ,
				'2'  => 'от своих' ,
				'3' => 'от противников и от своих'
			)
		) ,
		array (
			'name' => 'Разрешить ботам использовать гранаты.' ,
			'var'  => 'bot_allow_grenades' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить ботам использовать пулемёты.' ,
			'var'  => 'bot_allow_machine_guns' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить ботам использовать пистолеты.' ,
			'var'  => 'bot_allow_pistols' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить ботам использовать винтовки.' ,
			'var'  => 'bot_allow_rifles' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить ботам использовать дробовики.' ,
			'var'  => 'bot_allow_shotguns' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить ботам использовать снайперские винтовки.' ,
			'var'  => 'bot_allow_snipers' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить ботам использовать мини-пулемёты.' ,
			'var'  => 'bot_allow_sub_machine_guns' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить NPC на сервере.' ,
			'var'  => 'mp_allowNPCs' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить игрокам после смерти наблюдать за чужими игроками.' ,
			'var'  => 'mp_allowspectators' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить автоприцеливание.' ,
			'var'  => 'mp_autocrosshair' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить автокик за убийство игроков своей команды.' ,
			'var'  => 'mp_autokick' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить автоматическую балансировку команд.' ,
			'var'  => 'mp_autoteambalance' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Время покупки в минутах.' ,
			'var'  => 'mp_buytime' ,
			'type' => '2',
			'default' => '1',
		) ,
		array (
			'name' => 'Время таймера бомбы.' ,
			'var'  => 'mp_c4timer' ,
			'type' => '2',
			'default' => '35',
		) ,
		array (
			'name' => 'Время, в течении которого игроки смогут разговаривать между собой после окончания текущей карты.' ,
			'var'  => 'mp_chattime' ,
			'type' => '2',
			'default' => '10',
		) ,
		array (
			'name' => 'Колличество разрешённых декалей (спреи, пятна крови, пулевые отверстия).' ,
			'var'  => 'mp_decals' ,
			'type' => '2',
			'default' => '200',
		) ,
		array (
			'name' => 'После смерти экран становится чёрным, не давая игроку смотреть за другими игроками в режиме спектатора.' ,
			'var'  => 'mp_fadetoblack' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Урон от падения.' ,
			'var'  => 'mp_falldamage' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить фонарик.' ,
			'var'  => 'mp_flashlight' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить переключения камер в режиме спектатора.' ,
			'var'  => 'mp_forcecamera' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'После смерти игрок может следить только за своей командой.' ,
			'var'  => 'mp_forcechasecam' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Максимальное колличество фрагов, при достижении которого определённым игроком карта сменится на следующую.' ,
			'var'  => 'mp_fraglimit' ,
			'type' => '2',
			'default' => '0',
		) ,
		array (
			'name' => 'Начальный отсчёт времени в начале каждого раунда(для покупки), в секундах.' ,
			'var'  => 'mp_freezetime' ,
			'type' => '2',
			'default' => '5',
		) ,
		array (
			'name' => 'Слышимость звуков шагов.' ,
			'var'  => 'mp_footsteps' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Возможность атаковать своих.' ,
			'var'  => 'mp_friendlyfire' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Кикает террориста при убийстве заданного лимита заложников.' ,
			'var'  => 'mp_hostagepenalty' ,
			'type' => '2',
			'default' => '4',
		) ,
		array (
			'name' => 'Максимальное количество превышения игроков одной команды над другой.' ,
			'var'  => 'mp_limitteams' ,
			'type' => '2',
			'default' => '1',
		) ,
		array (
			'name' => 'Для смены карты нужно 51% голосов.' ,
			'var'  => 'mp_mapvoteratio' ,
			'type' => '2',
			'default' => '0.51',
		) ,
		array (
			'name' => 'Максимальное количество раундов, при достижении которого игра на карте будет считаться законченной.' ,
			'var'  => 'mp_maxrounds' ,
			'type' => '2',
			'default' => '0',
		) ,
		array (
			'name' => 'Контролирует информацию которую игрок видит на панели статуса.' ,
			'var'  => 'mp_playerid' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'0'  => 'все имена' ,
				'1' => 'только имена игроков своей команды',
				'2' => 'без имён'
			)
		) ,
		array (
			'name' => 'Кикает игроков которые убивают членов своей команды в течении # секунд после перезапуска раунда.' ,
			'var'  => 'mp_spawnprotectiontime' ,
			'type' => '2',
			'default' => '10',
		) ,
		array (
			'name' => 'Длина раунда в минутах.' ,
			'var'  => 'mp_roundtime' ,
			'type' => '2',
			'default' => '10',
		) ,
		array (
			'name' => 'Колличество начальных денег у игроков.' ,
			'var'  => 'mp_startmoney' ,
			'type' => '2',
			'default' => '800',
		) ,
		array (
			'name' => 'Ограничение по времени на карту, в минутах.' ,
			'var'  => 'mp_timelimit' ,
			'type' => '2',
			'default' => '30',
		) ,
		array (
			'name' => 'В следующем раунде убить того, кто убил игрока своей команды.' ,
			'var'  => 'mp_tkpunish ' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Количество выигрышей одной команды при достижении которого игра на карте считается законченной.' ,
			'var'  => 'mp_winlimit' ,
			'type' => '2',
			'default' => '0',
		) ,
		array (
			'name' => 'Определяет ускорение игрока, когда он находится в воздухе(например падает).' ,
			'var'  => 'sv_airaccelerate' ,
			'type' => '2',
			'default' => '10',
		) ,
		array (
			'name' => 'Разрешить загрузку с сервера(например карт).' ,
			'var'  => 'sv_allowdownload' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить закачку файлов (например decals, спрей-логи, карты) на сервер.' ,
			'var'  => 'sv_allowupload' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить всем слышать переговоры друг друга по микрофону, вне зависимости от команды (даже мертвые).' ,
			'var'  => 'sv_alltalk' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить игровые читы на сервере.' ,
			'var'  => 'sv_cheats' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Предписывает ли сервер последовательность файла для критических файлов.' ,
			'var'  => 'sv_consistency' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'E-mail администратора сервера.' ,
			'var'  => 'sv_contact' ,
			'type' => '2',
			'default' => 'your@email.ru',
		) ,
		array (
			'name' => 'Разрешить поддержку старого стиля (Half-life 1) серверных запросов.' ,
			'var'  => 'sv_enableoldqueries' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Использовать звук шагов при передвижении игрока.' ,
			'var'  => 'sv_footsteps' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Коэффициент трения в игре.' ,
			'var'  => 'sv_friction' ,
			'type' => '2',
			'default' => '4',
		) ,
		array (
			'name' => 'Гравитация в игре.' ,
			'var'  => 'sv_gravity' ,
			'type' => '2',
			'default' => '800',
		) ,
		array (
			'name' => 'Устанавливает язык.' ,
			'var'  => 'sv_language' ,
			'type' => '2',
			'default' => '0',
		) ,
		array (
			'name' => 'Максимальное кол-во спектаторов.' ,
			'var'  => 'sv_maxspectators' ,
			'type' => '2',
			'default' => '8',
		) ,
		array (
			'name' => 'Максимальная скорость игрока.' ,
			'var'  => 'sv_maxspeed' ,
			'type' => '2',
			'default' => '320',
		) ,
		array (
			'name' => 'Минимальное колличество обновлений(колличество пакетов) которое разрешено на сервере.' ,
			'var'  => 'sv_minupdaterate' ,
			'type' => '2',
			'default' => '10',
		) ,
		array (
			'name' => 'Разрешить ставить паузу во время игры.' ,
			'var'  => 'sv_pausable' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Колличество минут на которое банится игрок пытавшийся подобрать rcon-пароль к серверу.' ,
			'var'  => 'sv_rcon_banpenalty' ,
			'type' => '2',
			'default' => '10',
		) ,
		array (
			'name' => 'Максимальное колличество попыток при наборе rcon-пароля, после истечения которых игрок будет забанен.' ,
			'var'  => 'sv_rcon_maxfailures' ,
			'type' => '2',
			'default' => '3',
		) ,
		array (
			'name' => 'Колличество попыток при наборе rcon-пароля во время заданное sv_rcon_minfailuretime, после истечения которых игрок будет забанен.' ,
			'var'  => 'sv_rcon_minfailures' ,
			'type' => '2',
			'default' => '3',
		) ,
		array (
			'name' => 'Колличество секунд для определения неверной rcon-аутенфикации.' ,
			'var'  => 'sv_rcon_minfailuretime' ,
			'type' => '2',
			'default' => '30',
		) ,
		array (
			'name' => 'Ускорение при передвижения в режиме spectator.' ,
			'var'  => 'sv_specaccelerate' ,
			'type' => '2',
			'default' => '5',
		) ,
		array (
			'name' => 'Игрок в режиме spectator может пролетать через стены и объекты.' ,
			'var'  => 'sv_specnoclip' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Скорость передвижения в режиме spectator.' ,
			'var'  => 'sv_specspeed' ,
			'type' => '2',
			'default' => '3',
		) ,
		array (
			'name' => 'Участие спектаторов в общем чате.' ,
			'var'  => 'sv_spectalk' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Собирать статистику использования процессора.' ,
			'var'  => 'sv_stats' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Длина шага в юнитах.' ,
			'var'  => 'sv_stepsize' ,
			'type' => '2',
			'default' => '18'
		) ,
		array (
			'name' => 'Минимальная скорость остановки на поверхности.' ,
			'var'  => 'sv_stopspeed' ,
			'type' => '2',
			'default' => '75'
		) ,
		array (
			'name' => 'Eсли сервер не получает отклика от клиента в течении # секунд, клиент отключается от сервера.' ,
			'var'  => 'sv_timeout' ,
			'type' => '2',
			'default' => '30'
		) ,
		array (
			'name' => 'Разрешить использование микрофона.' ,
			'var'  => 'sv_voiceenable' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Максимальный предел байт в секунду который КЛИЕНТ может послать на сервер.' ,
			'var'  => 'cl_rate' ,
			'type' => '2',
			'default' => '2500'
		) ,
		array (
			'name' => 'Число раз в секунду которое КЛИЕНТ информирует сервер о своих действиях.' ,
			'var'  => 'cl_cmdrate' ,
			'type' => '2',
			'default' => '30'
		) ,
		array (
			'name' => 'Сколько раз в секунду СЕРВЕР говорит клиенту что происходит на карте.' ,
			'var'  => 'cl_updaterate' ,
			'type' => '2',
			'default' => '20'
		) ,
		array (
			'name' => 'Ограничивает частоту обновлений сервера. Чем выше значение,тем больше пакетов будет послано клиентам.' ,
			'var'  => 'sv_maxupdaterate' ,
			'type' => '2',
			'default' => '40'
		) ,
		array (
			'name' => 'Минимальное колличество байт в секунду, которое может быть передано сервером.' ,
			'var'  => 'sv_minrate' ,
			'type' => '2',
			'default' => '3000'
		) ,
		array (
			'name' => 'Максимальное колличество байт в секунду, которое может быть передано сервером.' ,
			'var'  => 'sv_maxrate' ,
			'type' => '2',
			'default' => '8000'
		) ,
		array (
			'name' => 'Лагокомпенсация.' ,
			'var'  => 'sv_unlag' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Максимальная лагокомпенсация, в секунду.' ,
			'var'  => 'sv_maxunlag' ,
			'type' => '2',
			'default' => '1'
		) ,
	);

}

?>