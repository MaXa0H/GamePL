<?php


	class isp_api
	{
		public static $rules      = array (
			'shell' ,
			'ssl' ,
			'cgi' ,
			'phpmod' ,
			'phpcgi' ,
			'disklimit' ,
			'ftplimit' ,
			'maillimit' ,
			'domainlimit' ,
			'webdomainlimit' ,
			'maildomainlimit' ,
			'baselimit' ,
			'baseuserlimit'
		);
		public static $rules_isp5 = array (
			'shell'           => 'limit_shell' ,
			'ssl'             => 'limit_ssl' ,
			'cgi'             => 'limit_cgi' ,
			'phpmod'          => 'limit_php_mode_mod' ,
			'phpcgi'          => 'limit_php_mode_cgi' ,
			'disklimit'       => 'limit_quota' ,
			'ftplimit'        => 'limit_ftp_users' ,
			'maillimit'       => 'limit_emails' ,
			'domainlimit'     => 'limit_domains' ,
			'webdomainlimit'  => 'limit_webdomains' ,
			'maildomainlimit' => 'limit_emaildomains' ,
			'baselimit'       => 'limit_db' ,
			'baseuserlimit'   => 'limit_db_users'
		);
		public static $auth       = false;
		public static $ip         = false;

		public static function connect ( $ip , $pass )
		{
			self::$ip = $ip;
			$d = simplexml_load_file ( 'https://' . self::$ip . '/ispmgr?out=xml&func=auth&username=root&password=' . $pass );
			if ( $d->error ) {
				if ( ! servers_isp::$cron ) {
					api::result ( l::t('Не удалось установить соединение') );
				}
			} elseif ( $d->authfail ) {
				if ( ! servers_isp::$cron ) {
					api::result ( l::t('Не удалось установить соединение') );
				}

			} else {
				self::$auth = $d->auth;

				return true;
			}

			return false;
		}

		public static function install ( $cfg , $ver )
		{
			$c = "";
			foreach ( $cfg as $key => $value ) {
				if ( $ver == 5 ) {
					if ( in_array ( $key , self::$rules ) ) {
						$c .= "&" . self::$rules_isp5[ $key ] . "=" . $value;
					} else {
						$c .= "&" . $key . "=" . $value;
					}
				} else {
					$c .= "&" . $key . "=" . $value;
				}
			}
			$d = simplexml_load_file ( 'https://' . self::$ip . '/ispmgr?auth=' . self::$auth . '&out=xml&func=user.edit&sok=yes&owner=root' . $c );
			if ( ! $d->error ) {
				return true;
			} else {
				if ( $ver == 5 ) {
					api::result ( $d->error->msg );
				} else {
					api::result ( $d->error );
				}
			}

			return false;
		}

		public static function dell ( $id )
		{
			$d = simplexml_load_file ( 'https://' . self::$ip . '/ispmgr?auth=' . self::$auth . '&out=xml&func=user.delete&elid=u' . $id );
			if ( ! $d->error ) {
				return true;
			} else {
				api::result ( l::t('Не удалось удалить пользователя') );
			}

			return false;
		}
	}
?>