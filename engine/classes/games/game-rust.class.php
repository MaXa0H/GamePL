<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_rust
{
	public static function info ( $data )
	{
		global $conf;
		$cfg[ 'rcon' ] = 0;
		$cfg[ 'admins' ] = '';
		$cfg[ 'update' ] = 0;
		$cfg[ 'online' ] = 1;
		$cfg[ 'gadget' ] = 0;
		$cfg[ 'repository' ] = 1;
		$cfg[ 'fastdl' ] = 0;
		$cfg[ 'fps' ] = 0;
		$cfg[ 'reinstall' ] = 1;
		$cfg[ 'friends' ] = 0;
		$cfg[ 'ftp' ] = 1;
		$cfg[ 'settings' ] = 1;
		$cfg[ 'console' ] = 0;
		$cfg[ 'tv' ] = 0;
		$cfg[ 'eac' ] = 0;
		$cfg[ 'maps' ] = 0;
		$cfg[ 'rcon_kb' ] = 0;
		$cfg[ 'tickrate' ] = 0;
		$cfg[ 'ftp_root' ] = "/server/";

		return $cfg[ $data ];
	}

	public static function install ( $id )
	{
		$data = array ();
		$data[ 'conf_rcon.password' ] = api::generate_password ( '10' );
		$data[ 'conf_server.hostname' ] = 'Rust ';
		$data[ 'conf_server.globalchat' ] = 'true';
		$data[ 'conf_airdrop.min_players' ] = '10';
		servers::configure ( $data , $id );
	}

	public static function on ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$server = db::r ();
		$sql = db::q ( 'SELECT * FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
		$rate = db::r ( $sql );
		$cfg = servers::cfg ( $id );

		tpl::set_block ( "'\\[ver\\](.*?)\\[/ver\\]'si" , "\\1" );
		$versionsa = json_decode ( $rate[ 'versions' ] , true );
		$build = $versionsa[$cfg['bild']]['type'];

		$slots = $server[ 'slots' ];
		$sid = $server[ 'sid' ];
		$run = trim(file_get_contents ( ROOT . '/data/wine' ));
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "echo \"cd /\">\"run.sh\";";
		$exec .= "echo \"rm -f /host/" . $server[ 'user' ] . "/" . $sid . "/server/RustDedicated.exe\">\"run.sh\";";
		$exec .= "echo \"cp -rv /host/" . $versionsa[$cfg['bild']]['dir'] . "/RustDedicated.exe /host/" . $server[ 'user' ] . "/" . $sid . "/server/RustDedicated.exe\">>\"run.sh\";";
		$exec .= "echo \"cd /host/" . $server[ 'user' ] . "/" . $sid . "/server/\">>\"run.sh\";";
		if ( $build != "0" ) {
			foreach ( self::$conf_leg as $key => $val ) {
				$exec .= "echo \"sed -i \"/" . $val[ 'var' ] . "/d\" \"serverdata/cfg/server.cfg\"\">>\"run.sh\";";
				if ( $cfg[ 'conf_' . $val[ 'var' ] ] || $cfg[ 'conf_' . $val[ 'var' ] ] === 0 ) {
					$exec .= "echo 'echo \"" . $val[ 'var' ] . " \\\"" . $cfg[ 'conf_' . $val[ 'var' ] ] . "\\\"\">>\"serverdata/cfg/server.cfg\"'>>\"run.sh\";";
				}
			}

			$exec .= "echo \"sed -i \"/server.maxplayers/d\" \"serverdata/cfg/server.cfg\"\">>\"run.sh\";";
			$exec .= "echo 'echo \"server.maxplayers " . $slots . "\">>\"serverdata/cfg/server.cfg\"'>>\"run.sh\";";

			$exec .= "echo \"sed -i \"/server.port/d\" \"serverdata/cfg/server.cfg\"\">>\"run.sh\";";
			$exec .= "echo 'echo \"server.port " . $server[ 'port' ] . "\">>\"serverdata/cfg/server.cfg\"'>>\"run.sh\";";


			$exec .= "echo \"sed -i \"/save.autosavetime/d\" \"serverdata/cfg/server.cfg\"\">>\"run.sh\";";
			$exec .= "echo 'echo \"save.autosavetime 120\">>\"serverdata/cfg/server.cfg\"'>>\"run.sh\";";

		}
		$exec .= "echo \"chown -R s".$sid.":s".$sid." .\">>\"run.sh\";";
		$exec .= "echo \"chmod -R 771 .\">>\"run.sh\";";
		if ($build == "0" ) {
			$put = "screen -dmS server_" . $sid . " su s" . $sid . " -c 'xvfb-run --auto-servernum ".$run." RustDedicated.exe -batchmode";
			$put .= " +server.port " . $server[ 'port' ];
			$put .= " +server.identity \\\"gamepl\\\"";
			$put .= " +server.maxplayers " . $slots;
			$put .= " +rcon.port " . ( $server[ 'port' ] + 1 );
			$put .= " +rcon.ip " . $server[ 'ip' ];
			$put .= " +server.saveinterval 60";
			$put .= " -cfg \\\"server/gamepl/cfg/server.cfg\\\"";
			$put .= " -logFile \\\"log.txt\\\"";
			$put .= " +server.tickrate 30";
			if ( $slots > 250 ) {
				$put .= " +server.worldsize 4000";
			} else {
				$put .= " +server.worldsize 3000";
			}
			foreach ( self::$conf as $key => $val ) {
				$put .= " +" . $val[ 'var' ] . " \\\"" . $cfg[ 'conf_' . $val[ 'var' ] ] . "\\\"";
			}
			$put .= " +chat.serverlog false'";
			$exec .= "echo \"" . $put . "\">>\"run.sh\";";

		} else {
			$put = "";

			$put .= "screen -dmS server_" . $sid . " ";
			$put .= "su s" . $sid . " -c '";
			$put .= "xvfb-run --auto-servernum";
			$put .= " ".$run." RustDedicated.exe";
			$put .= " -batchmode";
			$put .= " -hostname \\\"" . api::cl ( $cfg[ 'conf_server.hostname' ] ) . "\\\"";
			$put .= " -port " . $server[ 'port' ] . "";
			$put .= " -datadir \\\"serverdata/\\\"";
			$put .= " -maxplayers " . $slots . "";
			$put .= " -cfg \\\"serverdata/cfg/server.cfg\\\"";
			$put .= " -oxidedir \\\"save/oxide\\\"'";
			$exec .= "echo \"" . $put . "\">>\"run.sh\";";
		}


		ssh::exec_cmd ( $exec );

		$exec = "cd /host/" . $server[ 'user' ] . "/;";
		$exec .= "chown -R  s" . $sid . ":s" . $sid . " " . $sid . ";";
		$exec .= "chmod -R 771 " . $sid . ";";
		$exec .= "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "./run.sh;";
		ssh::exec_cmd ( $exec );

	}

	public static function update ( $data )
	{

	}

	public static function get_pid ( $id )
	{
		ssh::exec_cmd ( "top  -n 1 -b -u s" . $id . " | grep Rust | awk '{ print $1}';" );
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

		api::inc ( 'SourceQuery/SourceQuery' );
		$Query = new SourceQuery();
		try {
			$Query->Connect ( $data[ 'ip' ] , $data[ 'port' ] + 1 , 3 , SourceQuery :: SOURCE );
			$Info = $Query->GetInfo ();
			if (!trim ( $Info[ 'Map' ] ) ) {
				$Info[ 'Map' ] = "- - - -";
			}
			$players = $Query->GetPlayers ();
			$u = array ();
			$sp = base64_encode ( json_encode ( $u ) );

			$ttt = api::gettime ();
			$cfg = servers::cfg ( $data[ 'id' ] );
			$sql344 = db::q ( 'SELECT * FROM gh_boxes_games where box="' . $data[ 'box' ] . '" and game="' . $data[ 'game' ] . '"' );
			$rows344 = db::r ( $sql344 );
			$pcpu = (int) ( ( 100 / ( $rows344[ 'cpu' ] * $data[ 'slots' ] ) ) * $cfg[ 'cpu' ] );
			if ( $pcpu > 100 ) {
				$pcpu = 100;
			}
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
			if ( $pram > 100 ) {
				$pram = 100;
			}
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
			if ( $phdd > 100 ) {
				$phdd = 100;
			}
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
			if ( ! $Info[ 'HostName' ] ) {
				$Info[ 'HostName' ] = "Rust";
			}
			db::q ( 'SELECT * FROM gh_monitoring where sid="' . $data[ "id" ] . '"' );
			if ( db::n () == "1" ) {
				db::q ( "UPDATE gh_monitoring set name='" . api::cl ( $Info[ 'HostName' ] ) . "',map='" . api::cl ( $Info[ 'Map' ] ) . "',online='" . (int) $Info[ 'Players' ] . "',gamers='" . $sp . "',guard='0' where sid='" . $data[ "id" ] . "'" );
			} else {
				db::q ( "INSERT INTO gh_monitoring set name='" . api::cl ( $Info[ 'HostName' ] ) . "',map='" . api::cl ( $Info[ 'Map' ] ) . "',online='" . (int) $Info[ 'Players' ] . "',gamers='" . $sp . "',sid='" . $data[ "id" ] . "',guard='0'" );
			}
			db::q ( "UPDATE gh_servers set name='" . api::cl ( $Info[ 'HostName' ] ) . "' where id='" . $data[ "id" ] . "'" );
		} catch ( Exception $e ) {
		}
		$Query->Disconnect ();
	}

	public static $conf     = array (
		array (
			'name' => 'Название сервера' ,
			'var'  => 'server.hostname' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\#\[\.\-\/\_\?\(\)\\ ]{2,90}$/i"
		) ,
		array (
			'name' => 'Rcon пароль' ,
			'var'  => 'rcon.password' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Z\.\-\_\(\)\ ]{2,40}$/i"
		) ,
		array (
			'name' => 'Минимальное количество игроков для airdrops' ,
			'var'  => 'airdrop.min_players' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{0,3}$/i"
		) ,
		array (
			'name' => 'Seed от 1 до 5 символов' ,
			'var'  => 'server.seed' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{0,5}$/i"
		) ,
		array (
			'name' => 'Разрешить на сервер заходить только с данной стим группы' ,
			'var'  => 'server.steamgroup' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{0,30}$/i"
		) ,
		array (
			'name' => 'Общий чат' ,
			'var'  => 'server.globalchat' ,
			'type' => '1' ,
			'val'  => array (
				'true'  => 'Включен' ,
				'false' => 'Выключен'
			)
		) ,
		array (
			'name' => 'VAC and EAC secured' ,
			'var'  => 'server.secure' ,
			'type' => '1' ,
			'val'  => array (
				'1' => 'Включен' ,
				'0' => 'Выключен'
			)
		)
	);
	public static $conf_leg = array (
		array (
			'name' => 'Название сервера' ,
			'var'  => 'server.hostname' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\[\.\-\/\_\?\(\)\\ ]{2,60}$/i"
		) ,
		array (
			'name' => 'Rcon пароль' ,
			'var'  => 'rcon.password' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Z\.\-\_\(\)\ ]{2,40}$/i"
		) ,
		array (
			'name' => 'Минимальное количество игроков для airdrops' ,
			'var'  => 'airdrop.min_players' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{0,3}$/i"
		) ,
		array (
			'name' => 'Разрешить на сервер заходить только с данной стим группы' ,
			'var'  => 'server.steamgroup' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{0,30}$/i"
		) ,
		array (
			'name' => 'PVP' ,
			'var'  => 'server.pvp' ,
			'type' => '1' ,
			'val'  => array (
				'1' => 'Включен' ,
				'0' => 'Выключен'
			)
		) ,
		array (
			'name' => 'VAC and EAC secured' ,
			'var'  => 'server.secure' ,
			'type' => '1' ,
			'val'  => array (
				'1' => 'Включен' ,
				'0' => 'Выключен'
			)
		)
	);

	public static function settings ( $data )
	{
		$data2 = $_POST[ 'data' ];
		$conf = servers::cfg ( $data[ 'id' ] );
		if ( $data2 ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( api::captcha_chek () ) {
				$new = array ();
				if ( $conf[ 'bild' ] == "0" ) {
					$cfg = self::$conf;
				} else {
					$cfg = self::$conf_leg;
				}
				foreach ( $cfg as $key => $val ) {
					if ( $val[ 'type' ] == "2" ) {
						if ( ! preg_match ( $val[ 'reg' ] , $data2[ $val[ 'var' ] ] ) ) {
							api::result ( l::t('Параметр').' ' . $val[ 'name' ] .' ' .l::t('заполнен неверно.') );

							return false;
						}
					}
					if ( $val[ 'type' ] == "1" ) {
						if ( ! $val[ 'val' ][ $data2[ $val[ 'var' ] ] ] ) {
							api::result ( l::t('Параметр').' ' . $val[ 'name' ] . ' '.l::t('заполнен неверно.') );

							return false;
						}
					}
					$new[ 'conf_' . $val[ 'var' ] ] = api::cl ( $data2[ $val[ 'var' ] ] );
				}
				servers::configure ( $new , $data[ 'id' ] );
				api::result ( l::t('Настройки сохранены') , true );
			}
		}
		if ( $conf[ 'bild' ] == "0" ) {
			$cfg = self::$conf;
		} else {
			$cfg = self::$conf_leg;
		}
		foreach ( $cfg as $key => $val ) {
			tpl::load ( 'servers-settings-game-option' );
			tpl::set_block ( "'\\[input\\](.*?)\\[/input\\]'si" , "" );
			tpl::set_block ( "'\\[select\\](.*?)\\[/select\\]'si" , "" );
			if ( $val[ 'type' ] == "2" ) {
				tpl::set_block ( "'\\[input\\](.*?)\\[/input\\]'si" , "\\1" );
				tpl::set ( '{val}' , $conf[ 'conf_' . $val[ 'var' ] ] );
			}
			if ( $val[ 'type' ] == "1" ) {
				$set = "";
				foreach ( $val[ 'val' ] as $key2 => $val2 ) {
					if ( $conf[ 'conf_' . $val[ 'var' ] ] == $key2 ) {
						$set .= '<option value="' . $key2 . '" selected>' . l::t($val2) . '</option>';
					} else {
						$set .= '<option value="' . $key2 . '">' . l::t($val2) . '</option>';
					}
				}
				tpl::set ( '{val}' , $set );
				tpl::set_block ( "'\\[select\\](.*?)\\[/select\\]'si" , "\\1" );
			}
			tpl::set ( '{name}' , l::t($val[ 'name' ]) );
			tpl::set ( '{key}' , $val[ 'var' ] );

			tpl::compile ( 'options' );
		}
		tpl::load ( 'servers-settings-game-rust' );
		tpl::set ( '{id}' , $data[ 'id' ] );
		tpl::set ( '{options}' , tpl::result ( 'options' ) );
		api::captcha_create ();
		tpl::compile ( 'content' );
	}


	public static function fastdl_on ()
	{
	}

	public static function admins ( $data )
	{
	}

	public static function rcon ( $data )
	{
	}

	public static function maps ( $data )
	{
	}

	public static function maps_go ( $map )
	{
	}

	public static function rcon_bk ( $data )
	{
	}
}

?>