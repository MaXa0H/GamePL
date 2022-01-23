<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_l4d2
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
		'admins'   => 'Управление администраторами',
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
		$conf[ 'update' ] = 1;
		$conf[ 'online' ] = 1;
		$conf[ 'gadget' ] = 1;
		$conf[ 'repository' ] = 1;
		$conf[ 'rcon' ] = 1;
		$conf[ 'admins' ] = '/left4dead2/addons/sourcemod/configs/admins.cfg';
		$conf[ 'fastdl' ] = 1;
		$conf[ 'fps' ] = 0;
		$conf[ 'reinstall' ] = 1;
		$conf[ 'friends' ] = 1;
		$conf[ 'ftp' ] = 1;
		$conf[ 'settings' ] = 1;
		$conf[ 'tv' ] = 0;
		$conf[ 'console' ] = 1;
		$conf[ 'ftp_root' ] = "/left4dead2/";

		return $conf[ $data ];
	}

	public static function install ( $id )
	{
		$data[ 'map' ] = 'c5m1_waterfront';
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
		$exec .= "chmod 755 srcds_run;";
		$exec .= "screen -dmS server_" . $sid . " sudo -u s" . $sid . " ";
		$exec .= "./srcds_run -console -game left4dead2";
		$exec .= " -norestart +sv_lan 0 +map " . $cfg[ 'map' ] . "";
		$exec .= " +maxplayers " . $server[ 'slots' ] . "";
		$exec .= " +ip " .servers::ip_server2($server['box']) . " -port " . $server[ 'port' ] . " ";
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
		source_engine::update ( $data , '222860' );
	}

	public static function get_pid ( $id )
	{
		self::engine ();

		return source_engine::get_pid ( $id );
	}

	public static function mon ( $data )
	{
		self::engine ();

		return source_engine::mon ( $data );
	}

	public static function settings ( $data )
	{
		self::engine ();

		return source_engine::settings ( $data , "/left4dead2/maps/" );
	}

	public static function fastdl_on ()
	{
		fastdl::data ( 'left4dead2/sound' , 'sound' );
		fastdl::data ( 'left4dead2/models' , 'models' );
		fastdl::data ( 'left4dead2/materials' , 'materials' );
		fastdl::data ( 'left4dead2/maps' , 'maps' );
	}

	public static function admins ( $data )
	{
		self::engine ();
		source_engine::admins ( $data );
	}

	public static function rcon ( $data )
	{
		self::engine ();
		source_engine::rcon ( $data );
	}
}

?>