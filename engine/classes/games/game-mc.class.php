<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_mc
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
		'friends'   => 'Управление друзьями' ,
		'console'   => 'Управление консолью',
		'slots'		=> 'Изменение слотов'
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
		$conf[ 'update' ] = 0;
		$conf[ 'online' ] = 1;
		$conf[ 'gadget' ] = 1;
		$conf[ 'repository' ] = 1;
		$conf[ 'fastdl' ] = 1;
		$conf[ 'fps' ] = 0;
		$conf[ 'reinstall' ] = 1;
		$conf[ 'friends' ] = 1;
		$conf[ 'ftp' ] = 1;
		$conf[ 'settings' ] = 1;
		$conf[ 'tv' ] = 0;
		$conf[ 'console' ] = 1;
		$conf[ 'ftp_root' ] = "/";

		return $conf[ $data ];
	}

	public static function install ( $id )
	{
		$data[ 'rcon.password' ] = api::generate_password ( '10' );
		$data[ 'level-name' ] = 'world';
		$data[ 'level-type' ] = 'DEFAULT';
		$data[ 'generate-structures' ] = 'true';
		$data[ 'allow-nether' ] = 'true';
		$data[ 'online-mode' ] = 'true';
		$data[ 'white-list' ] = 'false';
		$data[ 'spawn-monsters' ] = 'true';
		$data[ 'spawn-animals' ] = 'true';
		$data[ 'spawn-npcs' ] = 'true';
		$data[ 'difficulty' ] = '1';
		$data[ 'gamemode' ] = '0';
		$data[ 'pvp' ] = 'true';
		$data[ 'level-seed' ] = '';
		$data[ 'allow-flight' ] = 'false';
		$data[ 'motd' ] = 'New minecraft server';
		$data[ 'max-build-height' ] = '256';
		$data[ 'enable-rcon' ] = 'false';
		$data[ 'force-gamemode' ] = 'false';
		$data[ 'texture-pack' ] = '';
		$data[ 'snooper-enabled' ] = 'true';
		$data[ 'hardcore' ] = 'false';
		servers::configure ( $data , $id );
	}

	public static function on ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$server = db::r ();
		$cfg = servers::cfg ( $id );
		$sid = $server[ 'sid' ];
		$sql = db::q ( 'SELECT * FROM gh_boxes_games where box="' . $server[ 'box' ] . '" and game="' . $server[ 'game' ] . '"' );
		$rows = db::r ( $sql );
		$sql = db::q ( 'SELECT * FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
		$rate = db::r ( $sql );
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= 'chmod 777 server.properties;';
		$exec .= 'echo "allow-nether=' . $cfg[ 'allow-nether' ] . '" > server.properties;';
		$exec .= 'echo "level-name=' . $cfg[ 'map' ] . '" >> server.properties;';
		$exec .= 'echo "enable-query=true" >> server.properties;';
		$exec .= 'echo "allow-flight=' . $cfg[ 'allow-flight' ] . '" >> server.properties;';
		$exec .= 'echo "server-port=' . $server[ 'port' ] . '" >> server.properties;';
		$exec .= 'echo "level-type=' . $cfg[ 'level-type' ] . '" >> server.properties;';
		$exec .= 'echo "enable-rcon=' . $cfg[ 'enable-rcon' ] . '" >> server.properties;';
		$exec .= 'echo "level-seed=' . $cfg[ 'level-seed' ] . '" >> server.properties;';
		$exec .= 'echo "force-gamemode=' . $cfg[ 'force-gamemode' ] . '" >> server.properties;';
		$exec .= 'echo "server-ip=' . $server[ 'ip' ] . '" >> server.properties;';
		$exec .= 'echo "max-build-height=' . $cfg[ 'max-build-height' ] . '" >> server.properties;';
		$exec .= 'echo "spawn-npcs=' . $cfg[ 'spawn-npcs' ] . '" >> server.properties;';
		$exec .= 'echo "white-list=' . $cfg[ 'white-list' ] . '" >> server.properties;';
		$exec .= 'echo "spawn-animals=' . $cfg[ 'spawn-animals' ] . '" >> server.properties;';
		$exec .= 'echo "texture-pack=' . $cfg[ 'texture-pack' ] . '" >> server.properties;';
		$exec .= 'echo "snooper-enabled=' . $cfg[ 'snooper-enabled' ] . '" >> server.properties;';
		$exec .= 'echo "hardcore=' . $cfg[ 'hardcore' ] . '" >> server.properties;';
		$exec .= 'echo "online-mode=' . $cfg[ 'online-mode' ] . '" >> server.properties;';
		$exec .= 'echo "pvp=' . $cfg[ 'pvp' ] . '" >> server.properties;';
		$exec .= 'echo "difficulty=' . $cfg[ 'difficulty' ] . '" >> server.properties;';
		$exec .= 'echo "gamemode=' . $cfg[ 'gamemode' ] . '" >> server.properties;';
		$exec .= 'echo "max-players=' . $server[ 'slots' ] . '" >> server.properties;';
		$exec .= 'echo "spawn-monsters=' . $cfg[ 'spawn-monsters' ] . '" >> server.properties;';
		$exec .= 'echo "generate-structures=' . $cfg[ 'generate-structures' ] . '" >> server.properties;';
		$exec .= 'echo "view-distance=10" >> server.properties;';
		$exec .= 'echo "motd=' . $cfg[ 'motd' ] . '" >> server.properties;';
		$exec .= 'echo "rcon.password=' . $cfg[ 'rcon.password' ] . '" >> server.properties;';
		$exec .= 'echo "debug=false" >> server.properties;';
		$exec .= 'echo "announce-player-achievements=' . $cfg[ 'announce-player-achievements' ] . '" >> server.properties;';
		$exec .= 'echo "enable-command-block=' . $cfg[ 'enable-command-block' ] . '" >> server.properties;';
		$exec .= 'echo "rcon.port=' . ( (int) $server[ 'port' ] + 1302 ) . '" >> server.properties;';
		$exec .= 'echo "query.port=' . ( (int) $server[ 'port' ] ) . '" >> server.properties;';
		$exec .= 'echo "eula=true" > eula.txt;';
		$exec .= 'chmod 777 eula.txt;';
		//$exec .= "cd /;cp -rv /host/" . $rate[ 'dir' ] . "craftbukkit.jar /host/" . $server[ 'user' ] . "/" . $sid . "/craftbukkit.jar;";
		$exec .= "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";

		if(!$cfg['core']){
			$core = "craftbukkit.jar";
		}else{
			$core = $cfg['core'];
		}
		$exec .= "chmod 777 ".$core.";screen -dmS server_" . $sid . " sudo -u s" . $sid . " java -XX:MaxPermSize=128M -Xincgc -Xmx" . ( $server[ 'slots' ] * $rows[ 'ram' ] ) . "M -Xms100M -jar ".$core."  nogui;";
		ssh::exec_cmd ( $exec );
		sleep ( '2' );
		$pid = self::get_pid ( $sid );
		if ( $pid ) {
			servers::set_cpu ( $sid , $server[ 'slots' ] , $pid , $server[ 'rate' ] , $server[ 'game' ] );
			sleep ( '2' );
			servers::get_pid_screen ( $sid );
		}
	}

	public static function get_pid ( $id )
	{
		ssh::exec_cmd ( "top  -n 1 -b -u s" . $id . " | grep java | awk '{ print $1}';" );
		$data = trim ( ssh::get_output () );
		$data = explode ( "\n" , $data );
		if ( count ( $data ) >10 ) {
			servers::kill_pid ( $data );

			return false;
		} else {
			return $data[ 0 ];
		}
	}

	public static function mon ( $data )
	{
		if ( ! MinecraftQuery::Connect ( $data[ "ip" ] , $data[ "port" ] , 1 ) ) {
			return false;
		}
		if ( ( $Info = MinecraftQuery::GetInfo () ) != false ) {
			m::d ( 'server_offline_' . $data[ 'id' ] );
			$cfg = servers::cfg ( $data[ 'id' ] );
			$Players = MinecraftQuery::GetPlayers ();
			$u = array ();
			$u[ 'name' ] = 'Имя';
			$u[ 'title' ] = '1';
			$sp[ ] = $u;
			$n = "0";
			foreach ( $Players as $player ) {
				$n = $n + 1;
				$u = array ();
				$u[ 'name' ] = api::cl ( $player );
				$sp[ ] = $u;
			}
			$sp = base64_encode ( json_encode ( $sp ) );

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

			$pram = (int) ( 100 / ( $rows344[ 'ram' ] * $data[ 'slots' ] ) * (int) ( $cfg[ 'mem' ] / 1024 ) );
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
				if ( $time_row[ "online" ] < (int) $Info[ 'Players' ] ) {
					db::q ( 'UPDATE gh_monitoring_time set online="' . (int) $Info[ 'Players' ] . '" where time="' . $ttt . '" and sid="' . $data[ 'id' ] . '"' );
				}
			} elseif ( db::n ( $sql123 ) == "0" ) {
				db::q ( 'INSERT INTO gh_monitoring_time set online="' . (int) $Info[ 'Players' ] . '",time="' . $ttt . '",sid="' . $data[ 'id' ] . '"' );
			}

			db::q ( 'SELECT * FROM gh_monitoring where sid="' . $data[ "id" ] . '"' );
			if ( db::n () == "1" ) {
				db::q ( "UPDATE gh_monitoring set name='" . api::cl ( $Info[ 'HostName' ] ) . "',map='" . $cfg[ 'map' ] . "',online='" . (int) $Info[ 'Players' ] . "',gamers='" . $sp . "',guard='0' where sid='" . $data[ "id" ] . "'" );
			} else {
				db::q ( "INSERT INTO gh_monitoring set name='" . api::cl ( $Info[ 'HostName' ] ) . "',map='" . $cfg[ 'map' ] . "',online='" . (int) $Info[ 'Players' ] . "',gamers='" . $sp . "',sid='" . $data[ "id" ] . "',guard='0'" );
			}
			db::q ( "UPDATE gh_servers set name='" . api::cl ( $Info[ 'HostName' ] ) . "' where id='" . $data[ "id" ] . "'" );
		} else {
			$key = m::g ( 'server_offline_' . $data[ 'id' ] );
			if ( empty( $key ) ) {
				m::s ( 'server_offline_' . $data[ 'id' ] , 1 , 3600 );
			} else {
				if ( $key == 2 ) {
					m::d ( 'server_offline_' . $data[ 'id' ] );
					api::inc ( 'servers/act' );
					servers_act::off ( $data[ 'id' ] );
					if ( servers::$cron ) {
						cron::w ( "	->restart" );
					}
					servers_act::on ( $data[ 'id' ] );

					return false;
				} else {
					m::s ( 'server_offline_' . $data[ 'id' ] , $key + 1 , 3600 );
				}
			}
		}
	}

	public static function settings ( $data )
	{
		$cmd = "cd /host/" . $data[ 'user' ] . "/" . $data[ 'sid' ]. "; ls | grep .jar;";
		ssh::exec_cmd ( $cmd );
		$data1 = trim(ssh::get_output ());
		$data1 = explode ( "\n" , $data1 );
		$cfg = servers::cfg ( $data[ 'id' ] );
		if ( $_POST[ 'data' ] ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( api::captcha_chek () ) {
				$a = false;
				foreach ( $data1 as $value ) {
					if($_POST['data']['core']==$value){
						if ( preg_match ( "/^[0-9a-zA-Z\-\.]{5,40}\.jar$/i" , trim ( $value ) ) ) {
							$a = true;
						}
					}
				}
				if($a){
					$go = array ();
					$go[ 'core' ] = api::cl($_POST['data']['core']);
					$a = array (
						'generate-structures' ,
						'allow-nether' ,
						'online-mode' ,
						'white-list' ,
						'spawn-monsters' ,
						'spawn-animals' ,
						'spawn-npcs' ,
						'pvp' ,
						'allow-flight' ,
						'enable-rcon' ,
						'hardcore' ,
						'force-gamemode' ,
						'snooper-enabled' ,
						'enable-command-block' ,
						'announce-player-achievements'
					);
					$val = array ( 1 , 0 );
					foreach ( $a as $key ) {
						if ( in_array ( $_POST[ 'data' ][ $key ] , $val ) ) {
							$go[ $key ] = api::cl ( $_POST[ 'data' ][ $key ] );
						} else {
							$go[ $key ] = '0';
						}
					}
					$val = array ( 0 , 1 , 2 , 3 );
					if ( in_array ( $_POST[ 'data' ][ 'difficulty' ] , $val ) ) {
						$go[ 'difficulty' ] = api::cl ( $_POST[ 'data' ][ 'difficulty' ] );
					} else {
						$go[ 'difficulty' ] = '0';
					}

					$val = array ( 0 , 1 , 2 );
					if ( in_array ( $_POST[ 'data' ][ 'gamemode' ] , $val ) ) {
						$go[ 'gamemode' ] = api::cl ( $_POST[ 'data' ][ 'gamemode' ] );
					} else {
						$go[ 'gamemode' ] = '0';
					}

					$val = array (
						'DEFAULT' ,
						'FLAT' ,
						'LARGEBIOMES',
						'AMPLIFIED',
						'CUSTOMIZED',
						'Highlands'
					);
					if ( in_array ( $_POST[ 'data' ][ 'level-type' ] , $val ) ) {
						$go[ 'level-type' ] = api::cl ( $_POST[ 'data' ][ 'level-type' ] );
					} else {
						$go[ 'level-type' ] = 'DEFAULT';
					}
					if ( ! preg_match ( "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\#\[\.\,\-\/\_\?\(\)\\ ]{2,300}$/i" , trim ( $_POST[ 'data' ][ 'motd' ] ) ) ) {
						api::result ( l::t("Название сервера содержит недопустимые символы") );
					} else {
						if ( ! preg_match ( "/^[0-9a-zA-Z\., ]{2,40}$/i" , trim ( $_POST[ 'data' ][ 'level-name' ] ) ) ) {
							api::result ( l::t("level-name сервера содержит недопустимые символы") );
						} else {
							if ( ! preg_match ( "/^[0-9]{0,30}$/i" , $_POST[ 'data' ][ 'level-seed' ] ) ) {
								api::result ( l::t("level-seed сервера содержит недопустимые символы") );
							} else {
								if ( ! preg_match ( "/^[0-9a-zA-Z]{0,30}$/i" , $_POST[ 'data' ][ 'rcon.password' ] ) ) {
									api::result ( l::t("rcon.password сервера содержит недопустимые символы") );
								} else {
									if ( ! preg_match ( "/^[0-9a-zA-Z\.\/]{0,40}$/i" , $_POST[ 'data' ][ 'texture-pack' ] ) ) {
										api::result ( l::t("texture-pack сервера содержит недопустимые символы") );
									} else {
										$go[ 'texture-pack' ] = api::cl ( $_POST[ 'data' ][ 'texture-pack' ] );
										$go[ 'motd' ] = api::cl ( $_POST[ 'data' ][ 'motd' ] );
										$go[ 'map' ] = api::cl ( $_POST[ 'data' ][ 'level-name' ] );
										$go[ 'level-seed' ] = api::cl ( $_POST[ 'data' ][ 'level-seed' ] );
										$go[ 'rcon.password' ] = api::cl ( $_POST[ 'data' ][ 'rcon.password' ] );
										servers::configure ( $go , $data[ 'id' ] );
										api::result ( l::t('Сохранено') , true );
									}
								}
							}
						}
					}
				}else{
					api::result(l::t('Ядро не найдено!'));
				}
			}
		}
		tpl::load ( 'servers-settings-game-mc' );
		api::captcha_create ();
		$a = array (
			'generate-structures' ,
			'allow-nether' ,
			'online-mode' ,
			'white-list' ,
			'spawn-monsters' ,
			'spawn-animals' ,
			'spawn-npcs' ,
			'pvp' ,
			'allow-flight' ,
			'enable-rcon' ,
			'hardcore' ,
			'force-gamemode' ,
			'snooper-enabled' ,
			'enable-command-block' ,
			'announce-player-achievements'
		);
		foreach ( $a as $key ) {
			$b = '';
			if ( $cfg[ $key ] == "true" ) {
				$b .= '<option value="true" selected="selected">true</option>';
				$b .= '<option value="false">false</option>';
			} else {
				$b .= '<option value="true">true</option>';
				$b .= '<option value="false" selected="selected">false</option>';
			}
			tpl::set ( '{' . $key . '}' , $b );
		}
		$a = array (
			'0' => 'Survival' ,
			'1' => 'Creative' ,
			'2' => 'Adventure'
		);
		$b = '';
		foreach ( $a as $key => $value ) {
			if ( $cfg[ 'gamemode' ] == $key ) {
				$b .= '<option value="' . $key . '" selected="selected">' . $value . '</option>';
			} else {
				$b .= '<option value="' . $key . '">' . $value . '</option>';
			}
		}
		tpl::set ( '{gamemode}' , $b );
		$a = array (
			'true'  => 'Включить' ,
			'false' => 'Выключить'
		);
		$b = '';
		foreach ( $a as $key => $value ) {
			if ( $cfg[ 'enable-rcon' ] == $key ) {
				$b .= '<option value="' . $key . '" selected="selected">' . $value . '</option>';
			} else {
				$b .= '<option value="' . $key . '">' . $value . '</option>';
			}
		}
		tpl::set ( '{enable-rcon}' , $b );
		$a = array (
			'DEFAULT' ,
			'FLAT' ,
			'LARGEBIOMES',
			'AMPLIFIED',
			'CUSTOMIZED',
			'Highlands'
		);
		$b = '';
		foreach ( $a as $key ) {
			if ( $cfg[ 'level-type' ] == $key ) {
				$b .= '<option value="' . $key . '" selected="selected">' . $key . '</option>';
			} else {
				$b .= '<option value="' . $key . '">' . $key . '</option>';
			}
		}
		tpl::set ( '{level-type}' , $b );
		$a = array (
			'0' => 'Peaceful' ,
			'1' => 'Easy' ,
			'2' => 'Normal' ,
			'3' => 'Hard'
		);
		$b = '';
		foreach ( $a as $key => $value ) {
			if ( $cfg[ 'difficulty' ] == $key ) {
				$b .= '<option value="' . $key . '" selected="selected">' . $value . '</option>';
			} else {
				$b .= '<option value="' . $key . '">' . $value . '</option>';
			}
		}
		tpl::set ( '{difficulty}' , $b );
		tpl::set ( '{motd}' , $cfg[ 'motd' ] );
		tpl::set ( '{max-build-height}' , $cfg[ 'max-build-height' ] );
		tpl::set ( '{texture-pack}' , $cfg[ 'texture-pack' ] );
		tpl::set ( '{rcon.password}' , $cfg[ 'rcon.password' ] );
		tpl::set ( '{level-name}' , $cfg[ 'map' ] );
		tpl::set ( '{level-seed}' , $cfg[ 'level-seed' ] );
		tpl::set ( '{id}' , $data[ 'id' ] );
		$b = '';
		foreach ( $data1 as $value ) {
			if ( $cfg[ 'core' ] == $value ) {
				$b .= '<option value="' . $value . '" selected="selected">' . $value . '</option>';
			} else {
				$b .= '<option value="' . $value . '">' . $value . '</option>';
			}
		}
		tpl::set ( '{core}' , $b );
		tpl::compile ( 'content' );
	}
}

?>