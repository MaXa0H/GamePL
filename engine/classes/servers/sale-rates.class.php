<?php

	class servers_sale_rates
	{
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
				api::nav ( "/servers/sale/" . $id , l::t ( 'Админки / VIP' ) );
				api::nav ( "" , l::t ( 'Услуги' ) , 1 );
				servers::$speedbar = $id;
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'sale' ) ) {
					if ( $row[ 'time' ] < time () ) {
						api::result ( l::t ( 'Срок аренды сервера истек' ) );
					} else {
						$sql = db::q ( 'SELECT * FROM gh_servers_admins_rates where server="' . $id . '" order by id desc' );
						while ( $row2 = db::r ( $sql ) ) {
							tpl::load ( 'servers-sale-rates-listen-get' );
							tpl::set ( '{id}' , $row2[ 'id' ] );
							tpl::set ( '{server}' , $id );
							tpl::set ( '{name}' , $row2[ 'name' ] );
							tpl::set ( '{price}' , $row2[ 'price' ] );
							tpl::compile ( 'data' );
						};
						tpl::load ( 'servers-sale-rates-listen' );
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
						if ( $data ) {
							if(api::$demo){
								api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
								return false;
							}
							if ( ! preg_match ( "/^.{2,200}$/i" , $data[ 'name' ] ) ) {
								if ( mb_strlen ( $data[ 'name' ] , "utf-8" ) < 2 ) {
									api::result ( l::t ( 'Название слишком короткое' ) );
								} else {
									if ( mb_strlen ( $data[ 'name' ] , "utf-8" ) > 200 ) {
										api::result ( l::t ( 'Название слишком длинное' ) );
									} else {
										api::result ( l::t ( 'Название содержит недопустимые символы' ) );
									}
								}
							} else {
								if ( $data[ 'price' ] > 10000 || $data[ 'price' ] < 1 ) {
									api::result ( l::t ( 'Цена слота должна быть от 1.0 до 10000.0' ) );
								} else {
									if ( ! preg_match ( "/^[a-z]{0,50}$/i" , $data[ 'flags' ] ) ) {
										api::result ( l::t ( 'Недопустимые флаги' ) );
									} else {
										if ( $row[ 'game' ] != "cs" ) {
											if ( $data[ 'im' ] > 100 || $data[ 'im' ] < 0 ) {
												api::result ( l::t ( 'Иммунитет должен быть от 0 до 100' ) );

												return false;
											} else {
												$dop = "im='" . (int) $data[ 'im' ] . "',";
											}
										}
										db::q (
											"INSERT INTO gh_servers_admins_rates set
												name='" . api::cl ( $data[ 'name' ] ) . "',
												price='" . api::price ( $data[ 'price' ] ) . "',
												server='" . (int) $id . "',
												" . $dop . "
												flags='" . api::cl ( $data[ 'flags' ] ) . "'"
										);
										api::result ( l::t ( 'Успешно добавлено' ) , 1 );
									}
								}
							}
						}
						if ( $row[ 'game' ] == "cs" ) {
							tpl::load ( 'servers-sale-rates-add-cs' );
						} else {
							tpl::load ( 'servers-sale-rates-add-css' );
						}
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

		public static function edit ( $id , $rate )
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
				db::q ( "SELECT * FROM gh_servers_admins_rates where id='" . $rate . "' and server='" . $id . "'" );
				if ( db::n () == "1" ) {
					$rate = db::r ();
					if ( $class::info ( 'sale' ) ) {
						if ( $row[ 'time' ] < time () ) {
							api::result ( l::t ( 'Срок аренды сервера истек' ) );
						} else {
							$data = $_POST[ 'data' ];
							if ( $data ) {
								if(api::$demo){
									api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
									return false;
								}
								if ( ! preg_match ( "/^.{2,200}$/i" , $data[ 'name' ] ) ) {
									if ( mb_strlen ( $data[ 'name' ] , "utf-8" ) < 2 ) {
										api::result ( l::t ( 'Название слишком короткое' ) );
									} else {
										if ( mb_strlen ( $data[ 'name' ] , "utf-8" ) > 200 ) {
											api::result ( l::t ( 'Название слишком длинное' ) );
										} else {
											api::result ( l::t ( 'Название содержит недопустимые символы' ) );
										}
									}
								} else {
									if ( $data[ 'price' ] > 10000 || $data[ 'price' ] < 1 ) {
										api::result ( l::t ( 'Цена слота должна быть от 1.0 до 10000.0' ) );
									} else {
										if ( ! preg_match ( "/^[a-z]{0,50}$/i" , $data[ 'flags' ] ) ) {
											api::result ( l::t ( 'Недопустимые флаги' ) );
										} else {
											if ( $row[ 'game' ] != "cs" ) {
												if ( $data[ 'im' ] > 100 || $data[ 'im' ] < 0 ) {
													api::result ( l::t ( 'Иммунитет должен быть от 0 до 100' ) );

													return false;
												} else {
													$dop = "im='" . (int) $data[ 'im' ] . "',";
												}
											}
											db::q (
												"UPDATE gh_servers_admins_rates set
													name='" . api::cl ( $data[ 'name' ] ) . "',
													price='" . api::price ( $data[ 'price' ] ) . "',
													" . $dop . "
													flags='" . api::cl ( $data[ 'flags' ] ) . "' where id='" . $rate[ 'id' ] . "'"
											);
											$class::admins_reload ( $id );
											api::result ( l::t ( 'Успешно сохранено' ) , 1 );
										}
									}
								}
							}

							if ( $row[ 'game' ] == "cs" ) {
								tpl::load ( 'servers-sale-rates-edit-cs' );
							} else {
								tpl::load ( 'servers-sale-rates-edit-css' );
							}
							tpl::set ( '{id}' , $row[ 'id' ] );
							tpl::set ( '{name}' , $rate[ 'name' ] );
							tpl::set ( '{price}' , $rate[ 'price' ] );
							tpl::set ( '{flags}' , $rate[ 'flags' ] );
							tpl::set ( '{im}' , $rate[ 'im' ] );
							tpl::set ( '{data}' , tpl::result ( 'data' ) );
							tpl::compile ( 'content' );
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
				db::q ( "SELECT * FROM gh_servers_admins_rates where id='" . $rate . "' and server='" . $id . "'" );
				if ( db::n () == "1" ) {
					$rate = db::r ();
					if ( $class::info ( 'sale' ) ) {
						if ( $row[ 'time' ] < time () ) {
							api::result ( l::t ( 'Срок аренды сервера истек' ) );
						} else {
							db::q ( 'DELETE from gh_servers_admins_rates where id="' . $rate[ 'id' ] . '"' );
							db::q ( 'DELETE from gh_servers_admins where rate="' . $rate[ 'id' ] . '"' );
							$class::admins_reload ( $id );
							api::result ( 'Удалено' , true );
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