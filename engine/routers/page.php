<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
api::inc ( 'pages' );
pages::base ( api::cl ( r::g ( 1 ) ) );
?>