<?php

	class servers_maps
	{
		public static function install ( $id , $addon )
		{
			global $cfg;
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
				if ( ! servers::friend ( $id , 'maps' ) ) {
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

					return false;
				}
				$game = $row[ 'game' ];
				$class = servers::game_class ( $game );
				if ( $class::info ( 'maps2' ) ) {
					db::q ( 'SELECT * FROM gh_maps where id="' . $addon . '"' );
					if ( db::n () == 1 ) {
						$addons = db::r ();
						db::q ( 'SELECT * FROM gh_maps_cat where id="' . $addons[ 'cat' ] . '"' );
						$cat = db::r ();
						if ( $game == $cat[ 'game' ] ) {
							db::q ( 'SELECT id FROM gh_maps_install where server="' . $id . '" and maps="' . $addon . '"' );
							if ( db::n () == 0 ) {
								include_once ( ROOT . '/engine/classes/ssh2.class.php' );
								if ( ssh::gh_box ( $row[ 'box' ] ) ) {
									$exec = "screen -dmS " . time () . " /bin/bash -c 'cd /host/" . $row[ 'user' ] . '/' . $row[ 'sid' ] . '/' . $class::info ( 'maps2' ) . ';';
									$exec .= "rm -R " . $addon . ".zip;";
									$exec .= "wget http://" . $cfg[ 'domain' ] . "/maps/" . $addon . ".zip;";
									$exec .= "sudo -u s" . $row[ 'sid' ] . " unzip -o -u " . $addon . " -d . && rm -Rf " . $addon . ".zip;";
									$exec .= "cd /host/" . $row[ 'user' ] . "/;chmod -R 755 " . $row[ 'sid' ] . ";exit;'";
									ssh::exec_cmd ( $exec );
									$go = $addons[ 'install' ] + 1;
									db::q ( "UPDATE gh_maps set install='" . $go . "' where id='" . $addon . "'" );
									db::q ( "INSERT INTO gh_maps_install set maps='" . $addon . "',server='" . $id . "'" );
									ssh::disconnect ();
									api::result ( l::t ( 'Установлено' ) , true );
								} else {
									api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
								}
							} else {
								api::result ( l::t ( 'Уже установлено' ) );
							}
						} else {
							api::result ( l::t ( 'Данное дополнение не для вашей игры' ) );
						}
					} else {
						api::result ( l::t ( 'Дополнение не найдено' ) );
					}
				} else {
					api::result ( l::t ( 'Для данной игры репозиторий отключен' ) );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

		public static function del ( $id , $addon )
		{
			global $cfg;
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
				if ( ! servers::friend ( $id , 'maps' ) ) {
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

					return false;
				}
				$game = $row[ 'game' ];
				$class = servers::game_class ( $game );
				if ( $class::info ( 'repository' ) ) {
					db::q ( 'SELECT * FROM gh_maps where id="' . $addon . '"' );
					if ( db::n () == 1 ) {
						$addons = db::r ();
						db::q ( 'SELECT * FROM gh_maps_cat where id="' . $addons[ 'cat' ] . '"' );
						$cat = db::r ();
						if ( $game == $cat[ 'game' ] ) {
							db::q ( 'SELECT id FROM gh_maps_install where server="' . $id . '" and maps="' . $addon . '"' );
							if ( db::n () == 1 ) {
								include_once ( ROOT . '/engine/classes/ssh2.class.php' );
								if ( ssh::gh_box ( $row[ 'box' ] ) ) {
									$exec = "screen -dmS " . time () . " /bin/bash -c 'cd /host/" . $row[ 'user' ] . '/' . $row[ 'sid' ] . '/' . $class::info ( 'maps2' ) . ';';
									$exec .= "rm " . $addon . ".sh;wget http://" . $cfg[ 'domain' ] . "/maps/" . $addon . ".sh;";
									$exec .= "chmod 0755 " . $addon . ".sh;";
									$exec .= "./" . $addon . ".sh;rm " . $addon . ".sh;exit;';";
									ssh::exec_cmd ( $exec );
									db::q ( "DELETE FROM gh_maps_install where maps='" . $addon . "' and server='" . $id . "'" );
									ssh::disconnect ();
									api::result ( l::t ( 'Удалено' ) , true );
								} else {
									api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
								}
							} else {
								api::result ( l::t ( 'Дополнение еще не установлено' ) );
							}
						} else {
							api::result ( l::t ( 'Данное дополнение не для вашей игры' ) );
						}
					} else {
						api::result ( l::t ( 'Дополнение не найдено' ) );
					}
				} else {
					api::result ( l::t ( 'Для данной игры репозиторий отключен' ) );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

		public static function listen ( $id )
		{
			global $cfg , $title;
			$title = 'Карты';
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'maps' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				$game = $row[ 'game' ];
				$class = servers::game_class ( $game );
				if ( $class::info ( 'maps2' ) ) {
					$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
					api::nav ( "/servers" , l::t ( "Серверы" ) );
					api::nav ( "/servers/base/" . $id , $adress );
					api::nav ( "" , l::t ( 'Карты' ) , '1' );
					servers::$speedbar = $id;
					$sql = db::q ( 'SELECT * FROM gh_maps_cat where game="' . $game . '"' );
					while ( $row2 = db::r ( $sql ) ) {

						tpl::load ( 'servers-repository-maps2-base-get' );
						tpl::set ( '{id}' , $id );
						tpl::set ( '{name}' , $row2[ 'name' ] );
						$sql1 = db::q ( 'SELECT * FROM gh_maps where cat="' . $row2[ 'id' ] . '"' );
						tpl::set ( '{num}' , db::n ( $sql1 ) );
						tpl::set ( '{id-cat}' , $row2[ 'id' ] );
						tpl::compile ( 'addons_get' );
					}
					$pages = (int) r::g ( '4' );
					db::q ( 'SELECT id FROM gh_maps_install where server="' . $id . '" order by id asc' );
					$all = db::n ();
					$num = 30;
					if ( $pages ) {
						if ( ( $all / $num ) > $pages ) {
							$page = $num * $pages;
						} else {
							$page = 0;
						}
					} else {
						$page = 0;
					}
					$sqlq = db::q ( 'SELECT * FROM gh_maps_install where server="' . $id . '" order by id asc LIMIT ' . $page . ' ,' . $num );
					while ( $row = db::r ( $sqlq ) ) {
						$sqweas = db::q ( 'SELECT * FROM gh_maps where id="' . $row[ 'maps' ] . '"' );
						$sqweas = db::r ( $sqweas );
						tpl::load ( 'servers-repository-maps-base-listen-get' );
						$status2 = '<a onclick="maps_remove(' . $sqweas[ 'id' ] . ',' . $id . ')" class="t btn btn-danger btn-block">'.l::t ('Удалить').'</a>';
						tpl::set ( '{install}' , $status2 );
						tpl::set ( '{id}' , $sqweas[ 'id' ] );
						tpl::set ( '{server}' , $id );
						tpl::set ( '{name}' , $sqweas[ 'name' ] );
						tpl::set ( '{img}' , '/maps/' . $sqweas[ 'id' ] . '.png' );
						tpl::compile ( 'addons_get2' );
					}
					tpl::load ( 'servers-repository-maps2-base' );
					tpl::set ( '{id}' , $id );
					tpl::set ( '{data}' , tpl::result ( 'addons_get2' ) );
					if ( tpl::result ( 'addons_get2' ) ) {
						tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "" );
						tpl::set_block ( "'\\[yes\\](.*?)\\[/yes\\]'si" , "\\1" );
					} else {
						tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "\\1" );
						tpl::set_block ( "'\\[yes\\](.*?)\\[/yes\\]'si" , "" );
						tpl::set ( '{error}' , l::t ( 'У Вас нет установленных карт' ) );
					}
					tpl::set ( '{nav}' , api::pagination ( $all , $num , $pages , '/servers/maps2/' . $id , '' ) );
					tpl::set ( '{cat}' , tpl::result ( 'addons_get' ) );
					tpl::compile ( 'content' );
				} else {
					api::result ( l::t ( 'Репозиторий карт для данной игры отключен' ) );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

		public static function listen_base ( $id , $cat , $cat2 , $search )
		{
			if ( strlen ( $search ) < 3 ) {
				$search = "";
			}
			global $cfg , $title;
			$title = l::t ( 'Репозиторий' );
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'maps' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				$sql_1 = db::q ( 'SELECT * FROM gh_maps_cat where id="' . $cat . '" order by id asc' );
				if ( db::n () == 1 ) {
					$cat123 = db::r ( $sql_1 );
					$game = $row[ 'game' ];
					$class = servers::game_class ( $game );
					if ( $class::info ( 'maps2' ) ) {
						$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
						servers::$speedbar = $id;
						api::nav ( "/servers" , l::t ( "Серверы" ) );
						api::nav ( "/servers/base/" . $id , $adress );
						api::nav ( "/servers/maps/" . $id , 'Карты' );
						$add = "";
						api::nav ( "" , $cat123[ 'name' ] , true );
						if ( $search ) {
							$add .= ' and name LIKE  "%' . $search . '%"';
						}

						db::q ( 'SELECT id FROM gh_maps where cat="' . $cat123[ 'id' ] . '"' . $add );
						$all = db::n ();
						$pages = (int) r::g ( 6 );
						$num = 30;
						if ( $pages ) {
							if ( ( $all / $num ) > $pages ) {
								$page = $num * $pages;
							} else {
								$page = 0;
							}
						} else {
							$page = 0;
						}
						$sqlq = db::q ( 'SELECT * FROM gh_maps where cat="' . $cat123[ 'id' ] . '"' . $add . ' order by id asc LIMIT ' . $page . ' ,' . $num );
						while ( $rowq = db::r ( $sqlq ) ) {
							tpl::load ( 'servers-repository-maps-base-listen-get' );
							db::q ( 'SELECT * FROM gh_maps_install where maps="' . $rowq[ 'id' ] . '" and server="' . $id . '"' );
							if ( db::n () == "1" ) {
								$status2 = '<a onclick="maps_remove(' . $rowq[ 'id' ] . ',' . $id . ')" class="t btn btn-danger btn-block">' . l::t ( 'Удалить' ) . '</a>';
							} else {
								$status2 = '<a onclick="maps_install(' . $rowq[ 'id' ] . ',' . $id . ')" class="t btn btn-success btn-block">' . l::t ( 'Установить' ) . '</a>';
							}
							tpl::set ( '{install}' , $status2 );
							tpl::set ( '{id}' , $rowq[ 'id' ] );
							tpl::set ( '{server}' , $id );
							tpl::set ( '{name}' , $rowq[ 'name' ] );
							tpl::set ( '{img}' , '/maps/' . $rowq[ 'id' ] . '.png' );
							tpl::compile ( 'addons_get' );
						}
						$sql123 = db::q ( 'SELECT * FROM gh_maps_cat where game="' . $game . '"' );
						while ( $row212 = db::r ( $sql123 ) ) {
							tpl::load ( 'servers-repository-maps2-base-get' );
							tpl::set ( '{id}' , $id );
							tpl::set ( '{name}' , $row212[ 'name' ] );
							$sql1 = db::q ( 'SELECT * FROM gh_maps where cat="' . $row212[ 'id' ] . '"' );
							tpl::set ( '{num}' , db::n ( $sql1 ) );
							tpl::set ( '{id-cat}' , $row212[ 'id' ] );
							tpl::compile ( 'addons_cat' );
						}


						tpl::load ( 'servers-repository-maps2-base-listen' );
						tpl::set ( '{id}' , $id );

						tpl::set ( '{cat}' , tpl::result ( 'addons_cat' ) );
						tpl::set ( '{data}' , tpl::result ( 'addons_get' ) );
						if ( tpl::result ( 'addons_get' ) ) {
							tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "" );
						} else {
							tpl::set_block ( "'\\[error\\](.*?)\\[/error\\]'si" , "\\1" );
							tpl::set ( '{error}' , l::t ( 'По вашему запросу ничего не найдено' ) );
						}
						$s = "";
						if ( $search ) {
							$s = "/?search=" . $search;
						}
						tpl::set ( '{nav}' , api::pagination ( $all , $num , $pages , '/servers/maps/cat/' . $id . '/' . $cat ) );
						$link = "/servers/maps/cat/" . $id . "/" . $cat;
						if ( $cat2[ 'id' ] ) {
							$link .= "/" . $cat2;
						}
						tpl::set ( '{linked}' , $link );
						tpl::compile ( 'content' );
					} else {
						api::result ( l::t ( 'Репозиторий отключен' ) );
					}
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}
	}
?>