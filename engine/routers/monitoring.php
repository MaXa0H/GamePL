<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
api::inc ( 'servers' );
api::inc ( 'servers/monitoring' );
if ( r::g ( 1 ) == "img" ) {
	servers_monitoring::img ( (int) r::g ( 5 ) );
} else {
	try {
		if ( ! $array = r::p (
			"/^\/monitoring(\/([0-9]+))?(\/game\/([a-zA-Z0-9]+))?(\/page\/([0-9]+))?(\/)?$/i" ,
			array (
				'2' => 'id' ,
				'4' => 'game' ,
				'6' => 'page'
			)
		)
		) {
			throw new Exception ( l::t('Запрашиваемая страница не найдена.') );
		}
		$_GET[ 'page' ] = $array[ 'page' ];
		if ( $array[ 'id' ] ) {
			servers_monitoring::full ( (int) $array[ 'id' ] );
		} else {
			servers_monitoring::listen ( $array[ 'game' ] );
		}
	} catch ( Exception $e ) {
		api::e404 ( $e->getMessage () );
	}
}
?>