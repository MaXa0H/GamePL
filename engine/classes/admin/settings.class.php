<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class admin_settings
{
	public static $base    = array (
		'domain' ,
		'title' ,
		'mail' ,
		'mail_type' ,
		'm_ip' ,
		'm_port' ,
		'key' ,
		'keywords' ,
		'description' ,
		'curs' ,
		'curs-name' ,
		'mysql-price' ,
		'stats_profit' ,
		'buy' ,
		'tpl',
		'vk_id',
		'vk_key',
		'dell',
		'lang',
		'lang2'
	);
	public static $payment = array (
		'wmr1' ,
		'wmr2' ,
		'yandex1' ,
		'yandex2' ,
		'rbc1' ,
		'rbc2' ,
		'rbc3' ,
		'rbc4' ,
		'unitpay_key' ,
		'unitpay' ,
		'sp1' ,
		'sp2' ,
		'nextpay' ,
		'nextpay_key' ,
		'waytopay' ,
		'waytopay2' ,
		'interkassa' ,
		'interkassa2' ,
		'interkassa3',
		'qiwi',
		'qiwi2',
		'qiwi3',
		'qiwi4'
	);
	public static $sms2    = array (
		'key' ,
		'phone_admin'
	);

	public static $sms = array (
		'signup'   => 'Подтверждение регистрации' ,
		'recovery' => 'Восстановление пароля' ,
		'support'  => 'Уведомление о новом сообщении в поддержке' ,
		'time_pre' => 'Напоминании об оплате услуг за 3 дня' ,
		'time_end' => 'Уведомление об окончании оплаты услуги' ,
		'time_del' => 'Уведомление об удалении услуги' ,
		'boxes'    => 'Уведомление о пропаже соединения с физ. сервером.' ,
		'payment'  => 'Уведомление администратора о пополнение счетов.'
	);

	public static $index = array (
		'1' => 'Статистическая страница' ,
		'2' => 'Новости' ,
		'3' => 'Мониторинг' ,
		'4' => 'Шаблон main-index'
	);

	public static $mail = array (
		'aviras' => 'Aviras' ,
		'mail'   => 'mail' ,
		'smtp'   => 'smtp'
	);
	public static $smtp = array (
		'server' ,
		'port' ,
		'mail' ,
		'pass'
	);

	public static function base ()
	{
		global $title , $conf , $m;
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			$new_conf = $conf;
			foreach ( self::$base as $key ) {
				$new_conf[ $key ] = $data[ 'base' ][ $key ];
			}
			$new_conf[ 'signup' ] = (int) $data[ 'base' ][ 'signup' ];
			$new_conf[ 'sphone' ] = (int) $data[ 'base' ][ 'sphone' ];

			$new_conf[ 'tpl' ] = (int) $data[ 'base' ][ 'tpl' ];
			$new_conf[ 'tpl2' ] = (int) $data[ 'base' ][ 'tpl2' ];
			$new_conf[ 'tpl3' ] = (int) $data[ 'base' ][ 'tpl3' ];
			$new_conf[ 'tpl4' ] = (int) $data[ 'base' ][ 'tpl4' ];
			$new_conf[ 'fprice' ] = (int) $data[ 'fprice' ];
			foreach ( self::$payment as $key ) {
				$new_conf[ $key ] = api::cl ( $data[ 'payment' ][ $key ] );
			}
			foreach ( self::$sms as $key => $val ) {
				$new_conf[ 'sms_' . $key ] = (int) $data[ 'sms' ][ $key ];
			}
			foreach ( self::$sms2 as $key => $val ) {
				$new_conf[ 'sms_' . $val ] = api::cl ( $data[ 'sms' ][ $val ] );
			}
			unset( $new_conf[ 'invite' ] );
			foreach ( $data[ 'invite' ][ 'key' ] as $key => $val ) {
				$new_conf[ 'invite' ][ (int) $val ] = (int) $data[ 'invite' ][ 'val' ][ $key ];
			}
			unset( $new_conf[ 'price' ] );
			foreach ( $data[ 'price' ][ 'key' ] as $key => $val ) {
				$a = array();
				$a['day'] = $val;
				$a['price1'] = (int) $data[ 'price' ][ 'val' ][ $key ];
				$a['price2'] = (int) $data[ 'price' ][ 'val2' ][ $key ];
				$new_conf[ 'price' ][] = $a;
			}
			unset( $new_conf[ 'cup' ] );
			foreach ( $data[ 'cup' ][ 'key' ] as $key => $val ) {
				$d = array ();
				$d[ 'code' ] = api::cl ( $val );
				$d[ 'type' ] = (int) $data[ 'cup' ][ 'typ' ][ $key ];
				$d[ 'sum' ] = (int) $data[ 'cup' ][ 'val' ][ $key ];
				$d[ 'min' ] = (int) $data[ 'cup' ][ 'val2' ][ $key ];
				$new_conf[ 'cup' ][ ] = $d;
			}
			foreach ( self::$smtp as $key ) {
				$new_conf[ 'smtp' ][ $key ] = api::cl ( $data[ 'smtp' ][ $key ] );
			}
			$new_conf[ 'index' ] = (int) $data[ 'base' ][ 'index' ];
			$new_conf[ 'mail_type' ] = api::cl ( $data[ 'base' ][ 'mail_type' ] );
			$new_conf[ 'index-page' ] = (int) $data[ 'base' ][ 'index-page' ];
			file_put_contents ( ROOT . '/data/conf.ini' , json_encode ( $new_conf , JSON_UNESCAPED_UNICODE ) );
			$m->flush ();
			api::result ( 'Настройки сохранены' , true );
		}
		api::nav ( '' , l::t("Настройка панели")." GamePL" , 1 );
		$title = l::t("Настройка панели")." GamePL";
		tpl::load2 ( 'admin-settings' );
		foreach ( self::$base as $key ) {
			tpl::set ( '{base=' . $key . '}' , $conf[ $key ] );
		}
		foreach ( self::$smtp as $key ) {
			tpl::set ( '{smtp=' . $key . '}' , $conf[ 'smtp' ][ $key ] );
		}
		foreach ( self::$sms2 as $key ) {
			tpl::set ( '{sms=' . $key . '}' , $conf[ 'sms_' . $key ] );
		}
		foreach ( self::$payment as $key ) {
			tpl::set ( '{payment=' . $key . '}' , $conf[ $key ] );
		}
		$pr = $conf[ 'invite' ];
		foreach ( $pr as $key => $val ) {
			tpl::load2 ( 'admin-settings-invite' );
			tpl::set ( '{key}' , $key );
			tpl::set ( '{val}' , $val );
			tpl::compile ( 'data' );
		}
		tpl::set ( '{invite}' , tpl::$result[ 'data' ] );
		$pr = $conf[ 'cup' ];
		foreach ( $pr as $key => $val ) {
			tpl::load2 ( 'admin-settings-cup' );
			tpl::set ( '{key}' , $val[ 'code' ] );
			tpl::set ( '{val}' , $val[ 'sum' ] );
			tpl::set ( '{val2}' , $val[ 'min' ] );
			if ( $val[ 'type' ] == 1 ) {
				tpl::set ( '{select1}' , 'selected' );
				tpl::set ( '{select2}' , '' );
			} else {
				tpl::set ( '{select2}' , 'selected' );
				tpl::set ( '{select1}' , '' );
			}
			tpl::compile ( 'data12' );
		}
		tpl::set ( '{cup}' , tpl::$result[ 'data12' ] );
		tpl::$result[ 'data' ] = "";


		tpl::set ( '{license}' , tpl::$result[ 'data' ] );
		tpl::$result[ 'data' ] = "";

		foreach ( self::$sms as $key => $value ) {
			tpl::load2 ( 'admin-settings-sms' );
			tpl::set ( '{key}' , $key );
			tpl::set ( '{name}' , l::t($value) );
			if ( $conf[ 'sms_' . $key ] == 1 ) {
				tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "\\1" );
				tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
				tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "\\1" );
			}

			tpl::compile ( 'data' );
		}
		$index = "";
		foreach ( self::$index as $key => $value ) {
			if ( $conf[ 'index' ] == $key ) {
				$index .= '<option value="' . $key . '" selected>' . l::t($value) . '</option>';
			} else {
				$index .= '<option value="' . $key . '">' . l::t($value) . '</option>';
			}
		}
		tpl::set ( '{index}' , $index );
		$sql = db::q ( 'SELECT id,name FROM pages order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			if ( $conf[ 'index-page' ] == $row[ 'id' ] ) {
				$pages .= '<option value="' . $row[ 'id' ] . '" selected>' . $row[ 'name' ] . '</option>';
			} else {
				$pages .= '<option value="' . $row[ 'id' ] . '">' . $row[ 'name' ] . '</option>';
			}
		}
		tpl::set ( '{pages}' , $pages );
		$mail = "";
		foreach ( self::$mail as $key => $value ) {
			if ( $conf[ 'mail_type' ] == $key ) {
				$mail .= '<option value="' . $key . '" selected>' . $value . '</option>';
			} else {
				$mail .= '<option value="' . $key . '">' . $value . '</option>';
			}
		}
		tpl::set ( '{mail-type}' , $mail );
		if ( $conf[ 'signup' ] == 0 ) {
			$signup = '<option value="0" selected>'.l::t('Выключено').'</option>';
			$signup .= '<option value="1" >'.l::t('Включено').'</option>';
		} else {
			$signup = '<option value="0" >'.l::t('Выключено').'</option>';
			$signup .= '<option value="1" selected>'.l::t('Включено').'</option>';
		}

		tpl::set ( '{signup}' , $signup );
		if ( $conf[ 'sphone' ] == 0 ) {
			$signup = '<option value="0" selected>'.l::t('Выключено').'</option>';
			$signup .= '<option value="1" >'.l::t('Включено').'</option>';
		} else {
			$signup = '<option value="0" >'.l::t('Выключено').'</option>';
			$signup .= '<option value="1" selected>'.l::t('Включено').'</option>';
		}

		tpl::set ( '{sphone}' , $signup );
		if ( $conf[ 'tpl' ] == 0 ) {
			$signup = '<option value="0" selected>'.l::t('Первый').'</option>';
			$signup .= '<option value="1" >'.l::t('Второй').'</option>';
			$signup .= '<option value="2" >'.l::t('Третий').'</option>';
		} else {
			if ( $conf[ 'tpl' ] == 1 ) {
				$signup = '<option value="0">'.l::t('Первый').'</option>';
				$signup .= '<option value="1" selected>'.l::t('Второй').'</option>';
				$signup .= '<option value="2" >'.l::t('Третий').'</option>';
			}else{
				$signup = '<option value="0">'.l::t('Первый').'</option>';
				$signup .= '<option value="1">'.l::t('Второй').'</option>';
				$signup .= '<option value="2" selected>'.l::t('Третий').'</option>';
			}
		}

		tpl::set ( '{tpl}' , $signup );
		if ( $conf[ 'tpl2' ] == 0 ) {
			$signup = '<option value="0" selected>'.l::t('Темный').'</option>';
			$signup .= '<option value="1" >'.l::t('Светлый').'</option>';
		} else {
			$signup = '<option value="0" >'.l::t('Темный').'</option>';
			$signup .= '<option value="1" selected>'.l::t('Светлый').'</option>';
		}

		tpl::set ( '{tpl2}' , $signup );

		if ( $conf[ 'tpl3' ] == 0 ) {
			$signup = '<option value="0" selected>Fluid</option>';
			$signup .= '<option value="1" >Boxes</option>';
		} else {
			$signup = '<option value="0" >Fluid</option>';
			$signup .= '<option value="1" selected>Boxes</option>';
		}

		tpl::set ( '{tpl3}' , $signup );
		if ( $conf[ 'tpl4' ] == 0 ) {
			$signup = '<option value="0" selected>'.l::t('Закругленный').'</option>';
			$signup .= '<option value="1" >'.l::t('Квадратный').'</option>';
		} else {
			$signup = '<option value="0" >'.l::t('Закругленный').'</option>';
			$signup .= '<option value="1" selected>'.l::t('Квадратный').'</option>';
		}

		tpl::set ( '{tpl4}' , $signup );

		if ( $conf[ 'fprice' ] ) {
			$signup = '<option value="1" selected>'.l::t('фиксировано 30,60 дней и т.д, согласно таблице ниже').'</option>';
			$signup .= '<option value="0" >'.l::t('от N дней заданного в настройках, и согласно таблице ниже').'</option>';
		} else {
			$signup = '<option value="1" >'.l::t('фиксировано 30,60 дней и т.д, согласно таблице ниже').'</option>';
			$signup .= '<option value="0" selected>'.l::t('от N дней заданного в настройках, и согласно таблице ниже').'</option>';
		}

		tpl::set ( '{fprice}' , $signup );
		tpl::set ( '{sms}' , tpl::$result[ 'data' ] );
		tpl::$result[ 'data' ] = '';
		$pr = $conf[ 'price' ];
		foreach ( $pr as $key => $val ) {
			tpl::load2 ( 'admin-settings-price' );
			tpl::set ( '{key}' , $val['day'] );
			tpl::set ( '{val}' , $val['price1'] );
			tpl::set ( '{val2}' , $val['price2'] );
			tpl::compile ( 'data' );
		}
		tpl::set ( '{price}' , tpl::$result[ 'data' ] );
		$f = scandir(ROOT.'/engine/lang/');
		$signup = '';
		foreach ($f as $file){
			if(preg_match('/\.(json)/', $file)){
				$v = str_replace('.json','',$file);
				if ( $file2 = file_get_contents ( ROOT . '/engine/lang/'.$v.'.json' ) ) {
					if ( $array = json_decode ( $file2 , true ) ) {
						if($v==$conf['lang']){
							$signup .= '<option value="'.$v.'" selected>'.$array[$v].'</option>';
						}else{
							$signup .= '<option value="'.$v.'">'.$array[$v].'</option>';
						}
					}
				}
			}
		}
		tpl::set ( '{langs}' , $signup );
		if ( $conf[ 'lang2' ] == 0 ) {
			$signup = '<option value="0" selected>'.l::t('Выключено').'</option>';
			$signup .= '<option value="1" >'.l::t('Включено').'</option>';
		} else {
			$signup = '<option value="0" >'.l::t('Выключено').'</option>';
			$signup .= '<option value="1" selected>'.l::t('Включено').'</option>';
		}

		tpl::set ( '{langs2}' , $signup );
		tpl::compile ( 'content' );
	}
}

?>