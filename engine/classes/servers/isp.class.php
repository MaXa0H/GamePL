<?php

	class servers_isp
	{
		public static $cron = false;

		public static function dell ( $id )
		{
			api::inc ( 'isp-api' );
			if ( servers_isp::$cron ) {
				db::q ( 'SELECT * FROM isp where id="' . $id . '"' );
				if ( db::n () == "1" ) {
					$row = db::r ();
					db::q ( 'SELECT * FROM isp_boxes where id="' . $row[ 'boxes' ] . '"' );
					$box = db::r ();
					if ( isp_api::connect ( $box[ 'ip' ] , $box[ 'pass' ] ) ) {
						if ( isp_api::dell ( $row[ 'sid' ] ) ) {
							db::q ( "delete from isp where id='" . $id . "'" );
						}
					}
				}
			} else {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( api::admin ( 'isp' ) ) {
					db::q ( 'SELECT * FROM isp where id="' . $id . '"' );
					if ( db::n () == "1" ) {
						$row = db::r ();
						db::q ( 'SELECT * FROM isp_boxes where id="' . $row[ 'boxes' ] . '"' );
						$box = db::r ();
						if ( isp_api::connect ( $box[ 'ip' ] , $box[ 'pass' ] ) ) {
							if ( isp_api::dell ( $row[ 'sid' ] ) ) {
								db::q ( "delete from isp where id='" . $id . "'" );
								api::result ( l::t ('Удалено') , true );
							}
						} else {
							api::result ( l::t ('Не удалось установить соединение') );
						}
					} else {
						api::result ( l::t ('Не найдено') );
					}
				} else {
					api::result ( l::t ('Недостаточно привелегий') );
				}
			}
		}

		public static function pass ( $id )
		{
			if ( api::admin ( 'isp' ) ) {
				db::q ( 'SELECT * FROM isp where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM isp where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () == 1 ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$row = db::r ();
				db::q ( 'SELECT * FROM isp_boxes where id="' . $row[ 'boxes' ] . '"' );
				$box = db::r ();
				api::inc ( 'isp-api' );
				if ( isp_api::connect ( $box[ 'ip' ] , $box[ 'pass' ] ) ) {
					$pass = $_POST[ 'data' ][ 'password' ];
					if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $pass ) ) {
						api::result ( l::t ("Новый пароль указан неверно") );
					} else {
						db::q ( 'SELECT * FROM isp_rates where id="' . $row[ 'rate' ] . '"' );
						$rate = db::r ();
						$rate_cfg = json_decode ( base64_decode ( $rate[ 'cfg' ] ) , true );
						$rate_cfg[ 'passwd' ] = $pass;
						$rate_cfg[ 'confirm' ] = $pass;
						$rate_cfg[ 'name' ] = "u" . $row[ 'sid' ];
						$rate_cfg[ 'elid' ] = "u" . $row[ 'sid' ];
						if ( isp_api::install ( $rate_cfg,$box['version']) ) {
							db::q ( 'UPDATE isp set pass="' . api::cl ( $pass ) . '" where id="' . $id . '"' );
							api::result ( l::t ('Сохранено') , true );
						}
					}
				} else {
					api::result ( l::t ('Не удалось установить соединение') );
				}
			} else {
				api::result ( l::t ('Услуга не найдена') );
			}
		}

		public static function time ( $id )
		{
			global $conf;
			if ( api::admin ( 'isp' ) ) {
				db::q ( 'SELECT * FROM isp where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM isp where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () == 1 ) {

				$row = db::r ();
				db::q ( 'SELECT price FROM isp_rates where id="' . $row[ 'rate' ] . '"' );
				$rate = db::r ();
				if ( $rate[ 'free' ] == "1" ) {
					api::result ( l::t ('Данная функция отключена для бесплатной услуги') );
				}
				$price = $rate[ 'price' ];
				$data = $_POST[ 'data' ];
				if ( $data ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					if ( api::captcha_chek () ) {
						$date = (int) $data[ 'time' ];
						if ( $date == 3 || $date == 6 || $date == 12 || $date == 1 ) {
							$price = $price * $date;
						} else {
							api::result ( l::t ('Критическая ошибка') );

							return false;
						}
						$datego = 2592000 * $date;
						$balance = api::info ( 'balance' );
						$price = (int) $price;
						if ( $balance < $price and ! api::admin ( 'puy_isp' ) ) {
							api::result ( l::t ('Для оплаты пополните свой счет на')." " . (int) ( $price - $balance ) . ' '.$conf['curs-name'] );
						} else {
							$balance = (int) ( $balance - $price );
							if ( ! api::admin ( 'puy_isp' ) ) {
								db::q ( 'update users set balance="' . $balance . '" where id="' . api::info ( 'id' ) . '"' );
							}
							if ( $row[ 'time' ] < time () ) {
								$timego = time () + $datego;
							} else {
								$timego = $row[ 'time' ] + $datego;
							}
							db::q ( 'update isp set time="' . $timego . '" where id="' . $id . '"' );
							if ( ! api::admin ( 'puy_isp' ) ) {
								if ( $date == "1" ) {
									$time = l::t ("месяц.");
								}
								if ( $date == "3" ) {
									$time = l::t ("3 месяця.");
								}
								if ( $date == "6" ) {
									$time = l::t ("6 месяцев.");
								}
								if ( $date == "12" ) {
									$time = l::t ("12 месяцев.");
								}
								$msg = l::t ("Продление Web хостинга u") . $id . " ".l::t ('на')." " . $time;
								api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
							}
							api::result ( l::t ('Успешно продлен') , true );
						}
					}
				}
				tpl::load ( 'isp-buy-time' );
				tpl::set ( '{price}' , $rate[ 'price' ] );
				tpl::set ( '{id}' , $id );
				api::captcha_create ();
				tpl::compile ( 'content' );
				if ( api::modal () ) {
					die( tpl::result ( 'content' ) );
				} else {
					api::nav ( "/web" , l::t ("Web хостинг") );
					api::nav ( '' , l::t ('Продление') , '1' );
				}
			} else {
				api::result ( l::t ('Услуга не найдена') );
			}
		}

		public static function base ( $id )
		{
			global $title;
			$title = l::t ("Web хостинг");
			if ( api::admin ( 'isp' ) ) {
				db::q ( 'SELECT * FROM isp where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM isp where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () == 1 ) {
				$isp = db::r ();
				$sql2 = db::q ( 'SELECT name FROM isp_rates where id="' . $isp[ 'rate' ] . '"' );
				$row2 = db::r ( $sql2 );
				$sql3 = db::q ( 'SELECT * FROM isp_boxes where id="' . $isp[ 'boxes' ] . '"' );
				$row3 = db::r ( $sql3 );
				$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row3[ 'loc' ] . '"' );
				$row4 = db::r ( $sql2 );
				api::nav ( "/web" , l::t ("Web хостинг") );
				api::nav ( "" , $row2[ 'name' ] . ' - u' . $isp[ 'sid' ] , '1' );
				tpl::load ( 'isp-base' );
				tpl::set ( '{id}' , $isp[ 'id' ] );
				tpl::set ( '{rate}' , $row2[ 'name' ] );
				tpl::set ( '{user}' , 'u' . $isp[ 'sid' ] );
				tpl::set ( '{pass}' , $isp[ 'pass' ] );
				tpl::set ( '{loc}' , $row4[ 'name' ] );
				tpl::set ( '{link}' , 'https://' . $row3[ 'ip' ] . '/ispmgr' );
				if ( $row2[ 'free' ] == 1 ) {
					tpl::set ( '{time}' , l::t ('До конца аренды серверов') );
				} else {
					tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $isp[ 'time' ] ) );
				}
				tpl::compile ( 'content' );
			} else {
				api::result ( l::t ('Услуга не найдена') );
			}
		}

		public static function listen ()
		{
			global $title,$conf;
			if(!$conf['dell']){
				$conf['dell'] = 3;
			}
			if ( r::g ( 1 ) == "user" ) {
				$user = (int) r::g ( 2 );
				$pages = (int) r::g ( 4 );
			} else {
				$pages = (int) r::g ( 2 );
			}
			if ( $user != 0 ) {
				db::q ( 'SELECT * FROM users where id="' . $user . '"' );
				if ( db::n () == 0 ) {
					api::result ( l::t ('Пользователь не найден') );

					return false;
				}
			}
			if ( api::admin ( 'isp' ) ) {
				if ( $user != 0 ) {
					db::q ( 'SELECT id FROM isp where user="' . $user . '" order by id desc' );
				} else {
					db::q ( 'SELECT id FROM isp order by id desc' );
				}
			} else {
				db::q ( 'SELECT id FROM isp where user="' . api::info ( 'id' ) . '"  order by id desc' );
			}
			$all = db::n ();
			if ( $pages ) {
				if ( ( $all / 10 ) > $pages ) {
					$page = 10 * $pages;
				} else {
					$page = 0;
				}
			} else {
				$page = 0;
			}
			if ( api::admin ( 'isp' ) ) {
				if ( $user != 0 ) {
					$sql = db::q ( 'SELECT * FROM isp where user="' . $user . '"  order by id desc LIMIT ' . $page . ' ,10' );
				} else {
					$sql = db::q ( 'SELECT * FROM isp order by id desc LIMIT ' . $page . ' ,10' );
				}
			} else {
				$sql = db::q ( 'SELECT * FROM isp where user="' . api::info ( 'id' ) . '" order by id desc LIMIT ' . $page . ' ,10' );
			}
			while ( $row = db::r ( $sql ) ) {
				tpl::load ( 'isp-listen-get' );
				tpl::set ( '{id}' , $row[ 'id' ] );
				$sql2 = db::q ( 'SELECT name,free FROM isp_rates where id="' . $row[ 'rate' ] . '"' );
				$row2 = db::r ( $sql2 );
				tpl::set ( '{user}' , $row[ 'user' ] );
				tpl::set ( '{name}' , $row2[ 'name' ] );
				$sql3 = db::q ( 'SELECT * FROM isp_boxes where id="' . $row[ 'boxes' ] . '"' );
				$row3 = db::r ( $sql3 );
				$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row3[ 'loc' ] . '"' );
				$row4 = db::r ( $sql2 );
				tpl::set ( '{login}' , 'u' . $row[ 'sid' ] );
				tpl::set ( '{pass}' , $row[ 'pass' ] );
				tpl::set ( '{loc}' , $row4[ 'name' ] );
				tpl::set ( '{link}' , 'https://' . $row3[ 'ip' ] . '/ispmgr' );
				if ( $row2[ 'free' ] == 1 ) {
					tpl::set ( '{date}' , l::t ('До конца аренды серверов') );
				} else {
					tpl::set ( '{date}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
				}
				if($row[ 'time' ]>time()){
					$n = (int)(($row[ 'time' ]-time())/(3600*24));
					$date2 = $n.' '.api::getNumEnding($n,array(l::t ('день'),l::t ('дня'),l::t ('дней')));
				}else{
					$n = $conf['dell']-(int)((time()-$row[ 'time' ])/(3600*24));
					$date2 = $n.' '.api::getNumEnding($n,array(l::t ('день'),l::t ('дня'),l::t ('дней'))).' '.l::t ('до удаления');
				}
				tpl::set ( '{date2}' , $date2 );
				tpl::compile ( 'data' );
			}
			$title = l::t ("Web хостинг");
			if ( tpl::result ( 'data' ) ) {
				tpl::load ( 'isp-listen' );
				if ( api::admin ( 'isp' ) ) {
					tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "\\1" );
					tpl::set_block ( "'\\[noadmins\\](.*?)\\[/noadmins\\]'si" , "" );
				} else {
					tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "" );
					tpl::set_block ( "'\\[noadmins\\](.*?)\\[/noadmins\\]'si" , "\\1" );
				}
				if ( $user != 0 ) {
					tpl::set ( '{nav}' , api::pagination ( $all , 10 , $pages , '/web/user/'.$user ) );
				}else{
					tpl::set ( '{nav}' , api::pagination ( $all , 10 , $pages , '/web' ) );
				}
				tpl::set ( '{data}' , tpl::result ( 'data' ) );
				tpl::compile ( 'content' );
			} else {
				api::error ( l::t ('У вас нет доступных услуг.').' <a href="/web/buy">'.l::t ('Заказать Web хостинг.').'</a>' );
			}
			api::nav ( "" , l::t ("Web хостинг") , '1' );
		}

		public static function crate ($user,$rate,$time){
			api::inc ( 'isp-api' );
			db::q ( 'SELECT * FROM isp_rates where id="' . $rate. '"' );
			$rate = db::r ();
			$rate_cfg = json_decode ( base64_decode ( $rate[ 'cfg' ] ) , true );
			$sql2 = db::q ( 'SELECT * FROM isp_boxes where loc="' . $rate[ 'loc' ] . '"' );
			while ( $box = db::r ( $sql2 ) ) {
				$sql3 = db::q ( 'SELECT id FROM isp where boxes="' . $box[ 'id' ] . '"' );
				$n = db::n ( $sql3 );
				$n ++;
				if ( $box[ 'disklimit' ] > ( $rate_cfg[ 'disklimit' ] * $n ) ) {
					$go = $box;
					break;
				}
			}
			if ( $go == 0 ) {
				return false;
			} else {
				if ( isp_api::connect ( $go[ 'ip' ] , $go[ 'pass' ] ) ) {
					$pass = api::generate_password ( '12' );
					$rate_cfg[ 'passwd' ] = $pass;
					$rate_cfg[ 'confirm' ] = $pass;
					$rate_cfg[ 'name' ] = "u" . $go[ 'sid' ];
					if ( isp_api::install ( $rate_cfg,$box['version']) ) {
						db::q (
							"INSERT INTO isp set
														rate='" . (int) $rate[ 'id' ] . "',
														user='" . $user . "',
														time='" . $time . "',
														boxes='" . $go[ 'id' ] . "',
														sid='" . $go[ 'sid' ] . "',
														pass='" . $pass . "'"
						);
						$id = db::i();
						db::q ( 'UPDATE isp_boxes set sid="' . ( $go[ 'sid' ] + 1 ) . '" where id="' . $go[ 'id' ] . '"' );
						$s = explode ( ":" , trim ( $go[ 'ip' ] ) );
						$go[ 'ip' ] = $s[ 0 ];
						$sql2 = db::q ( 'SELECT * FROM gh_boxes where ip="' . $go[ 'ip' ] . '"' );
						$row2 = db::r ();
						db::q ( 'UPDATE gh_boxes set server="' . ( $row2[ 'server' ] + 1 ) . '" where ip="' . $go[ 'ip' ] . '"' );
						return $id;
					}
				} else {
					return false;
				}
			}
		}

		public static function buy ()
		{
			global $title;
			api::inc ( 'isp-api' );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( api::$go ) {
					if ( api::captcha_chek () ) {
						db::q ( 'SELECT * FROM isp_rates where power="1" and id="' . (int) $data[ 'rate' ] . '"' );
						if ( db::n () != "1" ) {
							api::result ( l::t ('Тариф не найден') );
						} else {
							$rate = db::r ();
							$rate_cfg = json_decode ( base64_decode ( $rate[ 'cfg' ] ) , true );
							if ( $rate[ 'loc' ] != $data[ 'loc' ] ) {
								api::result ( l::t ('Локация не найдена') );
							} else {
								if ( in_array ( $data[ 'time' ] , array ( 1 , 3 , 6 , 12 ) ) ) {
									$gogo = 0;
									$price = api::price( $rate[ 'price' ] * $data[ 'time' ] );
									if ( ! api::admin ( 'puy_isp' ) ) {
										if ( $rate[ 'free' ] == "1" ) {
											db::q ( 'SELECT * FROM gh_servers where user="' . api::info ( 'id' ) . '"' );
											if ( db::n () == 0 ) {
												$gogo = 1;
												api::result ( l::t ('Данная услуга доступна при наличии игрового сервера') );
											} else {
												db::q ( 'SELECT * FROM isp where rate="' . (int) $data[ 'rate' ] . '"' );
												if ( db::n () == 1 ) {
													$gogo = 1;
													api::result ( l::t ('У вас уже есть данная услуга') );
												}
											}
										} else {
											if ( api::info ( 'balance' ) < $price ) {
												$gogo = 1;
												api::result ( l::t ('Недостаточно средств на счете') );
											}
										}
									}
									if ( $gogo == 0 ) {
										$go = "0";
										$sql2 = db::q ( 'SELECT * FROM isp_boxes where power="1" and loc="' . (int) $data[ 'loc' ] . '"' );
										while ( $box = db::r ( $sql2 ) ) {
											$sql3 = db::q ( 'SELECT id FROM isp where boxes="' . $box[ 'id' ] . '"' );
											$n = db::n ( $sql3 );
											$n ++;
											if ( $box[ 'disklimit' ] > ( $rate_cfg[ 'disklimit' ] * $n ) ) {
												$go = $box;
												break;
											}
										}
										if ( $go == 0 ) {
											api::result ( l::t ('На выбранной локации недостаточно свободного места') );
										} else {
											if ( isp_api::connect ( $go[ 'ip' ] , $go[ 'pass' ] ) ) {
												$pass = api::generate_password ( '12' );
												$rate_cfg[ 'passwd' ] = $pass;
												$rate_cfg[ 'confirm' ] = $pass;
												$rate_cfg[ 'name' ] = "u" . $go[ 'sid' ];
												if ( isp_api::install ( $rate_cfg,$box['version']) ) {
													$date = time () + 2592000 * $data[ 'time' ];
													db::q (
														"INSERT INTO isp set
														rate='" . (int) $data[ 'rate' ] . "',
														user='" . api::info ( 'id' ) . "',
														time='" . $date . "',
														boxes='" . $go[ 'id' ] . "',
														sid='" . $go[ 'sid' ] . "',
														pass='" . $pass . "'"
													);
													db::q ( 'UPDATE isp_boxes set sid="' . ( $go[ 'sid' ] + 1 ) . '" where id="' . $go[ 'id' ] . '"' );
													$s = explode ( ":" , trim ( $go[ 'ip' ] ) );
													$go[ 'ip' ] = $s[ 0 ];

													$sql2 = db::q ( 'SELECT * FROM gh_boxes where ip="' . $go[ 'ip' ] . '"' );
													$row2 = db::r ();
													db::q ( 'UPDATE gh_boxes set server="' . ( $row2[ 'server' ] + 1 ) . '" where ip="' . $go[ 'ip' ] . '"' );
													if ( ! api::admin ( 'puy_servers' ) ) {
														if ( $rate[ 'free' ] != "1" ) {
															$msg = l::t ("Приобретение Web хостинга") ." ".$rate['name'];
															api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
															db::q ( 'UPDATE users set balance="' . api::price( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
														}
													}
													api::result ( l::t ('Ваш заказ успешно выполнен.') , true );
												}
											} else {
												api::result ( l::t ('Не удалось установить соединение') );
											}
										}
									} else {
										api::result ( l::t ('Критическая ошибка' ));
									}
								} else {
									api::result (l::t ( 'Критическая ошибка') );
								}
							}
						}
					}
				} else {
					api::result ( l::t ('Для доступа к данной странице нужно войти на сайт') );
				}
			}
			$title = l::t ("Покупка Web хостинга");
			api::nav ( "/web" , l::t ("Web хостинг") );
			api::nav ( "" , l::t ('Покупка') , '1' );
			tpl::load ( 'isp-buy' );
			$sql = db::q ( 'SELECT * FROM isp_rates where power="1" order by id desc' );
			$data = "";
			while ( $row = db::r ( $sql ) ) {
				$d = "";
				$d[ 'loc' ] = $row[ 'loc' ];
				$d[ 'id' ] = $row[ 'id' ];
				$d[ 'price' ] = $row[ 'price' ];
				$d[ 'name' ] = $row[ 'name' ];
				$data[ ] = $d;
			}
			tpl::set ( '{data}' , json_encode ( $data ) );
			$sql = db::q ( 'SELECT * FROM gh_location order by id asc' );
			$loc ="";
			while ( $row = db::r ( $sql ) ) {
				$loc .= '<option value="' . $row[ 'id' ] . '">' . $row[ 'name' ] . '</option>';
			}
			tpl::set ( '{loc}' , $loc );
			api::captcha_create ();
			tpl::compile ( 'content' );
		}
	}
?>