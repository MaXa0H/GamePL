<?php
class game_samp
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
		$cfg = array();
		foreach ( self::$conf as $key => $val ) {
			$cfg[ 'conf_' . $val[ 'var' ] ] = $val[ 'd' ];
		}
		servers::configure ( $cfg, $id );
	}

	public static function on ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$server = db::r ();
		$sql = db::q ( 'SELECT * FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
		$rate = db::r ( $sql );
		$cfg = servers::cfg ( $id );
		$sid = $server[ 'sid' ];
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= 'echo "maxplayers '.$server['slots'].'">"server.cfg";';
		$exec .= 'echo "port '.$server['port'].'">>"server.cfg";';
		foreach ( self::$conf as $key => $val ) {
			$exec .= 'echo "'.$val[ 'var' ].' '.$cfg[ 'conf_' . $val[ 'var' ] ].'">>"server.cfg";';
		}
		$conf2 = json_decode ( $cfg['dop'] , true );
		foreach($conf2 as $key=>$val){
			$exec .= 'echo "'.$key.' '.$val.'">>"server.cfg";';
		}
		if($rate['dir']==1){
			$versionsa = json_decode ( $rate[ 'versions' ] , true );
			$dir = $versionsa[$cfg['bild']]['dir'];
		}else{
			$dir = $rate['dir'];
		}
		$exec .= "cd /;cp -rv /host/" . $dir . "samp03svr /host/" . $server[ 'user' ] . "/" . $sid . "/samp03svr;";
		$exec .= "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "iconv -f UTF-8 -t WINDOWS-1251 -o server.cfg server.cfg;";
		$exec .= "chmod 755 samp03svr;";
		$exec .= "screen -dmS server_" . $sid . " sudo -u s" . $sid . " ./samp03svr;";
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
		ssh::exec_cmd ( "ps -ef  | grep s" . $id . " | grep -v sudo | grep -v screen | grep samp03svr | awk '{ print $3}';" );
		$data = trim ( ssh::get_output () );
		$data = explode ( "\n" , $data );
		if ( count ( $data ) > 3 ) {
			servers::kill_pid ( $data );

			return false;
		} else {
			return $data[ 0 ];
		}
	}

	public static function mon ( $data )
	{
		$server = lgsl_query_live ( "samp" , $data[ "ip" ] , $data[ "port" ] , $data[ "port" ] , $data[ "port" ] , "sep" );
		$hostname = mb_convert_encoding($server['s']['name'], "UTF-8", 'WINDOWS-1251');
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
				db::q ( "UPDATE gh_monitoring set name='" . api::cl ( $hostname ) . "',map='" . api::cl ( $server[ 's' ][ 'map' ] ) . "',online='" . api::cl ( $server[ 's' ][ 'players' ] ) . "',gamers='" . $sp . "',guard='" . $stats . "' where sid='" . $data[ "id" ] . "'" );
			} else {
				db::q ( "INSERT INTO gh_monitoring set name='" . api::cl ( $hostname ) . "',map='" . api::cl ( $server[ 's' ][ 'map' ] ) . "',online='" . api::cl ( $server[ 's' ][ 'players' ] ) . "',gamers='" . $sp . "',sid='" . $data[ "id" ] . "',guard='" . $stats . "'" );
			}
			db::q ( "UPDATE gh_servers set name='" . api::cl ( $hostname ) . "' where id='" . $data[ "id" ] . "'" );
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
	public static $conf     = array (
		array (
			'var'  => 'lanmode' ,
			'type' => '1' ,
			'val'  => array (
				'1'  => 'Включен' ,
				'0' => 'Выключен'
			),
			'd'=>'0'
		),
		array (
			'var'  => 'announce' ,
			'type' => '1' ,
			'val'  => array (
				'1'  => 'Включен' ,
				'0' => 'Выключен'
			),
			'd'=>'0'

		),
		array (
			'var'  => 'query' ,
			'type' => '1' ,
			'val'  => array (
				'1'  => 'Включен' ,
				'0' => 'Выключен'
			),
			'd'=>'1'
		),
		array (
			'var'  => 'chatlogging' ,
			'type' => '1' ,
			'val'  => array (
				'1'  => 'Включен' ,
				'0' => 'Выключен'
			),
			'd'=>'0'
		),
		array (
			'var'  => 'logtimeformat' ,
			'type' => '1' ,
			'val'  => array (
				'[%H:%M:%S]'  => '[%H:%M:%S]' ,
				'[%d/%m/%Y %H:%M:%S]' => '[%d/%m/%Y %H:%M:%S]'
			),
			'd'=>'[%H:%M:%S]'
		),
		array(
			'var'  => 'rcon_password' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Z\.\-\_\(\)\ ]{5,40}$/i",
			'd'=>'854374130'
		),
		array (
			'var'  => 'hostname' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\#\[\.\,\-\/\_\?\(\)\\ ]{2,300}$/i",
			'd'=>"Managed By [game-panel.ru] SA-MP"
		) ,
		array (
			'var'  => 'language' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\#\[\.\,\-\/\_\?\(\)\\ ]{2,300}$/i",
			'd'=>"Russia"
		) ,
		array (
			'var'  => 'gamemode0' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\#\[\.\-\/\_\?\(\)\\ ]{0,300}$/i",
			'd'=>"grandlarc 1"
		) ,
		array (
			'var'  => 'plugins' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\#\[\.\-\/\_\?\(\)\\ ]{0,300}$/i",
			'd'=>""
		) ,
		array (
			'var'  => 'filterscripts' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\#\[\.\-\/\_\?\(\)\\ ]{0,300}$/i",
			'd'=>"base gl_actions gl_property gl_realtime"
		) /*,
		array (
			'var'  => 'maxnpc' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,5}$/i",
			'd'=>'0'
		) */,
		array (
			'var'  => 'onfoot_rate' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,5}$/i",
			'd'=>'40'
		) ,
		array (
			'var'  => 'incar_rate' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,5}$/i",
			'd'=>'40'
		) ,
		array (
			'var'  => 'weapon_rate' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,5}$/i",
			'd'=>'40'
		) ,
		array (
			'var'  => 'stream_distance' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,5}$/i",
			'd'=>'300'
		) ,
		array (
			'var'  => 'stream_rate' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,5}$/i",
			'd'=>'1000'
		) ,
		array (
			'var'  => 'messageholelimit' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,10}$/i",
			'd'=>'1000'
		) ,
		array (
			'var'  => 'ackslimit' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,10}$/i",
			'd'=>'1000'
		) ,
		array (
			'var'  => 'playertimeout' ,
			'type' => '2' ,
			'reg'  => "/^[0-9]{1,10}$/i",
			'd'=>'1000'
		) ,
		array (
			'var'  => 'weburl' ,
			'type' => '2' ,
			'reg'  => "/^[0-9a-zA-Z\.\/\:\-\_]{4,100}$/i",
			'd'=>'game-panel.ru'
		)
	);
	public static function settings ( $data , $dir )
	{
		$conf = servers::cfg ( $data[ 'id' ] );
		$data2 = $_POST[ 'data' ];
		if ( $data2 ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( api::captcha_chek () ) {
				$new = array ();
				$cfg = self::$conf;
				foreach ( $cfg as $key => $val ) {
					if ( $val[ 'type' ] == "2" ) {
						if ( ! preg_match ( $val[ 'reg' ] , $data2[ $val[ 'var' ] ] ) ) {
							api::result ( l::t('Параметр').' ' . $val[ 'var' ] .' ' .l::t('заполнен неверно.') );
							return false;
						}
					}
					if ( $val[ 'type' ] == "1" ) {
						if ( ! $val[ 'val' ][ $data2[ $val[ 'var' ] ] ] ) {
							api::result ( l::t('Параметр').' ' . $val[ 'var' ] .' ' .l::t('заполнен неверно.') );

							return false;
						}
					}
					$new[ 'conf_' . $val[ 'var' ] ] = api::cl ( $data2[ $val[ 'var' ] ] );
				}
				$data3 = $_POST[ 'data3' ];
				$data4 = $_POST[ 'data4' ];
				$in = array();
				$i = 0;
				foreach ( $data3 as $key => $val ) {
					if ( in_array ( $val , array('bind','port','maxplayers') ) ) {
						api::result(l::t('Параметр').' '.$val.' '.l::t('запрещен!'));
						return false;
					}
					$i++;
					if($i>40){
						api::result(l::t('Максимальное количество дополнительных параметров 40 шт.'));
						return false;
					}
					if ( ! preg_match ( "/^[0-9a-zA-Z\_]{4,40}$/i" , $val ) ) {
						api::result(l::t('Ошибка ввода параметра'));
						return false;
					}
					if ( ! preg_match ( "/^[0-9a-zA-Zа-яйцукенгшщзхъфывапролджэячсмитьбюА-ЯЙЦУКЕНГШЩЗХЪФЫВАПРОЛЖЭЯЧСМИТЬБЮ\]\|\#\[\.\-\/\_\?\(\)\\ ]{0,90}$/i" , $data4[$key] ) ) {
						api::result(l::t('Ошибка ввода параметра'));
						return false;
					}
					$in[api::cl($val)] = api::cl($data4[$key]);
				}
				$new['dop'] = json_encode($in);
				servers::configure ( $new , $data[ 'id' ] );
				api::result ( l::t('Настройки сохранены') , true );
			}
		}
		tpl::load ( 'servers-settings-game-samp' );
		api::captcha_create ();
		foreach ( self::$conf as $key => $val ) {
			tpl::load ( 'servers-settings-game-data' );
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
			tpl::set ( '{name}' , $val[ 'var' ] );
			tpl::set ( '{key}' , $val[ 'var' ] );

			tpl::compile ( 'options' );
		}
		$conf2 = json_decode ( $conf['dop'] , true );
		foreach($conf2 as $key=>$val){
			tpl::load ( 'servers-settings-game-dop' );
			tpl::set('{key}',$key);
			tpl::set('{val}',$val);
			tpl::compile ( 'options' );
		}
		tpl::set ( '{id}' , $data[ 'id' ] );
		tpl::set ( '{cfg}' , tpl::result('options') );
		tpl::compile ( 'content' );
	}
}

?>
