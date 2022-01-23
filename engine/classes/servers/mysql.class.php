<?php

	class servers_mysql
	{
		public static $cron = false;

		public static function crate ( $user , $rate , $time )
		{
			db::q ( 'SELECT * FROM mysql_rates where id="' . $rate . '"' );
			$rate = db::r ();
			$go = "0";
			$sql2 = db::q ( 'SELECT * FROM mysql_boxes where loc="' . $rate[ 'loc' ] . '"' );
			while ( $box = db::r ( $sql2 ) ) {
				$sql3 = db::q ( 'SELECT id FROM mysql where boxes="' . $box[ 'id' ] . '"' );
				$n = db::n ( $sql3 );
				$n ++;
				if ( $box[ 'maxdb' ] > $n ) {
					$go = $box;
					break;
				}
			}
			if ( $go != 0 ) {
				if ( mysql_api::connect ( $go[ 'ip' ] , $go[ 'port' ] , $go[ 'pass' ] ) ) {
					$pass = api::generate_password ( '12' );
					$gogo = false;
					while ( true ) {
						$num = mt_rand ( 10000 , 30000 );
						$sql3 = db::q ( 'SELECT id FROM mysql where boxes="' . $go . '" and sid="' . $num . '"' );
						if ( db::n ( $sql3 ) == 0 ) {
							$gogo = $num;
							break;
						}
					}
					if ( $gogo ) {
						$rate_cfg[ 'passwd' ] = $pass;
						$rate_cfg[ 'confirm' ] = $pass;
						$rate_cfg[ 'name' ] = "u" . $go[ 'sid' ];
						$sql = "CREATE USER 'user" . $gogo . "'@'%' IDENTIFIED BY '" . $pass . "';";
						if ( mysql_api::q ( $sql ) ) {
							$sql = "GRANT USAGE ON *.* TO 'user" . $gogo . "'@'%' IDENTIFIED BY '" . $pass . "' WITH MAX_QUERIES_PER_HOUR " . $rate[ 'mqph' ] . " MAX_CONNECTIONS_PER_HOUR " . $rate[ 'mcph' ] . " MAX_UPDATES_PER_HOUR " . $rate[ 'muph' ] . " MAX_USER_CONNECTIONS  " . $rate[ 'muc' ] . ";";
							mysql_api::q ( $sql );
							$sql = "CREATE DATABASE IF NOT EXISTS `user" . $gogo . "`;";
							mysql_api::q ( $sql );
							$sql = "GRANT ALL PRIVILEGES ON `user" . $gogo . "`.* TO 'user" . $gogo . "'@'%';";
							mysql_api::q ( $sql );
							db::q (
								"INSERT INTO mysql set
															user='" . $user . "',
															time='" . $time . "',
															boxes='" . $go[ 'id' ] . "',
															sid='" . $gogo . "',
															pass='" . $pass . "',
															rate='" . $rate[ 'id' ] . "'"
							);

							return db::i ();
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		public static function buy ()
		{
			global $title , $conf;
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( api::$go ) {
					if ( api::captcha_chek () ) {
						db::q ( 'SELECT * FROM mysql_rates where power="1" and id="' . (int) $data[ 'rate' ] . '"' );
						if ( db::n () != "1" ) {
							api::result ( l::t ( 'Тариф не найден' ) );
						} else {
							$rate = db::r ();
							if ( $rate[ 'loc' ] != $data[ 'loc' ] ) {
								api::result ( l::t ( 'Локация не найден' ) );
							} else {
								if ( in_array ( $data[ 'time' ] , array ( 1 , 3 , 6 , 12 ) ) ) {
									$gogo = 0;
									$price = (int) ( $rate[ 'price' ] * $data[ 'time' ] );
									if ( ! api::admin ( 'puy_mysql' ) ) {
										if ( api::info ( 'balance' ) < $price ) {
											$gogo = 1;
											api::result ( l::t ( 'Недостаточно средств на счете' ) );
										}
									}
									if ( $gogo == 0 ) {
										$go = "0";
										$sql2 = db::q ( 'SELECT * FROM mysql_boxes where power="1" and loc="' . (int) $data[ 'loc' ] . '"' );
										while ( $box = db::r ( $sql2 ) ) {
											$sql3 = db::q ( 'SELECT id FROM mysql where boxes="' . $box[ 'id' ] . '"' );
											$n = db::n ( $sql3 );
											$n ++;
											if ( $box[ 'maxdb' ] > $n ) {
												$go = $box;
												break;
											}
										}
										if ( $go == 0 ) {
											api::result ( l::t ( 'На выбранной локации недостаточно свободного места' ) );
										} else {
											if ( mysql_api::connect ( $go[ 'ip' ] , $go[ 'port' ] , $go[ 'pass' ] ) ) {
												$pass = api::generate_password ( '12' );
												$gogo = false;
												while ( true ) {
													$num = mt_rand ( 10000 , 30000 );
													$sql3 = db::q ( 'SELECT id FROM mysql where boxes="' . $go . '" and sid="' . $num . '"' );
													if ( db::n ( $sql3 ) == 0 ) {
														$gogo = $num;
														break;
													}
												}
												if ( $gogo ) {
													$rate_cfg[ 'passwd' ] = $pass;
													$rate_cfg[ 'confirm' ] = $pass;
													$rate_cfg[ 'name' ] = "u" . $go[ 'sid' ];
													$sql = "CREATE USER 'user" . $gogo . "'@'%' IDENTIFIED BY '" . $pass . "';";
													if ( mysql_api::q ( $sql ) ) {
														$sql = "GRANT USAGE ON *.* TO 'user" . $gogo . "'@'%' IDENTIFIED BY '" . $pass . "' WITH MAX_QUERIES_PER_HOUR " . $rate[ 'mqph' ] . " MAX_CONNECTIONS_PER_HOUR " . $rate[ 'mcph' ] . " MAX_UPDATES_PER_HOUR " . $rate[ 'muph' ] . " MAX_USER_CONNECTIONS  " . $rate[ 'muc' ] . ";";
														mysql_api::q ( $sql );
														$sql = "CREATE DATABASE IF NOT EXISTS `user" . $gogo . "`;";
														mysql_api::q ( $sql );
														$sql = "GRANT ALL PRIVILEGES ON `user" . $gogo . "`.* TO 'user" . $gogo . "'@'%';";
														mysql_api::q ( $sql );
														$date = time () + 2592000 * $data[ 'time' ];
														db::q (
															"INSERT INTO mysql set
															user='" . api::info ( 'id' ) . "',
															time='" . $date . "',
															boxes='" . $go[ 'id' ] . "',
															sid='" . $gogo . "',
															pass='" . $pass . "',
															rate='" . $rate[ 'id' ] . "'"
														);

														if ( ! api::admin ( 'puy_servers' ) ) {
															$msg = "Приобретение MySQL";
															api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
															db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
														}
														api::result ( l::t ( 'Ваш заказ успешно выполнен.' ) , true );
													} else {
														api::result ( l::t ( 'Критическая ошибка' ) );
													}
												} else {
													api::result ( l::t ( 'Нет свободных мест' ) );
												}
											} else {
												api::result ( l::t ( 'Не удалось установить соединение' ) );
											}
										}
									} else {
										api::result ( l::t ( 'Критическая ошибка' ) );
									}
								}
							}
						}
					}
				} else {
					api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ));
				}
			}
			$title = l::t ( "Покупка MySQL" );
			api::nav ( "/mysql" , "MySQL" );
			api::nav ( "" , l::t ( 'Покупка' ) , '1' );
			tpl::load ( 'mysql-buy' );
			$sql = db::q ( 'SELECT * FROM mysql_rates where  power="1" order by id desc' );
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
			$loc = "";
			while ( $row = db::r ( $sql ) ) {
				$loc .= '<option value="' . $row[ 'id' ] . '">' . $row[ 'name' ] . '</option>';
			}
			tpl::set ( '{loc}' , $loc );
			tpl::set ( '{price}' , $conf[ 'mysql-price' ] );

			api::captcha_create ();
			tpl::compile ( 'content' );
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
					api::result ( l::t ( 'Пользователь не найден' ) );

					return false;
				}
			}
			if ( api::admin ( 'mysql' ) ) {
				if ( $user != 0 ) {
					db::q ( 'SELECT id FROM mysql where user="' . $user . '" order by id desc' );
				} else {
					db::q ( 'SELECT id FROM mysql order by id desc' );
				}
			} else {
				db::q ( 'SELECT id FROM mysql where user="' . api::info ( 'id' ) . '"  order by id desc' );
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
			if ( api::admin ( 'mysql' ) ) {
				if ( $user != 0 ) {
					$sql = db::q ( 'SELECT * FROM mysql where user="' . $user . '"  order by id desc LIMIT ' . $page . ' ,10' );
				} else {
					$sql = db::q ( 'SELECT * FROM mysql order by id desc LIMIT ' . $page . ' ,10' );
				}
			} else {
				$sql = db::q ( 'SELECT * FROM mysql where user="' . api::info ( 'id' ) . '" order by id desc LIMIT ' . $page . ' ,10' );
			}
			while ( $row = db::r ( $sql ) ) {
				tpl::load ( 'mysql-listen-get' );
				tpl::set ( '{id}' , $row[ 'id' ] );
				tpl::set ( '{user}' , $row[ 'user' ] );
				tpl::set ( '{date}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
				$sql2 = db::q ( 'SELECT name FROM mysql_rates where id="' . $row[ 'rate' ] . '"' );
				$row2 = db::r ( $sql2 );
				$sql3 = db::q ( 'SELECT * FROM mysql_boxes where id="' . $row[ 'boxes' ] . '"' );
				$row3 = db::r ( $sql3 );
				$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row3[ 'loc' ] . '"' );
				$row4 = db::r ( $sql2 );
				tpl::set ( '{name}' , $row2[ 'name' ] );
				tpl::set ( '{login}' , 'user' . $row[ 'sid' ] );
				tpl::set ( '{pass}' , $row[ 'pass' ] );
				tpl::set ( '{loc}' , $row4[ 'name' ] );
				tpl::set ( '{ip}' , $row3[ 'ip' ] );
				tpl::set ( '{port}' , $row3[ 'port' ] );
				tpl::set ( '{link}' , $row3[ 'link' ] );
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
			$title = "MySQL";
			if ( tpl::result ( 'data' ) ) {
				tpl::load ( 'mysql-listen' );
				if ( api::admin ( 'isp' ) ) {
					tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "\\1" );
					tpl::set_block ( "'\\[noadmins\\](.*?)\\[/noadmins\\]'si" , "" );
				} else {
					tpl::set_block ( "'\\[admins\\](.*?)\\[/admins\\]'si" , "" );
					tpl::set_block ( "'\\[noadmins\\](.*?)\\[/noadmins\\]'si" , "\\1" );
				}
				if ( $user != 0 ) {
					tpl::set ( '{nav}' , api::pagination ( $all , 10 , $pages , '/mysql/user/' . $user ) );
				} else {
					tpl::set ( '{nav}' , api::pagination ( $all , 10 , $pages , '/mysql' ) );
				}
				tpl::set ( '{data}' , tpl::result ( 'data' ) );
				tpl::compile ( 'content' );
			} else {
				api::error ( l::t ( 'У вас нет доступных услуг.' ) . ' <a href="/mysql/buy">' . l::t ( 'Заказать MySQL.' ) . '</a>' );
			}
			api::nav ( "" , "MySQL" , '1' );
		}

		public static function base ( $id )
		{
			global $title;
			$title = "MySQL";
			if ( api::admin ( 'mysql' ) ) {
				db::q ( 'SELECT * FROM mysql where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM mysql where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () == 1 ) {
				$isp = db::r ();
				$sql2 = db::q ( 'SELECT name FROM mysql_rates where id="' . $isp[ 'rate' ] . '"' );
				$row2 = db::r ( $sql2 );
				$sql3 = db::q ( 'SELECT * FROM mysql_boxes where id="' . $isp[ 'boxes' ] . '"' );
				$row3 = db::r ( $sql3 );
				$sql2 = db::q ( 'SELECT name FROM gh_location where id="' . $row3[ 'loc' ] . '"' );
				$row4 = db::r ( $sql2 );
				api::nav ( "/mysql" , "MySQL" );
				api::nav ( "" , 'user' . $isp[ 'sid' ] , '1' );
				tpl::load ( 'mysql-base' );
				tpl::set ( '{id}' , $isp[ 'id' ] );
				tpl::set ( '{rate}' , $row2[ 'name' ] );
				tpl::set ( '{user}' , 'user' . $isp[ 'sid' ] );
				tpl::set ( '{pass}' , $isp[ 'pass' ] );
				tpl::set ( '{loc}' , $row4[ 'name' ] );
				tpl::set ( '{ip}' , $row3[ 'ip' ] );
				tpl::set ( '{port}' , $row3[ 'port' ] );
				tpl::set ( '{link}' , $row3[ 'link' ] );
				tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $isp[ 'time' ] ) );
				tpl::compile ( 'content' );
			} else {
				api::result ( l::t ( 'Услуга не найдена' ) );
			}
		}

		public static function time ( $id )
		{
			global $conf;
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( api::admin ( 'mysql' ) ) {
				db::q ( 'SELECT * FROM mysql where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM mysql where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				db::q ( 'SELECT price FROM mysql_rates where id="' . $row[ 'rate' ] . '"' );
				$rate = db::r ();
				$price = $rate[ 'price' ];
				$data = $_POST[ 'data' ];
				if ( $data ) {
					if ( api::captcha_chek () ) {
						$date = (int) $data[ 'time' ];
						if ( $date == 3 || $date == 6 || $date == 12 || $date == 1 ) {
							$price = $price * $date;
						} else {
							api::result ( l::t ( 'Критическая ошибка' ) );

							return false;
						}
						$datego = 2592000 * $date;
						$balance = api::info ( 'balance' );
						$price = (int) $price;
						if ( $balance < $price and ! api::admin ( 'puy_mysql' ) ) {
							api::result ( l::t ( 'Для оплаты пополните свой счет на' ) . ' ' . (int) ( $price - $balance ) . ' ' . $conf[ 'curs-name' ] );
						} else {
							$balance = (int) ( $balance - $price );
							if ( ! api::admin ( 'puy_mysql' ) ) {
								db::q ( 'update users set balance="' . $balance . '" where id="' . api::info ( 'id' ) . '"' );
							}
							if ( $row[ 'time' ] < time () ) {
								$timego = time () + $datego;
							} else {
								$timego = $row[ 'time' ] + $datego;
							}
							db::q ( 'update mysql set time="' . $timego . '" where id="' . $id . '"' );
							if ( ! api::admin ( 'puy_mysql' ) ) {
								if ( $date == "1" ) {
									$time = l::t ( "месяц." );
								}
								if ( $date == "3" ) {
									$time = l::t ( "3 месяца." );
								}
								if ( $date == "6" ) {
									$time = l::t ( "6 месяцев." );
								}
								if ( $date == "12" ) {
									$time = l::t ( "12 месяцев." );
								}
								$msg = l::t ( "Продление mysql user" ) . $row[ 'sid' ] . " " . l::t ( 'на' ) . " " . $time;
								api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
							}
							api::result ( l::t ( 'Успешно продлен' ) , true );
						}
					}
				}
				tpl::load ( 'mysql-buy-time' );
				tpl::set ( '{price}' , $rate[ 'price' ] );
				tpl::set ( '{id}' , $id );
				api::captcha_create ();
				tpl::compile ( 'content' );
				if ( api::modal () ) {
					die( tpl::result ( 'content' ) );
				} else {
					api::nav ( "/mysql" , "MySQL" );
					api::nav ( '' , l::t ( 'Продление' ) , '1' );
				}
			} else {
				api::result ( l::t ( 'Услуга не найдена' ) );
			}
		}

		public static function pass ( $id )
		{
			if ( api::admin ( 'mysql' ) ) {
				db::q ( 'SELECT * FROM mysql where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM mysql where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () == 1 ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$row = db::r ();
				db::q ( 'SELECT * FROM mysql_boxes where id="' . $row[ 'boxes' ] . '"' );
				$box = db::r ();
				if ( mysql_api::connect ( $box[ 'ip' ] , $box[ 'port' ] , $box[ 'pass' ] ) ) {
					$pass = $_POST[ 'data' ][ 'password' ];
					if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $pass ) ) {
						api::result ( l::t ( "Новый пароль указан неверно" ) );
					} else {
						if ( mysql_api::q ( "GRANT USAGE ON *.* TO 'user" . $row[ 'sid' ] . "'@'%' IDENTIFIED BY '" . $pass . "'" ) ) {
							db::q ( 'UPDATE mysql set pass="' . api::cl ( $pass ) . '" where id="' . $id . '"' );
							api::result ( l::t ( 'Сохранено' ) , true );
						}
					}
				} else {
					api::result ( l::t ( 'Не удалось установить соединение' ) );
				}
			} else {
				api::result ( l::t ( 'Услуга не найдена' ) );
			}
		}

		public static function dell ( $id )
		{
			if ( servers_mysql::$cron ) {
				db::q ( 'SELECT * FROM mysql where id="' . $id . '"' );
				if ( db::n () == "1" ) {
					$row = db::r ();
					db::q ( 'SELECT * FROM mysql_boxes where id="' . $row[ 'boxes' ] . '"' );
					$box = db::r ();
					if ( mysql_api::connect ( $box[ 'ip' ] , $box[ 'port' ] , $box[ 'pass' ] ) ) {
						if ( mysql_api::q ( "drop user 'user" . $row[ 'sid' ] . "'@'%'" ) ) {
							mysql_api::q ( "drop database user" . $row[ 'sid' ] );
							db::q ( "delete from mysql where id='" . $id . "'" );
						}
					}
				}
			} else {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				if ( api::admin ( 'mysql_dell' ) ) {
					db::q ( 'SELECT * FROM mysql where id="' . $id . '"' );
					if ( db::n () == "1" ) {
						$row = db::r ();
						db::q ( 'SELECT * FROM mysql_boxes where id="' . $row[ 'boxes' ] . '"' );
						$box = db::r ();
						if ( mysql_api::connect ( $box[ 'ip' ] , $box[ 'port' ] , $box[ 'pass' ] ) ) {
							if ( mysql_api::q ( "drop user 'user" . $row[ 'sid' ] . "'@'%'" ) ) {
								mysql_api::q ( "drop database user" . $row[ 'sid' ] );
								db::q ( "delete from mysql where id='" . $id . "'" );
								api::result ( l::t ( 'Удалено' ) , true );
							}
						} else {
							api::result ( l::t ( 'Не удалось установить соединение' ) );
						}
					} else {
						api::result ( l::t ( 'Не найдено' ) );
					}
				} else {
					api::result ( l::t ( 'Недостаточно привелегий' ) );
				}
			}
		}

	}

	class mysql_api
	{
		public static $dbftp = false;

		public static function connect ( $ip , $port = 3306 , $pass )
		{
			self::$dbftp = @mysql_connect ( $ip . ':' . $port , 'root' , $pass );
			if ( ! self::$dbftp ) {
				return false;
			} else {
				return true;
			}
		}

		public static function q ( $query )
		{
			if ( ! self::$dbftp ) {
				return false;
			}
			if ( ! ( $query_id = mysql_query ( $query , self::$dbftp ) ) ) {
				api::result ( mysql_error () );

				return false;
			}

			return $query_id;
		}
	}
?>