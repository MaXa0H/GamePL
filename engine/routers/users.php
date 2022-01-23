<?php

if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
api::inc ( 'users' );
$act = r::g ( 1 );
switch ( $act ) {
	case "tpl" :
		$array = array (
			"tpl"  => $conf[ 'tpl2' ] ,
			"tpl2" => $conf[ 'tpl3' ] ,
			"tpl3" => $conf[ 'tpl4' ]
		);
		echo json_encode ( $array );
		exit;
		break;
	case "faq" :
		api::inc ( 'support' );
		faq::base ();
		break;
	case "feedback" :
		api::inc ( 'support' );
		support::feedback ();
		break;
	case "feedback-end" :
		api::inc ( 'support' );
		support::feedback_end ();
		break;
	case "edit" :
		api::inc ( 'users/edit' );
		$act2 = r::g ( 2 );
		switch ( $act2 ) {
			case "mail" :
				users_edit::edit_mail ();
				break;
			case "phone" :
				users_edit::edit_phone ();
				break;
			default :
				api::e404 ( l::t ( 'Запрашиваемая страница не найдена.' ) );
				break;
		}
		break;
	case "settings" :
		if ( api::$go ) {
			users::settings ( $_POST[ 'data' ] );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "profile" :
		if ( api::$go ) {
			users::profile ( r::g ( 2 ) );
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "photo" :
		if ( api::$go ) {
			users::avatar ();
		} else {
			api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
		}
		break;
	case "vk-auth" :
		users::vk_auth ();
		break;
	case "invite" :
		users::invite ( r::g ( 2 ) );
		break;
	case "login" :
		users::login ( $_POST[ 'data' ] );
		break;
	case "deposit" :
		api::inc ( 'deposit' );
		$act2 = r::g ( 2 );
		switch ( $act2 ) {
			case "result-robokassa" :
				deposit::result_robokassa ();
				break;
			case "result-qiwi" :
				deposit::result_qiwi();
				break;
			case "result-waytopay" :
				deposit::result_waytopay ();
				break;
			case "result-nextpay" :
				deposit::result_nextpay ();
				break;
			case "result-wm" :
				deposit::result_wm ();
				break;
			case "result-yandex" :
				deposit::result_yandex ();
				break;
			case "result-interkassa" :
				deposit::result_interkassa ();
				break;
			case "result-interkassa2" :
				deposit::result_interkassa2 ();
				break;
			case "result-unitpay" :
				deposit::result_unitpay ();
				break;
			case "result-sp" :
				deposit::result_sp ();
				break;
			case "fail" :
				if ( api::$go ) {
					deposit::fail ();
				} else {
					api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
				}
				break;
			case "success" :
				if ( api::$go ) {
					deposit::success ();
				} else {
					api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
				}
				break;
			default :
				if ( api::$go ) {
					deposit::base ();
				} else {
					api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
				}
				break;
		}
		break;
	case "logout" :
		users::logout ();
		break;
	case "signup" :
		if ( ! r::g ( 2 ) ) {
			users::signup ( $_POST[ 'data' ] );
		} else {
			users::signup_end ( r::g ( 2 ) );
		}
		break;
	case "signup-end" :
		users::signup_get ();
		break;
	case "recovery-get" :
		users::recovery_get ();
		break;
	case "recovery" :
		if ( ! r::g ( 2 ) ) {
			users::recovery ( $_POST[ 'data' ] );
		} else {
			users::recovery_end ( r::g ( 2 ) );
		}
		break;
	default :
		api::e404 ();
		break;
}
?>