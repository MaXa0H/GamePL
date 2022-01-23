<?php

class servers_settings
{
	public static function conf($id,$server){
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $server . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $server . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'settings' )){
				api::result(l::t('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $server. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек') );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'settings2' ) ) {
					$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
					api::nav ( "/servers" , l::t("Серверы") );
					api::nav ( "/servers/base/" . $server , $adress );
					api::nav ( "/servers/settings/base/".$server , l::t('Параметры запуска') );
					if($id==1){
						api::nav ( "", "server.cfg",1 );
						$exec = '/host/' . $row[ 'user' ] . '/' . $row[ 'sid' ] . $class::info ( 'settings_servercfg' );
					}else{
						$exec = '/host/' . $row[ 'user' ] . '/' . $row[ 'sid' ] . $class::info ( 'settings_motd' );
						api::nav ( "", "motd.txt",1 );
					}
					servers::$speedbar = $server;
					api::inc('ssh2');
					if ( ssh::gh_box ( $row[ 'box' ] ) ) {
						$type = $class::$servercfg;
						$exec1 = ROOT . '/conf/' . $row[ 'user' ] . '_' . $row[ 'id' ] . '.txt';
						$data2 = $_POST[ 'data' ];
						if($id==1) {
							if ( $data2 ) {
								if(api::$demo){
									api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
									return false;
								}
								if ( api::captcha_chek () ) {
									$new = "";
									foreach ( $type as $key => $value ) {
										$new .= $value[ 'var' ] . " \"" . $data2[ $value[ 'var' ] ] . "\"\n";
									}
									$data3 = $_POST[ 'data3' ];
									$data4 = $_POST[ 'data4' ];
									foreach ( $data3 as $key => $val ) {
										$new .= $val . " \"" . $data4[ $key ] . "\"\n";
									}
									file_put_contents ( $exec1 , $new );
									ssh::send_file ( $exec1 , $exec , 0777 );
									unlink ( $exec1 );
									api::result ( l::t('Сохранено') , 1 );
								}
							}
							unlink ( $exec1 );
							ssh::get_file ( $exec , $exec1 );
							$file = trim ( file_get_contents ( $exec1 ) );
							unlink ( $exec1 );
							$array = explode ( "\n" , $file );
							$base = array ();
							foreach ( $array as $value ) {
								if ( trim ( $value ) ) {
									if ( $value{0} == "/" && $value{1} == "/" ) {
										continue;
									} else {
										$value = str_replace ( '"' , "" , $value );
										$value = str_replace ( "'" , "" , $value );
										$value = str_replace ( "`" , "" , $value );
										$value2 = explode ( " " , $value );
										$name = "";
										$last = "";
										foreach ( $value2 as $key => $value ) {
											if ( $key == 0 ) {
												$name = $value;
											} else {
												$last .= " " . $value;
											}
										}
										if ( $name == "say" ) {
											continue;
										}
										if ( $name == "alias" ) {
											continue;
										}
										if ( $name == "tv_enable" ) {
											continue;
										}
										if ( $name == "tv_port" ) {
											continue;
										}
										if ( $name == "rcon_password" ) {
											continue;
										}
										if ( $name == "sv_password" ) {
											continue;
										}
										if ( $name == "fps_max" ) {
											continue;
										}
										if ( $name == "sv_lan" ) {
											continue;
										}
										if ( $name == "tv_title" ) {
											continue;
										}
										$last = trim ( $last );
										$base[ ] = array ( $name , $last );
									}
								}
							}

							foreach ( $type as $key => $value ) {
								tpl::load ( 'servers-settings-conf-base-get' );
								tpl::set_block ( "'\\[input\\](.*?)\\[/input\\]'si" , "" );
								tpl::set_block ( "'\\[select\\](.*?)\\[/select\\]'si" , "" );
								$ba = "-1";
								foreach ( $base as $key1 => $val1 ) {
									if ( $val1[ '0' ] == $value[ 'var' ] ) {
										unset( $base[ $key1 ] );
										$ba = $val1[ '1' ];
										break;
									}
								}
								if ( $value[ 'type' ] == "2" ) {
									tpl::set_block ( "'\\[input\\](.*?)\\[/input\\]'si" , "\\1" );
									if($ba!="-1"){
										tpl::set ( '{val}' , $ba );
									}else{
										tpl::set ( '{val}' , $value[ 'default' ] );
									}
									tpl::set ( '{def}' , $value[ 'default' ] );
								}
								if ( $value[ 'type' ] == "1" ) {
									$set = "";
									foreach ( $value[ 'val' ] as $key2 => $val2 ) {
										if($ba!="-1"){
											if ( $ba == $key2 ) {
												$set .= '<option value="' . $key2 . '" selected>' . l::t($val2) . '</option>';
											} else {
												$set .= '<option value="' . $key2 . '">' . l::t($val2) . '</option>';
											}
										}else{
											if ( $value[ 'default' ] == $key2 ) {
												$set .= '<option value="' . $key2 . '" selected>' . l::t($val2) . '</option>';
											} else {
												$set .= '<option value="' . $key2 . '">' . l::t($val2) . '</option>';
											}
										}
									}
									tpl::set ( '{val}' , $set );
									tpl::set_block ( "'\\[select\\](.*?)\\[/select\\]'si" , "\\1" );
								}
								tpl::set ( '{name}' , l::t($value[ 'name' ]) );
								tpl::set ( '{key}' , $value[ 'var' ] );
								tpl::compile ( 'data' );
							}
							foreach ( $base as $key => $value ) {
								tpl::load ( 'servers-settings-conf-base-dop' );
								tpl::set ( '{key}' , $value[ '0' ] );
								tpl::set ( '{val}' , $value[ '1' ] );
								tpl::compile ( 'data2' );
							}
							tpl::load ( 'servers-settings-conf-base' );
							tpl::set('{title}','server.cfg');
							tpl::set ( '{cfg}' , tpl::result ( 'data' ) . tpl::result ( 'data2' ) );
						}else{
							if ( $data2 ) {
								file_put_contents ( $exec1 , $data2['info'] );
								ssh::send_file ( $exec1 , $exec , 0777 );
								unlink ( $exec1 );
								api::result ( l::t('Сохранено') , 1 );
							}
							unlink ( $exec1 );
							ssh::get_file ( $exec , $exec1 );
							$file = trim ( file_get_contents ( $exec1 ) );
							unlink ( $exec1 );
							tpl::load ( 'servers-settings-conf-motd' );
							tpl::set ( '{cfg}' , $file );
						}
						api::captcha_create ();
						tpl::compile ( 'content' );
					} else {
						api::result ( l::t('Не удалось установить соединение с сервером') );
					}
				}else{
					api::result ( l::t('Данная функция отключена') );
				}
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}
	public static function cfg ( $id )
	{
		global $title;
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'modules' )){
				api::result(l::t('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == "1" ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек' ));
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'settings' ) ) {
					$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
					api::nav ( "/servers" , l::t("Серверы") );
					api::nav ( "/servers/base/" . $id , $adress );
					api::nav ( "" , l::t('Доступные конфиги' ), '1' );
					$title = l::t("Настройки сервера");
					servers::$speedbar = $id;
					$sql = db::q ( 'SELECT * FROM gh_addons_install where server="' . $id . '" order by id desc' );
					while ( $row_1 = db::r ( $sql ) ) {
						$sql_2 = db::q ( 'SELECT * FROM gh_addons where id="' . $row_1[ 'addon' ] . '"' );
						$row_2 = db::r ( $sql_2 );
						$sql_3 = db::q ( 'SELECT * FROM gh_addons_cfg_add where addon="' . $row_1[ 'addon' ] . '"' );
						if ( db::n () > "0" ) {
							while ( $row_3 = db::r ( $sql_3 ) ) {
								$data .= '<tr><td><a href="/servers/settings/repository/' . $id . '/' . $row_3[ 'id' ] . '" >' . $row_2[ 'dir' ] . $row_3[ 'dir' ] . $row_3[ 'file' ] . '</a></td><td>' . $row_2[ 'name' ] . '</td></tr>';
							}
						}
					}
					tpl::load ( 'servers-settings-files' );
					tpl::set ( '{id}' , $id );
					if ( ! $data ) {
						$data .= '<tr><td>'.l::t('Нет доступных файлов для редактирования').'</td></tr>';
					}
					tpl::set ( '{data}' , $data );
					tpl::compile ( 'content' );
				} else {
					api::result ( l::t('Данная функция отключена') );
				}
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}

	public static function repository ( $id , $addon )
	{
		global $title;
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'modules' )){
				api::result(l::t('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек') );
			} else {
				$game = $row[ 'game' ];
				$class = servers::game_class ( $game );
				$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
				api::nav ( "/servers" , l::t("Серверы") );
				api::nav ( "/servers/base/" . $id , $adress );
				api::nav ( "/servers/settings/cfg/" . $id , l::t('Доступные конфиги') );
				servers::$speedbar = $id;
				db::q ( 'SELECT * FROM gh_addons_cfg_add where id="' . $addon . '"' );
				if ( db::n () != "1" ) {
					api::result ( l::t('Дополнение не найдено') );
				} else {
					$conf1 = db::r ();
					db::q ( 'SELECT * FROM gh_addons_install where addon="' . $conf1[ 'addon' ] . '" and server="' . $id . '"' );
					if ( db::n () != "1" ) {
						api::result ( l::t('Дополнение не установлено'));
					} else {
						db::q ( 'SELECT * FROM gh_addons where id="' . $conf1[ 'addon' ] . '"' );
						$addons = db::r ();
						api::nav ( '' , $addons[ 'dir' ] . $conf1[ 'dir' ] . $conf1[ 'file' ] , '1' );
						api::inc ( 'ssh2' );
						$exec = '/host/' . $row[ 'user' ] . '/' . $row[ 'sid' ] . '/';
						$exec .= $addons[ 'dir' ] . $conf1[ 'dir' ] . $conf1[ 'file' ];
						$exec1 = ROOT . '/conf/' . $row[ 'user' ] . time () . $conf1[ 'id' ] . '.txt';
						if ( $_POST[ 'data' ] ) {
							if(api::$demo){
								api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
								return false;
							}
							if ( ssh::gh_box ( $row[ 'box' ] ) ) {
								$file = fopen ( $exec1 , "w" );
								fputs ( $file , $_POST[ 'data' ][ 'text' ] );
								fclose ( $file );
								ssh::send_file ( $exec1 , $exec , 0777 );
								unlink ( $exec1 );
								ssh::disconnect ();
								api::result ( l::t('Сохранено') , true );
							} else {
								api::result ( l::t('Не удалось установить соединение с сервером') );
							}
						}
						if ( ssh::gh_box ( $row[ 'box' ] ) ) {
							ssh::get_file ( $exec , $exec1 );
							tpl::load ( 'servers-settings-repository-edit' );
							tpl::set ( '{id}' , $id );
							tpl::set ( '{addon}' , $addon );
							tpl::set ( '{data}' , file_get_contents ( $exec1 ) );
							tpl::compile ( 'content' );
							unlink ( $exec1 );
							ssh::disconnect ();
						} else {
							api::result ( l::t('Не удалось установить соединение с сервером') );
						}
					}
				}
			}
		} else {
			api::result ( l::t('Сервер не найден'));
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
			if(!servers::friend ( $id  , 'settings' )){
				api::result(l::t('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек') );
			} else {
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $class::info ( 'settings' ) ) {
					$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
					api::nav ( "/servers" , l::t("Серверы") );
					api::nav ( "/servers/base/" . $id , $adress );
					api::nav ( "" , l::t('Параметры запуска') , '1' );
					servers::$speedbar = $id;
					if($row['game']=="rust"){
						$class::settings ( $row );
					}else{
						api::inc ( 'ssh2' );
						if ( ssh::gh_box ( $row[ 'box' ] ) ) {
							$class::settings ( $row );
							ssh::disconnect ();
						} else {
							api::result ( l::t('Не удалось установить соединение с сервером') );
						}
					}
				} else {
					api::result ( l::t('Данная функция отключена') );
				}
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}
}

?>