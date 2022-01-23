<?php

	if ( api::inc ( 'forum/base' ) ) {
		if ( r::g ( 1 ) == "lock" ) {
			if ( api::admin ( 'forum' ) ) {
				forum::lock ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t('У вас нет привилегий') );
			}
		} elseif ( r::g ( 1 ) == "del" ) {
			if ( api::admin ( 'forum' ) ) {
				forum::del ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t('У вас нет привилегий') );
			}

		} elseif ( r::g ( 1 ) == "dell" ) {
			if ( api::admin ( 'forum' ) ) {
				forum::dell ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t('У вас нет привилегий') );
			}
		} elseif ( r::g ( 1 ) == "mes-edit" ) {
			if ( api::admin ( 'forum' ) ) {
				forum::edit ( (int) r::g ( 2 ) );
			} else {
				api::result ( l::t('У вас нет привилегий' ));
			}
		} elseif ( r::g ( 1 ) ) {
			if ( r::g ( 2 ) == "add" ) {
				if ( ! api::$go ) {
					api::result ( l::t('Для доступа к данной странице нужно авторизоваться на сайте') );
				} else {
					forum::post_add ( r::g ( 1 ) );
				}
			} elseif ( r::g ( 2 ) ) {
				forum::post ( r::g ( 2 ) );
			} else {
				forum::cat ( r::g ( 1 ) );
			}
		} else {
			forum::base ();
		}
	}
?>