<?php
api::$admin = true;

if ( api::admin () ) {
	$act = r::g ( 1 );
	if($act!="update"){
		if ( $act ) {
			api::nav ( '/admin' , l::t('Административный раздел') );
		}
	}
	switch ( $act ) {
		case "maps" :
			if ( api::admin ( 'maps' ) ) {
				if(api::inc ( 'admin/maps' )){
					$act2 = r::g ( 2 );
					switch ( $act2 ) {
						case "add" :
							admin_maps::add ();
							break;
						case "del" :
							admin_maps::del ( (int) r::g ( 3 ) );
							break;
						default:
							admin_maps::listen ();
							break;
					}
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;
		case "maps-cat" :
			if ( api::admin ( 'maps' ) ) {
				api::inc ( 'admin/maps-cat' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_maps_cat::add ();
						break;
					case "edit" :
						admin_maps_cat::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_maps_cat::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_maps_cat::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "mysql" :
			if ( api::admin ( 'mysql' ) ) {
				if(api::inc ( 'admin/mysql' )){
					$act2 = r::g ( 2 );
					switch ( $act2 ) {
						case "add" :
							admin_mysql::add ();
							break;
						case "del" :
							admin_mysql::del ( (int) r::g ( 3 ) );
							break;
						case "edit" :
							admin_mysql::edit ( (int) r::g ( 3 ) );
							break;
						case "on-off" :
							admin_mysql::on_off ( (int) r::g ( 3 ) );
							break;
						default:
							admin_mysql::listen ();
							break;
					}
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "mysql-rates" :
			if ( api::admin ( 'mysql' ) ) {
				if ( api::inc ( 'admin/mysql-rates' ) ) {
					$act2 = r::g ( 2 );
					switch ( $act2 ) {
						case "add" :
							admin_mysql_rates::add ();
							break;
						case "edit" :
							admin_mysql_rates::edit ( (int) r::g ( 3 ) );
							break;
						case "del" :
							admin_mysql_rates::del ( (int) r::g ( 3 ) );
							break;
						case "on-off" :
							admin_mysql_rates::on_off ( (int) r::g ( 3 ) );
							break;
						default:
							admin_mysql_rates::listen ();
							break;
					}
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "settings" :
			if ( api::admin ( 'settings' ) ) {
				api::inc ( 'admin/settings' );
				admin_settings::base ();
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			
			break;
		case "users" :
			if ( api::admin ( 'users' ) ) {
				api::inc ( 'admin/users' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "edit" :
						admin_users::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_users::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_users::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "rules" :
			if ( api::admin ( 'admins' ) ) {
				api::inc ( 'admin/rules' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_rules::add ();
						break;
					case "edit" :
						admin_rules::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_rules::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_rules::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "pages" :
			if ( api::admin ( 'pages' ) ) {
				api::inc ( 'admin/pages' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_pages::add ();
						break;
					case "edit" :
						admin_pages::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_pages::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_pages::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "news-cat" :
			if ( api::admin ( 'news' ) ) {
				api::inc ( 'admin/news-cat' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_news_cat::add ();
						break;
					case "edit" :
						admin_news_cat::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_news_cat::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_news_cat::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "news" :
			if ( api::admin ( 'news' ) ) {
				api::inc ( 'admin/news' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_news::add ();
						break;
					case "edit" :
						admin_news::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_news::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_news::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "location" :
			if ( api::admin ( 'locations' ) ) {
				api::inc ( 'admin/locations' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_locations::add ();
						break;
					case "edit" :
						admin_locations::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_locations::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_locations::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "forum" :
			if ( api::admin ( 'forum' ) ) {
				api::inc ( 'admin/forum' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_forum::add ();
						break;
					case "edit" :
						admin_forum::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_forum::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_forum::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "templates" :
			if ( api::admin ( 'tpl' ) ) {
				api::inc ( 'admin/templates' );
				admin_templates::load ();
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "rates" :
			if ( api::admin ( 'rates' ) ) {
				api::inc ( 'admin/rates' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_rates::add ();
						break;
					case "edit" :
						admin_rates::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_rates::del ( (int) r::g ( 3 ) );
						break;
					case "on-off" :
						admin_rates::on_off ( (int) r::g ( 3 ) );
						break;
					default:
						admin_rates::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "money" :
			if ( api::admin ( 'logs_puy' ) ) {
				api::inc ( 'admin/money' );
				admin_money::listen ();
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "boxes" :
			if ( api::admin ( 'boxes' ) ) {
				api::inc ( 'admin/boxes' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_boxes::add ();
						break;
					case "edit" :
						admin_boxes::edit ( (int) r::g ( 3 ) );
						break;
					case "on-off" :
						admin_boxes::on_off ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_boxes::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_boxes::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "boxes-ts3" :
			if ( api::admin ( 'boxes' ) ) {
				api::inc ( 'admin/boxes-ts3' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_boxes_ts3::add ();
						break;
					case "edit" :
						admin_boxes_ts3::edit ( (int) r::g ( 3 ) );
						break;
					case "on-off" :
						admin_boxes_ts3::on_off ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_boxes_ts3::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_boxes_ts3::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "faq" :
			if ( api::admin ( 'faq' ) ) {
				api::inc ( 'admin/faq' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_faq::add ();
						break;
					case "edit" :
						admin_faq::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_faq::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_faq::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "addons" :
			if ( api::admin ( 'addons' ) ) {
				api::inc ( 'admin/addons' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_addons::add ();
						break;
					case "del" :
						admin_addons::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_addons::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "addons-cat" :
			if ( api::admin ( 'addons' ) ) {
				api::inc ( 'admin/addons-cat' );
				$act2 = r::g ( 2 );
				switch ( $act2 ) {
					case "add" :
						admin_addons_cat::add ();
						break;
					case "edit" :
						admin_addons_cat::edit ( (int) r::g ( 3 ) );
						break;
					case "del" :
						admin_addons_cat::del ( (int) r::g ( 3 ) );
						break;
					default:
						admin_addons_cat::listen ();
						break;
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		case "rise" :
			if ( api::admin ( 'rise' ) ) {
				if ( api::inc ( 'admin/rise' ) ) {
					$act2 = r::g ( 2 );
					switch ( $act2 ) {
						case "add" :
							admin_rise::add ();
							break;
						case "edit" :
							admin_rise::edit ( (int) r::g ( 3 ) );
							break;
						case "del" :
							admin_rise::del ( (int) r::g ( 3 ) );
							break;
						case "logs" :
							admin_rise::money ();
							break;
						default:
							admin_rise::listen ();
							break;
					}
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "isp-rates" :
			if ( api::admin ( 'isp' ) ) {
				if ( api::inc ( 'admin/isp-rates' ) ) {
					$act2 = r::g ( 2 );
					switch ( $act2 ) {
						case "add" :
							admin_isp_rates::add ();
							break;
						case "edit" :
							admin_isp_rates::edit ( (int) r::g ( 3 ) );
							break;
						case "del" :
							admin_isp_rates::del ( (int) r::g ( 3 ) );
							break;
						case "on-off" :
							admin_isp_rates::on_off ( (int) r::g ( 3 ) );
							break;
						default:
							admin_isp_rates::listen ();
							break;
					}
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;


		case "isp-boxes" :
			if ( api::admin ( 'isp' ) ) {
				if ( api::inc ( 'admin/isp-boxes' ) ) {
					$act2 = r::g ( 2 );
					switch ( $act2 ) {
						case "add" :
							admin_isp_boxes::add ();
							break;
						case "edit" :
							admin_isp_boxes::edit ( (int) r::g ( 3 ) );
							break;
						case "del" :
							admin_isp_boxes::del ( (int) r::g ( 3 ) );
							break;
						case "on-off" :
							admin_isp_boxes::on_off ( (int) r::g ( 3 ) );
							break;
						default:
							admin_isp_boxes::listen ();
							break;
					}
				}
			} else {
				api::result ( l::t('Недостаточно привелегий') );
			}
			break;

		default :
			api::nav ( '/admin' , l::t('Административный раздел') , 1 );
				tpl::load2 ( 'admin' );
				$sql2 = db::q ( "SELECT sum FROM logs_balance WHERE tip='0' and time>'" . ( time () - 86400 * 30 ) . "'" );
				$money30 = 0;
				while ( $ew = db::r ( $sql2 ) ) {
					$money30 += $ew[ 'sum' ];
				}
				tpl::set ( '{$}' , $money30 );
				tpl::set ( '{$_%}' , (int) ( $money30 / ( $conf[ 'stats_profit' ] / 100 ) ) );

				$sql2 = db::q ( "SELECT id FROM users" );
				$all = db::n ( $sql2 );
				tpl::set ( '{users}' , $all );
				$sql2 = db::q ( "SELECT id FROM users where signup='0'" );
				$all2 = db::n ( $sql2 );
				tpl::set ( '{users_%}' , (int) ( $all2 / ( $all / 100 ) ) );

				$sql2 = db::q ( "SELECT id FROM gh_servers" );
				$all = db::n ( $sql2 );
				tpl::set ( '{servers}' , $all );
				$sql2 = db::q ( "SELECT id FROM gh_servers where status='1'" );
				$all2 = db::n ( $sql2 );
				tpl::set ( '{servers_%}' , (int) ( $all2 / ( $all / 100 ) ) );

				$sql2 = db::q ( "SELECT t2.online,t1.slots FROM gh_servers AS t1, gh_monitoring AS t2 WHERE t1.status='1'&&t1.id = t2.sid" );
				$online = $slots = 0;
				while ( $ew = db::r ( $sql2 ) ) {
					$online += $ew[ 'online' ];
					$slots += $ew[ 'slots' ];
				}
				tpl::set ( '{online}' , $online );
				tpl::set ( '{online_%}' , (int) ( $online / ( $slots / 100 ) ) );
				api::inc('admin/admin');
				admin::base();
				tpl::compile ( 'content' );
			break;
	}
} else {
	api::result ( l::t('Запрашиваемая страница не найдена.'));
}
if ( ! api::mobile () ) {
	tpl::load2 ( 'main-header' );
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
	tpl::load2 ( 'main' );
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
	api::inc('admin/admin');
	admin::base();
	tpl::compile ( 'main' );
	echo tpl::result ( 'main' );
} else {
	echo tpl::result ( 'content' );
	echo '<div style="display:none;" class="auto_load_content">' . json_encode ( $logget_key ) . '</div>';
}
db::e ();
die;
?>