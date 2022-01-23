<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
api::inc ( 'support' );
$act = r::g ( 1 );
if ( api::$go ) {
	switch ( $act ) {
		case "ticket" :
			support::ticket ( (int) r::g ( 2 ) );
			break;
		case "add" :
			support::add ();
			break;
		case "locked" :
			support::listen_locked ();
			break;
		case "ajax" :
			support::ajax ();
			break;
		case "lock" :
			support::locked ( (int) r::g ( 2 ) );
			break;
		default :
			support::listen ();
			break;
	}
} else {
	api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
}
?>