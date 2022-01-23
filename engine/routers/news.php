<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
try {
	if ( ! $array = r::p (
		"/^\/news(\/([^\/]+))?\/([0-9]{4})(\/([0-9]{2}))?(\/([0-9]{2}))?(\/page\/([0-9]+))?(\/)?$/i" ,
		array (
			'2' => 'cat' ,
			'3' => 'year' ,
			'5' => 'month' ,
			'7' => 'day' ,
			'9' => 'page'
		)
	)
	) {
		if ( ! $array = r::p (
			"/^\/news(\/([^\/-]+))?(\/([0-9]+)-([a-zA-Z0-9_\-]+))?(\/page\/([0-9]+))?(\/)?$/i" ,
			array (
				'2' => 'cat' ,
				'4' => 'id' ,
				'5' => 'name' ,
				'7' => 'page'
			)
		)
		) {
			throw new Exception ( l::t('Запрашиваемая страница не найдена.') );
		}
	}
	api::inc ( 'news' );
	if ( (int) $array[ 'id' ] != 0 ) {
		news::full_base ( (int) $array[ 'id' ] );
	} else {
		news::base ( 1 );
	}
} catch ( Exception $e ) {
	api::e404 ( $e->getMessage () );
}
?>
