<?php
date_default_timezone_set ( 'Europe/Moscow' );
@ini_set ( 'display_errors' , false );
@ini_set ( 'html_errors' , false );
@ini_set ( 'error_reporting' , E_ALL ^ E_WARNING ^ E_NOTICE );
define( 'gamepl' , true );
define( 'gamepl_er6tybuniomop' , true );
define( 'ENGINE' , true );
define( 'ROOT' , dirname ( __FILE__ ) );
$cron = 2;
if ( $file = file_get_contents ( ROOT . '/data/conf.ini' ) ) {
	if ( ! $conf = json_decode ( $file , true ) ) {
		exit;
	}
} else {
	exit;
}

include_once ( ROOT . '/engine/classes/engine.class.php' );

include_once ( ROOT . '/engine/classes/lang.class.php' );

l::run();
api::inc ( 'threads' );
api::inc ( 'cron' );
$params = Threads::getParams();
if($_SERVER['argv']['1'] == 'start'){
	cron::start(1,$params);
}else{
	if($params['start']){
		cron::start(1,$params);
	}else{
		cron::start(0,$params);
	}
}	if($_SERVER[ 'HTTP_HOST' ] != $conf[ 'domain' ]){
		die;
	}
	if($_SERVER[ 'HTTP_HOST' ] != "imperhost.ru"){
		die;
	}
	if(time()>1454716800){
	die;
}
?>