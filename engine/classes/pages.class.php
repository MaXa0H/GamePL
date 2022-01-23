<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class pages
{
	public static function base ( $url,$true=false )
	{
		global $title;
		if($true){
			db::q ( 'SELECT * FROM pages where id="' . $url . '"' );
		}else{
			db::q ( 'SELECT * FROM pages where url="' . $url . '"' );
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			tpl::load ( 'main-page' );
			tpl::set ( '{name}' , $row[ 'name' ] );
			api::inc ( 'bbcode' );
			tpl::set ( '{data}' , bbcode::html ( base64_decode ( $row[ 'info' ] ) ) );
			tpl::compile ( 'content' );
			if(!$true) {
				api::nav ( "" , $row[ 'name' ] , '1' );
				$title = $row[ 'name' ];
				$conf[ 'keywords' ] = $row[ 'keywords' ];
				$conf[ 'description' ] = $row[ 'description' ];
			}
			db::q ( 'UPDATE pages set visits="' . ( $row[ 'visits' ] + 1 ) . '" where id="' . $row[ 'id' ] . '"' );
		} else {
			api::e404 ( l::t('Запрашиваемая страница не найдена') );
		}
	}
}

?>