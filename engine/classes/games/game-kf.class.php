<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_kf
{
	public static $engine = false;

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
		$conf[ 'fastdl' ] = 1;
		$conf[ 'fps' ] = 0;
		$conf[ 'reinstall' ] = 1;
		$conf[ 'friends' ] = 1;
		$conf[ 'ftp' ] = 1;
		$conf[ 'settings' ] = 1;
		$conf[ 'tv' ] = 0;
		$conf[ 'console' ] = 1;
		$conf[ 'ftp_root' ] = "/cstrike/";

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
		$sql = db::q('SELECT * FROM gh_rates where id="' . $server['rate'] . '"');
		$rate = db::r($sql);
		$cfg = servers::cfg ( $id );
		$sid = $server[ 'sid' ];
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "chmod 755 srcds_run;";
		$exec .= "screen -dmS server_" . $sid . " su s" . $sid . " ";
		$exec .= "./srcds_run -console -game cstrike";
		$exec .= " -norestart +map '" . $cfg[ 'map' ] . "'";
		$exec .= " +maxplayers " . $server[ 'slots' ];
		$exec .= " +ip " . servers::ip_server2($server['box']). " -port " . $server[ 'port' ] . " ";
		if ( $cfg[ 'pass' ] ) {
			$exec .= " +sv_password '" . $cfg[ 'pass' ] . "'";
		}
		if ( $cfg[ 'rcon' ] ) {
			$exec .= " +rcon_password '" . $cfg[ 'rcon' ] . "'";
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
		source_engine::update ( $data , '232330' );
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

		return source_engine::settings ( $data , "/cstrike/maps/" );
	}

	public static function fastdl_on ()
	{
		fastdl::data ( 'cstrike/sound' , 'sound' );
		fastdl::data ( 'cstrike/models' , 'models' );
		fastdl::data ( 'cstrike/materials' , 'materials' );
		fastdl::data ( 'cstrike/maps' , 'maps' );
	}
}

?>