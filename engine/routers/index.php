<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
if($conf['index']=="5"){
	tpl::load('main-index-new');
	tpl::compile('content');
}elseif($conf['index']=="1"){
	api::inc ( 'pages' );
	$array = array ();
	pages::base ($conf['index-page'],1);
}elseif($conf['index']=="3"){
	api::inc ( 'servers' );
	api::inc ( 'servers/monitoring' );
	servers_monitoring::listen ( '','',1 );
}elseif($conf['index']=="4"){
	tpl::load('main-index');
	tpl::compile('content');
}else{
	api::inc ( 'news' );
	$array = array ();
	news::base (0);
}

?>
