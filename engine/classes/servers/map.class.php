<?php
class servers_map
{
	public static function base ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'maps' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				if ( $row[ 'status' ] != "1" ) {
					api::result ( l::t ( 'Включите сервер' ) );
				} else {
					$class = servers::game_class ( $row[ 'game' ] );
					if ( $class::info ( 'maps' ) ) {
						api::inc ( 'ssh2' );
						if ( ssh::gh_box ( $row[ 'box' ] ) ) {
							$data1 = $class::maps ( $row );
							if ( $_POST[ 'data' ] ) {
								if(api::$demo){
									api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
									return false;
								}
								foreach ( $data1 as $map ) {
									if ( ! preg_match ( '/\.(bsp.ztmp)/' , $map ) ) {
										if ( ! preg_match ( '/\.(ztmp)/' , $map ) ) {
											$map = str_replace ( ".bsp" , "" , $map );
											if ( $map == $_POST[ 'data' ][ 'map' ] ) {
												$ermap = "0";
											}
										}
									}
								}
								if ( $ermap == 0 ) {
									$exec = 'screen -S server_' . $row[ 'sid' ] . ' -p 0 -X stuff \'' . $class::maps_go ( $_POST[ 'data' ][ 'map' ] ) . '\'$\'\n\';';
									ssh::exec_cmd ( $exec );
									api::result ( l::t ( 'Выполнено' ) , true );
								} else {
									api::result ( l::t ( 'Карта не найдена' ) );
								}
							}
							$sql = db::q ( 'SELECT * FROM gh_monitoring where sid="' . $id . '"' );
							$row_s = db::r ( $sql );
							servers::$speedbar = $id;
							$maps = '';
							foreach ( $data1 as $map1 ) {
								if ( $map1 == $row_s[ 'map' ] ) {
									$maps .= '<option value="' . $map1 . '" selected="selected">' . $map1 . '</option>';
								} else {
									$maps .= '<option value="' . $map1 . '">' . $map1 . '</option>';
								}
							}
							tpl::load ( 'servers-map' );
							tpl::set ( '{id}' , $id );
							tpl::set ( '{maps}' , $maps );
							tpl::compile ( 'content' );
							if ( api::modal () ) {
								die( tpl::result ( 'content' ) );
							}
							ssh::disconnect ();
						} else {
							api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
						}
					} else {
						api::result ( l::t ( 'Данная функция отключена' ) );
					}
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}
}

?>
