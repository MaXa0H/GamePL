<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_mta
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
		@unlink (ROOT . '/conf/mta/' . $id . '.cfg');
		servers::configure ( array() , $id );
	}

	public static function on ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$server = db::r ();
		$sql = db::q ( 'SELECT * FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
		$rate = db::r ( $sql );
		$cfg = servers::cfg ( $id );
		$sid = $server[ 'sid' ];
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/mods/deathmatch/;";
		$file3 = ROOT . '/conf/mta/' . $server[ 'id' ] . '.cfg';
		$file2 = "/host/" . $server[ 'user' ] . "/" . $sid . "/mods/deathmatch/mtaserver.conf";
		if ( ! @file ( $file3 ) ) {
			api::result('Сконфигурируйте настройки сервера');
			return false;
		}
		ssh::send_file ( $file3 , $file2 , 0777 );
		$exec .= 'chmod 755 mtaserver.conf;';
		$exec .= "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		if($rate['dir']==1){
			$versionsa = json_decode ( $rate[ 'versions' ] , true );
			$dir = $versionsa[$cfg['bild']]['dir'];
		}else{
			$dir = $rate['dir'];
		}
		$exec .= "cd /;cp -rv /host/" . $dir . "mta-server /host/" . $server[ 'user' ] . "/" . $sid . "/mta-server;";
		$exec .= "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "chmod 755 mta-server;";
		$exec .= "screen -dmS server_" . $sid . " sudo -u s" . $sid . " ./mta-server;";
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
		ssh::exec_cmd ( "ps -ef  |grep s" . $id . " | grep -v sudo | grep -v screen | grep mta-server | awk '{ print $3}';" );
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
		$server = lgsl_query_live ( "mta" , $data[ "ip" ] , $data[ "port" ] , $data[ "port" ] + 123 , $data[ "port" ] , "sep" );
		$server = servers::convert ( $server , servers::detect ( $server ) );
		$status = servers::status ( $server[ 'b' ][ 'status' ] , $server[ 's' ][ 'password' ] , @$server[ 'b' ][ 'pending' ] );
		if ( @$status == "3" || @$status == "2" ) {
			m::d ( 'server_offline_' . $data[ 'id' ] );
			if ( (int) ( $server[ 's' ][ 'playersmax' ] - 1 ) > $data[ 'slots' ] ) {
				api::inc ( 'servers/act' );
				servers_act::off ( $data[ 'id' ] );
				if ( servers::$cron ) {
					cron::w ( "	->kill ( playersmax > slots )" );
				}

				return false;
			}
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

			$u = array ();
			$u[ 'name' ] = l::t('Имя');
			$u[ 'score' ] = l::t('Счет');
			$u[ 'time' ] = l::t('Время');
			$u[ 'title' ] = '1';
			$sp[ ] = $u;
			$n = 0;
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
		}else{
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

	public static function settings ( $data , $dir )
	{
		$file = ROOT . '/conf/mta/' . $data[ 'id' ] . '_1.cfg';
		$file3 = ROOT . '/conf/mta/' . $data[ 'id' ] . '.cfg';
		if ( $_POST[ 'data' ] ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( api::captcha_chek () ) {
				$cfg = servers::cfg ( $data[ 'id' ] );
				$exec = "<config>\n";
				$exec .= "<serverip>" . $data[ 'ip' ] . "</serverip>\n";
				$exec .= "<serverport>" . $data[ 'port' ] . "</serverport>\n";
				$exec .= "<maxplayers>" . $data[ 'slots' ] . "</maxplayers>\n";
				$exec .= "<httpserver>1</httpserver>";
				$exec .= "<httpport>" . ( (int) $data[ 'port' ] + 10000 ) . "</httpport>\n";
				$exec .= "<resource src='webadmin' startup='0' protected='0'/>\n";
				$exec .= $_POST[ 'data' ][ 'cfg' ] . "\n";
				$exec .= "<serverip>" . $data[ 'ip' ] . "</serverip>\n";
				$exec .= "<serverport>" . $data[ 'port' ] . "</serverport>\n";
				$exec .= "<httpserver>1</httpserver>";
				$exec .= "<httpport>" . ( (int) $data[ 'port' ] + 10000 ) . "</httpport>\n";
				$exec .= "<maxplayers>" . $data[ 'slots' ] . "</maxplayers>\n";
				$exec .= "<resource src='webadmin' startup='0' protected='0'/>\n";
				$exec .= "</config>";
				$file2 = fopen ( $file3 , "w" );
				fputs ( $file2 , $exec );
				fclose ( $file2 );
				$file2 = fopen ( $file , "w" );
				fputs ( $file2 , base64_encode ( $_POST[ 'data' ][ 'cfg' ] ) );
				fclose ( $file2 );
				api::result ( l::t('Настройки успешно сохранены') , true );
			}
		}
		tpl::load ( 'servers-settings-game-mta' );
		api::captcha_create ();
		if ( ! @file ( $file ) ) {
			$file2 = fopen ( $file , "w" );
			fputs ( $file2 , base64_encode ( file_get_contents ( ROOT . '/conf/mta/default.txt' ) ) );
			fclose ( $file2 );
		}
		tpl::set ( '{cfg}' , base64_decode ( file_get_contents ( $file ) ) );
		tpl::set ( '{id}' , $data[ 'id' ] );
		tpl::compile ( 'content' );
	}
}

?>