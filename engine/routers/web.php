<?php
$true = true;
if ( api::inc ( 'servers/isp' ) ) {
	$act = r::g ( 1 );
	switch ( $act ) {
		case "buy" :
			servers_isp::buy ();
			break;
		case "base" :
			if ( api::$go ) {
				servers_isp::base ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
			}
			break;
		case "dell" :
			if ( api::$go ) {
				servers_isp::dell ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
			}
			break;
		case "time" :
			if ( api::$go ) {
				servers_isp::time ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
			}
			break;
		case "pass" :
			if ( api::$go ) {
				servers_isp::pass ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
			}
			break;
		default :
			if ( api::$go ) {
				servers_isp::listen ();
			} else {
				api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
			}
			break;
	}
}
?>