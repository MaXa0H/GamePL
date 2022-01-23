<?php

if ( api::inc ( 'servers/mysql' ) ) {
	$act = r::g ( 1 );
	switch ( $act ) {
		case "buy" :
			servers_mysql::buy ();
			break;
		case "base" :
			if ( api::$go ) {
				servers_mysql::base ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t('Для доступа к данной странице нужно войти на сайт') );
			}
			break;
		case "dell" :
			if ( api::$go ) {
				servers_mysql::dell ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t('Для доступа к данной странице нужно войти на сайт') );
			}
			break;
		case "time" :
			if ( api::$go ) {
				servers_mysql::time ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t('Для доступа к данной странице нужно войти на сайт') );
			}
			break;
		case "pass" :
			if ( api::$go ) {
				servers_mysql::pass ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t('Для доступа к данной странице нужно войти на сайт') );
			}
			break;
		default :
			if ( api::$go ) {
				servers_mysql::listen ();
			} else {
				api::result ( l::t('Для доступа к данной странице нужно войти на сайт') );
			}
			break;
	}
}
?>