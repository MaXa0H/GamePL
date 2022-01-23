<?php

	class servers_eac
	{
		public static function base ( $id )
		{
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'eac' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				if ( $row[ 'time' ] < time () ) {
					api::result ( l::t ( 'Срок аренды сервера истек' ) );
				} else {
					$class = servers::game_class ( $row[ 'game' ] );
					if ( $class::info ( 'eac' ) ) {
						db::q ( 'SELECT eac,eac_dir FROM gh_boxes where id="' . $row[ 'box' ] . '"' );
						$row_serv = db::r ();
						$cfg = servers::cfg ( $id );
						$data = $_POST[ 'data' ];
						$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
						$d = '';
						if ( $data ) {
							if(api::$demo){
								api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
								return false;
							}
							switch ( $data[ 'act' ] ) {
								case "1" :
									if ( ! api::admin ( 'puy_servers' ) ) {
										$price = (int) ( 30 * $row_serv[ 'eac' ] );
										if ( api::info ( 'balance' ) < $price ) {
											api::result ( l::t ( 'Недостаточно средств на счете' ) );
											break;
										}
									}
									if ( ! api::admin ( 'puy_servers' ) ) {
										$msg = l::t ( "Приобретение EAC для" ) ." ". $adress;
										api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
										db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
									}
									$d[ 'eac_time' ] = time () + 86400 * 30;
									$d[ 'eac' ] = 1;
									servers::configure ( $d , $id );
									api::result ( l::t ( 'Оплачено' ) , true );
									break;
								case "2" :
									if ( $d[ 'eac_time' ] >= $row[ 'time' ] ) {
										api::result ( l::t ( 'EAC уже продлен до конца срока аренды сервера.' ) );
									} else {
										if ( ! api::admin ( 'puy_servers' ) ) {
											$price = (int) ( ( $row[ 'time' ] - time () ) / 86400 ) * $row_serv[ 'eac' ];
											if ( api::info ( 'balance' ) < $price ) {
												api::result ( l::t ( 'Недостаточно средств на счете' ) );
												break;
											}
										}
										if ( ! api::admin ( 'puy_servers' ) ) {
											$msg = l::t ( "Приобретение EAC для" ) . " " . $adress;
											api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
											db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
										}
										$d[ 'eac_time' ] = $row[ 'time' ];
										$d[ 'eac' ] = 1;
										servers::configure ( $d , $id );
										api::result ( l::t ( 'Оплачено' ) , true );
									}
									break;
								case "3" :
									if ( ! api::admin ( 'puy_servers' ) ) {
										$price = (int) ( 30 * $row_serv[ 'eac' ] );
										if ( api::info ( 'balance' ) < $price ) {
											api::result ( l::t ( 'Недостаточно средств на счете' ) );
											break;
										}
									}
									if ( ! api::admin ( 'puy_servers' ) ) {
										$msg = l::t ( "Приобретение EAC для" ) ." ". $adress;
										api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
										db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
									}
									$d[ 'eac_time' ] = $cfg[ 'eac_time' ] + 86400 * 30;
									servers::configure ( $d , $id );
									api::result ( l::t ( 'Оплачено' ) , true );
									break;
								case "4" :
									if ( $d[ 'eac_time' ] >= $row[ 'time' ] ) {
										api::result ( l::t ( 'EAC уже продлен до конца срока аренды сервера.' ) );
									} else {
										if ( ! api::admin ( 'puy_servers' ) ) {
											$price = (int) ( ( $row[ 'time' ] - time () ) / 86400 ) * $row_serv[ 'eac' ];
											if ( api::info ( 'balance' ) < $price ) {
												api::result ( l::t ( 'Недостаточно средств на счете' ) );
												break;
											}
										}
										if ( ! api::admin ( 'puy_servers' ) ) {
											$msg = l::t ( "Приобретение EAC для" ) . " " . $adress;
											api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
											db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
										}
										$d[ 'eac_time' ] = $row[ 'time' ];
										servers::configure ( $d , $id );
										api::result ( l::t ( 'Оплачено' ) , true );
									}
									break;
								case "5" :
									if ( $cfg[ 'eac_time' ] < time () ) {
										api::result ( l::t ( 'EAC не оплачен.' ) );
									} else {
										$val = array ( 1 , 2 , 3 );
										if ( in_array ( $data[ 'tip' ] , $val ) ) {
											include_once ( ROOT . '/engine/classes/ssh2.class.php' );
											if ( ssh::gh_box ( $row[ 'box' ] ) ) {
												$cmd = "cd " . $row_serv[ 'eac_dir' ] . $class::info ( 'eac_dir' ) . ";rm " . $row[ 'id' ] . ".ini;";
												ssh::exec_cmd ( $cmd );
												sleep ( 3 );
												if ( $data[ 'tip' ] == 2 ) {
													$cmd = "cd " . $row_serv[ 'eac_dir' ] . $class::info ( 'eac_dir' ) . ";echo '" . $adress . "-" . $cfg[ 'rcon' ] . "-2' > " . $row[ 'id' ] . ".ini;";
													ssh::exec_cmd ( $cmd );
												}
												if ( $data[ 'tip' ] == 3 ) {
													$cmd = "cd " . $row_serv[ 'eac_dir' ] . $class::info ( 'eac_dir' ) . ";echo '" . $adress . "-" . $cfg[ 'rcon' ] . "' > " . $row[ 'id' ] . ".ini;";
													ssh::exec_cmd ( $cmd );
												}
												$d[ 'eac' ] = $data[ 'tip' ];
												servers::configure ( $d , $id );
												api::result ( l::t ( 'Выполнено' ) , true );
											} else {
												api::result ( l::t ( 'Не удалось установить соединение с сервером' ) );
											}
										} else {
											api::result ( l::t ( 'Критическая ошибка' ) );
										}
									}
									break;
							}
						}
						api::nav ( "/servers" , l::t ( "Серверы" ) );
						api::nav ( "/servers/base/" . $id , $adress );
						api::nav ( "" , 'EAC' , '1' );
						tpl::load ( 'servers-eac' );
						tpl::set ( '{id}' , $id );
						if ( $cfg[ 'eac_time' ] < time () ) {
							tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "\\1" );
							tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "" );
							tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
							$t2 = time () + 86400 * 30;
							tpl::set ( '{time2}' , api::langdate ( "j F Y - H:i" , $t2 ) );
							tpl::set ( '{price}' , (int) ( ( $row[ 'time' ] - time () ) / 86400 ) * $row_serv[ 'eac' ] );
							tpl::set ( '{price2}' , (int) ( 30 * $row_serv[ 'eac' ] ) );
						} else {
							tpl::set_block ( "'\\[on\\](.*?)\\[/on\\]'si" , "\\1" );
							tpl::set_block ( "'\\[off\\](.*?)\\[/off\\]'si" , "" );
							if ( $cfg[ 'eac_time' ] >= $row[ 'time' ] ) {
								tpl::set_block ( "'\\[hide\\](.*?)\\[/hide\\]'si" , "" );
							} else {
								tpl::set_block ( "'\\[hide\\](.*?)\\[/hide\\]'si" , "\\1" );
							}
							tpl::set ( '{time3}' , api::langdate ( "j F Y - H:i" , $cfg[ 'eac_time' ] ) );
							tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
							$t2 = $cfg[ 'eac_time' ] + 86400 * 30;
							tpl::set ( '{time2}' , api::langdate ( "j F Y - H:i" , $t2 ) );
							tpl::set ( '{price}' , (int) ( ( $row[ 'time' ] - time () ) / 86400 ) * $row_serv[ 'eac' ] );
							tpl::set ( '{price2}' , (int) ( 30 * $row_serv[ 'eac' ] ) );
							if ( $cfg[ 'eac' ] == 2 ) {
								$stats = '<option value="1">' . l::t ( 'EAC отключен. Могут играть все.' ) . '</option>';
								$stats .= '<option value="2" selected>' . l::t ( 'EAC включен, но игроки без ЕАС не выбрасываются с сервера.' ) . '</option>';
								$stats .= '<option value="3">' . l::t ( 'ЕАС включен. Игроки без ЕАС не могут зайти на сервер.' ) . '</option>';
							} elseif ( $cfg[ 'eac' ] == 3 ) {
								$stats = '<option value="1">' . l::t ( 'EAC отключен. Могут играть все.' ) . '</option>';
								$stats .= '<option value="2">' . l::t ( 'EAC включен, но игроки без ЕАС не выбрасываются с сервера.' ) . '</option>';
								$stats .= '<option value="3" selected>' . l::t ( 'ЕАС включен. Игроки без ЕАС не могут зайти на сервер.' ) . '</option>';
							} else {
								$stats = '<option value="1" selected>' . l::t ( 'EAC отключен. Могут играть все.' ) . '</option>';
								$stats .= '<option value="2">' . l::t ( 'EAC включен, но игроки без ЕАС не выбрасываются с сервера.' ) . '</option>';
								$stats .= '<option value="3">' . l::t ( 'ЕАС включен. Игроки без ЕАС не могут зайти на сервер.' ) . '</option>';
							}
							tpl::set ( '{stats}' , $stats );
						}
						tpl::compile ( 'content' );
					} else {
						api::result ( l::t ( 'Данная функция отключена' ) );
					}
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

	}
?>