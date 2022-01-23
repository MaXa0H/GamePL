<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class admin_money
{
	public static function listen ()
	{
		global $title;
		api::nav ( '' , l::t ('История платежей') , '1' );
		$title = l::t ("История платежей");
		db::q ( 'SELECT id FROM logs_balance order by id desc' );
		$all = db::n ();
		$pages = (int) r::g ( 3 );
		if ( $pages ) {
			if ( ( $all / 10 ) > $pages ) {
				$page = 10 * $pages;
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		$sql = db::q ( 'SELECT * FROM logs_balance order by id desc LIMIT ' . $page . ' ,10' );
		while ( $row = db::r ( $sql ) ) {
			tpl::load2 ( 'admin-money-listen-get' );
			tpl::set ( '{id}' , $row[ 'id' ] );
			tpl::set ( '{com}' , $row[ 'mes' ] );
			tpl::set ( '{sum}' , $row[ 'sum' ] );
			tpl::set ( '{time}' , api::langdate ( "d.m.Y - H:i" , $row[ 'time' ] ) );
			if ( $row[ 'tip' ] == 0 ) {
				tpl::set_block ( "'\\[act-0\\](.*?)\\[/act-0\\]'si" , "\\1" );
				tpl::set_block ( "'\\[act-1\\](.*?)\\[/act-1\\]'si" , "" );
			} else {
				tpl::set_block ( "'\\[act-1\\](.*?)\\[/act-1\\]'si" , "\\1" );
				tpl::set_block ( "'\\[act-0\\](.*?)\\[/act-0\\]'si" , "" );
			}
			tpl::compile ( 'data' );

		}
		tpl::load2 ( 'admin-money-listen' );
		$key = m::g ( "money_chart" );
		if ( empty( $key ) ) {
			$key = self::chart ( );
			if ( ! empty( $key ) ) {
				tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "\\1" );
				tpl::set ( '{chart}' , '[' . $key . ']' );
			} else {
				tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "" );
			}
		} else {
			tpl::set_block ( "'\\[chart\\](.*?)\\[/chart\\]'si" , "\\1" );
			tpl::set ( '{chart}' , '[' . $key . ']' );
		}
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::set ( '{nav}' , api::pagination ( $all , 10 , $pages , '/admin/money' ) );
		tpl::compile ( 'content' );
	}

	public static function gettime ( $t )
	{
		$d = date ( "d" , $t );
		$m = date ( "m" , $t );
		$Y = date ( "Y" , $t );
		$time = mktime ( 0 , 0 , 0 , $m , $d , $Y );

		return $time . "000";
	}

	public static function chart ()
	{
		$key = m::g ( 'money_chart' );
		if ( empty( $key ) ) {
			$sql = db::q ( 'SELECT * FROM logs_balance where tip="0" order by id asc' );
			$data = '';
			while ( $row = db::r ( $sql ) ) {
				$data[ self::gettime ( $row[ 'time' ] ) ] += $row[ 'sum' ];
			}
			$g = "1";
			$echo = '';
			foreach ( $data as $go => $val ) {
				if ( $g == "1" ) {
					$echo .= "[" . $go . "," . $val . "]";
				} else {
					$echo .= ",[" . $go . "," . $val . "]";
				}
				$g = $g + 1;
			}
			m::s ( 'money_chart' , $echo , 180 );

			return $echo;
		} else {
			return $key;
		}
	}
}

?>