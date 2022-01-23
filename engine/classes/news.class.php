<?php

class news
{
	public static $langdate = array (
		'January'   => "январь" ,
		'February'  => "февраль" ,
		'March'     => "март" ,
		'April'     => "апрель" ,
		'May'       => "май" ,
		'June'      => "июнь" ,
		'July'      => "июль" ,
		'August'    => "август" ,
		'September' => "сентябрь" ,
		'October'   => "октябрь" ,
		'November'  => "ноябрь" ,
		'December'  => "декабрь"
	);

	public static function langdate ( $format , $stamp )
	{
		if(l::$lang!="ru"){
			foreach (self::$langdate as $key=>$val){
				self::$langdate[$key] = l::t($val);
			}
		}
		return strtr ( @date ( $format , $stamp ) , self::$langdate );
	}

	public static function base ( $pa = 1 )
	{
		global $title , $array;
		api::inc ( 'comments' );
		$year = (int) $array[ 'year' ];
		$month = (int) $array[ 'month' ];
		$day = (int) $array[ 'day' ];
		$ttt = mktime ( 0 , 0 , 0 , $month , $day , $year );
		if ( $pa == 1 ) {
			$title = l::t("Новости");
		}
		$date_url = "";
		if ( $year != 0 ) {
			$ttt2 = $ttt + 31556926;
			$ymd = "(time>='" . $ttt . "' and time<='" . $ttt2 . "')";
			$title = l::t("Материалы за")." ". $year;
			$date_url = "/" . $year;
		}
		if ( $month != 0 ) {
			$ttt2 = $ttt + 2629743;
			$nmonth = self::langdate ( "F" , $ttt2 );
			$ymd = "(time>='" . $ttt . "' and time<='" . $ttt2 . "')";
			$title = l::t("Материалы за")." ". $nmonth . ' ' . $year;
			$date_url = "/" . $year . "/" . $month;
		}
		if ( $day != 0 ) {
			$ttt2 = $ttt + 86400;
			$nmonth = api::langdate ( "F" , $ttt2 );
			$ymd = "(time>='" . $ttt . "' and time<='" . $ttt2 . "')";
			$title = l::t("Материалы за")." ". $day . ' ' . $nmonth . ' ' . $year;
			$date_url = "/" . $year . "/" . $month . "/" . $day;
		}

		if ( ! $news ) {
			$news = '10';
		}
		if ( $array[ 'cat' ] ) {
			$sql3 = db::q ( 'SELECT id,name,url FROM news_cat where url="' . api::cl ( $array[ 'cat' ] ) . '"' );
			if ( db::n ( $sql3 ) != 0 ) {
				$cat = db::r ( $sql3 );
				$title = $cat[ 'name' ];
				if ( $ymd ) {
					db::q ( 'SELECT id FROM news where cat="' . $cat[ 'id' ] . '" and ' . $ymd );
				} else {
					db::q ( 'SELECT id FROM news where cat="' . $cat[ 'id' ] . '"' );
				}
			} else {
				api::result ( l::t('Категория не найдена') );

				return false;
			}
		} else {
			db::q ( 'SELECT id FROM news' );
		}
		$all = db::n ();

		if ( (int) $array[ 'page' ] ) {
			if ( ( $all / $news ) > (int) $array[ 'page' ] ) {
				$page = $news * (int) $array[ 'page' ];
			} else {
				$page = 0;
			}
		} else {
			$page = 0;
		}
		if ( $cat ) {
			if ( $ymd ) {
				$sql = db::q ( 'SELECT * FROM news where cat="' . $cat[ 'id' ] . '" and ' . $ymd . ' order by id desc LIMIT ' . $page . ' ,' . $news );
			} else {
				$sql = db::q ( 'SELECT * FROM news where cat="' . $cat[ 'id' ] . '" order by id desc LIMIT ' . $page . ' ,' . $news );
			}
		} else {
			if ( $ymd ) {
				$sql = db::q ( 'SELECT * FROM news where ' . $ymd . ' order by id desc LIMIT ' . $page . ' ,' . $news );
			} else {
				$sql = db::q ( 'SELECT * FROM news order by id desc LIMIT ' . $page . ' ,' . $news );
			}
		}
		while ( $row = db::r ( $sql ) ) {
			tpl::load ( 'main-news-short' );
			tpl::set ( '{uid}' , $row[ 'user' ] );
			if ( $cat ) {
				tpl::set ( '{cat_link}' , '/news/' . $cat[ 'url' ] . '/' );
				tpl::set ( '{cat}' , $cat[ 'name' ] );
				tpl::set ( '{cat-id}' ,  $cat[ 'id' ] );
			} else {
				$sql3 = db::q ( 'SELECT name,url,id FROM news_cat where id="' . $row[ 'cat' ] . '"' );
				if ( db::n ( $sql3 ) != 0 ) {
					$row3 = db::r ( $sql3 );
					tpl::set ( '{cat_link}' , '/news/' . $row3[ 'url' ] . '/' );
					tpl::set ( '{cat}' , $row3[ 'name' ] );
					tpl::set ( '{cat-id}' , $row3[ 'id' ] );
				} else {
					tpl::set ( '{cat_link}' , '/news/' );
					tpl::set ( '{cat}' , l::t('Новости') );
					tpl::set ( '{cat-id}' , '0' );
				}
			}
			$d = array ();
			$d[ 'table' ] = 'news_comm';
			$d[ 'id' ] = $row[ 'id' ];
			tpl::set ( '{com}' , comments::num ( $d ) );
			tpl::set ( '{title}' , $row[ 'name' ] );
			api::inc ( 'bbcode' );
			tpl::set ( '{data}' , bbcode::html ( base64_decode ( $row[ 'info' ] ) ) );
			tpl::set ( '{id}' , $row[ 'id' ] );
			if ( $row[ 'cat' ] != 0 ) {
				if ( $cat ) {
					$link = '/news/' . $cat[ 'url' ] . '/' . $row[ 'id' ] . '-' . $row[ 'url' ];
				} else {
					$link = '/news/' . $row3[ 'url' ] . '/' . $row[ 'id' ] . '-' . $row[ 'url' ];
				}
			} else {
				$link = '/news/' . $row[ 'id' ] . '-' . $row[ 'url' ];
			}
			tpl::set ( '{link}' , $link );
			$d2 = date ( "d" , $row[ 'time' ] );
			$m = date ( "m" , $row[ 'time' ] );
			$Y = date ( "Y" , $row[ 'time' ] );
			if ( $row[ 'cat' ] != 0 ) {
				if ( $cat ) {
					tpl::set ( '{link_time}' , '/news/' . $cat[ 'url' ] . '/' . $Y . '/' . $m . '/' . $d2 . '/' );
				} else {
					tpl::set ( '{link_time}' , '/news/' . $row3[ 'url' ] . '/' . $Y . '/' . $m . '/' . $d2 . '/' );
				}
			} else {
				tpl::set ( '{link_time}' , '/news/' . $Y . '/' . $m . '/' . $d2 . '/' );
			}
			tpl::set ( '{time2}' , api::langdate ( "H:i" , $row[ 'time' ] ) );
			tpl::set ( '{time}' , api::langdate ( "d-m-Y H:i" , $row[ 'time' ] ) );
			tpl::set ( '{date}' , api::langdate ( "d/m/Y" , $row[ 'time' ] ) );
			tpl::set ( '{date2}' , api::langdate ( "d-m-Y" , $row[ 'time' ] ) );
			tpl::compile ( 'data' );
		}
		if ( ! tpl::result ( 'data' ) ) {
			api::e404 ( l::t('По данному критерию новости не найдены, либо у вас нет доступа для просмотра этих новостей.') );
		}
		tpl::load ( 'main-news' );
		tpl::set ( '{data}' , tpl::result ( 'data' ) );
		tpl::$result[ 'data' ] = '';
		if ( $pa == 1 ) {
			if ( $year != 0 ) {
				api::nav ( "/news/" , l::t("Новости"));
				if ( $cat ) {
					api::nav ( "/news/" . $cat[ 'url' ] . '/' , $cat[ 'name' ] );
					$l = "/" . $cat[ 'url' ] . "/";
				} else {
					$l = "/";
				}
				if ( $month == 0 && $day == 0 ) {
					api::nav ( "" , $title , 1 );
				} else {
					if ( $month != 0 && $day != 0 ) {
						api::nav ( "/news" . $l . $year . "/" , $year );
						api::nav ( "/news" . $l . $year . "/" . $month . "/" , $month );
					} else {
						api::nav ( "/news" . $l . $year . "/" , $year );
						api::nav ( "" , $title , 1 );
					}
					if ( $day != 0 ) {
						api::nav ( "" , $title , 1 );
					}
				}
			} else {
				if ( $cat ) {
					api::nav ( "/news" , l::t('Новости') );
					api::nav ( "" , $cat[ 'name' ] , '1' );
				} else {
					api::nav ( "/news/" , l::t("Новости"), '1' );
				}
			}
		}
		if ( $nav ) {
			tpl::set_block ( "'\\[nav\\](.*?)\\[/nav\\]'si" , "" );
		} else {
			tpl::set_block ( "'\\[nav\\](.*?)\\[/nav\\]'si" , "\\1" );
			if ( $cat ) {
				$l = '/news/' . $cat[ 'url' ];
			} else {
				$l = '/news';
			}
			$l .= $date_url;
			tpl::set ( '{nav}' , api::pagination ( $all , $news , (int) $array[ 'page' ] , $l ) );
		}
		tpl::compile ( 'content' );
	}

	public static function full_base ( $id )
	{
		global $title , $array;
		api::inc ( 'comments' );
		db::q ( 'SELECT * FROM news where id="' . $id . '"' );
		if ( db::n () == "1" ) {
			$row = db::r ();
			if ( $array[ 'name' ] == $row[ 'url' ] ) {
				$sql3 = db::q ( 'SELECT name,url FROM news_cat where id="' . $row[ 'cat' ] . '"' );
				$row3 = db::r ( $sql3 );
				$d[ 'table' ] = 'news_comm';
				$d[ 'id' ] = $id;
				if ( $_POST[ 'data' ] ) {
					if ( $_POST[ 'data' ][ 'id' ] ) {
						$d[ 'id' ] = $_POST[ 'data' ][ 'id' ];
						comments::dell ( $d );
					} else {
						$d[ 'comment' ] = $_POST[ 'data' ][ 'comment' ];
						comments::add ( $d );
						unset( $d[ 'comment' ] );
					}
				}
				if ( $array[ 'cat' ] ) {
					if ( $row[ 'cat' ] == 0 ) {
						api::e404 ( l::t('Новость не найдена') );

						return false;
					} else {
						if ( $array[ 'cat' ] != $row3[ 'url' ] ) {
							api::e404 ( l::t('Новость не найдена') );

							return false;
						}
					}
				} else {
					if ( $row[ 'cat' ] != 0 ) {
						api::e404 ( l::t('Новость не найдена') );

						return false;
					}
				}
				$title = $row[ 'name' ];
				tpl::load ( 'main-news-full' );
				tpl::set ( '{uid}' , $row[ 'user' ] );
				if ( $row[ 'cat' ] != 0 ) {
					tpl::set ( '{cat_link}' , '/news/' . $row3[ 'url' ] . '/' );
					tpl::set ( '{cat}' , $row3[ 'name' ] );
				} else {
					tpl::set ( '{cat_link}' , '/news/' );
					tpl::set ( '{cat}' , l::t('Новости') );
				}
				tpl::set ( '{com}' , comments::num ( $d ) );
				tpl::set ( '{title}' , $row[ 'name' ] );
				api::inc ( 'bbcode' );
				tpl::set ( '{data}' , bbcode::html ( base64_decode ( $row[ 'info2' ] ) ) );
				tpl::set ( '{time}' , api::langdate ( "j F Y - H:i" , $row[ 'time' ] ) );
				if ( $row[ 'cat' ] != 0 ) {
					$sql4 = db::q ( 'SELECT url FROM news_cat where id="' . $row[ 'cat' ] . '"' );
					$row4 = db::r ( $sql4 );
					$link = '/news/' . $row4[ 'url' ] . '/' . $row[ 'id' ] . '-' . $row[ 'url' ];
				} else {
					$link = '/news/' . $row[ 'id' ] . '-' . $row[ 'url' ];
				}
				tpl::set ( '{link}' , $link );
				$d2 = date ( "d" , $row[ 'time' ] );
				$m = date ( "m" , $row[ 'time' ] );
				$Y = date ( "Y" , $row[ 'time' ] );
				if ( $row[ 'cat' ] != 0 ) {
					tpl::set ( '{link_time}' , '/news/' . $row4[ 'url' ] . '/' . $Y . '/' . $m . '/' . $d2 . '/' );
				} else {
					tpl::set ( '{link_time}' , '/news/' . $Y . '/' . $m . '/' . $d2 . '/' );
				}
				$d[ 'link' ] = $link;
				tpl::set ( '{comments}' , comments::base ( $d ) );
				tpl::compile ( 'content' );
				api::nav ( '/news' , l::t('Новости') );
				if ( $row[ 'cat' ] != 0 ) {
					api::nav ( '/news/' . $row3[ 'url' ] . '/' , $row3[ 'name' ] );
				}
				api::nav ( "" , $row[ 'name' ] , '1' );
				db::q ( 'UPDATE news set visits="' . ( $row[ 'visits' ] + 1 ) . '" where id="' . $id . '"' );
			} else {
				api::e404 ( l::t('Новость не найдена') );
			}
		} else {
			api::e404 ( l::t('Новость не найдена') );
		}
	}
	public static function base_mobile ()
	{
		$sql = db::q ( 'SELECT * FROM news order by id desc LIMIT 0,10');
		$news = array();
		while ( $row = db::r ( $sql ) ) {
			$new = array();
			$new['name']= $row[ 'name' ];
			$new['data']= $row[ 'info' ];
			$new['date']= api::langdate ( "d/m/Y" , $row[ 'time' ] );
			$news[] = $new;
		}
		mobile::$data['news'] = $news;
	}
}

?>