<?php
class game_csgo
{
	public static $engine = false;
	public static $rules = array (
		'on'        => 'Включение сервера' ,
		'off'       => 'Выключение сервера' ,
		'restart'   => 'Перезагрузка сервера' ,
		'settings'  => 'Управление настройками' ,
		'reinstall' => 'Переустановка сервера' ,
		'update' 	=> 'Обновление сервера' ,
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
	public static function admins_reload($id){
		self::engine ();
		source_engine::admins_reload($id,'/csgo/addons/sourcemod/configs/admins.cfg');
	}
	public static function info ( $data )
	{
		$conf[ 'rcon' ] = 0;
		$conf[ 'update' ] = 1;
		$conf[ 'online' ] = 1;
		$conf[ 'gadget' ] = 1;
		$conf[ 'repository' ] = 1;
		$conf[ 'fastdl' ] = 1;
		$conf[ 'fps' ] = 0;
		$conf[ 'reinstall' ] = 1;
		$conf[ 'friends' ] = 1;
		$conf[ 'ftp' ] = 1;
		$conf[ 'settings' ] = 1;
		$conf[ 'tv' ] = 1;
		$conf[ 'maps3' ] = 1;
		$conf[ 'sale' ] = 1;
		$conf[ 'maps' ] = 1;
		$conf[ 'rcon_kb' ] = 1;
		$conf[ 'console' ] = 1;
		$conf['maps2'] = 'csgo/maps/';
		$conf[ 'ftp_root' ] = "/csgo/";

		return $conf[ $data ];
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
		$exec .= "sudo -u s" . $sid . " echo '740'>steam_appid.txt;";
		ssh::exec_cmd ( $exec );
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "chmod 755 srcds_run;";
		$exec .= "screen -dmS server_" . $sid . " sudo -u s" . $sid . " ";
		$exec .= "./srcds_run  -game csgo -console -usercon +servercfgfile server.cfg";
		$exec .= " -autoupdate -norestart +map " . $cfg[ 'map' ] . "";
		$exec .= " +maxplayers " . $server[ 'slots' ] . " +tv_port " . ( (int) $server[ 'port' ] + 10000 ) . " -maxplayers_override " . $server[ 'slots' ] . "";
		$exec .= " +ip " .  servers::ip_server2($server['box']). " +net_public_adr " . servers::ip_server2($server['box']) . " +hostport " . $server[ 'port' ] . " +clientport 40000 ";
		if ( $cfg[ 'pass' ] ) {
			$exec .= " +sv_password " . $cfg[ 'pass' ] . "";
		}
		if ( $cfg[ 'rcon' ] ) {
			$exec .= " +rcon_password " . $cfg.[ 'rcon' ] . "";
		}
		if ( $cfg[ 'mod' ] == "2" ) {
			$exec .= " +game_type 0 +game_mode 1 +mapgroup mg_active";
		} elseif ( $cfg[ 'mod' ] == "3" ) {
			$exec .= " +game_type 1 +game_mode 0 +mapgroup mg_armsrace";
		} elseif ( $cfg[ 'mod' ] == "4" ) {
			$exec .= " +game_type 1 +game_mode 1 +mapgroup mg_demolition";
		} elseif ( $cfg[ 'mod' ] == "5" ) {
			$exec .= " +game_type 1 +game_mode 2 +mapgroup mg_allclassic";
		} else {
			$exec .= " +game_type 0 +game_mode 0 +mapgroup mg_active";
		}
		$exec .= " +host_players_show 2";
		$exec .= " ".$rows['plus'];
		ssh::exec_cmd ( $exec );
		sleep ( '2' );
		$pid = self::get_pid ( $sid );
		if ( $pid ) {
			servers::set_cpu ( $sid , $server[ 'slots' ] , $pid , $server[ 'rate' ] , $server[ 'game' ] );
			sleep ( '4' );
			servers::get_pid_screen ( $sid );
		}
	}

	public static function update ( $data )
	{
		self::engine ();
		source_engine::update ( $data , '740' );
	}

	public static function get_pid ( $id )
	{
		self::engine ();

		return source_engine::get_pid ( $id );
	}

	public static function mon ( $data )
	{
		self::engine ();
		self::admins_reload($data['id']);
		return source_engine::mon ( $data );
	}

	public static function settings ( $data , $dir )
	{
		$cmd = "cd /host/" . $data[ 'user' ] . "/" . $data[ 'sid' ] . $dir . "/csgo/maps/; ls | grep .bsp;";
		ssh::exec_cmd ( $cmd );
		$data1 = ssh::get_output ();
		$data1 = explode ( "\n" , $data1 );
		if ( ! $data1 ) {
			api::result ( l::t('Не удалось установить соединение с сервером') );
		} else {
			$mods = array (
				'1' => 'Classic Casual' ,
				'2' => 'Classic Competitive' ,
				'3' => 'Arms Race' ,
				'4' => 'Demolition' ,
				'5' => 'Deathmatch'
			);
			if ( $_POST[ 'data' ] ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( api::captcha_chek () ) {
					if ( ! in_array ( $_POST[ 'data' ][ 'mods' ] , array ( 1 , 2 , 3 , 4 , 5 ) ) ) {
						api::result ( l::t('Выберите мод сервера') );
					} else {

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
							api::result ( l::t('Карта не найдена на сервере'));
						} else {
							if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $_POST[ 'data' ][ 'rcon' ] ) ) {
								api::result ( l::t("Rcon пароль содержит недопустимые символы") );
							} else {
								if ( ! preg_match ( "/^[0-9a-zA-Z]{0,20}$/i" , $_POST[ 'data' ][ 'pass' ] ) ) {
									api::result ( l::t("Пароль содержит недопустимые символы") );
								} else {
									if ( ! preg_match ( "/^[0-9a-zA-Z_]{6,20}$/i" , $_POST[ 'data' ][ 'map' ] ) ) {
										api::result ( l::t("Название карты содержит недопустимые символы") );
									} else {
										$data2[ 'map' ] = api::cl ( $_POST[ 'data' ][ 'map' ] );
										$data2[ 'rcon' ] = api::cl ( $_POST[ 'data' ][ 'rcon' ] );
										$data2[ 'pass' ] = api::cl ( $_POST[ 'data' ][ 'pass' ] );
										$data2[ 'mod' ] = (int) $_POST[ 'data' ][ 'mods' ];
										servers::configure ( $data2 , $data[ 'id' ] );
										api::result ( l::t('Настройки успешно сохранены') , true );
									}
								}
							}
						}
					}
				}
			}
			$cfg = servers::cfg ( $data[ 'id' ] );
			foreach ( $data1 as $map1 ) {
				if ( preg_match ( '/\.(bsp.ztmp)/' , $map1 ) ) {
				} else {
					$map1 = str_replace ( ".bsp" , "" , api::cl ( $map1 ) );
					if ( trim ( $map1 ) ) {
						if ( $map1 == $cfg[ 'map' ] ) {
							$maps .= '<option value="' . $map1 . '" selected="selected">' . $map1 . '</option>';
						} else {
							$maps .= '<option value="' . $map1 . '">' . $map1 . '</option>';
						}
					}
				}
			}

			tpl::load ( 'servers-settings-game-csgo' );
			foreach ( $mods as $key => $value ) {
				if ( $key == $cfg[ 'mod' ] ) {
					$mod .= '<option value="' . $key . '" selected="selected">' . $value . '</option>';
				} else {
					$mod .= '<option value="' . $key . '">' . $value . '</option>';
				}
			}
			api::captcha_create ();
			tpl::set ( '{mods}' , $mod );
			tpl::set ( '{rcon}' , $cfg[ 'rcon' ] );
			tpl::set ( '{pass}' , $cfg[ 'pass' ] );
			tpl::set ( '{maps}' , $maps );
			tpl::set ( '{id}' , $data[ 'id' ] );
			tpl::compile ( 'content' );
		}
	}

	public static function fastdl_on ()
	{
		fastdl::data ( 'csgo/sound' , 'sound' );
		fastdl::data ( 'csgo/models' , 'models' );
		fastdl::data ( 'csgo/materials' , 'materials' );
		fastdl::data ( 'csgo/maps' , 'maps' );
	}

	public static function admins ( $data )
	{
		self::engine ();
		source_engine::admins ( $data );
	}
	public static function maps ( $data )
	{
		self::engine ();

		return source_engine::maps ( $data , "/csgo/maps/" );
	}
	public static function maps_go ( $map )
	{
		return 'changelevel ' . $map;
	}

	public static function rcon ( $data )
	{
		self::engine ();
		source_engine::rcon ( $data );
	}
	public static function rcon_bk ( $data )
	{
		self::engine ();
		source_engine::rcon_bk ( $data );
	}

}

?>