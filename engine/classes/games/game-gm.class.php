<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_gm
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
		'sale'   => 'Управление администраторами',
		'slots'		=> 'Изменение слотов'
	);
	public static function admins_reload($id){
		self::engine ();
		source_engine::admins_reload($id,'/garrysmod/addons/sourcemod/configs/admins.cfg');
	}
	public static function engine ()
	{
		if ( ! self::$engine ) {
			self::$engine = true;
			include_once ( ROOT . '/engine/classes/source-engine.php' );
		}
	}

	public static function info ( $data )
	{
		$conf[ 'rcon' ] = 1;
		$conf[ 'sale' ] = '1';
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
		$conf[ 'tv' ] = 0;
		$conf[ 'console' ] = 1;
		$conf[ 'ftp_root' ] = "/garrysmod/";

		return $conf[ $data ];
	}

	public static function install ( $id )
	{
		$data[ 'map' ] = 'gm_flatgrass';
		$data[ 'rcon' ] = api::generate_password ( '10' );
		$data[ 'tickrate' ] = '66';
		servers::configure ( $data , $id );
	}

	public static function on ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$server = db::r ();
		$sql = db::q('SELECT * FROM gh_rates where id="' . $server['rate'] . '"');
		$rate = db::r($sql);
		$cfg = servers::cfg ( $id );
		$sid = $server[ 'sid' ];
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "sudo -u s" . $sid . " echo '4020'>steam_appid.txt;";
		ssh::exec_cmd ( $exec );
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "chmod 755 srcds_run;";
		$exec .= "screen -dmS server_" . $sid . " sudo -u s" . $sid . " ";
		$exec .= "./srcds_run -console -game garrysmod";
		$exec .= " +map " . $cfg[ 'map' ] . "";
		$exec .= " +maxplayers " . $server[ 'slots' ];
		$exec .= " +ip " .  servers::ip_server2($server['box']) . " -port " . $server[ 'port' ] . " ";
		if ( $cfg[ 'pass' ] ) {
			$exec .= " +sv_password " . $cfg[ 'pass' ] . "";
		}
		if ( $cfg[ 'rcon' ] ) {
			$exec .= " +rcon_password " . $cfg[ 'rcon' ] . "";
		}
		$exec .= " ".$rate['plus'];
		ssh::exec_cmd ( $exec );
		sleep ( '2' );
		$pid = self::get_pid ( $sid );
		if ( $pid ) {
			servers::set_cpu ( $sid , $server[ 'slots' ] , $pid , $server[ 'rate' ] , $server[ 'game' ] );
			sleep ( '2' );
			servers::get_pid_screen ( $sid );
		}
	}

	public static function update ( $data )
	{
		self::engine ();
		source_engine::update ( $data , '4020' );
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

	public static function settings ( $data )
	{
		self::engine ();

		return source_engine::settings ( $data , "/garrysmod/maps/" );
	}

	public static function fastdl_on ()
	{
		fastdl::data ( 'garrysmod/sound' , 'sound' );
		fastdl::data ( 'garrysmod/models' , 'models' );
		fastdl::data ( 'garrysmod/materials' , 'materials' );
		fastdl::data ( 'garrysmod/maps' , 'maps' );
	}
}

?>