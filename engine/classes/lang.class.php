<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class l{
	public static $lang = "ru";
	public static $array = array();
	public static function t($name){
		if(self::$array[$name]){
			return self::$array[$name];
		}else{
			return $name;
		}
	}
	public static function run(){
		global $conf;
		if(!$conf['lang']){
			$conf['lang'] = 'ru';
		}
		if($conf['lang2']) {
			if ( $_COOKIE[ 'lang' ] ) {
				if(preg_match ("/[^a-z,A-Z0-9_]/", api::cl ( $_COOKIE[ 'lang' ] ))){
					self::$lang = $conf['lang'];
				}else{
					self::$lang = api::cl ( $_COOKIE[ 'lang' ] );
				}
			}
		}else{
			self::$lang = $conf['lang'];
		}
		if ( $file = file_get_contents ( ROOT . '/engine/lang/'.self::$lang.'.json' ) ) {
			if ( ! self::$array = json_decode ( $file , true ) ) {
				echo 'Не удалось открыть языковой пакет';
				exit;
			}
		} else {
			echo 'Не удалось открыть языковой пакет';
			exit;
		}
	}
	public static function g($name){
		global $conf;
		if($conf['lang2']){
			if(preg_match ("/[^a-z,A-Z0-9_]/", $name)){
				return false;
			}else{
				$lang =  m::g ( 'lang_' .$name);
				if ( empty( $lang ) ) {
					if ( $file = file_get_contents ( ROOT . '/engine/lang/'.$name.'.json' ) ) {
						if ( ! self::$array = json_decode ( $file , true ) ) {
							return false;
						}
					} else {
						return false;
					}
					m::s ( 'lang_' .$name, self::$array , 3600 );
					api::set_cookie ( "lang" , $name , 30 );
					return true;
				}else{
					self::$array = $lang;
					api::set_cookie ( "lang" , $name , 30 );
					return true;
				}
			}
		}
	}
	public static function js(){
		$lang = array();
		$lang['Повторите запрос через пару секунд'] = l::t('Повторите запрос через пару секунд');
		$lang['У вас новое сообщение в центре поддержки']=l::t('У вас новое сообщение в центре поддержки');
		$lang['Пополнение счета']=l::t('Пополнение счета');
		$lang['На Ваши номера телефонов были отправлены СМС с кодами подтверждения, введите их в поля ниже.']=l::t('На Ваши номера телефонов были отправлены СМС с кодами подтверждения, введите их в поля ниже.');
		$lang['Код №1']=l::t('Код №1');
		$lang['од №2']=l::t('Код №2');
		$lang['На Ваши электронные адресы были отправлены письма с кодами подтверждения, введите их в поля ниже.']=l::t('На Ваши электронные адресы были отправлены письма с кодами подтверждения, введите их в поля ниже.');
		$lang['Удалить']=l::t('Удалить');
		$lang['Установить']=l::t('Установить');

		echo json_encode($lang);
	}
}
?>