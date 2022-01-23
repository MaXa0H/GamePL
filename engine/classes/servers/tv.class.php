<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class servers_tv
{
	public static $data   = false;
	public static $type   = false;
	public static $dir    = false;
	public static $server = false;
	public static $os     = 1;

	public static function base ( $id )
	{
		global $conf;

			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'tv' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				if ( $row[ 'time' ] < time () ) {
					api::result ( l::t ( 'Срок аренды сервера истек' ) );
				} else {
					$class = servers::game_class ( $row[ 'game' ] );
					db::q ( 'SELECT tv FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
					$rate = db::r ();
					if ( $rate[ 'tv' ] == 1 ) {
						$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
						api::nav ( "/servers" , l::t ( "Серверы" ) );
						api::nav ( "/servers/base/" . $id , $adress );
						api::nav ( "" , 'TV' , '1' );
						servers::$speedbar = $id;
						$class::tv ( $row );
					} else {
						api::result ( l::t ( 'Данная функция отключена' ) );
					}
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		
	}

	public static function listen ( $id )
	{
		global $conf;

			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			if ( db::n () == 1 ) {
				$row = db::r ();
				db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
				$rate = db::r ();
				if ( $rate[ 'tv' ] == 0 ) {
					api::result ( l::t ( 'Tv отключен в настройках тарифа у данного сервера' ) );

					return false;
				}
				api::inc ( 'servers' );
				$cfg = servers::cfg ( $row[ 'id' ] );
				$class = servers::game_class ( $row[ 'game' ] );
				if ( ! $class::info ( 'tv_dir' ) ) {
					api::result ( l::t ( 'Tv у данной игры отключен' ) );

					return false;
				}
				if ( $cfg[ 'tv' ] != 1 ) {
					api::result ( l::t ( 'Tv отключен в настройках сервера' ) );

					return false;
				}
				api::inc ( 'ssh2' );
				if ( ssh::gh_box ( $row[ 'box' ] ) ) {
					$cmd = "cd /host/" . $row[ 'user' ] . "/" . $row[ 'sid' ] . '/' . $class::info ( 'tv_dir' ) . "; ls | grep .dem;";
					ssh::exec_cmd ( $cmd );
					$data = trim ( ssh::get_output () );
					$data = explode ( "\n" , $data );
					tpl::load ( 'servers-tv-demos-listen' );
					foreach ( $data as $key => $val ) {
						if ( $val ) {
							tpl::load ( 'servers-tv-demos-listen-get' );
							tpl::set ( '{name}' , $val );
							tpl::set ( '{server}' , $id );
							tpl::compile ( 'data' );
						}
					}
					tpl::set ( '{data}' , tpl::$result[ 'data' ] );
					tpl::set ( '{server}' , $row[ 'ip' ] . ':' . $row[ 'port' ] );
					tpl::compile ( 'content' );
					echo tpl::result ( 'content' );
					exit;
				} else {
					api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );

					return false;
				}
			} else {
				api::result ( l::t ( 'Запрашиваемый сервер не найден' ) );
			}
		
	}

	public static function download ( $id , $file )
	{
		global $conf;
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			if ( db::n () == 1 ) {
				$row = db::r ();
				db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
				$rate = db::r ();
				if ( $rate[ 'tv' ] == 0 ) {
					api::result ( l::t ( 'Tv отключен в настройках тарифа у данного сервера' ) );

					return false;
				}
				api::inc ( 'servers' );
				$cfg = servers::cfg ( $row[ 'id' ] );
				$class = servers::game_class ( $row[ 'game' ] );
				if ( ! $class::info ( 'tv_dir' ) ) {
					api::result ( l::t ( 'Tv у данной игры отключен' ) );

					return false;
				}
				if ( $cfg[ 'tv' ] != 1 ) {
					api::result ( l::t ( 'Tv отключен в настройках сервера' ) );

					return false;
				}
				api::inc ( 'ssh2' );
				if ( ssh::gh_box ( $row[ 'box' ] ) ) {
					$cmd = "cd /host/" . $row[ 'user' ] . "/" . $row[ 'sid' ] . '/' . $class::info ( 'tv_dir' ) . "; ls | grep .dem;";
					ssh::exec_cmd ( $cmd );
					$data = trim ( ssh::get_output () );
					$data = explode ( "\n" , $data );
					$ermap = 1;
					foreach ( $data as $map ) {
						if ( ! preg_match ( '/\.(bsp.ztmp)/' , $map ) ) {
							$map = str_replace ( ".bsp" , "" , $map );
							if ( $map == $file ) {
								$ermap = "0";
							}
						}
					}
					if ( $ermap == 0 ) {
						$nfile = ROOT . '/cache/' . time ();
						ssh::get_file ( "/host/" . $row[ 'user' ] . "/" . $row[ 'sid' ] . '/' . $class::info ( 'tv_dir' ) . api::cl ( $file ) , $nfile );
						if ( ob_get_level () ) {
							ob_end_clean ();
						}
						header ( 'Content-Description: File Transfer' );
						header ( 'Content-Type:application/octet-stream' );
						header ( 'Content-Disposition: attachment; filename="' . $file . '"' );
						header ( 'Content-Transfer-Encoding: binary' );
						header ( 'Expires: 0' );
						header ( 'Cache-Control: must-revalidate' );
						header ( 'Pragma: public' );
						header ( 'Content-Length: ' . filesize ( $nfile ) );
						readfile ( $nfile );
						header ( "Connection: close" );
						unlink ( $nfile );
						exit;
					} else {
						api::result ( l::t ( 'Файл не найден' ) );

						return false;
					}

					exit;
				} else {
					api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );

					return false;
				}
			} else {
				api::result ( l::t ( 'Запрашиваемый сервер не найден' ) );
			}
		
	}
}

?>