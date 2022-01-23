<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class servers_slots
{
	public static function base ( $id )
	{
		if ( api::admin ( 'servers' ) ) {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		} else {
			db::q ( 'SELECT * FROM gh_servers where id="' . $id . '" and user="' . api::info ( 'id' ) . '"' );
		}
		if ( db::n () != 1 ) {
			if ( ! servers::friend ( $id , 'slots' ) ) {
				api::result ( l::t ( 'Недостаточно привилегий!' ) );

				return false;
			} else {
				db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
			}
		}
		if ( db::n () == 1 ) {
			$row = db::r ();
			if ( $row[ 'game' ] == "ts3" ) {
				api::result ( l::t ( 'Данная функция отключена у серверов TS3' ) );
			}
			if ( $row[ 'time' ] < time () ) {
				api::result ( l::t ( 'Срок аренды сервера истек' ) );
			} else {
				db::q ( 'SELECT * FROM gh_rates where id="' . $row[ 'rate' ] . '"' );
				$rate = db::r ();
				$adress = servers::ip_server($row['box']) . ':' . $row[ 'port' ];
				$data2 = $_POST[ 'data' ];
				if ( $data2 ) {
					if(api::$demo){
						api::result ( l::t ( 'Данная функция отключена в демо режиме.' ) );
						return false;
					}
					if ( $data2[ 'slots' ] == $row[ 'slots' ] ) {
						api::result ( l::t ( 'Ничего не изменено' ) , true );
					} else {
						if ( $data2[ 'slots' ] >= 10 && $data2[ 'slots' ] <= 255 ) {
							$pd = (int) ( ( $row[ 'time' ] - time () ) / 86400 );
							$pd2 = $rate[ 'price' ] / 30;
							if ( $row[ 'slots' ] > $data2[ 'slots' ] ) {
								$price = api::price ( api::price ( $pd * $row[ 'slots' ] * $pd2 ) - api::price ( $pd * $data2[ 'slots' ] * $pd2 ) );
								$price = api::price ( $price - ( $price / 100 * 10 ) );
								$t = 1;
							} else {
								$price = api::price ( api::price ( $pd * $data2[ 'slots' ] * $pd2 ) - api::price ( $pd * $row[ 'slots' ] * $pd2 ) );
								$price = api::price ( $price + ( $price / 100 * 10 ) );
								if ( api::info ( 'balance' ) < $price ) {
									api::result ( l::t ( 'Недостаточно средств на счете' ) );

									return false;
								}
								$t = 0;
							}
							if ( $price == 0 ) {
								api::result ( l::t ( 'Вы не можете изменить слоты, т.к. сумма равна 0' ) );

								return false;
							}
							if ( ! api::admin ( 'puy_servers' ) ) {
								$msg = "Смена слотов с " . $row[ 'slots' ] . " на " . (int) $data2[ 'slots' ] . " у " . $adress;
								if ( $t == 1 ) {
									api::log_balance ( api::info ( 'id' ) , $msg , '0' , $price );
									db::q ( 'UPDATE users set balance="' . api::price ( api::info ( 'balance' ) + $price ) . '" where id="' . api::info ( 'id' ) . '"' );
								} else {
									api::log_balance ( api::info ( 'id' ) , $msg , '1' , $price );
									db::q ( 'UPDATE users set balance="' . api::price ( api::info ( 'balance' ) - $price ) . '" where id="' . api::info ( 'id' ) . '"' );
								}
							}
							db::q ( 'UPDATE gh_servers set slots="' . (int) $data2[ 'slots' ] . '" where id="' . $id . '"' );
							api::result ( l::t ( 'Оплачено' ) , true );
						}
					}
				}
				tpl::load ( 'servers-slots' );
				tpl::set ( '{id}' , $id );
				tpl::set ( '{slot}' , $row[ 'slots' ] );
				tpl::set ( '{price_d}' , (int) ( ( $row[ 'time' ] - time () ) / 86400 ) );
				tpl::set ( '{price_d2}' , api::price ( $rate[ 'price' ] / 30 ) );
				$slots = '';
				for ( $i = ( $rate[ 'min_slots' ] + 1 ) ; $i <= $rate[ 'max_slots' ] ; $i ++ ) {
					if ( $row[ 'slots' ] == $i ) {
						$slots .= '<option value="' . $i . '" selected>' . $i . '</option>';
					} else {
						$slots .= '<option value="' . $i . '">' . $i . '</option>';
					}
				}
				tpl::set ( '{slots}' , $slots );
				tpl::compile ( 'content' );
				if ( api::modal () ) {
					die( tpl::result ( 'content' ) );
				}
			}
		} else {
			api::result ( l::t ( 'Сервер не найден' ) );
		}
	}
}

?>