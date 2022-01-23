<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class admin_rates
{
	public static function on_off ( $id )
	{
		global $title , $conf;
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT * FROM gh_rates where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$rate = db::r ();
			if ( $rate[ 'power' ] ) {
				$power = 0;
			} else {
				$power = 1;
			}
			db::q ( 'update gh_rates set power="' . $power . '" where id="' . $id . '"' );
			if ( $rate[ 'power' ] ) {
				api::result ( l::t ( 'Тариф выключен' ) , 1 );
			} else {
				api::result ( l::t ( 'Тариф включен' ) , 1 );
			}
		} else {
			api::result ( l::t ( 'Тариф не найден' ) );
		}
	}

	public static function del ( $id )
	{
		if(api::$demo){
			api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
			return false;
		}
		db::q ( "SELECT id FROM gh_rates where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			db::q ( 'SELECT id FROM gh_servers where rate="' . $id . '"' );
			if ( db::n () == "0" ) {
				db::q ( 'DELETE from gh_rates where id="' . $id . '"' );
				api::result ( l::t ( 'Тариф удалена' ) , true );
			} else {
				api::result ( l::t ( 'Сначала удалите все серверы' ) );
			}
		} else {
			api::result ( l::t ( 'Тариф не найден' ) );
		}
	}

	public static function add ()
	{
		global $title , $conf;
		api::inc ( 'servers' );
		$data = $_POST[ 'data' ];
		if ( $data ) {
			if(api::$demo){
				api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
				return false;
			}
			if ( $data[ 'loc' ] != "" ) {
				db::q ( 'SELECT * FROM gh_location where id="' . (int) $data[ 'loc' ] . '"' );
				if ( db::n () == 1 ) {
					$get_sql = "INSERT INTO gh_rates set ";
					$get_sql .= "loc='" . $data[ 'loc' ] . "',";
					if ( trim ( $data[ 'name' ] ) != "" ) {
						if ( ! servers::$games[ $data[ 'game' ] ] ) {
							api::result ( l::t ( 'Игра не найдена' ) );
						} else {
							$get_sql .= "game='" . $data[ 'game' ] . "',";
							if ( $data[ 'price' ] > 10000 ) {
								api::result ( l::t ( 'Цена слота должна быть не больше 10000' ) );
							} else {
								$get_sql .= "price='" . api::price ( $data[ 'price' ] ) . "',";
								if ( $data[ 'minp' ] < 100 or $data[ 'minp' ] > 65000 ) {
									api::result ( l::t ( 'Порты должны быть в диапазоне от 100 до 65000' ) );
								} else {
									$get_sql .= "min_ports='" . $data[ 'minp' ] . "',";
									if ( $data[ 'maxp' ] < 100 or $data[ 'maxp' ] > 65000 ) {
										api::result ( l::t ( 'Порты должны быть в диапазоне от 100 до 65000' ) );
									} else {
										$get_sql .= "max_ports='" . $data[ 'maxp' ] . "',";
										if ( $data[ 'mins' ] < 1 or $data[ 'mins' ] > 1000 ) {
											api::result ( l::t ( 'Слоты должны быть в диапазоне от 1 до 1000' ) );
										} else {
											$get_sql .= "min_slots='" . $data[ 'mins' ] . "',";
											if ( $data[ 'maxs' ] < 1 or $data[ 'maxs' ] > 1000 ) {
												api::result ( l::t ( 'Слоты должны быть в диапазоне от 1 до 1000' ) );
											} else {
												$get_sql .= "max_slots='" . $data[ 'maxs' ] . "',";
												$gogo = true;
												if ( $data[ 'game' ] != "ts3" ) {

														if ( $data[ 'versions' ] != 1 ) {
															if ( ! preg_match ( "/^[0-9a-zA-Z-_\/]{0,30}$/i" , $data[ 'dir' ] ) ) {
																api::result ( l::t ( 'Директория установки содержит недопустимые символы' ) );
																$gogo = false;
															}
														}

												}
												if ( $gogo ) {
													if ( $data[ 'game' ] != "ts3" ) {

															if ( $data[ 'versions' ] == 1 ) {
																$get_sql .= "dir='1',";
																foreach ( $data[ 'version' ][ 'name' ] as $key => $val ) {
																	$d = array ();
																	$d[ 'name' ] = api::cl ( $val );
																	$d[ 'type' ] = (int) $data[ 'version' ][ 'typ' ][ $key ];
																	$d[ 'dir' ] = $data[ 'version' ][ 'dir' ][ $key ];
																	$versions[ ] = $d;
																}
																$get_sql .= "versions='" . db::s ( json_encode ( $versions , JSON_UNESCAPED_UNICODE ) ) . "',";
															} else {
																$get_sql .= "dir='" . api::cl ( $data[ 'dir' ] ) . "',";
															}

													} else {
														$get_sql .= "dir='" . api::cl ( $data[ 'addtype' ] ) . "',";
													}
													$class = servers::game_class ( $data[ 'game' ] );
													$gogo = true;
													if ( $class::info ( 'fps' ) ) {
														if ( $data[ 'fps' ] < 100 or $data[ 'fps' ] > 1000 ) {
															api::result ( l::t ( 'FPS должны быть в диапазоне от 100 до 1000' ) );
															$gogo = false;
														} else {
															$get_sql .= "fps='" . (int) $data[ 'fps' ] . "',";
														}
													}
													$get_sql .= "sale='" . (int) $data[ 'sale' ] . "',";
													$get_sql .= "plus='" . $data[ 'addtype2' ] . "',";
													$get_sql .= "friends='" . (int) $data[ 'friends' ] . "',";
													if ( (int) $data[ 'mysql' ] ) {
														$get_sql .= "mysql='" . (int) $data[ 'mysql-rate' ] . "',";
													} else {
														$get_sql .= "mysql='0',";
													}
													if ( (int) $data[ 'web' ] ) {
														$get_sql .= "web='" . (int) $data[ 'web-rate' ] . "',";
													} else {
														$get_sql .= "web='0',";
													}
													if ( $gogo ) {
														$gogo = true;
														$val = array ( 1 , 0 );
														if ( $class::info ( 'ftp' ) ) {
															if ( in_array ( $data[ 'ftp' ] , $val ) ) {
																if ( $data[ 'ftp' ] == "1" ) {
																	$get_sql .= "ftp='1',";
																} else {
																	$get_sql .= "ftp='0',";
																}
															} else {
																api::result ( l::t ( 'Критическая ошибка' ) );
																$gogo = false;
															}
														}
														if ( $data[ 'game' ] != "ts3" ) {
															if ( $data[ 'hard' ] < 100 or $data[ 'hard' ] > 100000 ) {
																api::result ( l::t ( 'Квота должны быть в диапазоне от 100 до 100000' ) );
																$gogo = false;
															} else {
																$get_sql .= "hard='" . (int) $data[ 'hard' ] . "',";
															}
														}
														if ( $gogo ) {
															if ( $class::info ( 'tv' ) ) {
																$val = array ( 1 , 0 );
																if ( in_array ( $data[ 'tv' ] , $val ) ) {
																	if ( $data[ 'tv' ] == "1" ) {
																		$get_sql .= "tv='1',";
																	} else {
																		$get_sql .= "tv='0',";
																	}
																	$get_sql .= "tv_slots='" . (int) $data[ 'tv_slots' ] . "',";
																} else {
																	api::result ( l::t ( 'Критическая ошибка' ) );
																	$gogo = false;
																}
															}
															if ( $class::info ( 'fastdl' ) ) {
																if ( in_array ( $data[ 'fastdl' ] , $val ) ) {
																	if ( $data[ 'fastdl' ] == "1" ) {
																		$get_sql .= "fastdl='1',";
																	} else {
																		$get_sql .= "fastdl='0',";
																	}
																} else {
																	api::result ( l::t ( 'Критическая ошибка' ) );
																	$gogo = false;
																}
															}
															if ( $gogo ) {
																if ( $class::info ( 'repository' ) ) {
																	if ( in_array ( $data[ 'modules' ] , $val ) ) {
																		if ( $data[ 'modules' ] == "1" ) {
																			$get_sql .= "modules='1',";
																		} else {
																			$get_sql .= "modules='0',";
																		}
																	} else {
																		api::result ( l::t ( 'Критическая ошибка' ) );
																		$gogo = false;
																	}
																}
																if ( $data[ 'game' ] == "cs" ) {
																	$val = array ( 1 , 0 );
																	if ( in_array ( $data[ 'tip' ] , $val ) ) {
																		if ( $data[ 'tip' ] == "1" ) {
																			$get_sql .= "tipe='1',";
																		} else {
																			$get_sql .= "tipe='0',";
																		}
																	} else {
																		api::result ( l::t ( 'Критическая ошибка' ) );
																		$gogo = false;
																	}
																}
																if ( $gogo ) {
																	$get_sql .= "support='" . (int) $data[ 'price-support' ] . "',";
																	$get_sql .= "name='" . api::cl ( $data[ 'name' ] ) . "'";
																	db::q ( $get_sql );
																	api::result ( l::t ( 'Тариф добавлен' ) , true );
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					} else {
						api::result ( l::t ( 'Укажите название тарифа' ) );
					}
				} else {
					api::result ( l::t ( 'Локация не найдена' ) );
				}
			} else {
				api::result ( l::t ( 'Локация не найдена' ) );
			}
		}
		$loc = "";
		$sql = db::q ( 'SELECT * FROM gh_location order by id desc' );
		while ( $row2 = db::r ( $sql ) ) {
			$loc .= '<option value="' . $row2[ 'id' ] . '">' . $row2[ 'name' ] . '</option>';
		}
		$title = l::t ( "Новый тариф" );
		tpl::load2 ( 'admin-rates-add' );
		$games = '';
		$dop = '';
		foreach ( servers::$games as $key => $value ) {
			$games .= '<option value="' . $key . '">' . $value . '</option>';
			$class = servers::game_class ( $key );
			$dop .= "games['" . $key . "']=[];";
			$dop .= "games['" . $key . "']['ftp']=" . $class::info ( 'ftp' ) . ";";
			$dop .= "games['" . $key . "']['repository']=" . $class::info ( 'repository' ) . ";";
			$dop .= "games['" . $key . "']['fastdl']=" . $class::info ( 'fastdl' ) . ";";
			$dop .= "games['" . $key . "']['fps']=" . $class::info ( 'fps' ) . ";";
			$dop .= "games['" . $key . "']['tv']=" . $class::info ( 'tv' ) . ";";
		}

			tpl::set ( '{version}' , 1 );
		tpl::set ( '{games}' , $games );
		tpl::set ( '{dop}' , $dop );
		tpl::set ( '{loc}' , $loc );

		$sql = db::q ( 'SELECT * FROM mysql_rates order by id desc' );
		$rates = '';
		while ( $row = db::r ( $sql ) ) {
			$rates .= '<option value="' . $row[ 'id' ] . '">' . $row[ 'name' ] . '</option>';
		}
		tpl::set ( '{mysql-rates}' , $rates );

		$sql = db::q ( 'SELECT * FROM isp_rates order by id desc' );
		$rates = '';
		while ( $row = db::r ( $sql ) ) {
			$rates .= '<option value="' . $row[ 'id' ] . '">' . $row[ 'name' ] . '</option>';
		}
		tpl::set ( '{web-rates}' , $rates );

		tpl::compile ( 'content' );
		if ( api::modal () ) {
			die( tpl::result ( 'content' ) );
		} else {
			api::nav ( '/admin/rates' , l::t ( 'Тарифы' ) );
			api::nav ( '' , l::t ( 'Новый' ) , '1' );
		}
	}

	public static function edit ( $id )
	{
		global $title , $conf;
		db::q ( "SELECT * FROM gh_rates where id='" . $id . "'" );
		if ( db::n () == "1" ) {
			$rate = db::r ();
			api::inc ( 'servers' );
			$class = servers::game_class ( $rate[ 'game' ] );
			$data = $_POST[ 'data' ];
			if ( $data ) {
				if(api::$demo){
					api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
					return false;
				}
				$get_sql = "UPDATE gh_rates set ";
				if ( $data[ 'name' ] != "" ) {
					if ( $data[ 'price' ] > 10000 ) {
						api::result ( l::t ( 'Цена слота должна быть от 0.1 до 10000' ) );
					} else {
						$get_sql .= "price='" . api::price ( $data[ 'price' ] ) . "',";
						if ( $data[ 'minp' ] < 100 or $data[ 'minp' ] > 65000 ) {
							api::result ( l::t ( 'Порты должны быть в диапазоне от 100 до 65000' ) );
						} else {
							$get_sql .= "min_ports='" . $data[ 'minp' ] . "',";
							if ( $data[ 'maxp' ] < 100 or $data[ 'maxp' ] > 65000 ) {
								api::result ( l::t ( 'Порты должны быть в диапазоне от 100 до 65000' ) );
							} else {
								$get_sql .= "max_ports='" . $data[ 'maxp' ] . "',";
								if ( $data[ 'mins' ] < 1 or $data[ 'mins' ] > 1000 ) {
									api::result ( l::t ( 'Слоты должны быть в диапазоне от 1 до 1000' ) );
								} else {
									$get_sql .= "min_slots='" . $data[ 'mins' ] . "',";
									if ( $data[ 'maxs' ] < 1 or $data[ 'maxs' ] > 1000 ) {
										api::result ( l::t ( 'Слоты должны быть в диапазоне от 1 до 1000' ) );
									} else {
										$get_sql .= "max_slots='" . $data[ 'maxs' ] . "',";
										if ( ! preg_match ( "/^[0-9a-zA-Z-_\/]{0,30}$/i" , $data[ 'dir' ] ) ) {
											api::result ( l::t ( 'Дирeктория установки содежит недопустимые символы' ) );
										} else {
											if ( $data[ 'game' ] != "ts3" ) {

													if ( $data[ 'versions' ] == 1 ) {
														$get_sql .= "dir='1',";
														foreach ( $data[ 'version' ][ 'name' ] as $key => $val ) {
															$d = array ();
															$d[ 'name' ] = api::cl ( $val );
															$d[ 'type' ] = (int) $data[ 'version' ][ 'typ' ][ $key ];
															$d[ 'dir' ] = $data[ 'version' ][ 'dir' ][ $key ];
															$versions[ ] = $d;
														}
														$get_sql .= "versions='" . db::s ( json_encode ( $versions , JSON_UNESCAPED_UNICODE ) ) . "',";
													} else {
														$get_sql .= "dir='" . api::cl ( $data[ 'dir' ] ) . "',";
													}

											} else {
												$get_sql .= "dir='" . api::cl ( $data[ 'addtype' ] ) . "',";
											}
											$gogo = true;
											if ( $class::info ( 'fps' ) ) {
												if ( $data[ 'fps' ] < 100 or $data[ 'fps' ] > 1000 ) {
													api::result ( l::t ( 'FPS должны быть в диапазоне от 100 до 1000' ) );
													$gogo = false;
												} else {
													$get_sql .= "fps='" . $data[ 'fps' ] . "',";
												}
											}
											$get_sql .= "plus='" . api::cl ( $data[ 'addtype2' ] ) . "',";
											$get_sql .= "friends='" . (int) $data[ 'friends' ] . "',";
											$get_sql .= "sale='" . (int) $data[ 'sale' ] . "',";
											if ( (int) $data[ 'mysql' ] ) {
												$get_sql .= "mysql='" . (int) $data[ 'mysql-rate' ] . "',";
											} else {
												$get_sql .= "mysql='0',";
											}
											if ( (int) $data[ 'web' ] ) {
												$get_sql .= "web='" . (int) $data[ 'web-rate' ] . "',";
											} else {
												$get_sql .= "web='0',";
											}
											if ( $gogo ) {
												$gogo = true;
												$val = array ( 1 , 0 );
												if ( $class::info ( 'ftp' ) ) {
													if ( in_array ( $data[ 'ftp' ] , $val ) ) {
														if ( $data[ 'ftp' ] == "1" ) {
															$get_sql .= "ftp='1',";
														} else {
															$get_sql .= "ftp='0',";
														}
													} else {
														api::result ( l::t ( 'Критическая ошибка' ) );
														$gogo = false;
													}
												}
												if ( $data[ 'hard' ] < 100 or $data[ 'hard' ] > 100000 ) {
													api::result ( l::t ( 'Квота должны быть в диапазоне от 100 до 100000' ) );
													$gogo = false;
												} else {
													$get_sql .= "hard='" . $data[ 'hard' ] . "',";
												}
												if ( $gogo ) {
													if ( $class::info ( 'tv' ) ) {
														$val = array ( 1 , 0 );
														if ( in_array ( $data[ 'tv' ] , $val ) ) {
															if ( $data[ 'tv' ] == "1" ) {
																$get_sql .= "tv='1',";
															} else {
																$get_sql .= "tv='0',";
															}
															$get_sql .= "tv_slots='" . (int) $data[ 'tv_slots' ] . "',";
														} else {
															api::result ( l::t ( 'Критическая ошибка' ) );
															$gogo = false;
														}
													}
													if ( $class::info ( 'fastdl' ) ) {
														if ( in_array ( $data[ 'fastdl' ] , $val ) ) {
															if ( $data[ 'fastdl' ] == "1" ) {
																$get_sql .= "fastdl='1',";
															} else {
																$get_sql .= "fastdl='0',";
															}
														} else {
															api::result ( l::t ( 'Критическая ошибка' ) );
															$gogo = false;
														}
													}
													if ( $gogo ) {
														if ( $class::info ( 'repository' ) ) {
															if ( in_array ( $data[ 'modules' ] , $val ) ) {
																if ( $data[ 'modules' ] == "1" ) {
																	$get_sql .= "modules='1',";
																} else {
																	$get_sql .= "modules='0',";
																}
															} else {
																api::result ( l::t ( 'Критическая ошибка' ) );
																$gogo = false;
															}
														}
														if ( $rate[ 'game' ] == "cs" ) {
															$val = array ( 1 , 0 );
															if ( in_array ( $data[ 'tip' ] , $val ) ) {
																if ( $data[ 'tip' ] == "1" ) {
																	$get_sql .= "tipe='1',";
																} else {
																	$get_sql .= "tipe='0',";
																}
															} else {
																api::result ( l::t ( 'Критическая ошибка' ) );
																$gogo = false;
															}
														}
														if ( $gogo ) {
															$get_sql .= "support='" . (int) $data[ 'price-support' ] . "',";
															$get_sql .= "name='" . $data[ 'name' ] . "' where id='" . $id . "'";
															db::q ( $get_sql );
															api::result ( l::t ( 'Тариф сохранен' ) , true );
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				} else {
					api::result ( l::t ( 'Укажите название тарифа' ) );
				}
			}
			$dop = '';
			$title = l::t ( "Редактирование тарифа" );
			$dop .= "rate['ftp']=" . $class::info ( 'ftp' ) . ";";
			$dop .= "rate['repository']=" . $class::info ( 'repository' ) . ";";
			$dop .= "rate['fastdl']=" . $class::info ( 'fastdl' ) . ";";
			$dop .= "rate['fps']=" . $class::info ( 'fps' ) . ";";
			$dop .= "rate['tv']=" . $class::info ( 'tv' ) . ";";
			tpl::load2 ( 'admin-rates-edit' );
			tpl::set ( '{id}' , $id );
			tpl::set ( '{dop}' , $dop );
			tpl::set ( '{game}' , $rate[ 'game' ] );
			tpl::set ( '{name}' , $rate[ 'name' ] );
			tpl::set ( '{price}' , $rate[ 'price' ] );
			tpl::set ( '{minp}' , $rate[ 'min_ports' ] );
			tpl::set ( '{maxp}' , $rate[ 'max_ports' ] );
			tpl::set ( '{mins}' , $rate[ 'min_slots' ] );
			tpl::set ( '{maxs}' , $rate[ 'max_slots' ] );
			tpl::set ( '{dir}' , $rate[ 'dir' ] );
			tpl::set ( '{fps}' , $rate[ 'fps' ] );
			tpl::set ( '{hard}' , $rate[ 'hard' ] );

				tpl::set ( '{version}' , 1 );

			if ( $rate[ 'game' ] == "rust" ) {
				$versions = '<option value="0">' . l::t ( 'Выключено' ) . '</option><option value="1" selected>' . l::t ( 'Включено' ) . '</option>';
			} else {
				if ( $rate[ 'dir' ] == 1 ) {
					$versions = '<option value="0">' . l::t ( 'Выключено' ) . '</option><option value="1" selected>' . l::t ( 'Включено' ) . '</option>';
				} else {
					$versions = '<option value="0">' . l::t ( 'Выключено' ) . '</option><option value="1">' . l::t ( 'Включено' ) . '</option>';
				}
			}

			tpl::set ( '{base-version}' , $versions );
			$version = '';

				if ( $rate[ 'dir' ] == 1 ) {
					$versionsa = json_decode ( $rate[ 'versions' ] , true );
					foreach ( $versionsa as $key => $val ) {
						$v = array ();
						$version .= '<tr><td>';
						$version .= '<input type="text" class="form-control" name="data[version][name][]" value="' . $val[ 'name' ] . '">';
						$version .= '</td><td>';
						if ( $rate[ 'game' ] == "rust" ) {
							$version .= '<select name="data[version][typ][]" class="form-control">';
							if ( $val[ 'type' ] == "0" ) {
								$version .= '<option value="0" selected>Experimental</option><option value="1">Legacy</option>';
							} else {
								$version .= '<option value="0">Experimental</option><option value="1" selected>Legacy</option>';
							}
							$version .= '</select></td><td>';
						}
						if ( $rate[ 'game' ] == "cs" ) {
							$version .= '<select name="data[version][typ][]" class="form-control">';
							if ( $val[ 'type' ] == "0" ) {
								$version .= '<option value="0" selected>'.l::t('Старый').'</option><option value="1">'.l::t('Новый').'</option>';
							} else {
								$version .= '<option value="0">'.l::t('Старый').'</option><option value="1" selected>'.l::t('Новый').'</option>';
							}
							$version .= '</select></td><td>';
						}
						$version .= '<input type="text" class="form-control" name="data[version][dir][]" value="' . $val[ 'dir' ] . '">';
						$version .= '</td><td>';
						$version .= '<i class="glyphicon glyphicon-minus del-cup"></i>';
						$version .= '</td></tr>';
					}
				}

			tpl::set ( '{all-versions}' , $version );
			tpl::set ( '{price-support}' , $rate[ 'support' ] );
			tpl::set ( '{addtype2}' , $rate[ 'plus' ] );
			tpl::set ( '{tv_slots}' , $rate[ 'tv_slots' ] );
			if ( $rate[ 'tipe' ] == "1" ) {
				$tip = '<option value="0">' . l::t ( 'Старый' ) . '</option><option value="1"selected="selected">' . l::t ( 'Новый' ) . '</option>';
			} else {
				$tip = '<option value="0" selected="selected">' . l::t ( 'Старый' ) . '</option><option value="1">' . l::t ( 'Новый' ) . '</option>';
			}
			tpl::set ( '{tip}' , $tip );
			if ( $rate[ 'friends' ] == "1" ) {
				$tip = '<option value="0">' . l::t ( 'Отключено' ) . '</option><option value="1"selected="selected">' . l::t ( 'Доступно' ) . '</option>';
			} else {
				$tip = '<option value="0" selected="selected">' . l::t ( 'Отключено' ) . '</option><option value="1">' . l::t ( 'Доступно' ) . '</option>';
			}
			tpl::set ( '{friends}' , $tip );
			if ( $rate[ 'sale' ] == "1" ) {
				$tip = '<option value="0">' . l::t ( 'Отключено' ) . '</option><option value="1"selected="selected">' . l::t ( 'Доступно' ) . '</option>';
			} else {
				$tip = '<option value="0" selected="selected">' . l::t ( 'Отключено' ) . '</option><option value="1">' . l::t ( 'Доступно' ) . '</option>';
			}
			tpl::set ( '{sale}' , $tip );
			if ( $rate[ 'fastdl' ] == "1" ) {
				$fastdl = '<option value="0">' . l::t ( 'Отключено' ) . '</option><option value="1"selected="selected">' . l::t ( 'Доступно' ) . '</option>';
			} else {
				$fastdl = '<option value="0" selected="selected">' . l::t ( 'Отключено' ) . '</option><option value="1">' . l::t ( 'Доступно' ) . '</option>';
			}
			tpl::set ( '{fastdl}' , $fastdl );
			if ( $rate[ 'tv' ] == "1" ) {
				$tv = '<option value="0">' . l::t ( 'Отключено' ) . '</option><option value="1"selected="selected">' . l::t ( 'Доступно' ) . '</option>';
			} else {
				$tv = '<option value="0" selected="selected">' . l::t ( 'Отключено' ) . '</option><option value="1">' . l::t ( 'Доступно' ) . '</option>';
			}
			tpl::set ( '{tv}' , $tv );
			if ( $rate[ 'ftp' ] == "1" ) {
				$ftp = '<option value="0">' . l::t ( 'Отключено' ) . '</option><option value="1"selected="selected">' . l::t ( 'Доступно' ) . '</option>';
			} else {
				$ftp = '<option value="0" selected="selected">' . l::t ( 'Отключено' ) . '</option><option value="1">' . l::t ( 'Доступно' ) . '</option>';
			}
			tpl::set ( '{ftp}' , $ftp );

			if ( $rate[ 'modules' ] == "1" ) {
				$modules = '<option value="0">' . l::t ( 'Отключено' ) . '</option><option value="1"selected="selected">' . l::t ( 'Доступно' ) . '</option>';
			} else {
				$modules = '<option value="0" selected="selected">' . l::t ( 'Отключено' ) . '</option><option value="1">' . l::t ( 'Доступно' ) . '</option>';
			}
			tpl::set ( '{modules}' , $modules );


			if ( $rate[ 'mysql' ] != "0" ) {
				$modules = '<option value="0">' . l::t ( 'Нет' ) . '</option><option value="1"selected="selected">' . l::t ( 'Да' ) . '</option>';
			} else {
				$modules = '<option value="0" selected="selected">' . l::t ( 'Нет' ) . '</option><option value="1">' . l::t ( 'Да' ) . '</option>';
			}
			tpl::set ( '{mysql}' , $modules );
			$sql = db::q ( 'SELECT * FROM mysql_rates order by id desc' );
			$rates = '';
			while ( $row = db::r ( $sql ) ) {
				if ( $rate[ 'mysql' ] == $row[ 'id' ] ) {
					$rates .= '<option value="' . $row[ 'id' ] . '" selected>' . $row[ 'name' ] . '</option>';
				} else {
					$rates .= '<option value="' . $row[ 'id' ] . '">' . $row[ 'name' ] . '</option>';
				}
			}
			tpl::set ( '{mysql-rates}' , $rates );


			if ( $rate[ 'web' ] != "0" ) {
				$modules = '<option value="0">' . l::t ( 'Нет' ) . '</option><option value="1"selected="selected">' . l::t ( 'Да' ) . '</option>';
			} else {
				$modules = '<option value="0" selected="selected">' . l::t ( 'Нет' ) . '</option><option value="1">' . l::t ( 'Да' ) . '</option>';
			}
			tpl::set ( '{web}' , $modules );
			$sql = db::q ( 'SELECT * FROM isp_rates order by id desc' );
			$rates = '';
			while ( $row = db::r ( $sql ) ) {
				if ( $rate[ 'web' ] == $row[ 'id' ] ) {
					$rates .= '<option value="' . $row[ 'id' ] . '" selected>' . $row[ 'name' ] . '</option>';
				} else {
					$rates .= '<option value="' . $row[ 'id' ] . '">' . $row[ 'name' ] . '</option>';
				}
			}
			tpl::set ( '{web-rates}' , $rates );

			tpl::compile ( 'content' );
			if ( api::modal () ) {
				die( tpl::result ( 'content' ) );
			} else {
				api::nav ( '/admin/rates' , l::t ( 'Тарифы' ) );
				api::nav ( '' , l::t ( 'Редактирование' ) , '1' );
			}
		} else {
			api::result ( l::t ( 'Тариф не найден' ) );
		}
	}

	public static function listen ()
	{
		global $title;
		api::inc ( 'servers' );
		api::nav ( '' , l::t ( 'Тарифы' ) , '1' );
		$sql = db::q ( 'SELECT * FROM gh_rates order by id desc' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-rates-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{name}' , $row[ 'name' ] );
			tpl::set ( '{game}' , servers::$games[ $row[ 'game' ] ] );
			$sql2 = db::q ( 'SELECT * FROM gh_location where id="' . $row[ 'loc' ] . '"' );
			$row2 = db::r ( $sql2 );
			tpl::set ( '{loc}' , $row2[ 'name' ] );
			tpl::set ( '{price}' , $row[ 'price' ] );
			tpl::set ( '{slots}' , $row[ 'min_slots' ] . '-' . $row[ 'max_slots' ] );
			tpl::set ( '{ports}' , $row[ 'min_ports' ] . '-' . $row[ 'max_ports' ] );
			if ( $row[ 'power' ] ) {
				tpl::set ( '{color}' , 'blue' );
				tpl::set ( '{icon}' , 'fa fa-check-circle-o' );
				tpl::set ( '{status}' , '1' );
			} else {
				tpl::set ( '{icon}' , 'fa fa-circle-o' );
				tpl::set ( '{color}' , '' );
				tpl::set ( '{status}' , '0' );
			}
			tpl::compile ( 'data' );
		};
		$title = l::t ( "Управление тарифами" );
		tpl::load2 ( 'admin-rates-listen' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		tpl::compile ( 'content' );
	}
}

?>
