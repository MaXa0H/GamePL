<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
header ( 'Access-Control-Allow-Origin: *' );
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
$true = true;
$act = r::g ( 1 );
api::inc('ysupport');
switch ( $act ) {
	case "load" :
		ysupport::load();
		break;
	case "login" :
		ysupport::login(r::g ( 2 ),r::g (3),r::g (4));
		break;
	default :
		break;
}
die;
?>