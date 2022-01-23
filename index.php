<?php
@session_start ();

date_default_timezone_set ( 'Europe/Moscow' );
@ini_set ( 'display_errors' , false );
@ini_set ( 'html_errors' , false );
@ini_set ( 'error_reporting' , E_ALL ^ E_WARNING ^ E_NOTICE );
@ob_start ();
@ob_implicit_flush ( 0 );
define( 'gamepl_er6tybuniomop' , true );
define( 'gamepl' , true );
define( 'ROOT' , dirname ( __FILE__ ) );
define( 'START_TIME' , microtime ( true ) );
if ( $file = file_get_contents ( ROOT . '/data/conf.ini' ) ) {
	if ( ! $conf = json_decode ( $file , true ) ) {
		echo 'Не удалось открыть настройки панели';
		exit;
	}
} else {
	echo 'Не удалось открыть настройки панели';
	exit;
}
if ( ! $conf[ 'curs' ] ) {
	$conf[ 'curs' ] = 1;
	$conf[ 'curs-name' ] = 'руб';
}
include_once ( ROOT . '/engine/classes/engine.class.php' );
include_once ( ROOT . '/engine/classes/lang.class.php' );
l::run ();
if ( api::phone () ) {
	header ( 'Access-Control-Allow-Origin: *' );
}
r::run ();
$do = r::g ( 0 );
if ( preg_match ( "/[^a-zA-Z0-9]/i" , $do ) ) {
	api::e404 ( 'Запрашиваемая страница не найдена.' );
} else {
	if ( $do ) {
		$file = ROOT . "/engine/routers/" . $do . ".php";
		if ( ! @file ( $file ) ) {
			api::e404 ( 'Запрашиваемая страница не найдена.' );
		} else {
			try {
				if ( ! @include_once ( $file ) ) {
					throw new Exception ( 'Не удалось загрузить модуль ' . $do );
				}
			} catch ( Exception $e ) {
				api::e404 ( $e->getMessage () );
			}

		}
	} else {
		$file = ROOT . "/engine/routers/index.php";
		try {
			if ( ! @include_once ( $file ) ) {
				throw new Exception ( 'Не удалось загрузить модуль ' . $do );
			}
		} catch ( Exception $e ) {
			api::e404 ( $e->getMessage () );
		}
	}
}
if ( ! api::mobile () ) {
	tpl::load ( 'main-header' );
	if ( ! empty( $title ) ) {
		$title = $title . ' - ' . $conf[ 'title' ];
	} else {
		$title = $conf[ 'title' ];
	}
	tpl::set ( '{title}' , $title );
	tpl::set ( '{keywords}' , $conf[ 'keywords' ] );
	tpl::set ( '{description}' , $conf[ 'description' ] );
	tpl::set ( '{domain}' , htmlspecialchars ( trim ( $_SERVER[ 'HTTP_HOST' ] ) ) );
	tpl::set ( '{balance}' , api::price ( api::info ( 'balance' ) ) . ' ' . $conf[ 'curs-name' ] );
	if ( api::$go ) {
		tpl::set ( '{userid}' , api::$logget[ 'id' ] );
	} else {
		tpl::set ( '{userid}' , '0' );
	}
	tpl::compile ( 'header' );
	tpl::load ( 'main' );
	if ( api::$go ) {
		tpl::set ( '{userid}' , api::$logget[ 'id' ] );
	} else {
		tpl::set ( '{userid}' , '0' );
	}
	tpl::set ( '{balance}' , api::price ( api::info ( 'balance' ) ) . ' ' . $conf[ 'curs-name' ] );
	tpl::set ( '{header}' , str_replace ( '<link rel="shortcut icon" href="{icon}" />' , '' , tpl::result ( 'header' ) ) );
	tpl::set ( '{title}' , $title );
	tpl::set ( '{title2}' , $title );
	api::nav_base ();
	tpl::set ( '{speedbar}' , api::speedbar () );
	tpl::set ( '{menu-left}' , api::speedbar ( '1' ) );
	if ( tpl::result ( 'nav_get' ) ) {
		$nav = tpl::result ( 'nav' );
	} else {
		$nav = "";
	}
	api::inc ( 'servers' );
	if ( servers::$speedbar != 0 ) {
		tpl::set ( '{content}' , $nav . tpl::result ( 'content' ) . tpl::result ( 'error' ) );
	} else {
		tpl::set ( '{content}' , $nav . tpl::result ( 'error' ) . tpl::result ( 'content' ) );
	}

	tpl::compile ( 'main' );
	echo tpl::result ( 'main' );
} else {
	echo tpl::result ( 'content' );
	echo '<div style="display:none;" class="auto_load_content">' . json_encode ( $logget_key ) . '</div>';
}
db::e ();
?>