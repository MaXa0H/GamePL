<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class source_engine
{
	public static function admins_reload($id,$config){
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
				$data = "\"Admins\"{\n";
				while ( $row2 = db::r ( $sql ) ) {
					if($row2['time']<time()){
						$act = 1;
						continue;
					}
					$sql2 = db::q ( 'SELECT * FROM gh_servers_admins_rates where id="'.$row2['rate'].'" order by id desc' );
					$row3 = db::r($sql2);
					$data .= "\"".$row2['login']."\"{\n";
					$data .="\"flags\" \"".$row3['flags']."\"\n";
					$data .="\"immunity\" \"".$row3['im']."\"\n";
					if($row2['type']==1){
						$data .="\"auth\" \"steam\"\n";
						$data .="\"identity\" \"".$row2['login']."\"\n";
						$data .="\"password\" \"".$row2['pass']."\"\n";
					}
					if($row2['type']==2){
						$data .="\"auth\" \"name\"\n";
						$data .="\"identity\" \"".$row2['login']."\"\n";
						$data .="\"password\" \"".$row2['pass']."\"\n";
					}
					if($row2['type']==3){
						$data .="\"auth\" \"ip\"\n";
						$data .="\"identity\" \"".$row2['login']."\"\n";
					}
					$data .= "}\n";
				}
				$data .= "}\n";
				$file2 = fopen ( ROOT . '/conf/'.$file , "w" );
				fputs ( $file2 , $data );
				fclose ( $file2 );
				ssh::send_file ( ROOT . '/conf/'.$file , "/host/" . $row[ 'user' ] . "/" . $row[ 'sid' ] . $config, 0777 );
				unlink(ROOT . '/conf/'.$file);
				$comand = "sm_reloadadmins";
				$exec = 'screen -S server_' . $row[ 'sid' ] . ' -p 0 -X stuff \'' . $comand . '\'$\'\n\';';
				ssh::exec_cmd ( $exec );
			}
		}
	}
	public static function get_pid ( $id )
	{
		ssh::exec_cmd ( "ps -ef  | grep s" . $id . " | grep -v sudo | grep -v screen | grep srcds_linux | awk '{ print $3}';" );
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
		api::inc ( 'lgsl' );
		ini_set ( 'default_socket_timeout' , '1' );
		$server = lgsl_query_live ( "source" , $data[ "ip" ] , $data[ "port" ] , $data[ "port" ] , $data[ "port" ] , "sep" );
		$server = servers::convert ( $server , servers::detect ( $server ) );
		$status = servers::status ( $server[ 'b' ][ 'status' ] , $server[ 's' ][ 'password' ] , @$server[ 'b' ][ 'pending' ] );
		if ( $status != "0" && trim ( $server[ 's' ][ 'name' ] ) ) {
			m::d('server_offline_' . $data['id']);
			if ( (int)( $server[ 's' ][ 'playersmax' ]-1) > $data[ 'slots' ] ) {
				api::inc ( 'servers/act' );
				servers_act::off ( $data[ 'id' ] );
				if(servers::$cron){
					cron::w("	->kill ( playersmax > slots )");
				}
				servers_act::on ( $data[ 'id' ] );
				return false;
			}
			$ttt = api::gettime ();
			$cfg = servers::cfg ( $data[ 'id' ] );
			$sql344 = db::q ( 'SELECT * FROM gh_boxes_games where box="' . $data[ 'box' ] . '" and game="' . $data[ 'game' ] . '"' );
			$rows344 = db::r ( $sql344 );

			$pcpu = (int) ( ( 100 / ( $rows344[ 'cpu' ] * $data[ 'slots' ] ) ) * $cfg[ 'cpu' ] );
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


			$n = 0;
			$sp = array ();
			$class = servers::game_class ( $data[ 'game' ] );
			if ( $class::info ( 'rcon_kb' ) ) {
				$cfg = servers::cfg ( $data[ 'id' ] );
				try {
					$data1 = '';
					api::inc ( 'SourceQuery/CServerRcon' );
					$r = new CServerRcon($data['ip'], $data['port'], $cfg['rcon']);
					if(!$r->Auth()){
						api::result ( 'Не удалось авторизоваться' );
					}
					$data1 = $r->rconCommand("status");
					$r->Close();
                    define( 'STATUS_PARSE' , '/#[ ]+([0-9 ]+)[ ]+"(.+)"[ ]+(\[U:[0-9]:[0-9]+\])[ ]+([0-9]+[:[0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([a-zA-Z]+)[ ]+([0-9.:]+)/' );
					$search = preg_match_all ( STATUS_PARSE , $data1 , $matches , PREG_PATTERN_ORDER );
					$score = array ();
					foreach ( $server[ 'p' ] as $player_key => $player ) {
						if ( $player[ 'name' ] != "" ) {
							$name2 = $player[ 'name' ];
						}
						$score[ $name2 ] = $player[ 'score' ];
					}
					$u = array ();
					$u[ 'name' ] = l::t('Имя');
					$u[ 'steam' ] = 'Steam ID';
					$u[ 'ip' ] = 'IP';
					$u[ 'ping' ] = l::t('Пинг');
					$u[ 'score' ] = l::t('Счет');
					$u[ 'time' ] = l::t('Время');
					$u[ 'title' ] = '1';
					$sp[ ] = $u;

					foreach ( $matches[ 2 ] as $key => $value ) {
						if ( $value ) {
                            $steam = str_replace('[','',$matches[ 3 ][ $key ]);
                            $steam = str_replace(']','',$steam);
							$u = array ();
							$u[ 'name' ] = api::cl ( $matches[ 2 ][ $key ] );
							$u[ 'steam' ] = $steam;
							$u[ 'ip' ] = $matches[ 8 ][ $key ];
							$u[ 'ping' ] = (int) $matches[ 5 ][ $key ];
							$u[ 'score' ] = (int) $score[ $matches[ 3 ][ $key ] ];
							$u[ 'time' ] = $matches[ 4 ][ $key ];
							$sp[ ] = $u;
						}
					}
				} catch ( Exception $e ) {
					$e = $e->getMessage ();
				}
			} else {
				$u = array ();
				$u[ 'name' ] = l::t('Имя');
				$u[ 'score' ] = l::t('Счет');
				$u[ 'time' ] = l::t('Время');
				$u[ 'title' ] = '1';
				$sp[ ] = $u;
				foreach ( $server[ 'p' ] as $player_key => $player ) {
					$n = $n + 1;
					$u = array ();
					if ( $player[ 'name' ] == "" ) {
						$name2 = l::t("Подключается");
					} else {
						$name2 = $player[ 'name' ];
					}
					$u[ 'name' ] = $name2;
					$u[ 'score' ] = $player[ 'score' ];
					$u[ 'time' ] = $player[ 'time' ];
					$sp[ ] = $u;
				}
			}
			$sp = base64_encode ( json_encode ( $sp ) );

			if ( $status == "2" ) {
				$stats = 1;
			} else {
				$stats = 0;
			}
			if(servers::$cron){
				cron::w("	online: ".(int)$server[ 's' ][ 'players' ]);
			}
			db::q ( 'SELECT * FROM gh_monitoring where sid="' . $data[ "id" ] . '"' );
			if ( db::n () == "1" ) {
				db::q ( "UPDATE gh_monitoring set name='" . api::cl ( $server[ 's' ][ 'name' ] ) . "',map='" . api::cl ( $server[ 's' ][ 'map' ] ) . "',online='" . api::cl ( $server[ 's' ][ 'players' ] ) . "',gamers='" . $sp . "',guard='" . $stats . "' where sid='" . $data[ "id" ] . "'" );
			} else {
				db::q ( "INSERT INTO gh_monitoring set name='" . api::cl ( $server[ 's' ][ 'name' ] ) . "',map='" . api::cl ( $server[ 's' ][ 'map' ] ) . "',online='" . api::cl ( $server[ 's' ][ 'players' ] ) . "',gamers='" . $sp . "',sid='" . $data[ "id" ] . "',guard='" . $stats . "'" );
			}
			db::q ( "UPDATE gh_servers set name='" . api::cl ( $server[ 's' ][ 'name' ] ) . "' where id='" . $data[ "id" ] . "'" );
		}else{
			$key = m::g ( 'server_offline_' . $data['id'] );
			if ( empty( $key ) ) {
				m::s ( 'server_offline_' . $data['id'] , 1 , 3600 );
			}else{
				if($key==2){
					m::d('server_offline_' . $data['id']);
					api::inc ( 'servers/act' );
					servers_act::off ( $data[ 'id' ] );
					if(servers::$cron){
						cron::w ( "	->restart" );
					}
					servers_act::on ( $data[ 'id' ] );
					return false;
				}else{
					m::s ( 'server_offline_' . $data['id'] , $key+1 , 3600 );
				}
			}
		}
	}

	public static function settings ( $data , $dir )
	{
		$class = servers::game_class ( $data[ 'game' ] );
		$cmd = "cd /host/" . $data[ 'user' ] . "/" . $data[ 'sid' ] . $dir . "; ls | grep .bsp;";
		ssh::exec_cmd ( $cmd );
		$data1 = trim(ssh::get_output ());
		$data1 = explode ( "\n" , $data1 );
		if ( ! $data1 ) {
			api::result ( l::t('Не удалось установить соединение с сервером') );
		} else {
			$sql    = db::q('SELECT * FROM gh_rates where id="' . $data['rate'] . '"');
			$rate   = db::r($sql);
			if ( $_POST[ 'data' ] ) {
				if ( api::captcha_chek () ) {
					$ermap = "1";
					foreach ( $data1 as $map ) {
						if ( ! preg_match ( '/\.(bsp.ztmp)/' , $map ) ) {
							$map = str_replace ( ".bsp" , "" , $map );
							if ( $map == $_POST[ 'data' ][ 'map' ] ) {
								$ermap = "0";
							}
						}
					}
					if ( $ermap == "1" ) {
						api::result ( l::t('Карта не найдена') );
					} else {
						if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $_POST[ 'data' ][ 'rcon' ] ) ) {
							api::result ( l::t("Rcon пароль содержит недопустимые символы") );
						} else {
							if ( ! preg_match ( "/^[0-9a-zA-Z]{0,20}$/i" , $_POST[ 'data' ][ 'pass' ] ) ) {
								api::result ( l::t("Пароль содержит недопустимые символы") );
							} else {
								if ( ! preg_match ( "/^[0-9A-Za-z_=-]{2,60}$/i" , $_POST[ 'data' ][ 'map' ] ) ) {
									api::result ( l::t("Название карты содержит недопустимые символы") );
								} else {
									$data2[ 'map' ] = $_POST[ 'data' ][ 'map' ];
									$data2[ 'rcon' ] = $_POST[ 'data' ][ 'rcon' ];
									$data2[ 'pass' ] = $_POST[ 'data' ][ 'pass' ];
									$data2[ 'vac' ] = (int) $_POST[ 'data' ][ 'vac' ];
									if( $data['game']!='cs'){
										$data2[ 'tv' ] = (int) $_POST[ 'data' ][ 'tv' ];
									}
									servers::configure ( $data2 , $data[ 'id' ] );
									api::result ( l::t('Настройки успешно сохранены') , true );
								}
							}
						}
					}
				}
			}
			$cfg = servers::cfg ( $data[ 'id' ] );
			$maps = '';
			foreach ( $data1 as $map1 ) {
				if ( preg_match ( '/\.(bsp.ztmp)/' , $map1 ) ) {
				}
				if ( preg_match ( '/\.(bsp.bz2)/' , $map1 ) ) {
				} else {
					$map1 = str_replace ( ".bsp" , "" , api::cl ( $map1 ) );
					if ( $map1 == $cfg[ 'map' ] ) {
						$maps .= '<option value="' . $map1 . '" selected="selected">' . $map1 . '</option>';
					} else {
						$maps .= '<option value="' . $map1 . '">' . $map1 . '</option>';
					}
				}
			}
			$tv = '';
			if ( $cfg[ 'tv' ] == 1 ) {
				$tv .= '<option value="1" selected="selected">'.l::t('Включен').'</option>';
				$tv .= '<option value="0">'.l::t('Выключен').'</option>';
			} else {
				$tv .= '<option value="1">'.l::t('Включен').'</option>';
				$tv .= '<option value="0" selected="selected">'.l::t('Выключен').'</option>';
			}
			$vac = '';
			if ( $cfg[ 'vac' ] == 0 ) {
				$vac .= '<option value="0" selected="selected">'.l::t('Включен').'</option>';
				$vac .= '<option value="1">'.l::t('Выключен').'</option>';
			} else {
				$vac .= '<option value="0">'.l::t('Включен').'</option>';
				$vac .= '<option value="1" selected="selected">'.l::t('Выключен').'</option>';
			}
			tpl::load ( 'servers-settings-game-source-engine' );
			if($rate['tv']==1){
				if($data['game']!='cs'){
					tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si", "\\1" );
				}else{
					tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si", "" );
				}
			}else{
				tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si", "" );
			}
			api::captcha_create ();
			if ( $class::info ( 'settings2' ) ) {
				tpl::set_block ( "'\\[dop\\](.*?)\\[/dop\\]'si", "\\1" );
			}else{
				tpl::set_block ( "'\\[dop\\](.*?)\\[/dop\\]'si", "" );
			}
			tpl::set ( '{rcon}' , $cfg[ 'rcon' ] );
			tpl::set ( '{pass}' , $cfg[ 'pass' ] );
			tpl::set ( '{maps}' , $maps );
			tpl::set ( '{vac}' , $vac );
			tpl::set ( '{tv}' , $tv );
			tpl::set ( '{id}' , $data[ 'id' ] );
			tpl::compile ( 'content' );
		}
	}

	public static function update ( $server , $game )
	{
		$exec = "cd /host/servers/cmd/;";
		$exec .= "screen -dmS update_" . $server[ 'sid' ] . " /bin/bash -c 'STEAMEXE=steamcmd ./steam.sh +login anonymous +force_install_dir /host/" . $server[ 'user' ] . "/" . $server[ 'sid' ] . " +app_update " . $game . " validate +quit';";
		ssh::exec_cmd ( $exec );
	}

	public static function rcon ( $data )
	{
		if ( $_POST[ 'data' ] ) {
			$cfg = servers::cfg ( $data[ 'id' ] );
			api::inc ( 'SourceQuery/SourceQuery' );
			$Query = new SourceQuery();
			try {
				$Query->Connect ( $data[ 'ip' ] , $data[ 'port' ] , 1 , SourceQuery :: SOURCE );
				$Query->SetRconPassword ( $cfg[ 'rcon' ] );
				if ( $_POST[ 'data' ][ 'rcon' ] == "" ) {
					$_POST[ 'data' ][ 'rcon' ] = "status";
				}
				$data1 = $Query->Rcon ( $_POST[ 'data' ][ 'rcon' ] );
				api::ajax_d ( $data1 );
			} catch ( Exception $e ) {
				api::ajax_d ( $e->getMessage () );
			}
			$Query->Disconnect ();
		}
		tpl::load ( 'source_rcon' );
		tpl::set ( '{id}' , $data[ 'id' ] );
		tpl::compile ( 'content' );
	}

	public static function rcon_bk ( $data )
	{
		global $conf;
		if ( $_POST[ 'data' ] ) {
			$cfg = servers::cfg ( $data[ 'id' ] );
			try {
				api::inc ( 'SourceQuery/CServerRcon' );
				$r = new CServerRcon($data['ip'], $data['port'], $cfg['rcon']);
				if(!$r->Auth()){
					api::result ( l::t('Не удалось авторизоваться') );
					return false;
				}
				$name = $_POST[ 'data' ][ 'name' ];
				if ( $_POST[ 'data' ][ 't' ] == "2" ) {
					$d = $r->rconCommand( "removeid " . $name);

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
					$r->Close();
					api::result ( l::t('Игрок разбанен') , true );

					return true;
				}
				$data1 = $r->rconCommand( "status");
                define( 'STATUS_PARSE' , '/#[ ]+([0-9 ]+)[ ]+"(.+)"[ ]+(\[U:[0-9]:[0-9]+\])[ ]+([0-9]+[:[0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([a-zA-Z]+)[ ]+([0-9.:]+)/' );
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
					$steam = $matches[ 3 ][ $index ];
                    $steam = str_replace('[','',$steam);
                    $steam = str_replace(']','',$steam);
					$class = servers::game_class ( $data[ 'game' ] );
					if ( $_POST[ 'data' ][ 't' ] == "1" ) {
						$d = $r->rconCommand( "banid 60 " . $steam . " kick " );
						$class::mon ( $data );
						if ( $cfg[ 'baneds' ] ) {
							$bans = json_decode ( base64_decode ( $cfg[ 'baneds' ] ) , true );
						} else {
							$bans = array ();
						}
						$ban[ 'name' ] = api::cl ( $matches[ 2 ][ $index ] );
						$ban[ 'steam' ] = $steam;
						$ban[ 'ip' ] = $matches[ 8 ][ $index ];
						$ban[ 'time' ] = time ();
						$bans[ ] = $ban;
						$dat[ 'baneds' ] = base64_encode ( json_encode ( $bans ) );
						servers::configure ( $dat , $data[ 'id' ] );
						$r->Close();
						api::result ( l::t('Игрок забанен на 60 минут') , true );
					} else {
						$d = $r->rconCommand("kickid " . $steam . " kick" );
						$class::mon ( $data );
						$r->Close();
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

	public static function admins ( $data )
	{
		api::inc ( 'ssh2' );
		api::inc ( 'servers/admins' );
		if ( ssh::gh_box ( $data[ 'box' ] ) ) {
			$class = servers::game_class ( $data[ 'game' ] );
			$tips = array ( 'auth' , 'identity' , 'password' , 'group' , 'flags' , 'immunity' );
			$exec = '/host/' . $data[ 'user' ] . '/' . $data[ 'sid' ] . $class::info ( 'admins' );
			$exec1 = ROOT . '/conf/' . $data[ 'user' ] . '_' . $data[ 'id' ] . '.txt';
			if ( $_POST[ 'data_name' ] ) {
				$insert = "\"Admins\"{\n";
				foreach ( $_POST[ 'data_name' ] as $key => $value ) {
					if ( $value == "" ) {
						continue;
					} else {
						$insert .= "\"" . api::cl ( $value ) . "\"\n";
						$insert .= "{\n";
						foreach ( $tips as $tipse ) {
							if ( $_POST[ 'data_' . $tipse ][ $key ] ) {
								$insert .= "\"" . $tipse . "\" \"" . api::cl ( $_POST[ 'data_' . $tipse ][ $key ] ) . "\"\n";
							}
						}
						$insert .= "}\n";
					}
				}
				$insert .= "}\n";
				$file = fopen ( $exec1 , "w" );
				fputs ( $file , $insert );
				fclose ( $file );
				ssh::send_file ( $exec1 , $exec , 0777 );
				unlink ( $exec1 );
				ssh::disconnect ();
				api::result ( l::t('Сохранено'), true );
			}
			ssh::get_file ( $exec , $exec1 );
			$admins = source_parser_admins::GetArray ( $exec1 );
			$ui = 0;
			foreach ( $admins[ 'Admins' ] as $key => $value ) {
				$ui ++;
				tpl::load ( 'servers-admins-source-engine-get' );
				tpl::set ( '{id}' , $ui );
				tpl::set ( '{name}' , $key );
				foreach ( $tips as $tipse ) {
					tpl::set ( '{' . $tipse . '}' , api::cl ( $value[ $tipse ] ) );
				}
				tpl::compile ( 'admines' );
			}
			tpl::load ( 'servers-admins-source-engine' );
			tpl::set ( '{id}' , $data[ 'id' ] );
			tpl::set ( '{data}' , tpl::result ( 'admines' ) );
			tpl::compile ( 'content' );
			ssh::disconnect ();
		} else {
			api::result ( l::t('Не удалось установить соединение с сервером') );
		}
	}

	public static function maps ( $data , $dir )
	{
		$cmd = "cd /host/" . $data[ 'user' ] . "/" . $data[ 'sid' ] . $dir . "; ls | grep .bsp$;";
		ssh::exec_cmd ( $cmd );
		$data1 = ssh::get_output ();
		$data1 = explode ( "\n" , trim ( $data1 ) );
		foreach ( $data1 as $map1 ) {
			if ( preg_match ( '/\.(bsp.ztmp)/' , $map1 ) ) {
			}
			if ( preg_match ( '/\.(bsp.bz2)/' , $map1 ) ) {
			} else {
				$map1 = str_replace ( ".bsp" , "" , api::cl ( $map1 ) );
				$maps[ ] = $map1;
			}
		}

		return $maps;
	}
}

?>