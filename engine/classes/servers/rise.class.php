<?php

	api::inc ( 'servers' );

	class servers_rise
	{
		public static function log_boost ( $user , $mes , $tip , $sum )
		{
			db::q (
				"INSERT INTO logs_boost set
			user='" . $user . "',
			mes='" . $mes . "',
			tip='" . $tip . "',
			time='" . time () . "',
			sum='" . $sum . "'
		"
			);
		}

		public static function buy ( $id , $ip , $port )
		{
			db::q ( 'SELECT * FROM gh_rise where id="' . $id . '"' );
			if ( db::n () != "1" ) {
				api::result ( l::t ('Не найдено') );
			} else {
				$rise = db::r ();
				$gdata = json_decode ( base64_decode ( $rise[ 'options' ] ) , true );
				$data = array ();
				foreach ( $gdata[ 'options' ] as $key => $value ) {
					$value = str_replace ( '{ip}' , $ip , $value );
					$value = str_replace ( '{port}' , $port , $value );
					$data[ $key ] = $value;
				}
				if ( $gdata[ 'type' ] == 1 ) {
					$url = $rise[ 'domain' ];
				} else {
					$url = $rise[ 'domain' ] . '?' . urldecode ( http_build_query ( $data ) );
				}

				$curl = curl_init ();
				curl_setopt ( $curl , CURLOPT_URL , $url );
				curl_setopt ( $curl , CURLOPT_HEADER , 1 );
				if ( $gdata[ 'type' ] == 1 ) {
					curl_setopt ( $curl , CURLOPT_POST , 1 );
					curl_setopt ( $curl , CURLOPT_POSTFIELDS , urldecode ( http_build_query ( $data ) ) );
				}
				curl_setopt ( $curl , CURLOPT_RETURNTRANSFER , true );
				curl_setopt ( $curl , CURLOPT_SSL_VERIFYPEER , false );
				$result = curl_exec ( $curl );
				$header_size = curl_getinfo ( $curl , CURLINFO_HEADER_SIZE );
				curl_close ( $curl );
				$header = substr ( $result , 0 , $header_size );
				$body = substr ( $result , $header_size );
				$header = explode ( "\n" , trim ( $header ) );
				$hstatus = explode ( ' ' , $header[ 0 ] );
				foreach ( $gdata[ 'options2' ] as $key => $value ) {
					if ( $hstatus[ 1 ] == $value[ 'header' ] ) {
						if ( substr_count ( $body , $value[ 'text' ] ) > 0 ) {
							if ( $value[ 'type' ] == 1 ) {
								return true;
							} else {
								return false;
							}
							break;
						}
					}
				}

				return false;
				exit;
			}
		}

		public static function base ( $id )
		{
			global $title , $conf;
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if(!servers::friend ( $id  , 'rise' )){
					api::result(l::t ('Недостаточно привилегий!'));
					return false;
				}else{
					db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
				}
			}
			if ( db::n () == 1 ) {
				$server = db::r ();
				$adress = servers::ip_server($server['box']) . ':' . $server[ 'port' ];
				$data = $_POST[ 'data' ];
				servers::$speedbar = $id;
				if ( $data ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					db::q ( 'SELECT * FROM gh_rise where id="' . (int) $data[ 'rate' ] . '"' );
					if ( db::n () != "1" ) {
						api::result (l::t ( 'Не найдено') );
					} else {
						$rise = db::r ();
						$price = $rise[ 'price' ];
						if ( ! api::admin ( 'puy_servers' ) ) {
							if ( api::info ( 'balance' ) < $price ) {
								$gogo = 1;
								api::result ( l::t ('Недостаточно средств на счете') );
							}
						}
						if ( self::buy ( $data[ 'rate' ] , $server[ 'ip' ] , $server[ 'port' ] ) ) {
							if ( ! api::admin ( 'puy_servers' ) ) {
								$msg = l::t ("Приобретение").' ' . $rise[ 'name' ] . "";
								api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
								self::log_boost ( api::info ( 'id' ) , $msg , '1' , $price );
								db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
							}
							db::q (
								"INSERT INTO gh_boost set
							server='" . $server[ 'id' ] . "',
							boost='" . $rise[ 'name' ] . "',
							price='" . $rise[ 'price' ] . "',
							time='" . time () . "'"
							);
							api::result ( l::t ('Подключено') , true );
						} else {
							api::result ( l::t ('Не удалось подключить') );
						}
					}
				}
				api::nav ( "/servers" , l::t ("Серверы") );
				api::nav ( "/servers/base/" . $id , $adress );
				api::nav ( '' , l::t ('Раскрутки') , '1' );
				$title = l::t ("Раскрутки");

				db::q ( 'SELECT id FROM gh_boost where server="' . $id . '" order by id desc' );
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
				$sql = db::q ( 'SELECT * FROM gh_boost where server="' . $id . '" order by id desc LIMIT ' . $page . ' ,' . $num );
				$data = "";
				while ( $row = db::r ( $sql ) ) {
					tpl::load ( 'servers-boost-listen-get' );
					tpl::set ( '{id}' , $row[ 'id' ] );
					tpl::set ( '{name}' , $row[ 'boost' ] );
					tpl::set ( '{price}' , $row[ 'price' ] );
					tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
					tpl::compile ( 'data' );
				}
				$sql = db::q ( 'SELECT * FROM gh_rise where game="' . $server[ 'game' ] . '" order by id asc' );
				$data2 = "";
				while ( $row = db::r ( $sql ) ) {
					$data2 .= '<option value="' . $row[ 'id' ] . '" price="' . $row[ 'price' ] . '">' . $row[ 'name' ] . ' - ' . $row[ 'price' ] . ' ' . $conf[ 'curs-name' ] . '</option>';
				}
				tpl::load ( 'servers-boost-listen' );
				tpl::set ( '{data}' , tpl::result ( 'data' ) );
				tpl::set ( '{rates}' , $data2 );
				tpl::set ( '{nav}' , api::pagination ( $all , $num , $pages , '/servers/boost/' . $id ) );
				tpl::compile ( 'content' );
			} else {
				api::result ( l::t ('Сервер не найден') );
			}
		}
	}
?>