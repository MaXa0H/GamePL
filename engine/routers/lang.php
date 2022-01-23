<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
if(r::g ( 1 )=="js"){
	l::js();
}else{
	if(l::g(api::cl($_POST['lang']))){
		api::result('true',1);
	}else{
		api::result('false');
	}
}
die;
?>