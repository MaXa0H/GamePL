<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
api::inc ( 'support' );
if ( ! r::g ( 1 ) ) {
	faq::base ();
} else {
	api::e404 ( l::t('Запрашиваемая страница не найдена') );
}
?>