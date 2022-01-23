<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class servers
{
	public static $games       = array (
		'cs'     => 'Counter-Strike: 1.6' ,
		'csgo'   => 'Counter-Strike: GO' ,
		'css'    => 'Counter-Strike: Source' ,
		'cssold' => 'Counter-Strike: Source v34' ,
		'tf2'    => 'Team Fortress 2' ,
		'hldm'   => 'Half-Life: Deathmatch' ,
		'dods'   => 'Day of Defeat: Source' ,
		'gm'     => "Garrys mod" ,
		'l4d2'   => 'Left 4 Dead 2' ,
		'mc'     => 'Minecraft' ,
		'kf'     => 'Killing Floor' ,
		'mta'    => 'GTA: Multi Theft Auto' ,
		'samp'   => 'GTA: San Andreas Multiplayer' ,
		'crmp'   => 'GTA: Criminal Russia MP' ,
		'ts3'    => 'TeamSpeak 3'
	);
	public static $game_status = array (
		'1' => 'Включен' ,
		'2' => 'Выключен' ,
		'3' => 'Устанавливается' ,
		'4' => 'Обновляется' ,
		'5' => 'Переустанавливается' ,
		'6' => 'Не оплачен'
	);
	public static $img_mon     = array (
		'cs'     => array (
			'1' => 'img/mon/cs/1.jpg' ,
			'2' => 'img/mon/cs/2.jpg'
		) ,
		'csgo'   => array (
			'1' => 'img/mon/csgo/1.jpg' ,
			'2' => 'img/mon/csgo/2.jpg'
		) ,
		'css'    => array (
			'1' => 'img/mon/css/1.jpg' ,
			'2' => 'img/mon/css/2.jpg'
		) ,
		'cssold' => array (
			'1' => 'img/mon/cssold/1.jpg' ,
			'2' => 'img/mon/cssold/2.jpg'
		) ,
		'tf2'    => array (
			'1' => 'img/mon/tf2/1.jpg' ,
			'2' => 'img/mon/tf2/2.jpg'
		) ,
		'hldm'   => array (
			'1' => 'img/mon/hldm/1.jpg' ,
			'2' => 'img/mon/hldm/2.jpg'
		) ,
		'dods'   => array (
			'1' => 'img/mon/dods/1.jpg' ,
			'2' => 'img/mon/dods/2.jpg'
		) ,
		'gm'     => array (
			'1' => 'img/mon/gm/1.jpg' ,
			'2' => 'img/mon/gm/2.jpg'
		) ,
		'l4d2'   => array (
			'1' => 'img/mon/l4d2/1.jpg' ,
			'2' => 'img/mon/l4d2/2.jpg'
		) ,
		'mc'     => array (
			'1' => 'img/mon/mc/1.jpg' ,
			'2' => 'img/mon/mc/2.jpg'
		) ,
		'kf'     => array (
			'1' => 'img/mon/kf/1.jpg' ,
			'2' => 'img/mon/kf/2.jpg'
		) ,
		'mta'    => array (
			'1' => 'img/mon/mta/1.jpg' ,
			'2' => 'img/mon/mta/2.jpg'
		) ,
		'samp'   => array (
			'1' => 'img/mon/samp/1.jpg' ,
			'2' => 'img/mon/samp/2.jpg'
		) ,
		'crmp'   => array (
			'1' => 'img/mon/crmp/1.jpg' ,
			'2' => 'img/mon/crmp/2.jpg'
		) ,
	);
	public static $speedbar    = false;
	public static $cron        = false;
	public static function ip_server($box){
		db::q ( 'SELECT * FROM gh_boxes where id="' . $box . '"' );
		$box = db::r ();
		if($box['ip']){
			return $box['ip'];
		}else{
			return $box['rip'];
		}
	}
	public static function ip_server2($box){
		db::q ( 'SELECT * FROM gh_boxes where id="' . $box . '"' );
		$box = db::r ();
		if($box['rip']){
			return $box['rip'];
		}else{
			return $box['ip'];
		}
	}
	public static function ts3_token ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек') );
			} else {
				$class = self::game_class ( $row[ 'game' ] );
				if ( $row[ 'status' ] != "1" ) {
					api::result ( l::t('Включите сервер') );
				} else {
					api::inc ( 'ssh2' );
					api::inc ( 'telnet' );
					//if ( ssh::gh_box ( $row[ 'box' ] ) ) {
						$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
						$row3 = db::r ();
						if ( ts3::connect ( $row3[ 'ip' ] , $row3[ 'port' ] , $row3[ 'login' ] , $row3[ 'pass' ] ) ) {
							if($data123[ 'key' ] = $class::create_token ( $row[ 'sid' ] )){
								servers::configure ( $data123 , $row[ 'id' ] );
								telnet::disconnect ();
								api::result ( l::t('Новый токен сгенерирован') , true );
							}else{
								telnet::disconnect ();
								api::result (ts3::$error);
							}
						} else {
							api::result ( l::t('Не удалось установить соединение с сервером') );
						}
					//} else {
					//	api::result ( l::t('Не удалось установить соединение с сервером') );
					//}
				}
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}

	public static function ts3_token2 ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек') );
			} else {
				$class = self::game_class ( $row[ 'game' ] );
				if ( $row[ 'status' ] != "1" ) {
					api::result ( l::t('Включите сервер') );
				} else {
					api::inc ( 'ssh2' );
					api::inc ( 'telnet' );
					//if ( ssh::gh_box ( $row[ 'box' ] ) ) {
						$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
						$row3 = db::r ();
						if ( ts3::connect ( $row3[ 'ip' ] , $row3[ 'port' ] , $row3[ 'login' ] , $row3[ 'pass' ] ) ) {
							if($data123[ 'key' ] = $class::create_token ( $row[ 'sid' ] )){
								servers::configure ( $data123 , $row[ 'id' ] );
								telnet::disconnect ();
								tpl::load ( 'servers-ts3-token' );
								tpl::set ( '{key}' , $data123[ 'key' ] );
								tpl::compile ( 'content' );
								if ( api::modal () ) {
									die( tpl::result ( 'content' ) );
								}
								api::result ( l::t('Новый токен сгенерирован') , true );
							}else{
								telnet::disconnect ();
								api::result (ts3::$error);
							}
						} else {
							api::result ( l::t('Не удалось установить соединение с сервером') );
						}
					//} else {
					//	api::result ( l::t('Не удалось установить соединение с сервером') );
					//}
				}
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}

	public static function ts3_domain ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек') );
			} else {
				$class = self::game_class ( $row[ 'game' ] );
				if ( $file = file_get_contents ( ROOT . '/data/tsdns.ini' ) ) {
					if ( $conf2 = json_decode ( $file , true ) ) {
						if ( $conf2[ 'on' ] ) {
							$data = $_POST[ 'data' ];
							if ( $data ) {
								if ( ! in_array ( $_POST[ 'data' ][ 'domain2' ] , $conf2[ 'domain' ] ) ) {
									api::result ( l::t('Домен не найден') );
								} else {
									if ( ! preg_match ( "/^[0-9a-zA-Z]{3,20}$/i" , trim ( $_POST[ 'data' ][ 'domain' ] ) ) ) {
										api::result ( l::t('Поддомен указан не верно') );
									} else {
										$domain = api::cl ( $_POST[ 'data' ][ 'domain' ] . '.' . $_POST[ 'data' ][ 'domain2' ] );
										$sql12 = db::q ( 'SELECT * FROM gh_servers where domain="' . $domain . '"' );
										if ( db::n ( $sql12 ) == 1 ) {
											api::result (l::t( 'Домен занят') );
										} else {
											$sql12 = db::q ( 'UPDATE gh_servers set domain="' . $domain . '" where id="' . $id . '"' );
											$class::install_domain ( $row[ 'ip' ] , $row[ 'port' ] , $domain , $row[ 'domain' ] );
											api::result ( l::t('Домен изменен') , true );
										}
									}
								}
							}
							$domains = "";
							foreach ( $conf2[ 'domain' ] as $key => $val ) {
								$domains .= '<option value="' . $val . '">.' . $val . '</option>';
							}
							tpl::load ( 'servers-ts3-domain' );
							tpl::set ( '{id}' , $id );
							tpl::set ( '{domains}' , $domains );
							tpl::set ( '{id}' , $id );
							api::captcha_create ();
							tpl::compile ( 'content' );
							die( tpl::result ( 'content' ) );
						}
					}
				}
				api::result ( l::t('Данная функция отключена' ));
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}

	public static function rcon_bk ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
			api::nav ( "/servers" , l::t("Серверы") );
			api::nav ( "/servers/" . $id , $adress );
			api::nav ( "" , 'Rcon' , '1' );
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек') );
			} else {
				if ( $row[ 'status' ] != "1" ) {
					api::result ( l::t('Включите сервер') );
				} else {
					$class = self::game_class ( $row[ 'game' ] );
					if ( $class::info ( 'rcon_kb' ) ) {
						$class::rcon_bk ( $row );
					} else {
						api::result ( l::t('Данная функция отключена') );
					}
				}
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}

	public static function delete ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( api::admin ( 'servers_delete' ) ) {
			db::q ( 'SELECT id FROM gh_servers where id="' . $id . '"' );
			if ( db::n () == "1" ) {
				db::q ( "UPDATE gh_servers set time='0' where id='" . $id . "'" );
				api::result ( l::t('Сервер поставлен на удаление') , true );
			} else {
				api::result ( l::t('Сервер не найден') );
			}
		} else {
			api::result ( l::t('Недостаточно привелегий') );
		}
	}

	public static function full_del ( $id , $id2 , $user )
	{

		db::q ( 'SELECT * FROM gh_servers where id="' . $id2 . '"' );
		$server = db::r ();
		if($server['mysql']){
			api::inc ( 'servers/mysql' );
			servers_mysql::$cron = true;
			servers_mysql::dell($server['mysql']);
		}
		if($server['web']){
			api::inc ( 'servers/isp' );
			servers_isp::$cron = true;
			servers_isp::dell($server['web']);
		}
		api::inc ( 'servers/ftp' );
		servers_ftp::off ( $id2 );
		ssh::exec_cmd ( 'cd /host/' . $user . '/;screen -dmS del rm -R ' . $id . ';userdel s' . $id . ';' );
		ssh::exec_cmd ( 'cd /etc/apache2/fastdl/;rm ' . $id . '.conf;' );
		db::q ( "delete from gh_monitoring where sid='" . $id2 . "'" );
		db::q ( "delete from gh_monitoring_cpu_time where sid='" . $id2 . "'" );
		db::q ( "delete from gh_monitoring_hdd_time where sid='" . $id2 . "'" );
		db::q ( "delete from gh_monitoring_ram_time where sid='" . $id2 . "'" );
		db::q ( "delete from gh_monitoring_time where sid='" . $id2 . "'" );
		db::q ( "delete from gh_servers_cfg where server='" . $id2 . "'" );
		db::q ( "delete from gh_addons_install where server='" . $id2 . "'" );
		db::q ( "delete from gh_servers where id='" . $id2 . "'" );
		db::q ( "DELETE FROM maps_install where server='" . $id2 . "'" );
		db::q ( "DELETE FROM gh_servers_admins where server='" . $id2 . "'" );
		db::q ( "DELETE FROM gh_servers_admins_rates where server='" . $id2 . "'" );
	}

	public static function game_class ( $game )
	{
		if ( ! @file ( ROOT . '/engine/classes/games/game-' . $game . '.class.php' ) ) {
			die;
		} else {
			include_once ( ROOT . '/engine/classes/games/game-' . $game . '.class.php' );

			return $game = 'game_' . $game;
		}
	}

	public static function cfg ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers_cfg where server="' . $id . '"' );
		$data = array ();
		while ( $row = db::r () ) {
			$data[ $row[ 'cfg' ] ] = $row[ 'value' ];
		}

		return $data;
	}

	public static function get ( $id , $act )
	{
		global $lang;
		if ( ! preg_match ( "/^[a-z0-9A-Z]{1,20}$/i" , $act ) ) {
			api::result ( "Hacking attempt" );

			return false;
		}
		tpl::load ( 'servers-get-act' );
		if ( $act == "reinstall" ) {
			tpl::set_block ( "'\\[reinstall\\](.*?)\\[/reinstall\\]'si" , "\\1" );
			tpl::set_block ( "'\\[update\\](.*?)\\[/update\\]'si" , "" );
			$cfg = servers::cfg ( $id );
			if ( isset( $cfg[ 'bild' ] ) ) {
				db::q ( 'SELECT rate FROM gh_servers where id="' . $id . '"' );
				$server = db::r ();

				$sql = db::q ( 'SELECT versions FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
				$rate = db::r ( $sql );
				$cfg = servers::cfg ( $id );
				tpl::set_block ( "'\\[mc\\](.*?)\\[/mc\\]'si" , "\\1" );
				$versionsa = json_decode ( $rate[ 'versions' ] , true );
				foreach ( $versionsa as $key => $val ) {
					$ver .= '<option value="' . $key . '">' . $val[ 'name' ] . '</options>';
				}
				tpl::set ( '{versions}' , $ver );
			} else {
				tpl::set_block ( "'\\[mc\\](.*?)\\[/mc\\]'si" , "" );
			}
		} elseif ( $act == "update" ) {
			tpl::set_block ( "'\\[mc\\](.*?)\\[/mc\\]'si" , "" );
			tpl::set_block ( "'\\[update\\](.*?)\\[/update\\]'si" , "\\1" );
			tpl::set_block ( "'\\[reinstall\\](.*?)\\[/reinstall\\]'si" , "" );
		} else {
			api::result ( "Hacking attempt" );

			return false;
		}
		tpl::set ( '{act}' , $act );
		tpl::set ( '{id}' , $id );
		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		} else {
			api::nav ( '' , l::t('Подтверждение') , '1' );
		}
	}

	public static function configure ( $data , $id )
	{
		foreach ( $data as $key => $value ) {
			db::q ( "SELECT * FROM gh_servers_cfg where cfg='" . $key . "' and server='" . $id . "'" );
			if ( db::n () == 0 ) {
				db::q ( "INSERT INTO gh_servers_cfg set cfg='" . $key . "',value='" . $value . "',server='" . $id . "'" );
			} else {
				$row = db::r ();
				db::q ( "UPDATE gh_servers_cfg set value='" . $value . "' where id='" . $row[ 'id' ] . "'" );
			}
		}
	}

	public static function set_cpu ( $id , $slots , $pid , $box_id , $game )
	{
		db::q ( 'SELECT * FROM gh_boxes_games where box="' . $box_id . '" and game="' . $game . '"' );
		$box = db::r ();
		if ( $game == "samp" || $game == "mta" ) {
			$cpu = ( $slots / 10 ) * $box[ 'cpu' ];
		} else {
			$cpu = $slots * $box[ 'cpu' ];
		}
		$cpu2 = (int)$cpu;
		ssh::exec_cmd ( 'screen -dmS server_cpu_' . $id . ' cpulimit -v -z -p ' . $pid . ' -l ' . $cpu2 . ';' );
		return true;
		$cpu2 = $cpu / 100;
		$cpu3 = $cpu;
		if ( $cpu2 > 1 ) {
			$i = 1;
			while ( true == true ) {
				$i ++;
				if ( $cpu2 < $i ) {
					$cpu3 = $cpu2 / $i;
					$cpu3 = explode ( "." , $cpu3 );
					if ( strlen ( $cpu3[ '1' ] ) == 1 ) {
						$cpu3[ '1' ] = $cpu3[ '1' ] . '0';
					}
					$cpu3 = $cpu3[ '1' ] . 'x' . $i;
					break;
				}
			}
		}
		ssh::exec_cmd ( 'screen -dmS server_cpu_' . $id . ' cpulimit -v -z -p ' . $pid . ' -l ' . $cpu3 . ';' );
	}

	public static function get_cpu ( $id )
	{
		ssh::exec_cmd ( "ps -ef | grep SCREEN | grep -v grep | grep server_cpu_" . $id . " | awk '{ print $2}';" );
		$data = ssh::get_output ();
		$data = explode ( "\n" , $data );
		if ( count ( $data ) > 2 ) {
			servers::kill_pid ( $data );
		} else {
			return (int) $data[ 0 ];
		}
	}

	public static function get_pid_screen ( $id )
	{
		ssh::exec_cmd ( "ps -ef | grep SCREEN | grep -v grep | grep server_" . $id . " | awk '{ print $2}'" );
		$data1 = ssh::get_output ();

		$data = explode ( "\n" , $data1 );
		if ( count ( $data ) > 2 ) {
			servers::kill_pid ( $data );
		} else {
			return $data[ 0 ];
		}
	}

	public static function kill_pid ( $data )
	{
		if ( $data ) {
			if ( count ( $data ) > 1 ) {
				foreach ( $data as $value ) {
					ssh::exec_cmd ( "kill -9 " . $value . ';screen -wipe;' );
				}
			} else {
				ssh::exec_cmd ( "kill -9 " . $data . ';screen -wipe;' );
			}
		}
	}

	public static function kill_pid_all ( $id )
	{
		ssh::exec_cmd ( "top  -n 1 -b -u s" . $id . " | grep s" . $id . " | awk '{ print $1}';" );
		$data = trim ( ssh::get_output () );
		$data = explode ( "\n" , $data );
		if ( $data ) {
			if ( count ( $data ) > 1 ) {
				foreach ( $data as $value ) {
					ssh::exec_cmd ( "kill -9 " . $value . ';screen -wipe;' );
				}
			} else {
				ssh::exec_cmd ( "kill -9 " . $data[ 0 ] . ';screen -wipe;' );
			}
		}
	}

	public static function kill_pid_d ()
	{
		return false;
		ssh::exec_cmd ( "ps ajx | grep defunct | grep -v grep | awk '{ print $1}'" );
		$data = trim ( ssh::get_output () );
		$data = explode ( "\n" , $data );
		if ( $data ) {
			if ( count ( $data ) > 1 ) {
				foreach ( $data as $value ) {
					ssh::exec_cmd ( "kill -9 " . $value . ';screen -wipe;' );
				}
			} else {
				ssh::exec_cmd ( "kill -9 " . $data[ 0 ] . ';screen -wipe;' );
			}
		}
	}

	public static function online ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () == "1" ) {
			$key = m::g ( 'server_online_' . $id );
			if ( empty( $key ) ) {
				$sql = db::q ( 'SELECT * FROM gh_monitoring_time where sid="' . $id . '" order by id asc' );
				while ( $row = db::r ( $sql ) ) {
					$data[ $row[ 'time' ] ] = $row[ 'online' ];
				}
				$g = "1";
				foreach ( $data as $go => $val ) {
					if ( $g == "1" ) {
						$echo .= "[" . $go . "," . $val . "]";
					} else {
						$echo .= ",[" . $go . "," . $val . "]";
					}
					$g = $g + 1;
				}
				m::s ( 'server_online_' . $id , $echo , 180 );

				return $echo;
			} else {
				return $key;
			}
		} else {
			exit;
		}
	}

	public static function online_cpu ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () == "1" ) {
			$key = m::g ( 'server_cpu_online_' . $id );
			if ( empty( $key ) ) {
				$sql = db::q ( 'SELECT * FROM gh_monitoring_cpu_time where sid="' . $id . '" order by id asc' );
				while ( $row = db::r ( $sql ) ) {
					$data[ $row[ 'time' ] ] = $row[ 'cpu' ];
				}
				$g = "1";
				foreach ( $data as $go => $val ) {
					if ( $g == "1" ) {
						$echo .= "[" . $go . "," . $val . "]";
					} else {
						$echo .= ",[" . $go . "," . $val . "]";
					}
					$g = $g + 1;
				}
				m::s ( 'server_cpu_online_' . $id , $echo , 180 );

				return $echo;
			} else {
				return $key;
			}
		} else {
			exit;
		}
	}

	public static function online_ram ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () == "1" ) {
			$key = m::g ( 'server_ram_online_' . $id );
			if ( empty( $key ) ) {
				$sql = db::q ( 'SELECT * FROM gh_monitoring_ram_time where sid="' . $id . '" order by id asc' );
				while ( $row = db::r ( $sql ) ) {
					$data[ $row[ 'time' ] ] = $row[ 'ram' ];
				}
				$g = "1";
				foreach ( $data as $go => $val ) {
					if ( $g == "1" ) {
						$echo .= "[" . $go . "," . $val . "]";
					} else {
						$echo .= ",[" . $go . "," . $val . "]";
					}
					$g = $g + 1;
				}
				m::s ( 'server_ram_online_' . $id , $echo , 180 );

				return $echo;
			} else {
				return $key;
			}
		} else {
			exit;
		}
	}

	public static function online_hdd ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () == "1" ) {
			$key = m::g ( 'server_hdd_online_' . $id );
			if ( empty( $key ) ) {
				$sql = db::q ( 'SELECT * FROM gh_monitoring_hdd_time where sid="' . $id . '" order by id asc' );
				while ( $row = db::r ( $sql ) ) {
					$data[ $row[ 'time' ] ] = $row[ 'hdd' ];
				}
				$g = "1";
				foreach ( $data as $go => $val ) {
					if ( $g == "1" ) {
						$echo .= "[" . $go . "," . $val . "]";
					} else {
						$echo .= ",[" . $go . "," . $val . "]";
					}
					$g = $g + 1;
				}
				m::s ( 'server_hdd_online_' . $id , $echo , 180 );

				return $echo;
			} else {
				return $key;
			}
		} else {
			exit;
		}
	}

	public static function convert($server, $charset)
	{
		if (!function_exists("mb_convert_encoding")) { return $server; }

		if (is_array($server))
		{
			foreach ($server as $key => $value)
			{
				$server[$key] = self::convert($value, $charset);
			}
		}
		else
		{
			$server = @mb_convert_encoding($server, "UTF-8", $charset);
		}

		return $server;
	}

	public static function detect($server)
	{
		if (!function_exists("mb_detect_encoding")) { return "AUTO"; }

		$test = "";

		if (isset($server['s']['name'])) { $test .= " {$server['s']['name']} "; }

		if (isset($server['p']) && $server['p'])
		{
			foreach ($server['p'] as $player)
			{
				if (isset($player['name'])) { $test .= " {$player['name']} "; }
			}
		}

		$charset = @mb_detect_encoding($server['s']['name'], "UTF-8, Windows-1251, ISO-8859-1, ISO-8859-15");

		return $charset ? $charset : "AUTO";
	}

	public static function status ( $status , $password , $pending = 0 )
	{
		if ( $pending ) {
			return "0";
		}
		if ( ! $status ) {
			return "1";
		}
		if ( $password ) {
			return "2";
		}

		return "3";
	}

	public static function friend ( $server , $act )
	{
		if ( api::admin ( 'servers' )) {
			return true;
		}
		db::q ( 'SELECT user FROM gh_servers where id="' . $server . '"' );
		$row = db::r ();
		if ( $row[ 'user' ] == api::info ( 'id' ) ) {
			return true;
		}
		$sql = db::q ( 'SELECT data FROM gh_servers_friends where server="' . $server . '" and user="' . api::info ( 'id' ) . '"' );
		if ( db::n ( $sql ) == 1 ) {
			$row = db::r ( $sql );
			if($act=="base"){
				return true;
			}
			$data = json_decode ( base64_decode($row[ 'data' ]), true );
			if ( $data[ $act ] == 1 ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function speedbar ()
	{
		global $conf;
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT game,rate,ip,port,id,box FROM gh_servers where id="' . servers::$speedbar . '"' );
		} else {
			db::q ( 'SELECT game,rate,ip,port,id,box FROM gh_servers where id="' . servers::$speedbar . '" and user="' . api::info ( 'id' ) . '"' );
		}

		if ( db::n () != 1 ) {
			if(!servers::friend ( servers::$speedbar  , 'base' )){
				return false;
			}else{
				db::q ( 'SELECT game,rate,ip,port,id,box FROM gh_servers where id="' . servers::$speedbar . '"' );
			}
		}
		$row = db::r ();
		if ( $row[ 'game' ] != "ts3" ) {
			db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
			$rate = db::r ();
			$class = servers::game_class ( $row[ 'game' ] );
			tpl::load ( 'servers-nav' );
			tpl::set ( '{id}' , servers::$speedbar );
			tpl::set ( '{servers}' , servers::ip_server($row['box']) . ':' . $row[ 'port' ] );
			if ( $rate[ 'fastdl' ] != 0 and $class::info ( 'fastdl' ) ) {
				if ( servers::friend ( $row[ 'id' ] , 'fastdl' ) ) {
					tpl::set_block ( "'\\[fastdl\\](.*?)\\[/fastdl\\]'si" , "\\1" );
				} else {
					tpl::set_block ( "'\\[fastdl\\](.*?)\\[/fastdl\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[fastdl\\](.*?)\\[/fastdl\\]'si" , "" );
			}
			if ( $rate[ 'modules' ] != 0 and $class::info ( 'repository' ) ) {
				if ( servers::friend ( $row[ 'id' ] , 'modules' ) ) {
					tpl::set_block ( "'\\[modules\\](.*?)\\[/modules\\]'si" , "\\1" );
				} else {
					tpl::set_block ( "'\\[modules\\](.*?)\\[/modules\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[modules\\](.*?)\\[/modules\\]'si" , "" );
			}
			if ( $rate[ 'ftp' ] != 0 and $class::info ( 'ftp' ) ) {
				if ( servers::friend ( $row[ 'id' ] , 'ftp' ) ) {
					tpl::set_block ( "'\\[ftp\\](.*?)\\[/ftp\\]'si" , "\\1" );
				} else {
					tpl::set_block ( "'\\[ftp\\](.*?)\\[/ftp\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[ftp\\](.*?)\\[/ftp\\]'si" , "" );
			}
			if ( $class::info ( 'admins' ) ) {
				if ( servers::friend ( $row[ 'id' ] , 'admins' ) ) {
					tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "\\1" );
				} else {
					tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "" );
			}
			if ( $class::info ( 'eac' ) ) {
				if ( servers::friend ( $row[ 'id' ] , 'eac' ) ) {
					tpl::set_block ( "'\\[eac\\](.*?)\\[/eac\\]'si" , "\\1" );
				} else {
					tpl::set_block ( "'\\[eac\\](.*?)\\[/eac\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[eac\\](.*?)\\[/eac\\]'si" , "" );
			}
			if ( $row[ 'game' ] == "cs" ) {
				if ( $class::info ( 'tv' ) ) {
					if ( servers::friend ( $row[ 'id' ] , 'tv' ) ) {
						tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si" , "\\1" );
					} else {
						tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si" , "" );
					}
				} else {
					tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si" , "" );
			}

				if ( $row[ 'game' ] == "cs" || $row[ 'game' ] == "css" || $row[ 'game' ] == "cssold" || $row[ 'game' ] == "csgo" ) {
					tpl::set_block ( "'\\[maps2\\](.*?)\\[/maps2\\]'si" , "\\1" );
					tpl::set_block ( "'\\[maps\\](.*?)\\[/maps\\]'si" , "" );
				} else {
					tpl::set_block ( "'\\[maps2\\](.*?)\\[/maps2\\]'si" , "" );
					if ( $class::info ( 'maps2' ) ) {
						tpl::set_block ( "'\\[maps\\](.*?)\\[/maps\\]'si" , "\\1" );
					} else {
						tpl::set_block ( "'\\[maps\\](.*?)\\[/maps\\]'si" , "" );
					}
				}
			$sql = db::q ( 'SELECT * FROM gh_rise where game="' . $row[ 'game' ] . '" order by id asc' );
			if ( db::n ( $sql ) == 0 ) {
				tpl::set_block ( "'\\[rise\\](.*?)\\[/rise\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[rise\\](.*?)\\[/rise\\]'si" , "\\1" );
			}
			if ( $rate[ 'friends' ] == 1 ) {
				if ( servers::friend ( $row[ 'id' ] , 'friends' ) ) {
					tpl::set_block ( "'\\[friends\\](.*?)\\[/friends\\]'si" , "\\1" );
				} else {
					tpl::set_block ( "'\\[friends\\](.*?)\\[/friends\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[friends\\](.*?)\\[/friends\\]'si" , "" );
			}
			if ( $rate[ 'sale' ] == 1 ) {
				if ( servers::friend ( $row[ 'id' ] , 'sale' ) ) {
					tpl::set_block ( "'\\[sale\\](.*?)\\[/sale\\]'si" , "\\1" );
				} else {
					tpl::set_block ( "'\\[sale\\](.*?)\\[/sale\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[sale\\](.*?)\\[/sale\\]'si" , "" );
			}
			tpl::compile ( 'nav_servers' );
			tpl::$result[ 'content' ] = tpl::$result[ 'nav_servers' ] . tpl::$result[ 'content' ];
		}

	}
}

	servers::$games[ 'rust' ] = "Rust";

?>