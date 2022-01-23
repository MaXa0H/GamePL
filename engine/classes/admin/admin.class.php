<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class admin
{
	public static function base ()
	{
		global $conf;
		if ( api::admin () ) {
			if ( api::admin ( 'settings' ) ) {
				tpl::set_block ( "'\\[settings\\](.*?)\\[/settings\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[settings\\](.*?)\\[/settings\\]'si" , "" );
			}
			if ( api::admin ( 'pages' ) ) {
				tpl::set_block ( "'\\[pages\\](.*?)\\[/pages\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[pages\\](.*?)\\[/pages\\]'si" , "" );
			}
			if ( api::admin ( 'boxes' ) ) {
				tpl::set_block ( "'\\[boxes\\](.*?)\\[/boxes\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[boxes\\](.*?)\\[/boxes\\]'si" , "" );
			}
			if ( api::admin ( 'news' ) ) {
				tpl::set_block ( "'\\[news\\](.*?)\\[/news\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[news\\](.*?)\\[/news\\]'si" , "" );
			}
			if ( api::admin ( 'locations' ) ) {
				tpl::set_block ( "'\\[locations\\](.*?)\\[/locations\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[locations\\](.*?)\\[/locations\\]'si" , "" );
			}
			if ( api::admin ( 'rates' ) ) {
				tpl::set_block ( "'\\[rates\\](.*?)\\[/rates\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[rates\\](.*?)\\[/rates\\]'si" , "" );
			}
			if ( api::admin ( 'faq' ) ) {
				tpl::set_block ( "'\\[faq\\](.*?)\\[/faq\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[faq\\](.*?)\\[/faq\\]'si" , "" );
			}
			if ( api::admin ( 'tpl' ) ) {
				tpl::set_block ( "'\\[tpl\\](.*?)\\[/tpl\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[tpl\\](.*?)\\[/tpl\\]'si" , "" );
			}
			if ( api::admin ( 'logs_puy' ) ) {
				tpl::set_block ( "'\\[logs_puy\\](.*?)\\[/logs_puy\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[logs_puy\\](.*?)\\[/logs_puy\\]'si" , "" );
			}
			if ( api::admin ( 'files' ) ) {
				tpl::set_block ( "'\\[files\\](.*?)\\[/files\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[files\\](.*?)\\[/files\\]'si" , "" );
			}
			if ( api::admin ( 'admins' ) ) {
				tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "" );
			}
			if ( api::admin ( 'addons' ) ) {
				tpl::set_block ( "'\\[addons\\](.*?)\\[/addons\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[addons\\](.*?)\\[/addons\\]'si" , "" );
			}
			if ( api::admin ( 'maps' ) ) {
				tpl::set_block ( "'\\[maps\\](.*?)\\[/maps\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[maps\\](.*?)\\[/maps\\]'si" , "" );
			}
			if ( api::admin ( 'users' ) ) {
				tpl::set_block ( "'\\[users\\](.*?)\\[/users\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[users\\](.*?)\\[/users\\]'si" , "" );
			}
			if ( api::admin ( 'rise' ) ) {
				tpl::set_block ( "'\\[rise\\](.*?)\\[/rise\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[rise\\](.*?)\\[/rise\\]'si" , "" );
			}
			if ( api::admin ( 'isp' ) ) {
				tpl::set_block ( "'\\[isp\\](.*?)\\[/isp\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[isp\\](.*?)\\[/isp\\]'si" , "" );
			}
			if ( api::admin ( 'settings' ) ) {
				tpl::set_block ( "'\\[settings\\](.*?)\\[/settings\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[settings\\](.*?)\\[/settings\\]'si" , "" );
			}
			if ( api::admin ( 'update' ) ) {
				tpl::set_block ( "'\\[update\\](.*?)\\[/update\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[update\\](.*?)\\[/update\\]'si" , "" );
			}
			if ( api::admin ( 'forum' ) ) {
				tpl::set_block ( "'\\[forum\\](.*?)\\[/forum\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[forum\\](.*?)\\[/forum\\]'si" , "" );
			}
			if ( api::admin ( 'mysql' ) ) {
				tpl::set_block ( "'\\[mysql\\](.*?)\\[/mysql\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[mysql\\](.*?)\\[/mysql\\]'si" , "" );
			}
			if ( api::admin ( 'license' ) ) {
				tpl::set_block ( "'\\[license\\](.*?)\\[/license\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[license\\](.*?)\\[/license\\]'si" , "" );
			}
			if ( api::admin ( 'charts' ) ) {
				tpl::set_block ( "'\\[charts\\](.*?)\\[/charts\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[charts\\](.*?)\\[/charts\\]'si" , "" );
			}
			if ( api::admin ( 'ts3' ) ) {
				tpl::set_block ( "'\\[ts3\\](.*?)\\[/ts3\\]'si" , "\\1" );
			} else {
				tpl::set_block ( "'\\[ts3\\](.*?)\\[/ts3\\]'si" , "" );
			}
		} else {
			tpl::set_block ( "'\\[charts\\](.*?)\\[/charts\\]'si" , "\\1" );
			tpl::set_block ( "'\\[license\\](.*?)\\[/license\\]'si" , "\\1" );
			tpl::set_block ( "'\\[settings\\](.*?)\\[/settings\\]'si" , "\\1" );
			tpl::set_block ( "'\\[pages\\](.*?)\\[/pages\\]'si" , "\\1" );
			tpl::set_block ( "'\\[news\\](.*?)\\[/news\\]'si" , "\\1" );
			tpl::set_block ( "'\\[faq\\](.*?)\\[/faq\\]'si" , "\\1" );
			tpl::set_block ( "'\\[locations\\](.*?)\\[/locations\\]'si" , "\\1" );
			tpl::set_block ( "'\\[files\\](.*?)\\[/files\\]'si" , "\\1" );
			tpl::set_block ( "'\\[rates\\](.*?)\\[/rates\\]'si" , "\\1" );
			tpl::set_block ( "'\\[logs_puy\\](.*?)\\[/logs_puy\\]'si" , "\\1" );
			tpl::set_block ( "'\\[tpl\\](.*?)\\[/tpl\\]'si" , "\\1" );
			tpl::set_block ( "'\\[boxes\\](.*?)\\[/boxes\\]'si" , "\\1" );
			tpl::set_block ( "'\\[ts3\\](.*?)\\[/ts3\\]'si" , "\\1" );
			tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "\\1" );
			tpl::set_block ( "'\\[addons\\](.*?)\\[/addons\\]'si" , "\\1" );
			tpl::set_block ( "'\\[maps\\](.*?)\\[/maps\\]'si" , "\\1" );
			tpl::set_block ( "'\\[users\\](.*?)\\[/users\\]'si" , "\\1" );
			tpl::set_block ( "'\\[rise\\](.*?)\\[/rise\\]'si" , "\\1" );
			tpl::set_block ( "'\\[isp\\](.*?)\\[/isp\\]'si" , "\\1" );
			tpl::set_block ( "'\\[settings\\](.*?)\\[/settings\\]'si" , "\\1" );
			tpl::set_block ( "'\\[update\\](.*?)\\[/update\\]'si" , "\\1" );
			tpl::set_block ( "'\\[forum\\](.*?)\\[/forum\\]'si" , "\\1" );
			tpl::set_block ( "'\\[mysql\\](.*?)\\[/mysql\\]'si" , "\\1" );
		}
	}
}

?>