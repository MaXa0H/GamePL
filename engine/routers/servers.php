<?php

$true = true;
api::inc ( 'servers' );


switch ( r::g ( 1 ) ) {
	case "sale" :
		if ( api::$go ) {
			if ( api::inc ( 'servers/sale' ) ) {
				switch ( r::g ( 2 ) ) {
					case "rates" :
						api::inc ( 'servers/sale-rates' );
						switch ( r::g ( 3 ) ) {
							case "add" :
								servers_sale_rates::add ( (int) r::g ( 4 ) );
								break;
							case "edit" :
								servers_sale_rates::edit ( (int) r::g ( 4 ) , (int) r::g ( 5 ) );
								break;
							case "dell" :
								servers_sale_rates::dell ( (int) r::g ( 4 ) , (int) r::g ( 5 ) );
								break;
							default :
								servers_sale_rates::base ( (int) r::g ( 3 ) );
								break;
						}
						break;
					case "add" :
						servers_sale::add ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
						break;
					case "go" :
						servers_sale::buy ( (int) r::g ( 3 ) );
						break;
					case "dell" :
						servers_sale::dell ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
						break;
					case "edit" :
						servers_sale::edit ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
						break;
					default:
						servers_sale::base ( (int) r::g ( 2 ) );
						break;
				}
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "rcon-bk" :
		if ( api::$go ) {
			servers::rcon_bk ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "chart" :
		api::inc ( 'chart' );
		switch ( r::g ( 2 ) ){
			case "online" :
				chart::online ();
				break;
			case "online2" :
				chart::online (true);
				break;
			case "cpu" :
				chart::cpu ();
				break;
			case "cpu2" :
				chart::cpu (true);
				break;
			case "ram" :
				chart::ram ();
				break;
			case "ram2" :
				chart::ram (true);
				break;
			case "hdd" :
				chart::hdd ();
				break;
			case "hdd2" :
				chart::hdd (true);
				break;
			case "banner" :
				chart::banner ();
				break;
			default:
				api::result ( l::t ( 'Критическая ошибка' ) );
				break;
		}
		break;
	case "create-token" :
		if ( api::$go ) {
			servers::ts3_token ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "create-token2" :
		if ( api::$go ) {
			servers::ts3_token2 ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "ts3-domain" :
		if ( api::$go ) {
			servers::ts3_domain ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "map" :
		if ( api::$go ) {
			api::inc ( 'servers/map' );
			servers_map::base ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "maps2" :
		if ( api::$go ) {
			$act2 = r::g ( 2 );
			if ( api::inc ( 'servers/maps2' ) ) {
				switch ( $act2 ) {
					case "cat" :
						if ( r::g ( 5 ) == "page" ) {
							$p = 0;
						} else {
							$p = (int) r::g ( 5 );
						}
						servers_maps2::listen_base ( (int) r::g ( 3 ) , (int) r::g ( 4 ) , $p , api::cl ( $_GET[ 'search' ] ) );
						break;
					case "install" :
						servers_maps2::install ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
						break;
					case "remove" :
						servers_maps2::del ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
						break;
					default:
						servers_maps2::listen ( (int) r::g ( 2 ) );
						break;
				}
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "maps" :
		if ( api::$go ) {
			$act2 = r::g ( 2 );
			if ( api::inc ( 'servers/maps' ) ) {
				switch ( $act2 ) {
					case "cat" :
						if ( r::g ( 5 ) == "page" ) {
							$p = 0;
						} else {
							$p = (int) r::g ( 5 );
						}
						servers_maps::listen_base ( (int) r::g ( 3 ) , (int) r::g ( 4 ) , $p , api::cl ( $_GET[ 'search' ] ) );
						break;
					case "install" :
						servers_maps::install ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
						break;
					case "remove" :
						servers_maps::del ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
						break;
					default:
						servers_maps::listen ( (int) r::g ( 2 ) );
						break;
				}
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "slots" :
		if ( api::$go ) {
			api::inc ( 'servers/slots' );
			servers_slots::base ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "edit" :
		if ( api::$go ) {
			api::inc ( 'servers/edit' );
			servers_edit::base ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "eac" :
		if ( api::$go ) {
			if ( api::inc ( 'servers/eac' ) ) {
				servers_eac::base ( (int) r::g ( 2 ) );
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "tv" :
		if ( api::$go ) {
			if ( api::inc ( 'servers/tv' ) ) {
				servers_tv::base ( (int) r::g ( 2 ) );
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "tv-demos" :
		if ( api::$go ) {
			if ( api::inc ( 'servers/tv' ) ) {
				servers_tv::listen ( (int) r::g ( 2 ) );
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "tv-demos-download" :
		if ( api::$go ) {
			if ( api::inc ( 'servers/tv' ) ) {
				servers_tv::download ( (int) r::g ( 2 ) , r::g ( 3 ) );
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "boost" :
		if ( api::$go ) {
			if ( api::inc ( 'servers/rise' ) ) {
				servers_rise::base ( (int) r::g ( 2 ) );
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "delete" :
		if ( api::$go ) {
			servers::delete ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "console" :
		if ( api::$go ) {
			api::inc ( 'servers/console' );
			servers_console::base ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "repository" :
		if ( api::$go ) {
			$act2 = r::g ( 2 );
			api::inc ( 'servers/repository' );
			switch ( $act2 ) {
				case "cat" :
					if ( r::g ( 5 ) == "page" ) {
						$p = 0;
					} else {
						$p = (int) r::g ( 5 );
					}
					servers_repository::listen_mc ( (int) r::g ( 3 ) , (int) r::g ( 4 ) , $p , api::cl ( $_GET[ 'search' ] ) );
					break;
				case "wiev" :
					servers_repository::wiev ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
					break;
				case "install" :
					servers_repository::install ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
					break;
				case "remove" :
					servers_repository::remove ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
					break;
				default:
					servers_repository::listen ( (int) r::g ( 2 ) );
					break;
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "buy" :
		api::inc ( 'servers/buy' );
		$act2 = r::g ( 2 );
		switch ( $act2 ) {
			case "load" :
				servers_buy::load ();
				break;
			case "cupon":
				servers_buy::cupon ();
				break;
			case "game" :
				servers_buy::base2 ( r::g ( 3 ) );
				break;
			default:
				servers_buy::base2 ( '' );
				break;
		}
		break;
	case "fastdl" :
		if ( api::$go ) {
			api::inc ( 'servers/fastdl' );
			$act2 = r::g ( 2 );
			switch ( $act2 ) {
				case "on" :
					servers_fastdl::on ( (int) r::g ( 3 ) );
					break;
				case "off" :
					servers_fastdl::off ( (int) r::g ( 3 ) );
					break;
				default:
					servers_fastdl::base ( (int) r::g ( 2 ) );
					break;
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "settings" :
		if ( api::$go ) {
			api::inc ( 'servers/settings' );
			$act2 = r::g ( 2 );
			switch ( $act2 ) {
				case "cfg" :
					servers_settings::cfg ( (int) r::g ( 3 ) );
					break;
				case "conf" :
					servers_settings::conf ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
					break;
				case "repository" :
					servers_settings::repository ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
					break;
				case "base" :
					servers_settings::base ( (int) r::g ( 3 ) );
					break;
				default:
					servers_settings::base ( (int) r::g ( 2 ) );
					break;
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "ftp" :
		if ( api::$go ) {
			api::inc ( 'servers/ftp' );
			$act2 = r::g ( 2 );
			switch ( $act2 ) {
				case "on" :
					servers_ftp::on ( (int) r::g ( 3 ) );
					break;
				case "off" :
					servers_ftp::off ( (int) r::g ( 3 ) );
					break;
				case "password" :
					servers_ftp::password ( (int) r::g ( 3 ) );
					break;
				case "online" :
					servers_ftp::online ( (int) r::g ( 3 ) );
					break;
				default:
					servers_ftp::base ( (int) r::g ( 2 ) );
					break;
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "time" :
		if ( api::$go ) {
			api::inc ( 'servers/buy' );
			servers_buy::time2 ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "friends" :
		if ( api::$go ) {
			api::inc ( 'servers/friends' );
			$act2 = r::g ( 2 );
			switch ( $act2 ) {
				case "add" :
					servers_friends::add ( (int) r::g ( 3 ) );
					break;
				case "del" :
					servers_friends::dell ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
					break;
				case "edit" :
					servers_friends::edit ( (int) r::g ( 3 ) , (int) r::g ( 4 ) );
					break;
				default:
					servers_friends::base ( (int) r::g ( 2 ) );
					break;
			}
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "off" :
		if ( api::$go ) {
			api::inc ( 'servers/act' );
			servers_act::off ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "on" :
		if ( api::$go ) {
			api::inc ( 'servers/act' );
			servers_act::on ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "restart" :
		if ( api::$go ) {
			api::inc ( 'servers/act' );
			servers_act::restart ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "reinstall" :
		if ( api::$go ) {
			api::inc ( 'servers/act' );
			servers_act::reinstall ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "update" :
		if ( api::$go ) {
			api::inc ( 'servers/act' );
			servers_act::update ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "get" :
		if ( api::$go ) {
			servers::get ( (int) r::g ( 3 ) , r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "base" :
		if ( api::$go ) {
			api::inc ( 'servers/server' );
			servers_server::full ( (int) r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	default :
		if ( api::$go ) {
			api::inc ( 'servers/server' );
			servers_server::listen ();
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
}
if ( servers::$speedbar != 0 ) {
	servers::speedbar ();
}

?>