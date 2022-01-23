<?php

	class admin_isp_rates
	{
		public static $rules = array (
			'shell'  => 'Доступ к shell' ,
			'ssl'    => 'SSL' ,
			'cgi'    => 'CGI' ,
			'phpmod' => 'PHP как модуль Apache' ,
			'phpcgi' => 'PHP как CGI'
		);

		public static $rules_2 = array (
			'disklimit'       => 'Диск' ,
			'ftplimit'        => 'FTP аккаунты' ,
			'maillimit'       => 'Почтовые ящики' ,
			'domainlimit'     => 'Домены' ,
			'webdomainlimit'  => 'WWW домены' ,
			'maildomainlimit' => 'Почтовые домены' ,
			'baselimit'       => 'Базы данных' ,
			'baseuserlimit'   => 'Пользователи баз данных' ,
		);
		public static function on_off ( $id ){
			global $title,$conf;
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( "SELECT * FROM isp_rates where id='" . $id . "'" );
			if ( db::n () == "1" ) {
				$rate = db::r ();
				if($rate['power']){
					$power = 0;
				}else{
					$power = 1;
				}
				db::q('update isp_rates set power="' . $power . '" where id="' . $id . '"');
				if($rate['power']){
					api::result (  l::t ('Тариф выключен'),1 );
				}else{
					api::result (  l::t ('Тариф включен'),1 );
				}
			} else {
				api::result (  l::t ('Тариф не найден') );
			}
		}
		public static function del ( $id )
		{
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			db::q ( "SELECT * FROM isp_rates where id='" . $id . "'" );
			if ( db::n () == "1" ) {
				db::q ( "SELECT * FROM isp where rate='" . $id . "'" );
				if ( db::n () != "0" ) {
					api::result (  l::t ('Для начала удалите все оказанные услуги') );
				} else {
					db::q ( 'DELETE from isp_rates where id="' . $id . '"' );
					api::result (  l::t ('Удалено') , true );
				}
			} else {
				api::result (  l::t ('Тариф не найден') );
			}
		}

		public static function listen ()
		{
			global $title;
			api::nav ( '' ,  l::t ('Тарифы Web хостинга') , '1' );
			$sql = db::q ( 'SELECT * FROM isp_rates order by id desc' );
			while ( $row = db::r ( $sql ) ) {
				tpl::load2 ( 'admin-isp-rates-listen-get' );
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
			$title =  l::t ("Тарифы Web хостинга");
			tpl::load2 ( 'admin-isp-rates-listen' );
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::$result[ 'data' ] = '';
			tpl::compile ( 'content' );
		}

		public static function add ()
		{
			global $title;
			api::nav ( '/admin/isp-rates' ,  l::t ('Тарифы Web хостинга') );
			api::nav ( '' , l::t ('Новый') , 1 );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				db::q ( 'SELECT * FROM gh_location where id="' . (int) $data[ 'loc' ] . '"' );
				if ( db::n () == 1 ) {
					if ( $data[ 'price' ] < 0 or $data[ 'price' ] > 10000 ) {
						api::result (  l::t ('Цена должна быть от 0 до 10000') );
					} else {
						$gcfg = array ();
						$version = (int) $_POST[ 'data' ][ 'version' ];
						foreach ( self::$rules as $key => $value ) {
							if ( $data[ $key ] == '1' ) {
								$gcfg[ $key ] = "on";
							} else {
								$gcfg[ $key ] = "off";
							}
						}
							foreach ( self::$rules_2 as $key => $value ) {
								if ( $data[ $key ] ) {
									$gcfg[ $key ] = (int) $data[ $key ];
								}
							}


							if ( $gcfg[ 'disklimit' ] < 10 ) {
								api::result (  l::t ('Значение диск должно быть от 10 мб') );
							} else {
								$gcfg = base64_encode ( json_encode ( $gcfg ) );
								db::q ( "INSERT INTO isp_rates set free='" . (int) $data[ 'free' ] . "',price='" . api::price($data[ 'price' ]). "',name='" . api::cl ( $data[ 'name' ] ) . "',loc='" . (int) $data[ 'loc' ] . "',cfg='" . $gcfg . "'" );
								api::result (  l::t ('Создано') , 1 );
							}
						}
				} else {
					api::result (  l::t ('Локация не найдена') );
				}
			}
			$title =  l::t ("Новый тариф Web хостинга");
			$rules4 = "";

			foreach ( self::$rules as $key => $value ) {
				$rules4 .= '<div class="form-group">';
				$rules4 .= '<label class="col-sm-4 control-label">' . l::t ($value) . '</label>';
				$rules4 .= '<div class="col-sm-4"><select name="data[' . $key . ']" class="form-control">';
				$rules4 .= '<option value="0">'. l::t ('Отключено').'</option>';
				$rules4 .= '<option value="1">'. l::t ('Включено').'</option>';
				$rules4 .= '</select></div>';
				$rules4 .= '</div>';
			}
			foreach ( self::$rules_2 as $key => $value ) {
				$rules4 .= '<div class="form-group">';
				$rules4 .= '<label class="col-sm-4 control-label">' . l::t ($value) . '</label>';
				$rules4 .= '<div class="col-sm-4"><input type="text" name="data[' . $key . ']" class="form-control"></div>';
				$rules4 .= '</div>';
			}
			$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
			$loc= '';
			while ( $row2 = db::r ( $sql ) ) {
				$loc .= '<option value="' . $row2[ 'id' ] . '">' . $row2[ 'name' ] . '</option>';
			}
			$free = '<option value="0">'. l::t ('Нет').'</option>';
			$free .= '<option value="1">'. l::t ('Да').'</option>';

			tpl::load2 ( 'admin-isp-rates-add' );
			tpl::set ( '{free}' , $free );
			tpl::set ( '{rules}' , $rules4 );
			tpl::set ( '{loc}' , $loc );
			tpl::compile ( 'content' );
		}

		public static function edit ( $id )
		{
			global $title;
			$sql = db::q ( 'SELECT * FROM isp_rates where id="' . $id . '"' );
			if ( db::n ( $sql ) != 0 ) {
				$rate = db::r ();
				$rcfg = json_decode ( base64_decode ( $rate[ 'cfg' ] ) , true );
				api::nav ( '/admin/isp-rates' , l::t ('Тарифы Web хостинга') );
				api::nav ( '' , l::t ('Редактирование') , 1 );
				$data = $_POST[ 'data' ];
				if ( $data ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
						if ( $data[ 'price' ] < 0 or $data[ 'price' ] > 10000 ) {
							api::result ( l::t ('Цена должна быть от 0 до 10000') );
						} else {
							$gcfg = array ();
							$version = (int) $_POST[ 'data' ][ 'version' ];
							foreach ( self::$rules as $key => $value ) {
								if ( $data[ $key ] == '1' ) {
									$gcfg[ $key ] = "on";
								} else {
									$gcfg[ $key ] = "off";
								}
							}
							foreach ( self::$rules_2 as $key => $value ) {
								if ( $data[ $key ] ) {
									$gcfg[ $key ] = (int) $data[ $key ];
								}
							}

							if ( $gcfg[ 'disklimit' ] < 10 ) {
								api::result ( l::t ('Значение диск должно быть от 10 мб') );
							} else {
								$gcfg2 = base64_encode ( json_encode ( $gcfg ) );
								$sql3 = db::q ( 'SELECT * FROM isp_boxes where loc="' . $rate[ 'loc' ] . '"' );
								api::inc ( 'isp-api' );
								while ( $row3 = db::r ( $sql3 ) ) {
									if ( isp_api::connect ( $row3[ 'ip' ] , $row3[ 'pass' ] ) ) {
										$sql4 = db::q ( 'SELECT * FROM isp where boxes="' . $row3[ 'id' ] . '" and rate="' . $id . '"' );
										while ( $row4 = db::r ( $sql4 ) ) {
											$go = $gcfg;
											$go[ 'passwd' ] = $row4[ 'pass' ];
											$go[ 'confirm' ] = $row4[ 'pass' ];
											$go[ 'name' ] = "u" . $row4[ 'sid' ];
											$go[ 'elid' ] = "u" . $row4[ 'sid' ];
											isp_api::install ( $go,$row3['version']);
										}
									}
								}
								db::q ( "UPDATE isp_rates set free='" . (int) $data[ 'free' ] . "',price='" . api::price($data[ 'price' ]). "',name='" . api::cl ( $data[ 'name' ] ) . "',cfg='" . $gcfg2 . "' where id='" . $id . "'" );
								api::result ( l::t ('Сохранено' ), 1 );
							}
					}
				}
				$title = l::t ("Редактирование тарифа Web хостинга");
				$rules = "";
				foreach ( self::$rules as $key => $value ) {
					$rules .= '<div class="form-group">';
					$rules .= '<label class="col-sm-4 control-label">' . l::t ($value) . '</label>';
					$rules .= '<div class="col-sm-4"><select name="data[' . $key . ']" class="form-control">';
					if ( $rcfg[ $key ] == 'on' ) {
						$rules .= '<option value="0">'.l::t ('Отключено').'</option>';
						$rules .= '<option value="1" selected>'.l::t ('Включено').'</option>';
					} else {
						$rules .= '<option value="0" selected>'.l::t ('Отключено').'</option>';
						$rules .= '<option value="1">'.l::t ('Включено').'</option>';
					}
					$rules .= '</select></div>';
					$rules .= '</div>';
				}
				foreach ( self::$rules_2 as $key => $value ) {
					$rules .= '<div class="form-group">';
					$rules .= '<label class="col-sm-4 control-label">' . l::t ($value) . '</label>';
					$rules .= '<div class="col-sm-4"><input type="text" name="data[' . $key . ']" value="' . $rcfg[ $key ] . '" class="form-control"></div>';
					$rules .= '</div>';
				}

				tpl::load2 ( 'admin-isp-rates-edit' );
				if ( $rate[ 'free' ] == 0 ) {
					$free = '<option value="0" selected>'.l::t ('Нет').'</option>';
					$free .= '<option value="1">'.l::t ('Да').'</option>';
				} else {
					$free = '<option value="0">'.l::t ('Нет').'</option>';
					$free .= '<option value="1" selected>'.l::t ('Да').'</option>';
				}
				tpl::set ( '{free}' , $free );
				tpl::set ( '{rules}' , $rules );
				tpl::set ( '{name}' , $rate[ 'name' ] );
				tpl::set ( '{price}' , $rate[ 'price' ] );
				tpl::set ( '{id}' , $rate[ 'id' ] );
				tpl::compile ( 'content' );
			} else {
				api::result ( l::t ('Тариф не найден') );
			}
		}
	}
?>