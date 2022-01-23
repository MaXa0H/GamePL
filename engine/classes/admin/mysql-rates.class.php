<?php

	class admin_mysql_rates
	{
		public static function on_off ( $id ){
			global $title,$conf;
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( "SELECT * FROM mysql_rates where id='" . $id . "'" );
			if ( db::n () == "1" ) {
				$rate = db::r ();
				if($rate['power']){
					$power = 0;
				}else{
					$power = 1;
				}
				db::q('update mysql_rates set power="' . $power . '" where id="' . $id . '"');
				if($rate['power']){
					api::result ( l::t('Тариф выключен'),1 );
				}else{
					api::result ( l::t('Тариф включен'),1 );
				}
			} else {
				api::result (l::t( 'Тариф не найден') );
			}
		}
		public static function del ( $id )
		{
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( "SELECT * FROM mysql_rates where id='" . $id . "'" );
			if ( db::n () == "1" ) {
				db::q ( "SELECT * FROM mysql where rate='" . $id . "'" );
				if ( db::n () != "0" ) {
					api::result ( l::t('Для начала удалите все оказанные услуги') );
				} else {
					db::q ( 'DELETE from mysql_rates where id="' . $id . '"' );
					api::result ( l::t('Удалено' ), true );
				}
			} else {
				api::result ( l::t('Тариф не найден') );
			}
		}

		public static function listen ()
		{
			global $title;
			api::nav ( '' , l::t('Тарифы MySQL') , '1' );
			$sql = db::q ( 'SELECT * FROM mysql_rates order by id desc' );
			while ( $row = db::r ( $sql ) ) {
				tpl::load2 ( 'admin-mysql-rates-listen-get' );
				tpl::set ( '{id}' , $row[ 'id' ] );
				tpl::set ( '{name}' , $row[ 'name' ] );
				$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row[ 'loc' ] . '"' );
				$row2 = db::r ( $sql2 );
				tpl::set ( '{loc}' , $row2[ 'name' ] );
				if($row['power']){
					tpl::set('{color}','blue');
					tpl::set('{icon}','fa fa-check-circle-o');
					tpl::set('{status}','1');
				}else{
					tpl::set('{icon}','fa fa-circle-o');
					tpl::set('{color}','');
					tpl::set('{status}','0');
				}
				tpl::compile ( 'data' );
			};
			$title = l::t("Тарифы MySQL");
			tpl::load2 ( 'admin-mysql-rates-listen' );
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::$result[ 'data' ] = '';
			tpl::compile ( 'content' );
		}

		public static function add ()
		{
			global $title;
			api::nav ( '/admin/mysql/rates' ,l::t( 'Тарифы MySQL') );
			api::nav ( '' , l::t('Новый') , 1 );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				db::q ( 'SELECT * FROM gh_location where id="' . (int) $data[ 'loc' ] . '"' );
				if ( db::n () == 1 ) {
					if ( $data[ 'price' ] < 0 or $data[ 'price' ] > 10000 ) {
						api::result ( l::t('Цена должна быть от 0 до 10000') );
					} else {
						db::q ( "INSERT INTO mysql_rates set
									price='" . api::price($data[ 'price' ]). "',
									name='" . api::cl ( $data[ 'name' ] ) . "',
									loc='" . (int) $data[ 'loc' ] . "',
									mqph='" . (int) $data[ 'mqph' ] . "',
									muph='" . (int) $data[ 'muph' ] . "',
									mcph='" . (int) $data[ 'mcph' ] . "',
									muc='" . (int) $data[ 'muc' ] . "'
						" );
						api::result ( l::t('Создано' ), 1 );
					}
				} else {
					api::result ( l::t('Локация не найдена') );
				}
			}
			$title = l::t("Новый тариф MySQL");
			$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
			$loc = '';
			while ( $row2 = db::r ( $sql ) ) {
				$loc .= '<option value="' . $row2[ 'id' ] . '">' . $row2[ 'name' ] . '</option>';
			}
			tpl::load2 ( 'admin-mysql-rates-add' );
			tpl::set ( '{loc}' , $loc );
			tpl::compile ( 'content' );
		}

		public static function edit ( $id )
		{
			global $title;
			$sql = db::q ( 'SELECT * FROM mysql_rates where id="' . $id . '"' );
			if ( db::n ( $sql ) != 0 ) {
				$rate = db::r ();
				$rcfg = json_decode ( base64_decode ( $rate[ 'cfg' ] ) , true );
				api::nav ( '/admin/mysql-rates' , l::t('Тарифы MySQL') );
				api::nav ( '' , l::t('Редактирование') , 1 );
				$data = $_POST[ 'data' ];
				if ( $data ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					if ( $data[ 'price' ] < 0 or $data[ 'price' ] > 10000 ) {
						api::result ( l::t('Цена должна быть от 0 до 10000') );
					} else {
						db::q ( "UPDATE mysql_rates set price='" . api::price($data[ 'price' ]). "',
									name='" . api::cl ( $data[ 'name' ] ) . "',
									mqph='" . (int) $data[ 'mqph' ] . "',
									muph='" . (int) $data[ 'muph' ] . "',
									mcph='" . (int) $data[ 'mcph' ] . "',
									muc='" . (int) $data[ 'muc' ] . "'
									where id='".$id."'" );
						api::result ( l::t('Сохранено') , 1 );
					}
				}
				$title = l::t("Редактирование тарифа MySQL");
				tpl::load2 ( 'admin-mysql-rates-edit' );
				tpl::set ( '{name}' , $rate[ 'name' ] );
				tpl::set ( '{price}' , $rate[ 'price' ] );
				tpl::set ( '{mqph}' , $rate[ 'mqph' ] );
				tpl::set ( '{muph}' , $rate[ 'muph' ] );
				tpl::set ( '{mcph}' , $rate[ 'mcph' ] );
				tpl::set ( '{muc}' , $rate[ 'muc' ] );
				tpl::set ( '{id}' , $rate[ 'id' ] );
				tpl::compile ( 'content' );
			} else {
				api::result ( l::t('Тариф не найден') );
			}
		}
	}
?>