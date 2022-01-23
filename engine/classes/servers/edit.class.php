<?php

class servers_edit
{
	public static function base ( $id )
	{
		if ( api::admin ( 'servers_edit' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			if ( db::n () == 1 ) {
				$row = db::r ();
				$cfg = servers::cfg ( $row[ 'id' ] );
				$class = servers::game_class ( $row[ 'game' ] );
				if ( $_POST[ 'data' ] ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					$data = $_POST[ 'data' ];
					if ( ! preg_match ( "/^[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}+\.[0-9]{1,3}$/i" , $data[ 'ip' ] ) ) {
						api::result ( l::t ( 'Ip адрес введен неверно' ) );
					} else {
						if ( $data[ 'port' ] < 10 || $data[ 'port' ] > 65000 ) {
							api::result ( l::t ( 'Порт разрешено задавать в диапазоне от 10 до 65000' ) );
						} else {
							$date = $data[ 'time' ];
							$pack = explode ( "/" , $date );
							$date = mktime ( '23' , 0 , 0 , $pack[ '1' ] , $pack[ '0' ] , $pack[ '2' ] );
							$slots = (int) $data[ 'slots' ];
							db::q ( "UPDATE gh_servers set time='" . $date . "',ip='" . $data[ 'ip' ] . "',port='" . $data[ 'port' ] . "',slots='" . $slots . "' where id='" . $id . "'" );
							if ( $class::info ( 'tickrate' ) ) {
								$data3[ 'tickrate' ] = (int) $data[ 'tic' ];
								servers::configure ( $data3 , $id );
							}
							if ( $class::info ( 'fps' ) ) {
								$data3[ 'fps' ] = (int) $data[ 'fps' ];
								servers::configure ( $data3 , $id );
							}
							api::result ( l::t ( 'Сохранено' ) , true );
						}
					}
				}
				tpl::load ( 'servers-edit' );
				tpl::set ( '{id}' , $id );
				tpl::set ( '{ip}' , servers::ip_server($row['box']) );
				tpl::set ( '{port}' , $row[ 'port' ] );
				tpl::set ( '{time}' , api::langdate ( "j/m/Y" , $row[ 'time' ] ) );
				tpl::set ( '{slots}' , $row[ 'slots' ] );
				if ( $class::info ( 'tickrate' ) ) {
					tpl::set_block ( "'\\[tic\\](.*?)\\[/tic\\]'si" , "\\1" );
					tpl::set ( '{tic}' , $cfg[ 'tickrate' ] );
				} else {
					tpl::set_block ( "'\\[tic\\](.*?)\\[/tic\\]'si" , "" );
				}
				if ( $class::info ( 'fps' ) ) {
					tpl::set_block ( "'\\[fps\\](.*?)\\[/fps\\]'si" , "\\1" );
					tpl::set ( '{fps}' , $cfg[ 'fps' ] );
				} else {
					tpl::set_block ( "'\\[fps\\](.*?)\\[/fps\\]'si" , "" );
				}
				tpl::compile ( 'content' );
				if ( api::modal () ) {
					die( tpl::result ( 'content' ) );
				}
			} else {
				api::result ( l::t ( 'Сервер не найден' ) );
			}
		} else {
			api::result ( l::t ( 'Недостаточно привелегий' ) );
		}
	}
}

?>