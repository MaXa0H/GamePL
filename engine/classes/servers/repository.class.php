<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class servers_repository
{
	public static $color = array (
		'blue',
		'green',
		'red',
		'yellow',
		'purple',
		'grey',

		'blue-hoki',
		'green-meadow',
		'red-pink',
		'yellow-gold',
		'purple-plum',
		'grey-cascade',

		'blue-steel',
		'green-seagreen',
		'red-sunglo',
		'yellow-casablanca',
		'purple-medium',
		'grey-silver',

		'blue-madison',
		'green-turquoise',
		'red-intense',
		'yellow-crusta',
		'purple-studio',
		'grey-steel',

		'blue-chambray',
		'green-haze',
		'red-thunderbird',
		'yellow-lemon',
		'purple-wisteria',
		'grey-cararra',

		'blue-ebonyclay',
		'green-jungle',
		'red-flamingo',
		'yellow-saffron',
		'purple-seance',
		'grey-gallery'
	);
	
	public static function remove ( $id , $addon )
	{
		global $cfg,$conf;
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'modules' )){
				api::result(l::t ('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ('Срок аренды сервера истек' ));

				return false;
			}
			$game = $row[ 'game' ];
			$class = servers::game_class ( $game );
			if ( $class::info ( 'repository' ) ) {
				
				db::q ( 'SELECT * FROM gh_addons where id="' . $addon . '"' );
				if ( db::n () == 1 ) {
					$addons = db::r ();
					db::q ( 'SELECT id FROM gh_addons_cat where id="' . $addons[ 'cat' ] . '" and game="' . $game . '"' );
					if ( db::n () == 1 ) {
						db::q ( 'SELECT id FROM gh_addons_install where server="' . $id . '" and addon="' . $addon . '"' );
						if ( db::n () == 1 ) {
							api::inc ( 'ssh2' );
							if ( ssh::gh_box ( $row[ 'box' ] ) ) {
								$exec = "screen -dmS " . time () . " /bin/bash -c 'cd /host/" . $row[ 'user' ] . '/' . $row[ 'sid' ] . '/' . $addons[ 'dir' ] . ';';
								$exec .= "rm " . $addon . ".sh;wget http://" . $cfg[ 'domain' ] . "/addons/" . $addon . ".sh;";
								$exec .= "chmod 0755 " . $addon . ".sh;";
								$exec .= "./" . $addon . ".sh;rm " . $addon . ".sh;exit;';";
								ssh::exec_cmd ( $exec );
								$sql = db::q('SELECT * FROM gh_addons_cfg where addon="'.$addon.'"  order by id desc');
								while ( $row_1 = db::r( $sql ) ) {
									$exec = 'cd /host/'.$row['user'].'/'.$row['sid'].'/;';
									$row_1['value'] = str_replace('"','\"',$row_1['value']);
									$row_1['value'] = str_replace('/','\/',$row_1['value']);
									$exec .= 'sed -i "/'.$row_1['value'].'/d" "'.$addons['dir'].$row_1['file'].'";';
									ssh::exec_cmd($exec);
								}
								db::q ( "DELETE FROM gh_addons_install where addon='" . $addon . "' and server='" . $id . "'" );
								ssh::disconnect ();
								api::result (l::t ( 'Удалено' ), true );
							} else {
								api::result ( l::t ('Не удалось установить соединение с сервером') );
							}
						} else {
							api::result ( l::t ('Дополнение еще не установлено') );
						}
					} else {
						api::result ( l::t ('Данное дополнение не для вашей игры' ));
					}
				} else {
					api::result ( l::t ('Дополнение не найдено') );
				}
			} else {
				api::result ( l::t ('Для данной игры репозиторий отключен') );
			}
		} else {
			api::result ( l::t ('Сервер не найден') );
		}
	}

	public static function install ( $id , $addon )
	{
		global $cfg,$conf;
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'modules' )){
				api::result(l::t ('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ('Срок аренды сервера истек' ));

				return false;
			}
			$game = $row[ 'game' ];
			$class = servers::game_class ( $game );
			if ( $class::info ( 'repository' ) ) {
				db::q ( 'SELECT * FROM gh_addons where id="' . $addon . '"' );
				if ( db::n () == 1 ) {
					$addons = db::r ();
					db::q ( 'SELECT id FROM gh_addons_cat where id="' . $addons[ 'cat' ] . '" and game="' . $game . '"' );
					if ( db::n () == 1 ) {
						db::q ( 'SELECT id FROM gh_addons_install where server="' . $id . '" and addon="' . $addon . '"' );
						if ( db::n () == 0 ) {
							api::inc ( 'ssh2' );
							if ( ssh::gh_box ( $row[ 'box' ] ) ) {
								$exec = "screen -dmS " . time () . " /bin/bash -c 'cd /host/" . $row[ 'user' ] . '/' . $row[ 'sid' ] . '/' . $addons[ 'dir' ] . ';';
								$exec .= "rm " . $addon . ".sh;wget http://" . $cfg[ 'domain' ] . "/addons/" . $addon . ".sh;";
								$exec .= "chmod 0755 " . $addon . ".sh;";
								$exec .= "./" . $addon . ".sh;rm " . $addon . ".sh;cd /host/" . $row[ 'user' ] . '/' . $row[ 'sid' ] . '/;';
								$exec .= "rm -R " . $addon . ".zip;";
								$exec .= "wget http://" . $cfg[ 'domain' ] . "/addons/" . $addon . ".zip;";
								$exec .= "sudo -u s" . $row[ 'sid' ] . " unzip -o -u " . $addon . ".zip -d " . $addons[ 'dir' ] . " && rm -Rf " . $addon . ".zip;";
								$exec .= "cd /host/" . $row[ 'user' ] . "/;chmod -R 771 " . $row[ 'sid' ] . ";exit;'";
								ssh::exec_cmd ( $exec );
								$go = $addons[ 'install' ] + 1;
								db::q ( "UPDATE gh_addons set install='" . $go . "' where id='" . $addon . "'" );
								$sql = db::q ( 'SELECT * FROM gh_addons_cfg where addon="' . $addon . '"  order by id desc' );
								while ( $row_1 = db::r ( $sql ) ) {
									$exec = 'cd /host/' . $row[ 'user' ] . '/' . $row[ 'sid' ] . '/;';
									$exec .= "echo \"\n" . $row_1[ 'value' ] . "\" >> " . $addons[ 'dir' ] . $row_1[ 'file' ];
									ssh::exec_cmd ( $exec );
								}
								db::q ( "INSERT INTO gh_addons_install set addon='" . $addon . "',server='" . $id . "'" );
								ssh::disconnect ();
								api::result (l::t ( 'Установлено') , true );
							} else {
								api::result (l::t ( 'Не удалось установить соединение с сервером') );
							}
						} else {
							api::result (l::t ( 'Уже установлено') );
						}
					} else {
						api::result ( l::t ('Данное дополнение не для вашей игры') );
					}
				} else {
					api::result ( l::t ('Дополнение не найдено') );
				}
			} else {
				api::result (l::t ( 'Для данной игры репозиторий отключен' ));
			}
		} else {
			api::result (l::t ( 'Сервер не найден') );
		}
	}

	public static function listen ( $id )
	{
		global $cfg , $title,$conf;
		$title = 'Репозиторий';
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'modules' )){
				api::result(l::t ('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			$game = $row[ 'game' ];
			$class = servers::game_class ( $game );
			if ( $class::info ( 'repository' ) ) {
				$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
				api::nav ( "/servers" , l::t ("Серверы") );
				api::nav ( "/servers/base/" . $id , $adress );
				api::nav ( "" , l::t ('Репозиторий') , '1' );
				servers::$speedbar = $id;
				$sql_1 = db::q ( 'SELECT * FROM gh_addons_cat where game="' . $game . '" order by id asc' );
				if ( db::n () > 0 ) {
					$addons = "";
					$g_1 = 0;
					$g_2 = 1;
					$g_4 = 1;
					$g_3 = 0;
					while ( $row2 = db::r ( $sql_1 ) ) {
						$g_1 ++;
						$g_2 ++;
						$g_4 ++;
						$d = "";
						$d2 = "0px";
						$sql_2 = db::q ( 'SELECT * FROM gh_addons where cat="' . $row2[ 'id' ] . '" order by id asc' );
						while ( $row3 = db::r ( $sql_2 ) ) {
							$g_3 ++;
							db::q ( 'SELECT * FROM gh_addons_install where addon="' . $row3[ 'id' ] . '" and server="' . $id . '"' );
							if ( db::n () == "1" ) {
								$status = '<img src="/img/s_1.png" style="height:16px;"/>';
								$status2 = '<a onclick="addons_remove(' . $row3[ 'id' ] . ',' . $id . ')" class="t">'.l::t ('Удалить').'</a>';
							} else {
								$status = '<img src="/img/s_2.png" style="height:16px;"/>';
								$status2 = '<a onclick="addons_install(' . $row3[ 'id' ] . ',' . $id . ')" class="t">'.l::t ('Установить').'</a>';
							}
							if ( $row3[ 'install' ] == "" ) {
								$install = "0";
							} else {
								$install = $row3[ 'install' ];
							}
							tpl::load ( 'servers-repository-listen-get' );
							tpl::set ( '{name}' , $row3[ 'name' ] );
							tpl::set ( '{info}' , $row3[ 'info' ] );
							if ( $row3[ 'info' ] != "" ) {
								tpl::set_block ( "'\\[info\\](.*?)\\[/info\\]'si" , "\\1" );
							} else {
								tpl::set_block ( "'\\[info\\](.*?)\\[/info\\]'si" , "" );
							}
							tpl::set ( '{id_3}' , $g_3 );
							tpl::set ( '{id_2}' , $g_2 );
							tpl::set ( '{id_4}' , $g_4 );
							tpl::set ( '{id}' , $row3[ 'id' ] );
							tpl::set ( '{status}' , $status );
							tpl::set ( '{install}' , $status2 );
							tpl::set ( '{installed}' , $install );
							tpl::compile ( 'addons_get' );
						}
						tpl::load ( 'servers-repository-listen-cat' );
						tpl::set ( '{data}' , tpl::result ( 'addons_get' ) );
						tpl::set ( '{title}' , $row2[ 'name' ] );
						tpl::set ( '{id_1}' , $g_1 );
						tpl::set ( '{id_4}' , $g_4 );
						tpl::set ( '{in}' , $d );
						tpl::set ( '{height}' , $d2 );
						tpl::compile ( 'addons' );
						unset( tpl::$result[ 'addons_get' ] );
					}
					$key = tpl::result ( 'addons' );
					tpl::load ( 'servers-repository-listen' );
					tpl::set ( '{addons}' , $key );
					tpl::set ( '{id}' , $id );
					tpl::compile ( 'content' );
				} else {
					api::result ( l::t ('Нет доступных дополнений для вашей игры') );
				}
			} else {
				api::result ( l::t ('Репозиторий для данной игры отключен') );
			}
		} else {
			api::result ( l::t ('Сервер не найден') );
		}
	}
}
?>