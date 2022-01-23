<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class admin_templates
{
	public static $adm = 0;
	public static function load ()
	{
		global $conf;
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		if($conf['tpl']==1){
			$tpl = 'tpl2';
		}elseif($conf['tpl']==2){
			$tpl = 'tpl3';
		}else{
			$tpl = 'tpl';
		}
		if(self::$adm){
			$tpl = "admin_tpl";
		}
		if ( $_POST[ 'data' ][ 'name' ] ) {
			$sql = db::q ( 'SELECT * FROM '.$tpl.' where id="' . (int) $_POST[ 'data' ][ 'name' ] . '"' );
			if ( db::n () == 0 ) {
				api::result (  l::t ('Шаблон не найден') );
			} else {
				$row = db::r ();
				die( '{"d":"' . $row[ 'tpl' ] . '","i":"' . (int) $_POST[ 'data' ][ 'name' ] . '"}' );
			}
		} elseif ( $_POST[ 'data' ][ 'tpl' ] ) {
			$sql = db::q ( 'SELECT * FROM '.$tpl.' where id="' . (int) $_POST[ 'data' ][ 'tpl' ] . '"' );
			if ( db::n () == 0 ) {
				api::result (  l::t ('Шаблон не найден') );
			} else {
				$row = db::r ();
				self::save ( $row[ 'name' ] );
			}
		} elseif ( $_POST[ 'data' ][ 'tpl_new' ] ) {
			if ( api::info ( 'ugroup' ) == "1" ) {
				if ( ! preg_match ( "/^[0-9a-z-_]{2,40}$/i" , api::cl ( $_POST[ 'data' ][ 'tpl_name' ] ) ) ) {
					api::result (  l::t ('Название шаблона указано неверно') );
				} else {
					db::q ( 'SELECT * FROM '.$tpl.' where name="' . api::cl ( $_POST[ 'data' ][ 'tpl_name' ] ) . '"' );
					if ( db::n () != 0 ) {
						api::result (  l::t ('Шаблон уже существует') );
					} else {
						db::q ( "INSERT INTO ".$tpl." set name='" . api::cl ( $_POST[ 'data' ][ 'tpl_name' ] ) . "'" );
						api::result (  l::t ('Шаблон создан') , true );
					}
				}
			}
		}
		$sql = db::q ( 'SELECT * FROM '.$tpl.' order by name asc' );
		$tpl = '';
		while ( $row = db::r ( $sql ) ) {
			$tpl .= '<option value="' . $row[ 'id' ] . '">' . $row[ 'name' ] . '</option>';
		}
		tpl::load2 ( 'admin-templates' );
		tpl::set ( '{tpl}' , $tpl );
		tpl::compile ( 'content' );
		api::nav ( '' ,  l::t ('Редактор шаблонов') , '1' );
	}

	public static function save ( $name )
	{
		global $conf;
		if($conf['tpl']==1){
			$tpl2 = 'tpl2';
		}elseif($conf['tpl']==2){
			$tpl2 = 'tpl3';
		}else{
			$tpl2 = 'tpl';
		}
		if(self::$adm){
			$tpl2 = "admin_tpl";
		}
		$id = (int) $_POST[ 'data' ][ 'tpl' ];
		$tpl = $_POST[ 'data' ][ 'data' ];
		db::q ( 'update '.$tpl2.' set tpl="' . base64_encode ( $tpl ) . '" where id="' . $id . '"' );
		if ($handle = opendir(ROOT.'/engine/lang/')) {
			while (false !== ($file = readdir($handle))) {
				if (!preg_match ( "/[^a-zA-Z0-9].json/i" , $file ) ) {
					if(self::$adm) {
						m::d ( str_replace ( '.json' , '' , $file ) . '_tpla_' . $name );
					}else{
						m::d ( str_replace ( '.json' , '' , $file ) . '_tpl_' . $name );
					}
				}
			}
			closedir($handle);
		}
		api::result (  l::t ('Шаблон сохранен') , true );
	}
}

?>