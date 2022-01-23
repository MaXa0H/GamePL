<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class servers_monitoring
{
	public static function img ( $id )
	{
		global $cfg , $title;
		if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 2 ) ) ) {
			exit;
		}
		if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 3 ) ) ) {
			exit;
		}
		if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 4 ) ) ) {
			exit;
		}
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () == "1" ) {
			$row = db::r ();
			if ( ! servers::$img_mon[ $row[ 'game' ] ] ) {
				exit;
			}
			if ( ! servers::$img_mon[ $row[ 'game' ] ][ r::g ( 6 ) ] ) {
				exit;
			}
			header ( 'Content-Type: image/png' );
			$image = imagecreatetruecolor ( 468 , 88 );
			$image2 = imagecreatefromjpeg ( servers::$img_mon[ $row[ 'game' ] ][ r::g ( 6 ) ] );
			$image3 = imagecopymerge ( $image , $image2 , 0 , 0 , 0 , 0 , 468 , 88 , 100 );
			$data = explode ( "." , r::g ( 2 ) );
			$black = imageColorAllocate ( $image , $data[ '0' ] , $data[ '1' ] , $data[ '2' ] );
			$data = explode ( "." , r::g ( 3 ) );
			$white = imageColorAllocate ( $image , $data[ '0' ] , $data[ '1' ] , $data[ '2' ] );
			$data = explode ( "." , r::g ( 4 ) );
			$white2 = imageColorAllocate ( $image , $data[ '0' ] , $data[ '1' ] , $data[ '2' ] );
			imagettftext ( $image , 10 , 0 , 15 , 20 , $white2 , "./ccc.ttf" , $row[ 'name' ] );
			imagettftext ( $image , 10 , 0 , 15 , 40 , $black , "./ccc.ttf" , l::t ("Адрес") );
			imagettftext ( $image , 10 , 0 , 65 , 40 , $white , "./ccc.ttf" , $row[ 'ip' ] );
			imagettftext ( $image , 10 , 0 , 200 , 40 , $black , "./ccc.ttf" , l::t ("Игроки") );
			imagettftext ( $image , 10 , 0 , 15 , 60 , $black , "./ccc.ttf" , l::t ("Порт") );
			imagettftext ( $image , 10 , 0 , 65 , 60 , $white , "./ccc.ttf" , $row[ 'port' ] );
			imagettftext ( $image , 10 , 0 , 200 , 60 , $black , "./ccc.ttf" , l::t ("Карта") );
			imagettftext ( $image , 10 , 0 , 15 , 80 , $black , "./ccc.ttf" , l::t ("Статус") );
			if ( $row[ 'status' ] == "1" ) {
				db::q ( 'SELECT * FROM gh_monitoring where sid="' . $id . '"' );
				$row_s = db::r ();
				if ( db::n () == "1" ) {
					$map = $row_s[ 'map' ];
					$slots = $row_s[ 'online' ] . " | " . $row[ 'slots' ];
				} else {
					$slots = "0 | " . $row[ 'slots' ];
					$map = '- - -';
				}
				imagettftext ( $image , 10 , 0 , 65 , 80 , $white , "./ccc.ttf" , l::t ("Включен") );
				imagettftext ( $image , 10 , 0 , 260 , 60 , $white , "./ccc.ttf" , $map );
				imagettftext ( $image , 10 , 0 , 260 , 40 , $white , "./ccc.ttf" , $slots );
			} else {
				imagettftext ( $image , 10 , 0 , 260 , 40 , $white , "./ccc.ttf" , "0 | " . $row[ 'slots' ] );
				imagettftext ( $image , 10 , 0 , 260 , 60 , $white , "./ccc.ttf" , '- - -' );
				imagettftext ( $image , 10 , 0 , 65 , 80 , $white , "./ccc.ttf" , l::t ("Выключен") );
			}
			imagettftext ( $image , 10 , 0 , 200 , 80 , $black , "./ccc.ttf" , l::t ("Локация") );
			db::q ( 'SELECT loc FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
			$rows = db::r ();
			db::q ( 'SELECT * FROM gh_location where id="' . $rows[ 'loc' ] . '"' );
			$rows = db::r ();
			imagettftext ( $image , 10 , 0 , 260 , 80 , $white , "./ccc.ttf" , $rows[ 'name' ] );

			imagepng ( $image );
			imagedestroy ( $image );
		}
		exit;
	}

	public static function full ( $id )
	{
		global $cfg , $title,$conf;
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () == "1" ) {
			$row = db::r ();
			$game = $row[ 'game' ];
			$adress = servers::ip_server($row['box']) . ":" . $row[ 'port' ];
			api::nav ( '/monitoring' , l::t ('Мониторинг') );
			api::nav ( '/monitoring/game/' . $game , servers::$games[ $game ] );
			api::nav ( '' , $adress , '1' );
			$title = $row[ 'name' ];
			$cfg = servers::cfg($row['id']);
			if ( $row[ 'status' ] == "1" ) {
				tpl::load ( 'servers-montoring-server' );
				db::q ( 'SELECT * FROM gh_monitoring where sid="' . $id . '"' );
				$row_s = db::r ();
				if ( db::n () == "1" ) {
					if($row['game']=="cssold"){
						$game = "css";
					}else{
						$game = $row['game'];
					}
					$map_img_file = file ( ROOT . "/img/maps/" . $game . "/" . $row_s[ 'map' ] . ".jpg" );
					if ( ! $map_img_file ) {
						$map_img = "/img/status/not_image.png";
					} else {
						$map_img = "/img/maps/" . $game . "/" . $row_s[ 'map' ] . ".jpg" . "";
					}
					$online = base64_decode ( $row_s[ 'gamers' ] );
					$slots = $row_s[ 'online' ] . "/" . $row[ 'slots' ];
					$proc = (int) ( 100 / $row[ 'slots' ] * $row_s[ 'online' ] );
					$maps = $row_s[ 'map' ];
				} else {
					$maps ='';
					$slots = "0 | " . $row[ 'slots' ];
					$proc = 0;
					$map_img = "/img/status/not_image.png";
				}
				if ( $online ) {
					$gamers = "";
					$row_gamers = json_decode ( base64_decode ( $row_s[ 'gamers' ] ) , true );
					$i = 0;
					foreach ( $row_gamers as $key => $value ) {
						if ( $value[ 'title' ] == "1" ) {
							if ( count ( $row_gamers ) == 1 ) {
								break;
							}
							$gamers .= "<tr>";
							$gamers .= "<td>";
							$gamers .= 'ID';
							$gamers .= "</td>";
							foreach ( $value as $key2 => $value2 ) {
								if ( $key2 == "steam" | $key2 == "ip" | $key2 == "ping" | $key2 == "title" | $key2 == "ban" ) {
									continue;
								}
								$gamers .= "<td>";
								$gamers .= $value2;
								$gamers .= "</td>";
							}
							$gamers .= "</tr>";
						} else {
							$gamers .= "<tr>";
							$i ++;
							$gamers .= "<td>";
							$gamers .= $i;
							$gamers .= "</td>";
							foreach ( $value as $key2 => $value2 ) {
								if ( $key2 == "steam" | $key2 == "ip" | $key2 == "ping" ) {
									continue;
								}
								$gamers .= "<td>";
								$gamers .= $value2;
								$gamers .= "</td>";
							}
							$gamers .= "</tr>";
						}
					}
					if($gamers){
						tpl::set_block ( "'\\[online\\](.*?)\\[/online\\]'si" , "\\1" );
					}else{
						tpl::set_block ( "'\\[online\\](.*?)\\[/online\\]'si" , "" );
					}
					tpl::set ( '{online}' , $gamers );
				} else {
					tpl::set_block ( "'\\[online\\](.*?)\\[/online\\]'si" , "" );
				}

				$status = $row['status'];
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $status == 2 ) {
					$map_img = "/img/status/offline.png";
					if ( $class::info ( 'update' ) ) {
						tpl::set_block ( "'\\[update\\](.*?)\\[/update\\]'si" , "\\1" );
					}
					tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "\\1" );
				} elseif ( $status == 3 ) {
					$map_img = "/img/status/install.png";
				} elseif ( $status == 4 ) {
					$map_img = "/img/status/updated.png";
				} elseif ( $status == 5 ) {
					$map_img = "/img/status/reinstall.png";
				} elseif ( $status == 6 ) {
					$map_img = "/img/status/time_end.png";
				}
				tpl::set ( '{name}' , $row[ 'name' ] );
				tpl::set ( '{img-map}' , $map_img );
				tpl::set ( '{game}' , servers::$games[ $game ] );
				tpl::set ( '{games}' , $game );
				tpl::set ( '{ids}' , $row[ 'id' ] );
				tpl::set ( '{slots}' , $slots );
				tpl::set ( '{proc}' , $proc );
				tpl::set ( '{smap}' , $row[ 'map' ] );
				tpl::set ( '{id}' , $id );
				tpl::set ( '{adress}' , $adress );
				$sql2 = db::q ( 'SELECT name FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
				$rate = db::r ( $sql2 );
				if(isset($cfg['bild'])){
					tpl::set_block ( "'\\[ver\\](.*?)\\[/ver\\]'si" , "\\1" );
					$versionsa = json_decode ( $rate[ 'versions' ] , true );
					tpl::set('{ver}',$versionsa[$cfg['bild']]['name']);
				}else{
					tpl::set_block ( "'\\[ver\\](.*?)\\[/ver\\]'si" , "" );
				}
				if ( $cfg[ 'tv' ] == 1 && $rate[ 'tv' ] == 1 ) {
					tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si" , "\\1" );
					tpl::set ( '{adress_tv}' , servers::ip_server($row['box']) . ':' . ( $row[ 'port' ] + 10000 ) );
					tpl::set ( '{tv_link}' , 'http://' . $conf[ 'domain' ] . '/servers/tv-demos/' . $row[ 'id' ] . '/' );
					if ( $class::info ( 'tv_dir' ) ) {
						tpl::set_block ( "'\\[demo\\](.*?)\\[/demo\\]'si" , "\\1" );
					} else {
						tpl::set_block ( "'\\[demo\\](.*?)\\[/demo\\]'si" , "" );
					}
				} else {
					tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si" , "" );
				}
				if ( $class::info ( 'maps' ) ) {
					if ( $maps ) {
						tpl::set_block ( "'\\[map\\](.*?)\\[/map\\]'si" , "\\1" );
						tpl::set ( '{map}' , $maps );
					} else {
						tpl::set_block ( "'\\[map\\](.*?)\\[/map\\]'si" , "" );
					}
				} else {
					tpl::set_block ( "'\\[map\\](.*?)\\[/map\\]'si" , "" );
				}
				tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "\\1" );
				tpl::set ( '{chart}' ,'' );
				db::q ( 'SELECT loc FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
				$rows = db::r ();
				db::q ( 'SELECT * FROM gh_location where id="' . $rows[ 'loc' ] . '"' );
				$rows = db::r ();
				tpl::set ( '{serv}' , $rows[ 'name' ] );
				tpl::set ( '{date}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
				db::q ( "SELECT * FROM gh_servers_admins_rates where server='".$id."'" );
				if ( db::n () == "0" ) {
					tpl::set_block ( "'\\[buy\\](.*?)\\[/buy\\]'si" , "" );
				}else{
					tpl::set_block ( "'\\[buy\\](.*?)\\[/buy\\]'si" , "\\1" );
				}

				tpl::set ( '{rate}' , $rate[ 'name' ] );

				tpl::compile ( 'content' );
			} else {
				api::error (l::t ( 'Сервер выключен') );
			}
		} else {
			api::error (l::t ( 'Сервер не найден' ));
		}
	}

	public static function listen ( $game , $speedbar = true , $true = false )
	{
		if ( $game ) {
			if ( ! preg_match ( "/^[a-z0-9]{2,10}$/i" , $game ) ) {
				api::result (l::t ( "Игра не найдена") );

				return false;
			} else {
				if ( servers::$games[ $game ] ) {
					db::q("SELECT t1.id FROM gh_servers AS t1, gh_monitoring AS t2 WHERE t1.game='" . api::cl ( $game ) . "'&&t1.status='1'&&t1.id = t2.sid");
				} else {
					api::result (l::t ( 'Игра не найдена' ));

					return false;
				}
			}
		} else {
			db::q("SELECT t1.id FROM gh_servers AS t1, gh_monitoring AS t2 WHERE t1.status='1'&&t1.id = t2.sid");
		}
		$all = db::n ();
		if ( (int) $_GET[ 'page' ] ) {
			if ( ( $all / 21 ) > (int) $_GET[ 'page' ] ) {
				$page = 21 * (int) $_GET[ 'page' ];
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		if ( $game ) {
			if ( $speedbar ) {
				if ( ! $true ) {
					api::nav ( '/monitoring' , l::t ('Мониторинг') );
					api::nav ( '/monitoring/game/' . $game , servers::$games[ $game ] , '1' );
				}
			}
			$sql = db::q("SELECT t1.* FROM gh_servers AS t1, gh_monitoring AS t2 WHERE t1.game='" . api::cl ( $game ) . "'&&t1.status='1'&&t1.id = t2.sid  order by online desc LIMIT " . $page . " ,21");
			$url = "/monitoring/game/" . $game;
		} else {
			if ( ! $true ) {
				api::nav ( '/monitoring' ,l::t ( 'Мониторинг') , '1' );
			}
			$sql = db::q("SELECT t1.* FROM gh_servers AS t1, gh_monitoring AS t2 WHERE t1.status='1'&&t1.id = t2.sid  order by online desc LIMIT " . $page . " ,21");
			$url = "/monitoring";
		}
		while ( $row = db::r ( $sql ) ) {
				$sql2 = db::q ( 'SELECT * FROM gh_monitoring where sid="' . $row[ 'id' ] . '"' );
				$row2 = db::r ( $sql2 );
				$cfg = servers::cfg ( $row[ 'id' ] );
				if ( db::n ( $sql2 ) == "1" ) {
					tpl::load ( 'servers-montoring-listen-get' );
					tpl::set ( '{id}' , $row[ 'id' ] );
					tpl::set ( '{game}' , $row[ 'game' ] );
					tpl::set ( '{name}' , $row[ 'name' ] );
					if($row[ 'game' ]=="cssold"){
						$g = "css";
					}else{
						$g = $row[ 'game' ];
					}
					$map_img_file = file ( ROOT . "/img/maps/" . $g . "/" . $row2[ 'map' ] . ".jpg" );
					if ( ! $map_img_file ) {
						$map_img = "/img/status/not_image.png";
					} else {
						$map_img = "/img/maps/" . $g . "/" . $row2[ 'map' ] . ".jpg" . "";
					}
					tpl::set ( '{maps}' , $map_img );
					if ( $row2[ 'guard' ] == "1" ) {
						tpl::set_block ( "'\\[lock\\](.*?)\\[/lock\\]'si" , "\\1" );
					} else {
						tpl::set_block ( "'\\[lock\\](.*?)\\[/lock\\]'si" , "" );
					}
					if ( $game == "mc" ) {
						tpl::set ( '{map}' , $cfg[ 'map' ] );
					} else {
						if($row2[ 'map' ]){
							tpl::set_block ( "'\\[map\\](.*?)\\[/map\\]'si" , "\\1" );
						}else{
							tpl::set_block ( "'\\[map\\](.*?)\\[/map\\]'si" , "" );
						}
						tpl::set ( '{map}' , $row2[ 'map' ] );

					}
					tpl::set ( '{online}' , $row2[ "online" ] . '/' . $row[ "slots" ] );
					tpl::set ( '{adress}' , servers::ip_server($row['box']) . ':' . $row[ "port" ] );
					tpl::set ( '{proc}' , (int) ( 100 / $row[ 'slots' ] * $row2[ 'online' ] ) );
					tpl::compile ( 'data' );
				}
		}
		if ( ! tpl::result ( 'data' ) ) {
			api::result (l::t ( "Нет запущенных серверов" ));
		}
		tpl::load ( 'servers-montoring-listen' );
		tpl::set ( '{games}' , $games );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		foreach ( servers::$games as $key => $value ) {
			$sql = db::q("SELECT t1.id FROM gh_servers AS t1, gh_monitoring AS t2 WHERE t1.game='".$key."'&&t1.status='1'&&t1.id = t2.sid");
			$sql2 = db::q("SELECT t2.online FROM gh_servers AS t1, gh_monitoring AS t2 WHERE t1.game='".$key."'&&t1.status='1'&&t1.id = t2.sid");
			$online = 0;
			while($ew = db::r($sql2)){
				$online+=$ew['online'];
			}
			$n = db::n ( $sql );
			if ( $n != 0 ) {
				tpl::load ( 'servers-montoring-listen-game' );
				tpl::set ( '{game}' , $key );
				tpl::set ( '{name}' , $value );
				tpl::set ( '{servers}' , $n );
				tpl::set ( '{gamers}' , $online );
				tpl::compile ( 'data' );
			}
		}
		tpl::set ( '{games}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		if ( $_GET[ 'page' ] == 'all' ) {
			tpl::set ( '{nav}' , '' );
		} else {
			tpl::set ( '{nav}' , api::pagination ( $all , 21 , (int) $_GET[ 'page' ] , $url ) );
		}
		tpl::compile ( 'content' );
	}
}

?>