<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class servers_buy
{
	public static function cupon ()
	{
		global $conf;
		$cupon = $conf[ 'cup' ];
		$code = $_POST[ 'cupon' ];
		foreach ( $cupon as $key => $index ) {
			if ( $code == $index[ 'code' ] ) {
				$go = true;
				$d[ 'sum' ] = $index[ 'sum' ];
				$d[ 'type' ] = $index[ 'type' ];
				$d[ 'min' ] = $index[ 'min' ];
				break;
			}
		}
		if ( ! $go ) {
			if ( ! $d[ 'e' ] ) {
				$d[ 'e' ] = l::t("Купон не найден");
			}
		} else {
			$d[ 'r' ] = l::t("Купон активирован");
		}
		die( json_encode ( $d ) );
	}

	public static function cupon_check ( $code , $price )
	{
		global $conf;
		$cupon = $conf[ 'cup' ];
		foreach ( $cupon as $key => $index ) {
			if ( $code == $index[ 'code' ] ) {
				if ( $price < $index[ 'min' ] ) {
					$go = false;
					break;
				}
				$go = true;
				$d[ 'sum' ] = $index[ 'sum' ];
				$d[ 'type' ] = $index[ 'type' ];
				$d[ 'min' ] = $index[ 'min' ];
				break;
			}
		}
		if ( ! $go ) {
			return false;
		}

		return $d;
	}

	public static function gettime ( $time )
	{
		$d = date ( "d" , $time );
		$m = date ( "m" , $time );
		$Y = date ( "Y" , $time );
		$H = date ( "H" , $time );
		$time = mktime ( $H , 0 , 0 , $m , $d , $Y );

		return $time . "000";
	}

	public static function time2 ( $id )
	{
		global $conf;
		$data = $_POST[ 'data' ];
		if ( $conf[ 'buy' ] ) {
			$min_date = 86400 * $conf[ 'buy' ];
		} else {
			$conf[ 'buy' ] = 1;
			$min_date = 86400;
		}
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'buy' ) ) {
				api::result (  l::t('Недостаточно привилегий!') );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			$slots = $row[ 'slots' ];
			db::q ( 'SELECT price FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
			$rate = db::r ();
			$price_slots = ( $rate[ 'price' ] / 30 );
			if ( $data ) {
				if ( !$data[ 'price' ] ) {
					if ( !api::captcha_chek ()) {
						return false;
					}
				}
					$date = api::cl ( $data[ 'time' ] );
					if ($row['time'] < time()) {
						$row['time'] = api::gettime();
					}
					list( $price , $support , $data[ 'time' ] , $act , $price_s , $price_n ) = self::price ( $date , $row[ 'slots' ] , $rate[ 'price' ] ,0 , 0 , $_POST[ 'data' ][ 'cupon' ],$row['time']);
					if ( $data[ 'price' ] ) {
						echo json_encode ( array ( $price , $act , $price_s , $price_n ) );
						die;
					}
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
					if ( $price < 0 ) {
						api::result ( l::t('Стоимость заказа отрицательная!') );

						return false;
					}
					$ntime = $row['time']+$data[ 'time' ]*86400;
					$balance = api::info ( 'balance' );
					if ( $balance < $price and ! api::admin ( 'puy_servers' ) ) {
						api::result ( l::t('Для оплаты сервера пополните свой счет на:')." " . api::price ( $price - $balance ) . ' руб.' );
					} else {
						$balance = api::price ( $balance - $price );
						if ( ! api::admin ( 'puy_servers' ) ) {
							db::q ( 'update users set balance="' . $balance . '" where id="' . api::info ( 'id' ) . '"' );
						}
						db::q ( 'update gh_servers set time="' . $ntime . '" where id="' . $id . '"' );
						if ( $row[ 'mysql' ] ) {
							db::q ( 'update mysql set time="' . $ntime . '" where id="' . $row[ 'mysql' ] . '"' );
						}
						if ( $row[ 'web' ] ) {
							db::q ( 'update isp set time="' . $ntime . '" where id="' . $row[ 'web' ] . '"' );
						}
						if ( ! api::admin ( 'puy_servers' ) ) {
							$msg = l::t("Продление сервера #") . $id . " ( ".l::t("слотов")." " . $slots . " ) ".l::t("на")." ". $data[ 'time' ] . ' ' . api::getNumEnding (
									$data[ 'time' ] , array (
									l::t('день') ,
									l::t('дня') ,
									l::t('дней')
								)
								);
							api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
						}
						api::result ( l::t('Сервер успешно продлен') , true );
					}
				}
			tpl::load ( 'servers-buy-time' );
			tpl::set ( '{price}' , $rate[ 'price' ] );
			if ( $row[ 'time' ] < time () ) {
				$row[ 'time' ] = api::gettime ();
			}
			tpl::set ( '{time-day}' , date ( 'H-i-s' , $row[ 'time' ] ) );
			tpl::set ( '{start-day}' , date ( 'd-m-Y' , $row[ 'time' ] + $min_date ) );
			tpl::set ( '{start-day2}' , date ( 'd-m-Y' , $row[ 'time' ] ) );
			tpl::set ( '{end-day}' , date ( 'd-m-Y' , $row[ 'time' ] + 86400 * 30 * 12 ) );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{slots}' , $row[ 'slots' ] );
			tpl::set ( '{fprice}' , $conf[ 'fprice' ] );
			tpl::set ( '{price1}' , json_encode ( $conf[ 'price' ] ) );
			api::captcha_create ();
			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
				api::nav ( "/servers" , l::t("Серверы") );
				api::nav ( "/servers/" . $id , $adress );
				api::nav ( '' , l::t('Продление сервера') , '1' );
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}


	public static function price ( $time , $slots , $price2 , $price_support , $support , $cupon,$base_time=0 )
	{
		global $conf;
		if ( $conf[ 'fprice' ] ) {
			$asd = - 1;
			if ( ! $conf[ 'price' ][ $time ] ) {
				api::result ( l::t('Критическая ошибка') );
			} else {
				$data[ 'time' ] = $conf[ 'price' ][ $time ][ 'day' ];
				$price = ( $price2 / 30 ) * $slots;
				if ( $support ) {
					$price = $price + ( $price_support / 30 );
					$support = time () + 86400 * $data[ 'time' ];
				}
				$price = $price * $data[ 'time' ];
				$price3 = 0;
				$act = 1;
				if ( $conf[ 'price' ][ $time ][ 'price1' ] ) {
					$pricen = $price / 100 * $conf[ 'price' ][ $time ][ 'price1' ];
					$price3 = $price3 - $pricen;
					$price = $price - $pricen;
					$act = 1;
				} else {
					if ( $conf[ 'price' ][ $time ][ 'price2' ] ) {
						$act = 2;
						$pricen = $price / 100 * $conf[ 'price' ][ $time ][ 'price2' ];
						$price3 = $price3 + $pricen;
						$price = $price + $pricen;
					}
				}
				if ( $cup = self::cupon_check ( $cupon , $price ) ) {
					if ( $cup[ 'type' ] == 1 ) {
						$pricen = $price / 100 * $cup[ 'sum' ];
					} else {
						$pricen = $cup[ 'sum' ];
					}
					$act = 1;
					$price3 = $price3 - $pricen;
					$price = $price - $pricen;
				}
				$price = api::price ( $price );
				$price3 = api::price ( $price3 );
				$price4 = api::price ( $price - $price3 );

				return array ( $price , $support , $data[ 'time' ] , $act , $price3 , $price4 );
			}

		} else {
			if ( ! $conf[ 'buy' ] ) {
				$conf[ 'buy' ] = 1;
			}
			$date = api::cl ( $time );
			if ( ! preg_match ( "/^[0-9]{2}\-[0-9]{2}\-[0-9]{4}$/i" , $date ) ) {
				api::result ( l::t('Формат даты неверный!') );

				return false;
			}
			$ds = explode ( '-' , $date );
			$row[ 'time' ] =  api::gettime();
			if($base_time){
				$row[ 'time' ] = $base_time;
			}


			$ntime = mktime ( date ( "H" , $row[ 'time' ] ) , date ( "i" , $row[ 'time' ] ) , date ( "s" , $row[ 'time' ] ) , $ds[ '1' ] , $ds[ '0' ] , $ds[ '2' ] );
			if ( $ntime < time () ) {
				api::result ( l::t('Некорректная дата') );

				return false;
			}
			$dt = ( $ntime - $row[ 'time' ] ) / 86400;
			$data[ 'time' ] = $dt;
			if ( $data[ 'time' ] >= $conf[ 'buy' ] && $data[ 'time' ] <= 360 ) {
				$price = ( $price2 / 30 ) * $slots;
				if ( $support ) {
					$price = $price + ( $price_support / 30 );
					$support = time () + 86400 * $data[ 'time' ];
				}
				$price = $price * $data[ 'time' ];
				$asd = - 1;
				foreach ( $conf[ 'price' ] as $key => $val ) {
					if ( $data[ 'time' ] > $val[ 'day' ] ) {
						$asd = $key;
					}
				}
				$act = 1;
				$price3 = 0;
				if ( $asd != - 1 ) {
					if ( $conf[ 'price' ][ $asd ][ 'price1' ] ) {
						$pricen = $price / 100 * $conf[ 'price' ][ $asd ][ 'price1' ];
						$act = 1;
						$price3 = $price3 - $pricen;
						$price = $price - $pricen;
					} else {
						if ( $conf[ 'price' ][ $asd ][ 'price2' ] ) {
							$act = 2;
							$pricen = $price / 100 * $conf[ 'price' ][ $asd ][ 'price2' ];
							$price3 = $price3 + $pricen;
							$price = $price + $pricen;
						}
					}
				}
				if ( $cup = self::cupon_check ( $cupon , $price ) ) {
					if ( $cup[ 'type' ] == 1 ) {
						$pricen = $price / 100 * $cup[ 'sum' ];
					} else {
						$pricen = $cup[ 'sum' ];
					}
					$price3 = $price3 - $pricen;
					$price = $price - $pricen;
					$act = 1;
				}
				$price = api::price ( $price );
				$price3 = api::price ( $price3 );
				$price4 = api::price ( $price - $price3 );

				return array ( $price , $support , $data[ 'time' ] , $act , $price3 , $price4 );
			} else {
				api::result ( l::t('Критическая ошибка') );

				return false;
			}
		}

		return false;
	}


	public static function base2 ( $game )
	{
		global $conf , $title;
		if ( $conf[ 'buy' ] ) {
			$min_date = 86400 * $conf[ 'buy' ];
		} else {
			$conf[ 'buy' ] = 1;
			$min_date = 86400;
		}
		$data = $_POST[ 'data' ];
		if ( $game ) {
			if ( ! servers::$games[ $game ] ) {
				api::result ( l::t('Игра не найдена') );

				return false;
			}
		}

		if ( $data ) {
			if(!$data[ 'price' ] ){
				if (!$data[ 'off' ]) {
					api::result ( l::t('Вы не согласились с условиями договора-оферты!') );
					return false;
				}
				if ( !api::$go) {
					api::result ( l::t('Для доступа к данной странице нужно войти на сайт') );
					return false;
				}
				if ( !api::captcha_chek ()){
					return false;
				}
			}

						db::q ( 'SELECT * FROM gh_rates where  power="1" and  id="' . (int) $data[ 'rate' ] . '"' );
						if ( db::n () != "1" ) {
							api::result ( l::t('Тариф не найден') );
						} else {
							$rate = db::r ();
							if ( $rate[ 'game' ] != $data[ 'game' ] ) {
								api::result ( l::t('Критическая ошибка') );
							} else {
								if ( $rate[ 'loc' ] != $data[ 'loc' ] ) {
									api::result ( l::t('Локация не найдена') );
								} else {
									if ( $rate[ 'min_slots' ] > $data[ 'slots' ] ) {
										api::result ( l::t('Критическая ошибка') );
									} else {
										if ( $rate[ 'max_slots' ] < $data[ 'slots' ] ) {
											api::result ( l::t('Критическая ошибка') );
										} else {
											$date = api::cl ( $data[ 'time' ] );
											list( $price , $support , $data[ 'time' ] , $act , $price_s , $price_n ) = self::price ( $date , (int) $data[ 'slots' ] , $rate[ 'price' ] , $rate[ 'support' ] , $data[ 'support' ] , $_POST[ 'data' ][ 'cupon' ] );
											if ( $data[ 'price' ] ) {
												echo json_encode ( array ( $price , $act , $price_s , $price_n ) );
												die;
											}
											if(api::$demo){
												api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
												return false;
											}
											if ( $price < 0 ) {
												api::result ( l::t('Стоимость заказа отрицательная!') );

												return false;
											}
											if ( ! api::admin ( 'puy_servers' ) ) {
												if ( api::info ( 'balance' ) < $price ) {
													api::result ( l::t('Недостаточно средств на счете') );

													return false;
												}
											}
											if ( $rate[ 'dir' ] == 1 ) {
												$versionsa = json_decode ( $rate[ 'versions' ] , true );
												if ( ! $versionsa[ $data[ 'ver' ] ][ 'dir' ] ) {
													api::result ( l::t('Версия игры не найдена') );

													return false;
												}
											}
											if ( $rate[ 'game' ] == "ts3" ) {
												$sql1 = db::q ( 'SELECT * FROM gh_boxes_ts3 where loc="' . (int) $rate[ 'loc' ] . '" and power="1"' );
												if ( db::n ( $sql1 ) == 0 ) {
													api::result ( l::t('На выбранной локации недостаточно свободного места') );
												} else {
													$domain = '';
													$gogo = 0;
													if ( $file = file_get_contents ( ROOT . '/data/tsdns.ini' ) ) {
														if ( $conf2 = json_decode ( $file , true ) ) {
															if ( $conf2[ 'on' ] ) {
																if ( ! in_array ( $_POST[ 'data' ][ 'domain2' ] , $conf2[ 'domain' ] ) ) {
																	$gogo = 1;
																	api::result ( l::t('Домен не найден!') );
																} else {
																	if ( ! preg_match ( "/^[0-9a-zA-Z]{3,20}$/i" , trim ( $_POST[ 'data' ][ 'domain' ] ) ) ) {
																		$gogo = 1;
																		api::result ( l::t('Поддомен указан неверно!') );
																	} else {
																		$domain = api::cl ( $_POST[ 'data' ][ 'domain' ] . '.' . $_POST[ 'data' ][ 'domain2' ] );
																		$sql12 = db::q ( 'SELECT * FROM gh_servers where domain="' . $domain . '"' );
																		if ( db::n ( $sql12 ) == 1 ) {
																			$gogo = 1;
																			api::result ( l::t('Домен занят!') );
																		}
																	}
																}
															}
														}
													}
													if ( $gogo == 0 ) {
														$server = "0";
														$go = "0";
														while ( $boxe = db::r ( $sql1 ) ) {
															$cpu_box = "0";
															$cpu = $data[ 'slots' ];
															$sql3 = db::q ( 'SELECT * FROM gh_servers where game="ts3" and box="' . $boxe[ 'id' ] . '"' );
															while ( $row3 = db::r ( $sql3 ) ) {
																$cpu_box = $cpu_box + ( $row3[ 'slots' ] );
															}
															if ( $boxe[ 'slots' ] > ( $cpu_box + $cpu ) ) {
																$port_start = $rate[ 'min_ports' ];
																$port_stop = $rate[ 'max_ports' ];
																while ( ++ $port_start <= $port_stop ) {
																	$sql4 = db::q ( 'SELECT id FROM gh_servers where port="' . $port_start . '" and box="' . $boxe[ 'id' ] . '"' );
																	if ( db::n ( $sql4 ) == 0 ) {
																		$go = 1;
																		api::inc ( 'telnet' );
																		servers::game_class ( $data[ 'game' ] );
																		$class = 'game_' . $data[ 'game' ];
																		$date = time () + 86400 * $data[ 'time' ];
																		db::q (
																			"INSERT INTO gh_servers set
																						ip='" . $boxe[ 'ip' ] . "',
																						port='" . $port_start . "',
																						slots='" . (int) $data[ 'slots' ] . "',
																						rate='" . (int) $data[ 'rate' ] . "',
																						game='" . $data[ 'game' ] . "',
																						user='" . api::info ( 'id' ) . "',
																						name='" . servers::$games[ $data[ 'game' ] ] . "',
																						time='" . time () . "',
																						box='" . $boxe[ 'id' ] . "',
																						domain='" . $domain . "',
																						sid='0',
																						status='1'"
																		);
																		$lid = db::i ();
																		servers::$cron = true;
																		if ( $ssid = $class::install ( $boxe[ 'id' ] , $rate[ 'id' ] , $data[ 'slots' ] , $port_start , $lid ) ) {
																			if ( $domain ) {
																				$class::install_domain ( $boxe[ 'ip' ] , $port_start , $domain );
																			}
																			servers::$cron = false;
																			db::q ( 'UPDATE gh_servers set sid="' . $ssid . '",time="' . $date . '" where id="' . $lid . '"' );
																			if ( ! api::admin ( 'puy_servers' ) ) {
																				$msg =  l::t("Приобретение игрового сервера")." ". $data[ 'game' ] . " (" . $data[ 'slots' ] . ")";
																				api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
																				db::q ( 'UPDATE users set balance="' .  api::price( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
																			}
																			api::result ( l::t('Сервер установлен') , true );
																			break;
																		} else {
																			db::q ( "delete from gh_servers where id='" . $lid . "'" );
																			api::result ( ts3::$error );
																		}

																	}
																}
															}
															if ( $go == 1 ) {
																$server = 1;
																break;
															}
														}
													} else {
														api::result (  l::t('Критическая ошибка') );
													}
													if ( $server == 0 ) {
														api::result (  l::t('На выбранной локации недостаточно свободного места') );
													}
												}
											} else {
												$sql1 = db::q ( 'SELECT * FROM gh_boxes_games where game="' . $rate[ 'game' ] . '"' );
												if ( db::n ( $sql1 ) == 0 ) {
													api::result (  l::t('На выбранной локации недостаточно свободного места') );
												} else {
													$server = "0";
													$go = "0";
													while ( $game = db::r ( $sql1 ) ) {
														$cpu_box = "0";
														if ( $rate[ 'game' ] == "samp" ||$rate[ 'game' ] == "crmp" || $rate[ 'game' ] == "mta" ) {
															$cpu = (int) ( $data[ 'slots' ] / 10 ) * $game[ 'cpu' ];
														} else {
															$cpu = $data[ 'slots' ] * $game[ 'cpu' ];
														}
														$sql2 = db::q ( 'SELECT * FROM gh_boxes where id="' . $game[ 'box' ] . '"' );
														$row2 = db::r ();
														if ( $row2[ 'loc' ] != $data[ 'loc' ] ) {
															continue;
														}
														$sql3 = db::q ( 'SELECT * FROM gh_servers where box="' . $game[ 'box' ] . '"' );
														while ( $row3 = db::r ( $sql3 ) ) {
															$cpu_box = $cpu_box + ( $row3[ 'slots' ] * $game[ 'cpu' ] );
														}
														if ( $row2[ 'cpu' ] > ( $cpu_box + $cpu ) ) {
															$port_start = $rate[ 'min_ports' ];
															$port_stop = $rate[ 'max_ports' ];
															$g1 = 0;
															while ( ++ $port_start <= $port_stop ) {
																$sql4 = db::q ( 'SELECT id FROM gh_servers where port="' . $port_start . '" and box="' . $game[ 'box' ] . '"' );
																if ( db::n ( $sql4 ) == 0 ) {
																	if ( $g1 == 1 ) {
																		$g1 = 2;
																		$go = 1;
																	}
																	if ( $g1 == 0 ) {
																		$g1 = 1;
																		continue;
																	}
																	api::inc ( 'ssh2' );
																	if ( ssh::gh_box ( $game[ 'box' ] ) ) {
																		servers::game_class ( $data[ 'game' ] );
																		$class = 'game_' . $data[ 'game' ];
																		$date = time () + 86400 * $data[ 'time' ];
																		db::q (
																			"INSERT INTO gh_servers set
																						ip='" . $row2[ 'ip' ] . "',
																						port='" . $port_start . "',
																						slots='" . (int) $data[ 'slots' ] . "',
																						rate='" . (int) $data[ 'rate' ] . "',
																						game='" . $data[ 'game' ] . "',
																						user='" . api::info ( 'id' ) . "',
																						name='" . servers::$games[ $data[ 'game' ] ] . "',
																						time='" . $date . "',
																						box='" . $game[ 'box' ] . "',
																						sid='" . $row2[ 'server' ] . "',
																						support='" . $support . "',
																						status='3'"
																		);
																		$id = db::i ();
																		$id2 = $row2[ 'server' ];
																		$dop1 = '';
																		if ( $rate[ 'mysql' ] ) {
																			if ( api::inc ( 'servers/mysql' ) ) {
																				if ( $mysql = servers_mysql::crate ( api::info ( 'id' ) , $rate[ 'mysql' ] , $date ) ) {
																					$dop1 .= ', mysql="' . $mysql . '"';
																				}

																			}
																		}
																		if ( $rate[ 'web' ] ) {
																			if ( api::inc ( 'servers/isp' ) ) {
																				if ( $mysql = servers_isp::crate ( api::info ( 'id' ) , $rate[ 'web' ] , $date ) ) {
																					$dop1 .= ', web="' . $mysql . '"';
																				}

																			}
																		}
																		db::q ( 'UPDATE gh_boxes set server="' . ( $row2[ 'server' ] + 1 ) . '" where id="' . $game[ 'box' ] . '"' );
																		db::q ( 'UPDATE gh_servers set id="' . $id . '" ' . $dop1 . ' where id="' . $id . '"' );

																		if ( ! api::admin ( 'puy_servers' ) ) {
																			$msg = l::t("Покупка сервера").' '. $data[ 'game' ] . ", ".l::t("слотов")." " . $data[ 'slots' ] . ", ".l::t("тариф")." ".$rate['name'];
																			api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
																			db::q ( 'UPDATE users set balance="' . api::price( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
																		}
																		$class::install ( $id );
																		if ( $rate[ 'dir' ] == 1 ) {
																			$data12[ 'bild' ] = $data[ 'ver' ];
																			servers::configure ( $data12 , $id );
																			$dir = $versionsa[ $data[ 'ver' ] ][ 'dir' ];
																		} else {
																			$dir = $rate[ 'dir' ];
																		}
																		self::install ( api::info ( 'id' ) , $id2 , $dir , $row2[ 'os' ] , $data[ 'game' ] );


																		ssh::disconnect ();
																		api::result (  l::t('Сервер устанавливается') , true );
																	} else {
																		api::result (  l::t('Не удалось установить соединение') );
																	}
																	break;
																} else {
																	$g1 = 0;
																}
															}
														}
														if ( $go == 1 ) {
															break;
														}
													}
													if ( $server == 0 ) {
														api::result ( l::t('На выбранной локации недостаточно свободного места') );
													}
												}
											}
										}

									}
								}
							}
						}
		}
		$title = l::t("Покупка сервера");
		if ( $game ) {
			api::nav ( "/servers/buy" , l::t('Покупка сервера') );
			api::nav ( "" , servers::$games[ $game ] , 1 );
		} else {
			api::nav ( "" , l::t('Покупка сервера') , '1' );
		}

		tpl::load ( 'servers-buy' );
		if ( $game ) {
			tpl::set ( '{game}' , $game );
		} else {
			foreach ( servers::$games as $key => $value ) {
				db::q ( 'SELECT id FROM gh_rates where game="' . $key . '" and power="1" LIMIT 0,1' );
				if ( db::n () != 0 ) {
					tpl::set ( '{game}' , $key );
					break;
				}
			}

		}

		$time = api::gettime ();
		tpl::set ( '{time-day}' , date ( 'H-i-s' , $time ) );
		tpl::set ( '{fprice}' , $conf[ 'fprice' ] );
		tpl::set ( '{price1}' , json_encode ( $conf[ 'price' ] ) );
		tpl::set ( '{start-day}' , date ( 'd-m-Y' , $time + $min_date ) );
		tpl::set ( '{end-day}' , date ( 'd-m-Y' , $time + 86400 * 30 * 12 ) );
		tpl::set ( '{start-day2}' , date ( 'd-m-Y' , $time ) );
		api::captcha_create ();
		if ( $file = file_get_contents ( ROOT . '/data/tsdns.ini' ) ) {
			if ( $conf2 = json_decode ( $file , true ) ) {
				if ( $conf2[ 'on' ] ) {
					tpl::set_block ( "'\\[tsdns\\](.*?)\\[/tsdns\\]'si" , "\\1" );
					$domains = "";
					foreach ( $conf2[ 'domain' ] as $key => $val ) {
						$domains .= '<option value="' . $val . '">.' . $val . '</option>';
					}
					tpl::set ( '{domains}' , $domains );
				} else {
					tpl::set_block ( "'\\[tsdns\\](.*?)\\[/tsdns\\]'si" , "" );
				}
			} else {
				tpl::set_block ( "'\\[tsdns\\](.*?)\\[/tsdns\\]'si" , "" );
			}
		} else {
			tpl::set_block ( "'\\[tsdns\\](.*?)\\[/tsdns\\]'si" , "" );
		}
		tpl::compile ( 'content' );
	}

	public static function install ( $user , $id , $dir , $os , $game )
	{
		$exec = "cd /host/;mkdir " . $user . ";";
		$exec .= "cd /;groupadd -g " . $id . " s" . $id . ";";
		$exec .= "useradd -u " . $id . " -d /host/" . $user . "/" . $id . " -g s" . $id . " s" . $id . ";";
		$exec .= "cd /host/" . $user . "/;";
		$exec .= "rm -Rf " . $id . ";";
		$exec .= "cd /host/;";
		if ( $game == "rust" ) {
			$exec .= "screen -dmS install_" . $id . " ";
			$exec .= "/bin/bash -c 'cd /host/;";
			$exec .= "mkdir " . $user . ";";
			$exec .= "mkdir " . $user . "/" . $id . ";";
			$exec .= "cp -rv " . $dir . " " . $user . "/" . $id . "/server;";
			$exec .= "cd /host/" . $user . ";";
			$exec .= "cp /etc/skel/.bash_logout /host/" . $user . "/" . $id . "/.bash_logout;";
			$exec .= "cp /etc/skel/.bashrc /host/" . $user . "/" . $id . "/.bashrc;";
			$exec .= "cp /etc/skel/.profile /host/" . $user . "/" . $id . "/.profile;";
			$exec .= "chown -R  s" . $id . ":s" . $id . " " . $id . ";";
			$exec .= "chmod -R 771 " . $id . ";";
			$exec .= "cd " . $id . "/;";
			$exec .= "su s" . $id . " -c \"winecfg\";'";
		} else {
			$exec .= "screen -dmS install_" . $id . " cp -rv " . $dir . " " . $user . "/" . $id;
			if ( $os == "1" || $os == "2" ) {
				$exec .= "/;";
			}
		}
		ssh::exec_cmd ( $exec );
	}

	public static function load ()
	{
		global $conf;
		$post[ 'go' ] = api::cl ( $_POST[ 'go' ] );
		$post[ 'game' ] = api::cl ( $_POST[ 'game' ] );
		$post[ 'loc' ] = (int) $_POST[ 'loc' ];
		$post[ 'rate' ] = (int) $_POST[ 'rate' ];
		$data = array ();
		if ( $post[ 'go' ] == "game" ) {
			foreach ( servers::$games as $key => $value ) {
				$sqld = db::q ( 'SELECT * FROM gh_rates where power="1" and game="' . $key . '" LIMIT 0,1' );
				if ( db::n ( $sqld ) != 0 ) {
					$rowse = db::r ( $sqld );
					$game = array ();
					$game[ 'key' ] = $key;
					$sql23 = db::q ( 'SELECT tpl FROM tpl where name="servers-buy-informer-' . $key . '"' );
					if ( db::n () != 0 ) {
						$row23 = db::r ( $sql23 );
						$game[ 'informer' ] = $row23[ 'tpl' ];
					} else {
						$game[ 'informer' ] = '';
					}
					$game[ 'name' ] = $value;
					$data[ ] = $game;
				}
			}
			echo json_encode ( $data );
			exit;
		}
		if ( ! servers::$games[ $post[ 'game' ] ] ) {
			api::result ( l::t('Игра не найдена') );

			return false;
		}
		if ( $post[ 'go' ] == "loc" ) {
			//пришел запрос на локации
			$sql = db::q ( 'SELECT loc FROM gh_rates where power="1" and  game="' . $post[ 'game' ] . '" order by id desc' );
			if ( db::n () == 0 ) {
				api::result ( l::t('Для данной игры нет доступных локаций') );

				return false;
			} else {
				$l = array ();

				while ( $row = db::r ( $sql ) ) {
					$sql2 = db::q ( 'SELECT id,name FROM gh_location where id="' . $row[ 'loc' ] . '"' );
					while ( $row2 = db::r ( $sql2 ) ) {
						if ( ! $l[ $row2[ 'id' ] ] ) {
							//считаем cpu
							$cpuall = 0;
							$cpudel = 0;
							$sql3 = db::q ( 'SELECT id,cpu FROM gh_boxes where  power="1" and  loc="' . $row2[ 'id' ] . '"' );
							while ( $row3 = db::r ( $sql3 ) ) {
								$cpuall += $row3[ 'cpu' ];
								foreach ( servers::$games as $key => $value ) {
									if ( $key != "ts3" ) {
										$sql5 = db::q ( 'SELECT cpu FROM gh_boxes_games where box="' . $row3[ 'id' ] . '" and game="' . $key . '"' );
										$row5 = db::r ( $sql5 );
										$sql4 = db::q ( 'SELECT slots FROM gh_servers where box="' . $row3[ 'id' ] . '" and game="' . $key . '"' );
										$cpu = 0;
										while ( $row6 = db::r ( $sql4 ) ) {
											$cpu += $row6[ 'slots' ];
										}
										if ( $key == "samp" || $key == "mta" || $key == "crmp" ) {
											$a = (int) ( (int) ( $row5[ 'cpu' ] * $cpu ) ) / 10;
										} else {
											$a = (int) ( $row5[ 'cpu' ] * $cpu );
										}
										$cpudel += $a;
									}
								}
							}

							$loc = array ();
							$loc[ 'id' ] = $row2[ 'id' ];
							$loc[ 'cpu' ] = (int) ( 100 / $cpuall * $cpudel );
							$loc[ 'name' ] = $row2[ 'name' ];
							$data[ ] = $loc;
							$l[ $row2[ 'id' ] ] = 1;
						}
					}
				}
			}
		}
		if ( $post[ 'go' ] == "rate" ) {
			$sql = db::q ( 'SELECT * FROM gh_rates where  power="1" and game="' . $post[ 'game' ] . '" and loc="' . (int) $post[ 'loc' ] . '" order by id desc' );
			if ( db::n () == 0 ) {
				api::result ( l::t('Для данной игры нет доступных тарифов') );

				return false;
			} else {
				while ( $row = db::r ( $sql ) ) {
					$rate = array ();
					$rate[ 'id' ] = $row[ 'id' ];
					$rate[ 'name' ] = $row[ 'name' ];
					$rate[ 'price' ] = $row[ 'price' ];
					$rate[ 'support' ] = $row[ 'support' ];
					$rate[ 'min_slots' ] = $row[ 'min_slots' ];
					$rate[ 'max_slots' ] = $row[ 'max_slots' ];
					$ver = array ();
						if ( $row[ 'game' ] != "ts3" ) {
							if ( $row[ 'dir' ] == 1 ) {
								$versionsa = json_decode ( $row[ 'versions' ] , true );
								foreach ( $versionsa as $key => $val ) {
									$v = array ();
									$v[ 'key' ] = $key;
									$v[ 'name' ] = $val[ 'name' ];
									$ver[ ] = $v;
								}
							}
						}

					$rate[ 'vers' ] = $ver;
					$data[ ] = $rate;
				}
			}
		}
		echo json_encode ( $data );
		exit;

	}
}