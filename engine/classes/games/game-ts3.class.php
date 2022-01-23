<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_ts3
{
	public static function info ( $data )
	{
		$conf[ 'update' ] = 0;
		$conf[ 'online' ] = 0;
		$conf[ 'gadget' ] = 0;
		$conf[ 'repository' ] = 0;
		$conf[ 'fastdl' ] = 0;
		$conf[ 'fps' ] = 0;
		$conf[ 'reinstall' ] = 1;
		$conf[ 'friends' ] = 0;
		$conf[ 'ftp' ] = 0;
		$conf[ 'settings' ] = 1;
		$conf[ 'tv' ] = 0;
		$conf[ 'ftp_root' ] = "/";

		return $conf[ $data ];
	}
	public static function install_domain ( $ip , $port , $domain , $old=''){
		if ( $file = file_get_contents ( ROOT . '/data/tsdns.ini' ) ) {
			if ( $conf2 = json_decode ( $file , true ) ) {
				api::inc('ssh2');
				if ( ssh::connect ( $conf2[ 'ip' ] , $conf2[ 'port' ] ) ) {
					if ( ssh::auth_pwd ( $conf2[ 'login' ] , $conf2[ 'pass' ] ) ) {
						if($old){
							$exec = 'cd '.$conf2['dir'].';';
							$exec .= 'sed -i "/'.$old.'/d" "tsdns_settings.ini";';
							ssh::exec_cmd($exec);
						}
						$exec = 'cd '.$conf2['dir'].';';
						$exec .= 'echo "'.$domain.'='.$ip.':'.$port.'">>"tsdns_settings.ini";';
						ssh::exec_cmd($exec);
						$exec = 'cd '.$conf2['dir'].';';
						$exec .= './tsdns_startscript.sh update;';
						ssh::exec_cmd($exec);
					}
				}
			}
		}
		return false;
	}
	public static function remove_domain ( $ip , $port , $domain){
		if ( $file = file_get_contents ( ROOT . '/data/tsdns.ini' ) ) {
			if ( $conf2 = json_decode ( $file , true ) ) {
				api::inc('ssh2');
				if ( ssh::connect ( $conf2[ 'ip' ] , $conf2[ 'port' ] ) ) {
					if ( ssh::auth_pwd ( $conf2[ 'login' ] , $conf2[ 'pass' ] ) ) {
							$exec = 'cd '.$conf2['dir'].';';
							$exec .= 'sed -i "/'.$domain.'/d" "tsdns_settings.ini";';
							ssh::exec_cmd($exec);
						$exec = 'cd '.$conf2['dir'].';';
						$exec .= './tsdns_startscript.sh update;';
						ssh::exec_cmd($exec);
					}
				}
			}
		}
		return false;
	}
	public static function install ( $id , $rate , $slots , $port , $lid )
	{
		$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $id . '"' );
		$row = db::r ();
		db::q ( 'SELECT fps FROM gh_rates where id="' . $rate . '"' );
		$rows = db::r ();
		if ( ts3::connect ( $row[ 'ip' ] ,$row[ 'port' ] , $row[ 'login' ] , $row[ 'pass' ] ) ) {
			$cmd = "servercreate virtualserver_name=TeamSpeak\sServer virtualserver_port=" . $port . " virtualserver_maxclients=" . $slots;
			if ( $data = ts3::cmd ( $cmd ) ) {
				$sid = ts3::parse ( $data[ '0' ] , ' ' );
				$id = str_replace ( 'sid=' , '' , $sid[ '0' ] );
				$cmd = "use " . $id;

				if(ts3::cmd ( $cmd )){
					$cmd = "serveredit virtualserver_autostart=0 " . $rows[ 'dir' ];
					if(ts3::cmd ( $cmd )) {
						$cmd = "servergrouplist";

						if ( $data = ts3::cmd ( $cmd ) ) {
							$data2 = ts3::parse ( $data[ '0' ] , '|' );
							foreach ( $data2 as $value ) {
								$data3 = ts3::parse ( $value , ' ' );
								$data4 = str_replace ( 'sgid=' , '' , $data3[ '0' ] );
								$cmd = "servergroupdelperm sgid=" . $data4 . " permsid=b_virtualserver_modify_maxclients|permsid=b_virtualserver_modify_port";
								ts3::cmd ( $cmd );
							}
						}
						$data123[ 'key' ] = self::create_token ( $id );
						servers::configure ( $data123 , $lid );

						return $id;
					}
				}
			}

		}
		if(!ts3::$error ){
			ts3::$error .= l::t("Критическая ошибка");
		}

		return false;
	}

	public static function create_token ( $id )
	{
		$cmd = "use " . $id;
		ts3::cmd ( $cmd );
		$cmd = "servergrouplist";
		if ( $data = ts3::cmd ( $cmd ) ) {
			$data2 = ts3::parse ( $data[ '0' ] , '|' );
			foreach ( $data2 as $value ) {
				$g = false;
				$g2 = false;
				$data3 = ts3::parse ( $value , ' ' );
				foreach ( $data3 as $key2 => $value2 ) {
					$data4 = ts3::parse ( $value2 , '=' );
					if ( $data4[ '0' ] == "sgid" ) {
						$gid = $data4[ '1' ];
					}
					if ( $g == true ) {
						if ( $data4[ '0' ] == "type" ) {
							if ( $data4[ '1' ] == '1' ) {
								$g2 = true;
							}
						}
					} else {
						if ( $data4[ '0' ] == "name" ) {
							if ( $data4[ '1' ] == 'Server\sAdmin' ) {
								$g = true;
							}
						}
					}
				}
				if ( $g == true && $g2 == true ) {
					$cmd = "privilegekeyadd tokentype=0 tokenid1=" . $gid . " tokenid2=0";

					if ( $data = ts3::cmd ( $cmd ) ) {
						$data4 = ts3::parse ( $data[ '0' ] , '=' );

						return $data4[ '1' ];
					}
				}
				$g = false;
				$g2 = false;
			}
		}else{
			return false;
		}
	}

	public static function on ( $id )
	{
		api::inc ( 'telnet' );
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$server = db::r ();
		$sql = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $server[ 'box' ] . '"' );
		$row = db::r ();
		if ( ts3::connect ( $row[ 'ip' ] ,$row[ 'port' ] , $row[ 'login' ] , $row[ 'pass' ] ) ) {
			$cmd = "serverstart sid=" . $server[ 'sid' ];
			if ( ts3::cmd ( $cmd , true ) ) {
				return true;
			}
		}
		return false;
	}

}

?>