<?php

	class servers_sale
	{
		public static function buy ( $id )
		{
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			if ( db::n () == 1 ) {
				$row = db::r ();
				$class = servers::game_class ( $row[ 'game' ] );
				$sql = db::q ( 'SELECT * FROM gh_servers_admins_rates where server="' . $id . '" order by id desc' );
				if ( db::n () == 0 ) {
					api::result ( l::t ( 'Нет доступных услуг' ) );
				} else {
					$data = $_POST[ 'data' ];
					if ( $data ) {
						if(api::$demo){
							api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
							return false;
						}
						if ( api::$go ) {
							if ( api::captcha_chek () ) {
								db::q ( 'SELECT * FROM gh_servers_admins_rates where id="' . (int) $data[ 'rate' ] . '"' );
								if ( db::n () != "1" ) {
									api::result ( l::t ( 'Услуга не найдена!' ) );
								} else {
									$rate = db::r ();
									$val = array ( 2 , 1 , 3 );
									if ( ! in_array ( $data[ 'type' ] , $val ) ) {
										api::result ( l::t ( 'Критическая ошибка!' ) );
									} else {
										$gogo = false;
										if ( $data[ 'type' ] == 1 ) {
											if ( ! preg_match ( "/^STEAM_[0-9]{1}:[0-9]{1}:[0-9]{4,20}$/i" , trim ( $data[ 'name' ] ) ) ) {
												api::result ( l::t ( "SteamID указан неверно!" ) );
											} else {
												if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $data[ 'pass' ] ) ) {
													api::result ( l::t ( "Пароль указан неверно" ) );
												} else {
													$gogo = true;
												}
											}
										} elseif ( $data[ 'type' ] == 2 ) {
											if ( ! preg_match ( "/^[0-9a-zA-Z\[\]\-\=\.\-\+\|\ \(\)\/\!\?]{3,40}$/i" , trim ( $data[ 'name' ] ) ) ) {
												api::result ( l::t ( "Ник указан неверно, допустимые символы 0-9 a-Z ()|/[]-=.-+!?" ) );
											} else {
												if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $data[ 'pass' ] ) ) {
													api::result ( l::t ( "Пароль указан неверно" ) );
												} else {
													$gogo = true;
												}
											}
										} elseif ( $data[ 'type' ] == 3 ) {
											$gogo = true;
										}
										$val = array ( 2 , 1 , 3 );
										if ( ! in_array ( $data[ 'time' ] , $val ) ) {
											api::result ( l::t ( 'Критическая ошибка!' ) );
											$gogo = false;
										}
										if ( $gogo ) {
											$price = api::price ( (int) $data[ 'time' ] * $rate[ 'price' ] );
											$date = time () + 2592000 * $data[ 'time' ];
											if ( api::info ( 'balance' ) < $price ) {
												api::result ( l::t ( 'Пополните счет на' ) . ' ' . ( $price - api::info ( 'balance' ) ) );
											} else {
												if ( $data[ 'type' ] == 3 ) {
													$dop = "login='" . api::cl ( $_SERVER[ 'REMOTE_ADDR' ] ) . "',";
												} else {
													$dop = "login='" . api::cl ( $data[ 'name' ] ) . "',";
													$dop .= "pass='" . api::cl ( $data[ 'pass' ] ) . "',";
												}
												db::q (
													"INSERT INTO gh_servers_admins set
															user='" . api::info ( 'id' ) . "',
															server='" . $id . "',
															type='" . (int) $data[ 'type' ] . "',
															rate='" . (int) $data[ 'rate' ] . "',
															" . $dop . "
															time='" . $date . "'"
												);
												$msg = l::t ( "Приобретение" ) . ' ' . $rate[ 'name' ] . " " . l::t ( 'на сервере #' ) . " " . $id;
												api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
												db::q ( 'UPDATE users set balance="' . ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
												db::q ( 'SELECT * FROM users where id="' . $row[ 'user' ] . '"' );
												$user = db::r ();
												api::log_balance ( $row[ 'user' ] , $msg , '0' , $price );
												db::q ( 'UPDATE users set balance="' . ( $user[ 'balance' ] + $price ) . '" where id="' . $row[ 'user' ] . '"' );
												$class::admins_reload ( $id );
												api::result ( l::t ( 'Успешно' ) , 1 );
											}
										} else {
											api::result ( l::t ( 'Критическая ошибка!' ) );
										}

									}
								}
							}
						} else {
							api::result ( l::t ( 'Для доступа к данной странице нужно войти на сайт' ) );
						}
					}
					$data = "";
					while ( $row1 = db::r ( $sql ) ) {
						$d = "";
						$d[ 'id' ] = $row1[ 'id' ];
						$d[ 'price' ] = $row1[ 'price' ];
						$d[ 'name' ] = $row1[ 'name' ];
						$data[ ] = $d;
					}

					tpl::load ( 'servers-sale-buy-cs' );

					tpl::set ( '{data}' , json_encode ( $data ) );
					tpl::set ( '{id}' , $id );
					api::captcha_create ();
					tpl::compile ( 'content' );
					if ( api::modal () ) {
						die( tpl::result ( 'content' ) );
					} else {
						exit;
					}
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

		public static function add ( $id )
		{
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'sale' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
				api::nav ( "/servers" , l::t ( "Серверы" ) );
				api::nav ( "/servers/base/" . $id , $adress );
				api::nav ( "/servers/sale/" . $id , l::t ( 'Админки / VIP' ) );
				api::nav ( "/servers/sale/rates/" . $id , l::t ( 'Услуги' ) );
				api::nav ( "" , l::t ( 'Добавление' ) , 1 );
				servers::$speedbar = $id;
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'sale' ) ) {
					if ( $row[ 'time' ] < time () ) {
						api::result ( l::t ( 'Срок аренды сервера истек' ) );
					} else {
						$data = $_POST[ 'data' ];
						$sql = db::q ( 'SELECT * FROM gh_servers_admins_rates where server="' . $id . '" order by id desc' );
						if ( $data ) {
							if(api::$demo){
								api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
								return false;
							}
							$val = array ( 2 , 1 , 3 );
							if ( ! in_array ( $data[ 'type' ] , $val ) ) {
								api::result ( l::t ( 'Критическая ошибка!' ) );
							} else {
								$gogo = false;
								if ( $data[ 'type' ] == 1 ) {
									if ( ! preg_match ( "/^STEAM_[0-9]{1}:[0-9]{1}:[0-9]{4,20}$/i" , trim ( $data[ 'name' ] ) ) ) {
										api::result ( l::t ( "SteamID указан неверно!" ) );
									} else {
										if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $data[ 'pass' ] ) ) {
											api::result ( l::t ( "Пароль указан неверно" ) );
										} else {
											$gogo = true;
										}
									}
								} elseif ( $data[ 'type' ] == 2 ) {
									if ( ! preg_match ( "/^[0-9a-zA-Z\[\]\-\=\.\-\+\|\ \(\)\/\!\?]{3,40}$/i" , trim ( $data[ 'name' ] ) ) ) {
										api::result ( l::t ( "Ник указан неверно, допустимые символы 0-9 a-Z ()|/[]-=.-+!?" ) );
									} else {
										if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $data[ 'pass' ] ) ) {
											api::result ( l::t ( "Пароль указан неверно" ) );
										} else {
											$gogo = true;
										}
									}
								} elseif ( $data[ 'type' ] == 3 ) {
									if ( ! preg_match ( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i" , trim ( $data[ 'name' ] ) ) ) {
										api::result ( l::t ( "IP адрес указан неверно!" ) );
									} else {
										$gogo = true;
									}
								}
								$date = api::cl ( $data[ 'time' ] );
								if ( ! preg_match ( "/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/i" , $date ) ) {
									api::result ( l::t ( 'Формат даты неверный!' ) );

									return false;
								}
								$pack = explode ( "/" , $date );
								$date = mktime ( '23' , 0 , 0 , $pack[ '1' ] , $pack[ '0' ] , $pack[ '2' ] );
								if ( $gogo ) {
									if ( $data[ 'type' ] == 3 ) {
										$dop = "login='" . api::cl ( $data[ 'name' ] ) . "',";
										$dop .= "pass='',";
									} else {
										$dop = "login='" . api::cl ( $data[ 'name' ] ) . "',";
										$dop .= "pass='" . api::cl ( $data[ 'pass' ] ) . "',";
									}
									db::q (
										"INSERT INTO gh_servers_admins set
															user='" . api::info ( 'id' ) . "',
															server='" . $id . "',
															type='" . (int) $data[ 'type' ] . "',
															rate='" . (int) $data[ 'rate' ] . "',
															" . $dop . "
															time='" . $date . "'"
									);
									$class::admins_reload ( $id );
									api::result ( l::t ( 'Сохранено' ) , 1 );
								}
							}
						}
						$data = "";
						while ( $row1 = db::r ( $sql ) ) {
							$d = "";
							$d[ 'id' ] = $row1[ 'id' ];
							$d[ 'price' ] = $row1[ 'price' ];
							$d[ 'name' ] = $row1[ 'name' ];
							$data[ ] = $d;
						}

						tpl::load ( 'servers-sale-add-cs' );

						tpl::set ( '{server}' , $id );
						tpl::set ( '{time}' , api::langdate ( "j/m/Y" , time () ) );
						tpl::set ( '{data}' , json_encode ( $data ) );
						tpl::compile ( 'content' );
						if ( api::modal () ) {
							die( tpl::result ( 'content' ) );
						} else {
							exit;
						}
					}
				} else {
					api::result ( l::t ( 'Данная функция отключена' ) );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

		public static function edit ( $id , $order )
		{
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'sale' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
				api::nav ( "/servers" , l::t ( "Серверы" ) );
				api::nav ( "/servers/base/" . $id , $adress );
				api::nav ( "/servers/sale/" . $id , l::t ( 'Админки / VIP' ) );
				api::nav ( "/servers/sale/rates/" . $id , l::t ( 'Услуги' ) );
				api::nav ( "" , l::t ( 'Редактирование' ) , 1 );
				servers::$speedbar = $id;
				$class = servers::game_class ( $row[ 'game' ] );
				db::q ( "SELECT * FROM gh_servers_admins where id='" . $order . "' and server='" . $id . "'" );
				if ( db::n () == "1" ) {
					$rate = db::r ();
					if ( $class::info ( 'sale' ) ) {
						if ( $row[ 'time' ] < time () ) {
							api::result ( l::t ( 'Срок аренды сервера истек' ) );
						} else {
							$sql = db::q ( 'SELECT * FROM gh_servers_admins_rates where server="' . $id . '" order by id desc' );
							$data = $_POST[ 'data' ];
							if ( $data ) {
								if(api::$demo){
									api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
									return false;
								}
								$val = array ( 2 , 1 , 3 );
								if ( ! in_array ( $data[ 'type' ] , $val ) ) {
									api::result ( l::t ( 'Критическая ошибка!' ) );
								} else {
									$gogo = false;
									if ( $data[ 'type' ] == 1 ) {
										if ( ! preg_match ( "/^STEAM_[0-9]{1}:[0-9]{1}:[0-9]{4,20}$/i" , trim ( $data[ 'name' ] ) ) ) {
											api::result ( l::t ( "SteamID указан неверно!" ) );
										} else {
											if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $data[ 'pass' ] ) ) {
												api::result ( l::t ( "Пароль указан неверно" ) );
											} else {
												$gogo = true;
											}
										}
									} elseif ( $data[ 'type' ] == 2 ) {
										if ( ! preg_match ( "/^[0-9a-zA-Z\[\]\-\=\.\-\+\|\ \(\)\/\!\?]{3,40}$/i" , trim ( $data[ 'name' ] ) ) ) {
											api::result ( l::t ( "Ник указан неверно, допустимые символы 0-9 a-Z ()|/[]-=.-+!?" ) );
										} else {
											if ( ! preg_match ( "/^[0-9a-zA-Z]{6,20}$/i" , $data[ 'pass' ] ) ) {
												api::result ( l::t ( "Пароль указан неверно" ) );
											} else {
												$gogo = true;
											}
										}
									} elseif ( $data[ 'type' ] == 3 ) {
										if ( ! preg_match ( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i" , trim ( $data[ 'name' ] ) ) ) {
											api::result ( l::t ( "IP адрес указан неверно!" ) );
										} else {
											$gogo = true;
										}
									}
									$date = api::cl ( $data[ 'time' ] );
									if ( ! preg_match ( "/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/i" , $date ) ) {
										api::result ( l::t ( 'Формат даты неверный!' ) );

										return false;
									}
									$pack = explode ( "/" , $date );
									$date = mktime ( '23' , 0 , 0 , $pack[ '1' ] , $pack[ '0' ] , $pack[ '2' ] );
									if ( $gogo ) {
										if ( $data[ 'type' ] == 3 ) {
											$dop = "login='" . api::cl ( $data[ 'name' ] ) . "',";
											$dop .= "pass='',";
										} else {
											$dop = "login='" . api::cl ( $data[ 'name' ] ) . "',";
											$dop .= "pass='" . api::cl ( $data[ 'pass' ] ) . "',";
										}
										db::q (
											"UPDATE gh_servers_admins set
															server='" . $id . "',
															type='" . (int) $data[ 'type' ] . "',
															rate='" . (int) $data[ 'rate' ] . "',
															" . $dop . "
															time='" . $date . "' where id='" . $rate[ 'id' ] . "'"
										);
										$class::admins_reload ( $id );
										api::result ( l::t ( 'Сохранено' ) , 1 );
									}
								}
							}
							$data = "";
							while ( $row1 = db::r ( $sql ) ) {
								$d = "";
								$d[ 'id' ] = $row1[ 'id' ];
								$d[ 'price' ] = $row1[ 'price' ];
								$d[ 'name' ] = $row1[ 'name' ];
								$data[ ] = $d;
							}
							tpl::load ( 'servers-sale-edit-cs' );

							tpl::set ( '{id}' , $rate[ 'id' ] );
							tpl::set ( '{server}' , $id );
							tpl::set ( '{name}' , $rate[ 'login' ] );
							tpl::set ( '{pass}' , $rate[ 'pass' ] );
							tpl::set ( '{rate}' , $rate[ 'rate' ] );
							tpl::set ( '{type}' , $rate[ 'type' ] );
							tpl::set ( '{time}' , api::langdate ( "j/m/Y" , $rate[ 'time' ] ) );
							tpl::set ( '{data}' , json_encode ( $data ) );
							tpl::compile ( 'content' );
							if ( api::modal () ) {
								die( tpl::result ( 'content' ) );
							} else {
								exit;
							}
						}
					} else {
						api::result ( l::t ( 'Данная функция отключена' ) );
					}
				} else {
					api::result ( l::t ( 'Услуга не найдена!' ) );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

		public static function base ( $id )
		{
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'sale' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
				api::nav ( "/servers" , l::t ( "Серверы" ) );
				api::nav ( "/servers/base/" . $id , $adress );
				api::nav ( "" , l::t ( 'Админки / VIP' ) , '1' );
				servers::$speedbar = $id;
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'sale' ) ) {
					if ( $row[ 'time' ] < time () ) {
						api::result ( l::t ( 'Срок аренды сервера истек' ) );
					} else {
						tpl::load ( 'servers-sale-listen' );
						$sql = db::q ( 'SELECT * FROM gh_servers_admins where server="' . $id . '" order by id desc' );
						while ( $row2 = db::r ( $sql ) ) {
							tpl::load ( 'servers-sale-listen-get' );
							db::q ( 'SELECT * FROM users where id="' . $row2[ 'user' ] . '"' );
							$row1 = db::r ();
							tpl::set ( '{user}' , $row1[ 'mail' ] );
							tpl::set ( '{id}' , $row2[ 'id' ] );
							tpl::set ( '{server}' , $id );
							tpl::set ( '{name}' , $row2[ 'login' ] );
							db::q ( 'SELECT * FROM gh_servers_admins_rates where id="' . $row2[ 'rate' ] . '"' );
							$row3 = db::r ();
							tpl::set ( '{rate}' , $row3[ 'name' ] );
							tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $row2[ 'time' ] ) );
							tpl::compile ( 'data' );
						};
						tpl::set ( '{id}' , $row[ 'id' ] );
						tpl::set ( '{data}' , tpl::result ( 'data' ) );
						tpl::compile ( 'content' );
					}
				} else {
					api::result ( l::t ( 'Данная функция отключена' ) );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}

		public static function dell ( $id , $rate )
		{
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( api::admin ( 'servers' ) ) {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
			}
			if ( db::n () != 1 ) {
				if ( ! servers::friend ( $id , 'sale' ) ) {
					api::result ( l::t ( 'Недостаточно привилегий!' ) );

					return false;
				} else {
					db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
				}
			}
			if ( db::n () == 1 ) {
				$row = db::r ();
				$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
				api::nav ( "/servers" , l::t ( "Серверы" ) );
				api::nav ( "/servers/base/" . $id , $adress );
				api::nav ( "/servers/sale/" . $id , l::t ( 'Админки / VIP' ) );
				api::nav ( "/servers/sale/rates/" . $id , l::t ( 'Услуги' ) );
				api::nav ( "" , l::t ( 'Добавление' ) , 1 );
				servers::$speedbar = $id;
				$class = servers::game_class ( $row[ 'game' ] );
				db::q ( "SELECT * FROM gh_servers_admins where id='" . $rate . "' and server='" . $id . "'" );
				if ( db::n () == "1" ) {
					if ( $class::info ( 'sale' ) ) {
						if ( $row[ 'time' ] < time () ) {
							api::result ( l::t ( 'Срок аренды сервера истек' ) );
						} else {
							db::q ( 'DELETE from gh_servers_admins where id="' . $rate . '"' );
							api::result ( l::t ( 'Удалено' ) , true );
						}
					} else {
						api::result ( l::t ( 'Данная функция отключена' ) );
					}
				} else {
					api::result ( l::t ( 'Услуга не найдена!' ) );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		}
	}
?>