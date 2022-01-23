<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class servers_console
{
	public static function base ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if(!servers::friend ( $id  , 'console' )){
				api::result( l::t ('Недостаточно привилегий!'));
				return false;
			}else{
				db::q ( 'SELECT * FROM gh_servers where id="' . $id. '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
			api::nav ( "/servers" , l::t("Серверы") );
			api::nav ( "/servers/base/" . $id , $adress );
			api::nav ( "" , l::t('Консоль') , '1' );
			servers::$speedbar = $id;
			$class = servers::game_class ( $row[ 'game' ] );
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t('Срок аренды сервера истек') );
			} else {
				if ( $row[ 'status' ] != "1" ) {
					api::result ( l::t('Включите сервер') );
				} else {
					if ( $class::info ( 'console' ) ) {
						api::inc ( 'ssh2' );
						if ( ssh::gh_box ( $row[ 'box' ] ) ) {
							if ( $_REQUEST[ 'get' ] == 1 ) {
								if(api::$demo){
									api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
									return false;
								}
								$comand = api::cl ( $_POST[ 'data' ][ 'comand' ] );
								if ( $comand ) {
									if ( ! preg_match ( "/^[0-9a-zA-Z_ \/]{0,40}$/i" , $comand ) ) {
										if ( mb_strlen ( $comand , "utf-8" ) > 40 ) {
											api::result ( l::t('Максимальная длина команды 40 символов') );
										} else {
											api::result ( l::t('Команда содержит недопустимые символы') );

											return false;
										}
									} else {
										$pid = servers::get_pid_screen($row[ 'sid' ]);
										if(!$pid){
											api::result ( l::t('Не найден процесс') );
											return false;
										}
										$exec = 'screen -S server_' . $row[ 'sid' ] . ' -p 0 -X stuff \'' . $comand . '\'$\'\n\';';
										ssh::exec_cmd ( $exec );
										sleep ( 1 );
									}
								}
								$exec = "cd /host/" . $row[ 'user' ] . "/" . $row[ 'sid' ] . "/;";
								$exec .= "screen -S server_" . $row[ 'sid' ] . " -X -p 0 hardcopy -h ./console.txt";
								ssh::exec_cmd ( $exec );
								sleep ( 2 );
								$exec1 = ROOT . '/conf/logs/' . $row[ 'user' ] . '_' . $row[ 'id' ] . '.txt';
								ssh::get_file ( "/host/" . $row[ 'user' ] . "/" . $row[ 'sid' ] . "/console.txt" , $exec1 );
								sleep ( 2 );
								api::ajax_d ( str_replace ( '\n' , "\n" , htmlspecialchars ( trim ( file_get_contents ( $exec1 ) ) , null , '' ) ) );
							} else {
								tpl::load ( 'servers-console' );
								tpl::set ( '{id}' , $row[ 'id' ] );
								tpl::set ( '{data}' , ' ' );
								tpl::compile ( 'content' );
							}
						} else {
							api::result ( l::t('Не удалось установить соединение с сервером') );
						}
					}else{
						api::result ( l::t('Данная функция отключена') );
					}
				}
			}
		} else {
			api::result ( l::t('Сервер не найден') );
		}
	}
}

?>