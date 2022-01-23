<?php

	api::inc ( 'servers' );
	api::inc ( 'servers/rise' );

	class admin_rise
	{
		public static function money ()
		{
			global $title;
			api::nav ( '/admin/rise' , l::t('Раскрутки') );
			api::nav ( '' , l::t('История покупок раскрутки') , '1' );
			$title = l::t("История покупок раскрутки");
			db::q ( 'SELECT id FROM logs_boost order by id desc' );
			$all = db::n ();
			$num = 20;
			$pages = (int) r::g ( 3 );
			if ( $pages ) {
				if ( ( $all / $num ) > $pages ) {
					$page = $num * $pages;
				} else {
					$page = 0;
				}
			} else {
				$page = 0;
			}
			$sql = db::q ( 'SELECT * FROM logs_boost order by id desc LIMIT ' . $page . ' ,' . $num );
			$data = "";
			while ( $row = db::r ( $sql ) ) {
				tpl::load2 ( 'admin-money-listen-get' );
				tpl::set ( '{id}' , $row[ 'id' ] );
				tpl::set ( '{com}' , $row[ 'mes' ] );
				tpl::set ( '{sum}' , $row[ 'sum' ] );
				tpl::set ( '{time}' , api::langdate ( "d.m.Y - H:i" , $row[ 'time' ] ) );
				tpl::set_block ( "'\\[act-1\\](.*?)\\[/act-1\\]'si" , "" );
				tpl::set_block ( "'\\[act-0\\](.*?)\\[/act-0\\]'si" , "" );
				tpl::compile ( 'data' );
			}
			tpl::load2 ( 'admin-money-listen' );
			$key = m::g ( "money_chart_boost" );
			if ( empty( $key ) ) {
				$key = self::money_chart ( );
				if ( ! empty( $key ) ) {
					tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "\\1" );
					tpl::set ( '{chart}' , '[' . $key . ']' );
				} else {
					tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "\\1" );
				tpl::set ( '{chart}' , '[' . $key . ']' );
			}
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::set ( '{nav}' , api::pagination ( $all , $num , $pages , '/admin/rise/logs' ) );
			tpl::compile ( 'content' );
		}

		public static function gettime ( $t )
		{
			$d = date ( "d" , $t );
			$m = date ( "m" , $t );
			$Y = date ( "Y" , $t );
			$time = mktime ( 0 , 0 , 0 , $m , $d , $Y );

			return $time . "000";
		}

		public static function money_chart ()
		{
			$key = m::g ( 'money_chart_boost' );
			if ( empty( $key ) ) {
				$sql = db::q ( 'SELECT * FROM logs_boost order by id asc' );
				$data = '';
				while ( $row = db::r ( $sql ) ) {
					$data[ self::gettime ( $row[ 'time' ] ) ] += $row[ 'sum' ];
				}
				$g = "1";
				$echo = '';
				foreach ( $data as $go => $val ) {
					if ( $g == "1" ) {
						$echo .= "[" . $go . "," . $val . "]";
					} else {
						$echo .= ",[" . $go . "," . $val . "]";
					}
					$g = $g + 1;
				}
				m::s ( 'money_chart_boost' , $echo , 180 );

				return $echo;
			} else {
				return $key;
			}
		}

		public static function listen ()
		{
			global $title;
			api::nav ( '' , l::t('Раскрутки') , '1' );
			$title = l::t("Раскрутки");
			$sql = db::q ( 'SELECT * FROM gh_rise order by id asc' );
			$data = "";
			while ( $row = db::r ( $sql ) ) {
				tpl::load2 ( 'admin-rise-listen-get' );
				tpl::set ( '{id}' , $row[ 'id' ] );
				tpl::set ( '{name}' , $row[ 'name' ] );
				tpl::set ( '{price}' , $row[ 'price' ] );
				tpl::set ( '{game}' , servers::$games[ $row[ 'game' ] ] );
				tpl::compile ( 'data' );
			}
			tpl::load2 ( 'admin-rise-listen' );
			tpl::set ( '{data}' , tpl::result ( 'data' ) );
			tpl::compile ( 'content' );
		}

		public static function add ()
		{
			global $title;
			api::nav ( '/admin/rise' , l::t('Раскрутки') );
			api::nav ( '' , l::t('Создание') , '1' );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
					if ( $data[ 'price' ] < 1 || $data[ 'price' ] > 10000 ) {
						api::result ( l::t('Стоимость должна быть от 1 до 10000' ));
					} else {
						if ( ! in_array ( $data[ 'type' ] , array ( 1 , 2 ) ) ) {
							api::result ( l::t('Неправильный формат') );
						} else {
							if ( ! servers::$games[ $data[ 'game' ] ] ) {
								api::result (l::t( 'Игра не найдена' ));
							} else {
								$d1 = $_POST[ 'data_1' ];
								$d2 = $_POST[ 'data_2' ];
								$d3 = $_POST[ 'data_3' ];
								$d4 = $_POST[ 'data_4' ];
								$d5 = $_POST[ 'data_5' ];
								$gogo = array ();
								$go = array ();
								foreach ( $d1 as $key => $val ) {
									$go[ api::cl ( $val ) ] = api::cl ( $d2[ $key ] );
								}
								$gogo[ 'options' ] = $go;
								$go = array ();
								foreach ( $d3 as $key => $val ) {
									$go2 = array ();
									$go2[ 'type' ] = api::cl ( $d5[ $key ] );
									$go2[ 'header' ] = api::cl ( $d3[ $key ] );
									$go2[ 'text' ] = api::cl ( $d4[ $key ] );
									$go[ ] = $go2;
								}
								$gogo[ 'options2' ] = $go;
								$gogo[ 'type' ] = $data[ 'type' ];
								db::q ( "INSERT INTO gh_rise set game='" . api::cl ( $data[ 'game' ] ) . "',domain='" . api::cl ( $data[ 'domain' ] ) . "',name='" . api::cl ( $data[ 'name' ] ) . "',price='" . (int) $data[ 'price' ] . "',options='" . base64_encode ( json_encode ( $gogo ) ) . "'" );
								api::result ( l::t('Добавлено') , true );
							}
						}
					}
			}
			$games = '';
			foreach ( servers::$games as $key => $value ) {
				$games .= '<option value="' . $key . '">' . $value . '</option>';
			}
			$title = l::t ("Новый тариф");
			tpl::load2 ( 'admin-rise-add' );
			tpl::set ( '{games}' , $games );
			tpl::compile ( 'content' );
		}

		public static function edit ( $id )
		{
			global $title;
			db::q ( 'SELECT * FROM gh_rise where id="' . $id . '"' );
			if ( db::n () != "1" ) {
				api::result ( l::t('Не найдено' ));
			} else {
				$rise = db::r ();
				$gdata = json_decode ( base64_decode ( $rise[ 'options' ] ) , true );
				api::nav ( '/admin/rise' , l::t('Раскрутки') );
				api::nav ( '' ,l::t( 'Редактирование') , '1' );
				$data = $_POST[ 'data' ];
				if ( $data ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					if ( mb_strlen ( $data[ 'name' ] , "utf-8" ) < 5 || mb_strlen ( $data[ 'name' ] , "utf-8" ) > 40 ) {
						api::result ( l::t('Название должно быть от 5 до 40 символов') );
					} else {
						if ( $data[ 'price' ] < 1 || $data[ 'price' ] > 10000 ) {
							api::result ( l::t('Стоимость должна быть от 1 до 10000') );
						} else {
							if ( ! in_array ( $data[ 'type' ] , array ( 1 , 2 ) ) ) {
								api::result (l::t( 'Неправильный формат' ));
							} else {
								if ( ! servers::$games[ $data[ 'game' ] ] ) {
									api::result ( l::t('Игра не найдена') );
								} else {
									$d1 = $_POST[ 'data_1' ];
									$d2 = $_POST[ 'data_2' ];
									$d3 = $_POST[ 'data_3' ];
									$d4 = $_POST[ 'data_4' ];
									$d5 = $_POST[ 'data_5' ];
									$gogo = array ();
									$go = array ();
									foreach ( $d1 as $key => $val ) {
										$go[ api::cl ( $val ) ] = api::cl ( $d2[ $key ] );
									}
									$gogo[ 'options' ] = $go;
									$go = array ();
									foreach ( $d3 as $key => $val ) {
										$go2 = array ();
										$go2[ 'type' ] = api::cl ( $d5[ $key ] );
										$go2[ 'header' ] = api::cl ( $d3[ $key ] );
										$go2[ 'text' ] = api::cl ( $d4[ $key ] );
										$go[ ] = $go2;
									}
									$gogo[ 'options2' ] = $go;
									$gogo[ 'type' ] = $data[ 'type' ];
									db::q ( "UPDATE gh_rise set game='" . api::cl ( $data[ 'game' ] ) . "',domain='" . api::cl ( $data[ 'domain' ] ) . "',name='" . api::cl ( $data[ 'name' ] ) . "',price='" . (int) $data[ 'price' ] . "',options='" . base64_encode ( json_encode ( $gogo ) ) . "' where id='" . $id . "'" );
									api::result ( l::t('Сохранено') , true );
								}
							}
						}
					}
				}
				$games = '';
				foreach ( servers::$games as $key => $value ) {
					if ( $rise[ 'game' ] == $key ) {
						$games .= '<option value="' . $key . '" selected>' . $value . '</option>';
					} else {
						$games .= '<option value="' . $key . '">' . $value . '</option>';
					}
				}
				$key = 0;
				$key1 = '';
				foreach ( $gdata[ 'options' ] as $ky => $value ) {
					$key ++;
					$key1 .= '<tr id="' . $key . '"><td ><input class="form-control addons_data_1" name="data_1[' . $key . ']" type="text" value="' . $ky . '"></td><td><input class="form-control addons_data_2" name="data_2[' . $key . ']" type="text" value="' . $value . '"></td><td align="center" style="padding-top: 10px;"><i class="glyphicon glyphicon-minus" onclick="del_input(' . $key . ');"></i></td></tr>';
				}
				$key2 = '';
				foreach ( $gdata[ 'options2' ] as $ky => $value ) {
					$key ++;
					if ( $value[ 'type' ] == 1 ) {
						$k = '<option value="1" selected>'.l::t('Успешно').'</option><option value="2">'.l::t('Ошибка').'</option>';
					} else {
						$k = '<option value="1">'.l::t('Успешно').'</option><option value="2" selected>'.l::t('Ошибка').'</option>';
					}
					$key2 .= '<tr id="' . $key . '"><td><select name="data_5[' . $key . ']" class="form-control">' . $k . '</select></td><td ><input class="form-control addons_data_3" name="data_3[' . $key . ']" type="text" value="' . $value[ 'header' ] . '"></td><td><input class="form-control addons_data_4" name="data_4[' . $key . ']" type="text" value="' . $value[ 'text' ] . '"></td><td align="center" style="padding-top: 10px;"><i class="glyphicon glyphicon-minus" onclick="del_input(' . $key . ');"></i></td></tr>';
				}
				$title = l::t("Редактирование");
				tpl::load2 ( 'admin-rise-edit' );
				tpl::set ( '{games}' , $games );
				tpl::set ( '{name}' , $rise[ 'name' ] );
				tpl::set ( '{id}' , $rise[ 'id' ] );
				tpl::set ( '{price}' , $rise[ 'price' ] );
				tpl::set ( '{key}' , $key );
				tpl::set ( '{key1}' , $key1 );
				tpl::set ( '{key2}' , $key2 );
				tpl::set ( '{domain2}' , $rise[ 'domain' ] );
				if ( $gdata[ 'type' ] == 1 ) {
					$type = '<option value="1" selected>POST</option><option value="2">GET</option>';
				} else {
					$type = '<option value="1">POST</option><option value="2" selected>GET</option>';
				}
				tpl::set ( '{type}' , $type );
				tpl::compile ( 'content' );
			}
		}

		public static function del ( $id )
		{
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			$sql = db::q ( 'SELECT * FROM gh_rise where id="' . $id . '"' );
			if ( db::n ( $sql ) != 0 ) {
				$row = db::r ( $sql );
				db::q ( 'DELETE from gh_rise where id="' . $id . '"' );
				api::result ( l::t('Удалено') , true );
			} else {
				api::result ( l::t('Не найдено') );
			}
		}
	}
?>