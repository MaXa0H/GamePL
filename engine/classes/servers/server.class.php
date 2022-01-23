<?php
class servers_server
{
	public static function listen ()
	{
		global $title,$conf;
		if(!$conf['dell']){
			$conf['dell'] = 3;
		}
		if ( r::g ( 1 ) == "user" ) {
			$user = (int) r::g ( 2 );
			$_GET[ 'page' ] = (int) r::g ( 4 );
		} else {
			$_GET[ 'page' ] = (int) r::g ( 2 );
		}
		if ( $user != 0 ) {
			db::q ( 'SELECT * FROM users where id="' . $user . '"' );
			if ( db::n () == 0 ) {
				api::result ( l::t('Пользователь не найден') );

				return false;
			}
		}
		if ( api::admin ( 'servers' ) ) {
			if ( $user != 0 ) {
				db::q ('(SELECT id FROM gh_servers WHERE user="'.$user.'") UNION (SELECT t1.id FROM gh_servers as t1, gh_servers_friends as t2 WHERE t1.id = t2.server and t2.user="'.$user.'") order by id desc' );
			} else {
				db::q ( 'SELECT id FROM gh_servers order by id desc' );
			}
		} else {
			db::q ('(SELECT id FROM gh_servers WHERE user="'.api::info ( 'id' ).'") UNION (SELECT t1.id FROM gh_servers as t1, gh_servers_friends as t2 WHERE t1.id = t2.server and t2.user="'.api::info ( 'id' ).'") order by id desc' );
		}
		$all = db::n ();
		if ( (int) $_GET[ 'page' ] ) {
			if ( ( $all / 10 ) > (int) $_GET[ 'page' ] ) {
				$page = 10 * (int) $_GET[ 'page' ];
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		if ( api::admin ( 'servers' ) ) {
			if ( $user != 0 ) {
				$sql = db::q ('(SELECT * FROM gh_servers WHERE user="'.$user.'") UNION (SELECT t1.* FROM gh_servers as t1, gh_servers_friends as t2 WHERE t1.id = t2.server and t2.user="'.$user.'") order by id desc LIMIT ' . $page . ' ,10' );
			} else {
				$sql = db::q ( 'SELECT * FROM gh_servers order by id desc LIMIT ' . $page . ' ,10' );
			}
		} else {
			$sql = db::q ('(SELECT * FROM gh_servers WHERE user="'.api::info('id').'") UNION (SELECT t1.* FROM gh_servers as t1, gh_servers_friends as t2 WHERE t1.id = t2.server and t2.user="'.api::info('id').'") order by id desc LIMIT ' . $page . ' ,10' );
		}
		while ( $row = db::r ( $sql ) ) {
			tpl::load ( 'servers-listen-get' );
			if($row["game"]=="ts3"){
				$sql123asdas = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
				$row222 = db::r ($sql123asdas);
				$adress = $row222['ip'] . ":" . $row[ 'port' ];
			}else{
				$adress = servers::ip_server($row['box']) . ":" . $row[ 'port' ];
			}
			
			$date = api::langdate ( "j F Y - H:i" , $row[ 'time' ] );
			tpl::set ( '{game}' , $row[ 'game' ] );
			if($row[ 'time' ]>time()){
				$n = (int)(($row[ 'time' ]-time())/(3600*24));
				$date2 = $n.' '.api::getNumEnding($n,array(l::t ('день'),l::t ('дня'),l::t ('дней')));
			}else{
				$n = $conf['dell']-(int)((time()-$row[ 'time' ])/(3600*24));
				$date2 = $n.' '.api::getNumEnding($n,array(l::t ('день'),l::t ('дня'),l::t ('дней'))) .' '.l::t ('до удаления');
			}
			tpl::set ( '{date2}' , $date2 );
			tpl::set ( '{game_name}' , servers::$games[ $row[ 'game' ] ] );
			$status = $row[ 'status' ];
			$maps = "";
			if ( $status == 1 ) {
				$sql123 = db::q ( 'SELECT * FROM gh_monitoring where sid="' . $row['id'] . '"' );
				$row_s = db::r ( $sql123 );
				tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "\\1" );
				if ( db::n ( $sql123 ) == "1" ) {
					tpl::set ( '{online}' , $row_s[ "online" ] . '/' . $row[ "slots" ] );
					tpl::set ( '{proc}' , (int) ( 100 / $row[ 'slots' ] * $row_s[ 'online' ] ) );
					if($row['game']=="cssold"){
						$game = "css";
					}else{
						$game = $row['game'];
					}
					$map_img_file = file ( ROOT . "/img/maps/" . $game . "/" . $row_s[ 'map' ] . ".jpg" );
					if ( ! $map_img_file ) {
						$map_img = "/img/status/not_image.png";
						$maps = $row_s[ 'map' ];
					} else {
						$map_img = "/img/maps/" . $game . "/" . $row_s[ 'map' ] . ".jpg" . "";
						$maps = $row_s[ 'map' ];
					}
				}else{
					tpl::set ( '{online}' , '0/' . $row[ "slots" ] );
					tpl::set ( '{proc}' , 0 );
					$map_img = "/img/status/not_image.png";
				}
			}else{
				tpl::set ( '{online}' , '0/' . $row[ "slots" ] );
				tpl::set ( '{proc}' , 0 );
			}
			if ( $row[ 'time' ] < time () ) {
				$status = '6';
			}
			if ( $status == 2 ) {
				$map_img = "/img/status/offline.png";
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
			tpl::set ( '{img}' , $map_img );
			tpl::set ( '{status}' , $status );
			tpl::set ( '{status_txt}' , $txt_stats );
			tpl::set ( '{status_info}' , servers::$game_status[ $status ] );
			if ( $status == 2 ) {
				tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "\\1" );
				tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
				tpl::set_block ( "'\\[no\\](.*?)\\[/no\\]'si" , "" );
			} elseif ( $status == 1 ) {
				tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
				tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "\\1" );
				tpl::set_block ( "'\\[no\\](.*?)\\[/no\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
				tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
				tpl::set_block ( "'\\[no\\](.*?)\\[/no\\]'si" , "\\1" );
			}
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::set ( '{user}' , $row[ 'user' ] );
			tpl::set ( '{date}' , $date );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{adress}' , $adress );
			$class = servers::game_class ( $row[ 'game' ] );
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
			if($row['game']=="ts3"){
				$sql2 = db::q ( 'SELECT loc FROM gh_boxes_ts3 where id="' . $row[ 'box' ] . '"' );
				$row2 = db::r ( $sql2 );
			}else{
				$sql2 = db::q ( 'SELECT loc FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
				$row2 = db::r ( $sql2 );
			}
			$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row2[ 'loc' ] . '"' );
			$row2 = db::r ( $sql2 );
			tpl::set ( '{loc}' , $row2[ 'name' ] );
			$sql2 = db::q ( 'SELECT name FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
			$row2 = db::r ( $sql2 );
			tpl::set ( '{rate}' , $row2[ 'name' ] );
			tpl::set ( '{id}' , $row[ 'id' ] );
			if ( api::admin ( 'servers' ) ) {
				tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "\\1" );
				tpl::set_block ( "'\\[noadmins\\](.*?)\\[/noadmins\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "" );
				tpl::set_block ( "'\\[noadmins\\](.*?)\\[/noadmins\\]'si" , "\\1" );
			}
			tpl::compile ( 'server' );
		}
		$title = l::t("Серверы");
		if ( tpl::result ( 'server' ) ) {
			tpl::load ( 'servers-listen' );
			if ( api::admin ( 'servers' ) ) {
				tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "\\1" );
				tpl::set_block ( "'\\[noadmins\\](.*?)\\[/noadmins\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "" );
				tpl::set_block ( "'\\[noadmins\\](.*?)\\[/noadmins\\]'si" , "\\1" );
			}
			if ( r::g ( 2 ) == "user" ) {
				tpl::set ( '{nav}' , api::pagination ( $all , 10 , (int) $_GET[ 'page' ] , '/servers/user/' . (int) r::g ( 4 ) . '' ) );
			} else {
				tpl::set ( '{nav}' , api::pagination ( $all , 10 , (int) $_GET[ 'page' ] , '/servers' ) );
			}

			tpl::set ( '{servers}' , tpl::result ( 'server' ) );
			tpl::compile ( 'content' );
		} else {
			api::error ( l::t('У вас нет доступных серверов.').' <a href="/servers/buy">'.l::t('Заказать выделенный игровой сервер.').'</a>' );
		}
		api::nav ( "" , l::t("Серверы") , '1' );
	}

	public static function full ( $id )
	{
		global $title,$conf;
		if(!$conf['dell']){
			$conf['dell'] = 3;
		}
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'base' )){
				api::result(l::t('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$server = db::r ();
			$sql = db::q ( 'SELECT * FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
			$rate = db::r ( $sql );
			if($server["game"]=="ts3"){
				$sql123asdas = db::q ( 'SELECT * FROM gh_boxes_ts3 where id="' . $server[ 'box' ] . '"' );
				$row222 = db::r ($sql123asdas);
				$adress = $row222['ip'] . ":" . $server[ 'port' ];
			}else{
				$adress = servers::ip_server($server['box']) . ":" . $server[ 'port' ];
			}
			api::nav ( "/servers" , l::t("Серверы") );
			api::nav ( "" , $adress , '1' );
			tpl::load ( 'servers-base' );
			$cfg = servers::cfg ( $id );
			$class = servers::game_class ( $server[ 'game' ] );
			tpl::set_block ( "'\\[update\\](.*?)\\[/update\\]'si" , "" );
			tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
			tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
			tpl::set_block ( "'\\[online\\](.*?)\\[/online\\]'si" , "" );
			tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "" );
			tpl::set_block ( "'\\[baneds\\](.*?)\\[/baneds\\]'si" , "" );
			$status = $server[ 'status' ];
			if ( $status == 1 ) {
				if ( $server[ 'time' ] < time () ) {
					$slots = 0;
					$proc = 0;
					$map_img = "/img/status/time_end.png";
				} else {
					$sql = db::q ( 'SELECT * FROM gh_monitoring where sid="' . $id . '"' );
					$row_s = db::r ( $sql );
					tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "\\1" );
					if ( db::n ( $sql ) == "1" ) {
						if($server['game']=="cssold"){
							$game = "css";
						}else{
							$game = $server['game'];
						}
						$map_img_file = file ( ROOT . "/img/maps/" . $game. "/" . $row_s[ 'map' ] . ".jpg" );
						if ( ! $map_img_file ) {
							$map_img = "/img/status/not_image.png";
							$maps = $row_s[ 'map' ];
						} else {
							$map_img = "/img/maps/" . $game . "/" . $row_s[ 'map' ] . ".jpg" . "";
							$maps = $row_s[ 'map' ];
						}

						if ( $cfg[ 'baneds' ] ) {
							$row_gamers = json_decode ( base64_decode ( $cfg[ 'baneds' ] ) , true );
							if ( count ( $row_gamers ) >= 1 ) {
								tpl::set_block ( "'\\[baneds\\](.*?)\\[/baneds\\]'si" , "\\1" );
								$gamers = "";
								$i = 0;
								$gamers .= "<tr>";
								$gamers .= "<td>";
								$gamers .= 'ID';
								$gamers .= "</td>";

								$gamers .= "<td>".l::t('Имя')."</td>";
								$gamers .= "<td>Steam ID</td>";
								$gamers .= "<td>IP</td>";
								$gamers .= "<td>".l::t('Забанен')."</td>";
								$gamers .= "<td></td>";
								$gamers .= "</tr>";
								$bans = array ();
								foreach ( $row_gamers as $key => $value ) {
									if ( ( (int) $value[ 'time' ] + 3600 ) <= time () ) {
										continue;
									}

									$i ++;
									$gamers .= "<tr>";
									$gamers .= "<td>";
									$gamers .= $i;
									$gamers .= "</td>";
									$ban2 = array ();
									foreach ( $value as $key2 => $value2 ) {
										$ban2[ $key2 ] = $value2;
										if ( $key2 == "steam" ) {
											$ban = $value2;
										}
										if ( $key2 == "time" ) {
											$gamers .= "<td>";
											$gamers .= api::langdate ( "j F Y - H:i" , $value2 );
											$gamers .= "</td>";
											continue;
										}
										$gamers .= "<td>";
										$gamers .= $value2;
										$gamers .= "</td>";
									}
									$gamers .= "<td width=70>";
									$gamers .= '<a class="user_unban btn btn-info btn-xs" sid="' . $server[ 'id' ] . '" data="' . strip_tags ( $ban ) . '">Разбанить</a>';
									$gamers .= "</td>";
									$gamers .= "</tr>";
									$bans[ ] = $ban2;
								}
								tpl::set ( '{baneds}' , $gamers );
							}
						}
						$dat[ 'baneds' ] = base64_encode ( json_encode ( $bans ) );
						if ( $cfg[ 'baneds' ] != $dat[ 'baneds' ] ) {
							servers::configure ( $dat , $server[ 'id' ] );
						}
						if ( $class::info ( 'online' ) ) {
							if ( $row_s[ 'gamers' ] ) {
								$gamers = "";
								$row_gamers = array ();
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
											if ( $key2 == "title" ) {
												continue;
											}
											$gamers .= "<td>";
											$gamers .= $value2;
											$gamers .= "</td>";
										}
										if ( $class::info ( 'rcon_kb' ) ) {
											$gamers .= "<td></td>";
											$gamers .= "<td></td>";
										}
										$gamers .= "</tr>";
									} else {
										$i ++;
										$gamers .= "<tr>";
										$gamers .= "<td>";
										$gamers .= $i;
										$gamers .= "</td>";
										foreach ( $value as $key2 => $value2 ) {
											if ( $class::info ( 'rcon_kb' ) ) {
												if ( $key2 == "name" ) {
													$ban = $value2;
												}
											}
											$gamers .= "<td>";
											$gamers .= $value2;
											$gamers .= "</td>";
										}
										if ( isset( $ban ) ) {
											$gamers .= "<td width=60>";
											$gamers .= '<a class="user_kick btn btn-info btn-xs" sid="' . $server[ 'id' ] . '" data="' . strip_tags ( $ban ) . '">Кикнуть</a>';
											$gamers .= "</td>";
											$gamers .= "<td width=70>";
											$gamers .= '<a class="user_ban btn btn-info btn-xs" sid="' . $server[ 'id' ] . '" data="' . strip_tags ( $ban ) . '">Забанить</a>';
											$gamers .= "</td>";
										}
										$gamers .= "</tr>";
									}
								}
								if ( $gamers ) {
									tpl::set_block ( "'\\[online\\](.*?)\\[/online\\]'si" , "\\1" );
								}
								tpl::set ( '{online}' , $gamers );
							}
						}
						$slots = $row_s[ 'online' ];
						$proc = (int) ( 100 / $server[ 'slots' ] * $row_s[ 'online' ] );
					} else {
						$map_img = "/img/status/not_image.png";
					}
				}
			} else {
				$slots = 0;
				$proc = 0;
				$map_img = "/img/status/not_image.png";
			}
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
			if ( $cfg[ 'tv' ] == 1 && $rate[ 'tv' ] == 1 ) {
				tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si" , "\\1" );
				tpl::set ( '{adress_tv}' , $server[ 'ip' ] . ':' . ( $server[ 'port' ] + 10000 ) );
				tpl::set ( '{tv_link}' , 'http://' . $conf[ 'domain' ] . '/servers/tv-demos/' . $server[ 'id' ] . '/' );
				if ( $class::info ( 'tv_dir' ) ) {
					tpl::set_block ( "'\\[demo\\](.*?)\\[/demo\\]'si" , "\\1" );
				} else {
					tpl::set_block ( "'\\[demo\\](.*?)\\[/demo\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[tv\\](.*?)\\[/tv\\]'si" , "" );
			}
			if($server['game']=="ts3"){
				tpl::set_block ( "'\\[nots3\\](.*?)\\[/nots3\\]'si", "" );
				tpl::set_block ( "'\\[ts3\\](.*?)\\[/ts3\\]'si", "\\1" );
				$cfg = servers::cfg($server['id']);
				tpl::set ( '{keys}', $cfg['key']);
				if($server['domain']){
					tpl::set_block ( "'\\[domains\\](.*?)\\[/domains\\]'si", "\\1" );
					tpl::set ( '{adress2}', $server['domain']);
				}else{
					tpl::set_block ( "'\\[domains\\](.*?)\\[/domains\\]'si", "" );
				}
			}else{
				tpl::set_block ( "'\\[domains\\](.*?)\\[/domains\\]'si", "" );
				tpl::set_block ( "'\\[ts3\\](.*?)\\[/ts3\\]'si", "" );
				tpl::set_block ( "'\\[nots3\\](.*?)\\[/nots3\\]'si", "\\1" );
			}

			if ( $status == 1 ) {
				$sql = db::q ( 'SELECT ram,cpu FROM gh_boxes_games where box="' . $server[ 'box' ] . '" and game="' . $server[ 'game' ] . '"' );
				$rows = db::r ( $sql );
				$cpu = $cfg[ 'cpu' ];
				$ram = (int) ( $cfg[ 'mem' ] / 1024 );
				if ( $server[ 'game' ] == "mc" ) {
					$proc_ram = (int) ( 100 / ( $rows[ 'ram' ] * $server[ 'slots' ] ) * (int) ( $cfg[ 'mem' ] / 1024 ) );
				} else {
					$proc_ram = (int) ( 100 / $rows[ 'ram' ] * (int) ( $cfg[ 'mem' ] / 1024 ) );
				}
				$cpu_p = (int) ( ( 100 / ( $rows[ 'cpu' ] * $server[ 'slots' ] ) ) * $cpu );
			} else {
				$cpu_p = 0;
				$proc_ram = 0;
			}
			tpl::set ( '{proc-ram}' , $proc_ram );
			tpl::set ( '{proc-cpu}' , $cpu_p );
			$sql = db::q ( 'SELECT hard,ftp FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
			$rows = db::r ( $sql );
			if ( $rows[ 'ftp' ] == 1 ) {
				tpl::set_block ( "'\\[hdd\\](.*?)\\[/hdd\\]'si" , "\\1" );
				tpl::set ( '{proc-hdd}' , (int) ( 100 / $rows[ 'hard' ] * (int) ( $cfg[ 'hdd' ] / 1024 ) ) );
			} else {
				tpl::set ( '{proc-hdd}' , '0' );
				tpl::set_block ( "'\\[hdd\\](.*?)\\[/hdd\\]'si" , "" );
			}

			tpl::set ( '{id}' , $server[ 'id' ] );
			tpl::set ( '{user}' , $server[ 'user' ] );
			tpl::set ( '{name}' , $server[ 'name' ] );
			tpl::set ( '{adress}' , $adress );
			tpl::set ( '{img-map}' , $map_img );
			tpl::set ( '{txt-stats}' , '' );
			tpl::set ( '{game}' , servers::$games[ $server[ 'game' ] ] );
			tpl::set ( '{games}' , $server[ 'game' ] );
			tpl::set ( '{slots}' , (int) $slots . '/' . $server[ 'slots' ] );
			tpl::set ( '{proc}' , $proc );
			tpl::set ( '{date}' , api::langdate ( "j F Y - H:i" , $server[ 'time' ] ) );
			db::q ( 'SELECT loc FROM gh_boxes where id="' . $server[ 'box' ] . '"' );
			$row_serv = db::r ();
			db::q ( 'SELECT name FROM gh_location where id="' . $row_serv[ 'loc' ] . '"' );
			$row_serv2 = db::r ();
			tpl::set ( '{serv}' , $row_serv2[ 'name' ] );
			$sql2 = db::q ( 'SELECT name FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
			$row2 = db::r ( $sql2 );
			tpl::set ( '{rate}' , $row2[ 'name' ] );
			if($server[ 'time' ]>time()){
				$n = (int)(($server[ 'time' ]-time())/(3600*24));
				$date2 = $n.' '.api::getNumEnding($n,array(l::t ('день'),l::t ('дня'),l::t ('дней')));
			}else{
				$n = $conf['dell']-(int)((time()-$server[ 'time' ])/(3600*24));
				$date2 = $n.' '.api::getNumEnding($n,array(l::t ('день'),l::t ('дня'),l::t ('дней'))) .' '.l::t ('до удаления');
			}
			tpl::set ( '{date2}' , $date2 );
			$key = m::g ( 'server_online_' . $id );
			if ( empty( $key ) ) {
				$key = servers::online ( $id );
				if ( ! empty( $key ) ) {
					tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "\\1" );
					tpl::set ( '{chart}' , '[' . $key . ']' );
				}
			} else {
				tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "\\1" );
				tpl::set ( '{chart}' , '[' . $key . ']' );
			}
			$key = m::g ( 'server_cpu_online_' . $id );
			if ( empty( $key ) ) {
				$key = servers::online_cpu ( $id );
				if ( ! empty( $key ) ) {
					tpl::set ( '{chart2}' , '[' . $key . ']' );
				}
			} else {
				tpl::set ( '{chart2}' , '[' . $key . ']' );
			}
			$key = m::g ( 'server_ram_online_' . $id );
			if ( empty( $key ) ) {
				$key = servers::online_ram ( $id );
				if ( ! empty( $key ) ) {
					tpl::set ( '{chart3}' , '[' . $key . ']' );
				}
			} else {
				tpl::set ( '{chart3}' , '[' . $key . ']' );
			}
			$key = m::g ( 'server_hdd_online_' . $id );
			if ( empty( $key ) ) {
				$key = servers::online_hdd ( $id );
				if ( ! empty( $key ) ) {
					tpl::set ( '{chart4}' , '[' . $key . ']' );
				}
			} else {
				tpl::set ( '{chart4}' , '[' . $key . ']' );
			}
			if(isset($cfg['bild'])){
				tpl::set_block ( "'\\[ver\\](.*?)\\[/ver\\]'si" , "\\1" );
				$versionsa = json_decode ( $rate[ 'versions' ] , true );
				tpl::set('{ver}',$versionsa[$cfg['bild']]['name']);
			}else{
				tpl::set_block ( "'\\[ver\\](.*?)\\[/ver\\]'si" , "" );
			}

			$sql = db::q ( 'SELECT * FROM gh_monitoring where sid="' . $id . '"' );
			$row_s = db::r ( $sql );
			$cfg = servers::cfg ( $id );
			$sql = db::q ( 'SELECT * FROM gh_boxes_games where box="' . $server[ 'box' ] . '" and game="' . $server[ 'game' ] . '"' );
			$rows = db::r ( $sql );
			if ( db::n ( $sql ) == "1" and $server[ 'status' ] == 1 ) {
				$online = $row_s[ 'online' ];
				$proc_online = (int) ( 100 / $server[ 'slots' ] * $row_s[ 'online' ] );
				$cpu = $cfg[ 'cpu' ];
				if ( $server[ 'game' ] == "mc" ) {
					$proc_ram = (int) ( 100 / ( $rows[ 'ram' ] * $server[ 'slots' ] ) * (int) ( $cfg[ 'mem' ] / 1024 ) );
				} else {
					$proc_ram = (int) ( 100 / $rows[ 'ram' ] * (int) ( $cfg[ 'mem' ] / 1024 ) );
				}
				$ram = (int) ( $cfg[ 'mem' ] / 1024 );
			} else {
				$online = 0;
				$proc_online = 0;
				$cpu = 0;
				$proc_ram = 0;
				$ram = 0;
			}
			tpl::set ( '{online2}' , $online );
			tpl::set ( '{cpu}' , $cpu );
			tpl::set ( '{cpu_max}' , $rows[ 'cpu' ] * $server[ 'slots' ] );
			tpl::set ( '{proc_cpu}' , (int) ( ( 100 / ( $rows[ 'cpu' ] * $server[ 'slots' ] ) ) * $cpu ) );
			tpl::set ( '{proc_online}' , $proc_online );
			tpl::set ( '{ram}' , $ram );
			if ( $server[ 'game' ] == "mc" ) {
				tpl::set ( '{ram_max}' , $rows[ 'ram' ] * $server[ 'slots' ] );
			} else {
				tpl::set ( '{ram_max}' , $rows[ 'ram' ] );
			}
			tpl::set ( '{proc_ram}' , $proc_ram );

			$sql = db::q ( 'SELECT * FROM gh_rates where id="' . $server[ 'rate' ] . '"' );
			$rows = db::r ( $sql );
			if ( $rows[ 'ftp' ] == 1 ) {
				tpl::set_block ( "'\\[hdd\\](.*?)\\[/hdd\\]'si" , "\\1" );
				tpl::set ( '{hdd}' , (int) ( $cfg[ 'hdd' ] / 1024 ) );
				tpl::set ( '{hdd_max}' , (int) ( $rows[ 'hard' ] ) );
				tpl::set ( '{proc_hdd}' , (int) ( 100 / $rows[ 'hard' ] * (int) ( $cfg[ 'hdd' ] / 1024 ) ) );
			} else {
				tpl::set_block ( "'\\[hdd\\](.*?)\\[/hdd\\]'si" , "" );
			}
			tpl::set ( '{slotsall}' , $server[ 'slots' ] );
			if($server['game']=="ts3"){
				tpl::set_block ( "'\\[dop-s\\](.*?)\\[/dop-s\\]'si" , "" );
			}else{
				tpl::set_block ( "'\\[dop-s\\](.*?)\\[/dop-s\\]'si" , "\\1" );
			}
			tpl::compile ( 'content' );
			servers::$speedbar = $id;
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}
}

?>