<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class chart
{
	public static function online ($act=false)
	{
		$id = (int) r::g ( 3 );
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () != 0 ) {
			$row = db::r ();
			api::inc ( "chart/pData" );
			api::inc ( "chart/pDraw" );
			api::inc ( "chart/pImage" );
			$font = ROOT . '/engine/classes/chart/font.ttf';
			$font2 = ROOT . '/engine/classes/chart/font2.ttf';
			$font3 = ROOT . '/engine/classes/chart/font3.ttf';
			$font4 = ROOT . '/engine/classes/chart/font4.ttf';
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 4 ) ) ) {
				$color1[ 'r' ] = "120";
				$color1[ 'g' ] = "120";
				$color1[ 'b' ] = "120";
			} else {
				$a = explode ( '.' , r::g ( 4 ) );
				$color1[ 'r' ] = $a[ 0 ];
				$color1[ 'g' ] = $a[ 1 ];
				$color1[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 5 ) ) ) {
				$color2[ 'r' ] = "90";
				$color2[ 'g' ] = "90";
				$color2[ 'b' ] = "90";
			} else {
				$a = explode ( '.' , r::g ( 5 ) );
				$color2[ 'r' ] = $a[ 0 ];
				$color2[ 'g' ] = $a[ 1 ];
				$color2[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 6 ) ) ) {
				$color3[ 'r' ] = "0";
				$color3[ 'g' ] = "0";
				$color3[ 'b' ] = "0";
			} else {
				$a = explode ( '.' , r::g ( 6 ) );
				$color3[ 'r' ] = $a[ 0 ];
				$color3[ 'g' ] = $a[ 1 ];
				$color3[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 7 ) ) ) {
				$color4[ 'r' ] = "0";
				$color4[ 'g' ] = "0";
				$color4[ 'b' ] = "0";
			} else {
				$a = explode ( '.' , r::g ( 7 ) );
				$color4[ 'r' ] = $a[ 0 ];
				$color4[ 'g' ] = $a[ 1 ];
				$color4[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 8 ) ) ) {
				$color5[ 'r' ] = "255";
				$color5[ 'g' ] = "255";
				$color5[ 'b' ] = "255";
			} else {
				$a = explode ( '.' , r::g ( 8 ) );
				$color5[ 'r' ] = $a[ 0 ];
				$color5[ 'g' ] = $a[ 1 ];
				$color5[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 9 ) ) ) {
				$color6[ 'r' ] = "250";
				$color6[ 'g' ] = "250";
				$color6[ 'b' ] = "250";
			} else {
				$a = explode ( '.' , r::g ( 9 ) );
				$color6[ 'r' ] = $a[ 0 ];
				$color6[ 'g' ] = $a[ 1 ];
				$color6[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 10 ) ) ) {
				$color7[ 'r' ] = "153";
				$color7[ 'g' ] = "222";
				$color7[ 'b' ] = "121";
			} else {
				$a = explode ( '.' , r::g ( 10 ) );
				$color7[ 'r' ] = $a[ 0 ];
				$color7[ 'g' ] = $a[ 1 ];
				$color7[ 'b' ] = $a[ 2 ];
			}
			if(!$act){
				$act = '0';
			}else{
				$act = '1';
			}
			$md5 = l::$lang.$act.'_day_online_'.$id.md5(
					$color1[ 'r' ].
					$color1[ 'g' ].
					$color1[ 'b' ].
					$color2[ 'r' ].
					$color2[ 'g' ].
					$color2[ 'b' ].
					$color3[ 'r' ].
					$color3[ 'g' ].
					$color3[ 'b' ].
					$color4[ 'r' ].
					$color4[ 'g' ].
					$color4[ 'b' ].
					$color5[ 'r' ].
					$color5[ 'g' ].
					$color5[ 'b' ].
					$color6[ 'r' ].
					$color6[ 'g' ].
					$color6[ 'b' ].
					$color7[ 'r' ].
					$color7[ 'g' ].
					$color7[ 'b' ]
			);
			$a = m::g ($md5);
			if ( empty($a)){
				$time = api::gettime ();
				if(!$act){
					$title = l::t ( "Онлайн за сутки" );
				}else{
					$title = l::t ( "Онлайн за неделю" );
				}

				$title2 = l::t ( "Онлайн:" ) . " " . $row[ 'online' ] . "/" . $row[ 'slots' ];
				$title3 = l::t ( "Средний онлайн:" );
				$title4 = l::t ( "Игровой хостинг:" ).' ' . $_SERVER[ 'HTTP_HOST' ];
				$title5 = $row[ 'name' ] . " (" . servers::ip_server($row['box']) . ':' . $row[ 'port' ] . ")";
				$MyData = new pData();
				$i = 0;
				$keys = array ();
				$values = array ();
				$all = 0;
				$data = array();
				if(!$act) {
					$time = $time - 600 * 144;
				}else{
					$time = $time - 600 * 144*7;
				}
				$sql = db::q ( 'SELECT * FROM gh_monitoring_time where sid="' . $id . '" and time>"'.$time.'" order by id asc' );
				while ( $row = db::r ( $sql ) ) {
					$data[ $row[ 'time' ] ] = $row[ 'online' ];
				}
				while ( true ) {
					if(!$act) {
						if ( $i > 144 ) {
							break;
						}
						if($data[ $time ]){
							$on = $data[ $time];
						}else{
							$on = 0;
						}
					}else{
						if ( $i > 336 ) {
							break;
						}
						$on = 0;
						$t1 = (int)$data[ $time ];
						$t2 = (int)$data[ $time-600];
						$t3 = (int)$data[ $time-1200 ];
						$on = (int)(($t1+$t2+$t3)/3);
					}
					$MyData->addPoints ( abs ( $on ) , "Inbound" );
					$MyData->addPoints ( $time , "TimeStamp" );
					$all = $all + $on;
					if(!$act) {
						$time = $time + 600;
					}else{
						$time = $time + 1800;
					}
					$i ++;
				}
				if(!$act) {
					$title3 = $title3 . " " . (int) ( $all / 144 );
				}else{
					$title3 = $title3 . " " . (int) ( $all / 336 );
				}
				$MyData->setAxisName ( 0 , "" );
				$MyData->setAxisDisplay ( 0 , AXIS_FORMAT_DEFAULT );
				$MyData->setSerieDescription ( "TimeStamp" , "time" );
				$MyData->setAbscissa ( "TimeStamp" );
				if(!$act) {
					$MyData->setXAxisDisplay ( AXIS_FORMAT_TIME , "H:00" );
				}else{
					$MyData->setXAxisDisplay (AXIS_FORMAT_CUSTOM,"XAxisFormat");
				}


				/* Create the pChart object */
				$myPicture = new pImage( 705 , 235 , $MyData );

				/* Turn of Antialiasing */
				$myPicture->Antialias = false;

				/* Draw a background */
				$Settings = array ( "R" => $color2[ 'r' ] , "G" => $color2[ 'g' ] , "B" => $color2[ 'b' ] , "Dash" => 1 , "DashR" => $color1[ 'r' ] , "DashG" => $color1[ 'g' ] , "DashB" => $color1[ 'b' ] );
				$myPicture->drawFilledRectangle ( 0 , 0 , 705 , 235 , $Settings );

				/* Overlay with a gradient */
				$Settings = array ( "StartR" => $color3[ 'r' ] , "StartG" => $color3[ 'g' ] , "StartB" => $color3[ 'b' ] , "EndR" => $color4[ 'r' ] , "EndG" => $color4[ 'g' ] , "EndB" => $color4[ 'b' ] , "Alpha" => 50 );
				$myPicture->drawGradientArea ( 0 , 0 , 705 , 270 , DIRECTION_VERTICAL , $Settings );

				/* Add a border to the picture */
				$myPicture->drawRectangle ( 0 , 0 , 704 , 234 , array ( "R" => 0 , "G" => 0 , "B" => 0 ) );

				/* Write the chart title */
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 11 , "R" => $color5[ 'r' ] , "G" => $color5[ 'g' ] , "B" => $color5[ 'b' ] ) );
				$myPicture->drawText ( 353 , 30 , $title , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 353 , 205 , $title5 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 10 , 225 , $title2 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 353 , 225 , $title3 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );
				$myPicture->drawText ( 695 , 225 , $title4 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMRIGHT ) );

				/* Set the default font */
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 8 ) );

				/* Define the chart area */
				$myPicture->setGraphArea ( 40 , 40 , 680 , 170 );

				/* Draw the scale */
				if(!$act) {
					$scaleSettings = array ( "XMargin" => 10 , "YMargin" => 10 , "Floating" => true , "GridR" => $color6[ 'r' ] , "GridG" => $color6[ 'g' ] , "GridB" => $color6[ 'b' ] , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>8,"DrawSubTicks"=>false);
				}else{
					$scaleSettings = array ( "XMargin" => 10 , "YMargin" => 10 , "Floating" => true , "GridR" => $color6[ 'r' ] , "GridG" => $color6[ 'g' ] , "GridB" => $color6[ 'b' ] , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>23,"DrawSubTicks"=>false);
				}

				$myPicture->drawScale ( $scaleSettings );


				/* Turn on Antialiasing */
				$myPicture->Antialias = true;
				$Threshold = "";
				$Threshold[] = array("Min"=>0,"Max"=>1000,"R"=>$color7['r'],"G"=>$color7['g'],"B"=>$color7['b'],"Alpha"=>60);
				$myPicture->drawAreaChart(array("Threshold"=>$Threshold));
				/* Draw the line chart */


				/* Write a label over the chart */
				$myPicture->writeLabel ( "Inbound" , 720 );

				ob_start ();
				$myPicture->stroke ();
				$a = ob_get_contents ();
				ob_end_clean ();
				m::s ($md5, $a , 3600 );
			}
			header ('Content-Type: image/png');
			echo $a;
			die;
		}
	}
	public static function cpu ($act=false)
	{
		$id = (int) r::g ( 3 );
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () != 0 ) {
			$row = db::r ();
			api::inc ( "chart/pData" );
			api::inc ( "chart/pDraw" );
			api::inc ( "chart/pImage" );
			$font = ROOT . '/engine/classes/chart/font.ttf';
			$font2 = ROOT . '/engine/classes/chart/font2.ttf';
			$font3 = ROOT . '/engine/classes/chart/font3.ttf';
			$font4 = ROOT . '/engine/classes/chart/font4.ttf';
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 4 ) ) ) {
				$color1[ 'r' ] = "120";
				$color1[ 'g' ] = "120";
				$color1[ 'b' ] = "120";
			} else {
				$a = explode ( '.' , r::g ( 4 ) );
				$color1[ 'r' ] = $a[ 0 ];
				$color1[ 'g' ] = $a[ 1 ];
				$color1[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 5 ) ) ) {
				$color2[ 'r' ] = "90";
				$color2[ 'g' ] = "90";
				$color2[ 'b' ] = "90";
			} else {
				$a = explode ( '.' , r::g ( 5 ) );
				$color2[ 'r' ] = $a[ 0 ];
				$color2[ 'g' ] = $a[ 1 ];
				$color2[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 6 ) ) ) {
				$color3[ 'r' ] = "0";
				$color3[ 'g' ] = "0";
				$color3[ 'b' ] = "0";
			} else {
				$a = explode ( '.' , r::g ( 6 ) );
				$color3[ 'r' ] = $a[ 0 ];
				$color3[ 'g' ] = $a[ 1 ];
				$color3[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 7 ) ) ) {
				$color4[ 'r' ] = "0";
				$color4[ 'g' ] = "0";
				$color4[ 'b' ] = "0";
			} else {
				$a = explode ( '.' , r::g ( 7 ) );
				$color4[ 'r' ] = $a[ 0 ];
				$color4[ 'g' ] = $a[ 1 ];
				$color4[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 8 ) ) ) {
				$color5[ 'r' ] = "255";
				$color5[ 'g' ] = "255";
				$color5[ 'b' ] = "255";
			} else {
				$a = explode ( '.' , r::g ( 8 ) );
				$color5[ 'r' ] = $a[ 0 ];
				$color5[ 'g' ] = $a[ 1 ];
				$color5[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 9 ) ) ) {
				$color6[ 'r' ] = "250";
				$color6[ 'g' ] = "250";
				$color6[ 'b' ] = "250";
			} else {
				$a = explode ( '.' , r::g ( 9 ) );
				$color6[ 'r' ] = $a[ 0 ];
				$color6[ 'g' ] = $a[ 1 ];
				$color6[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 10 ) ) ) {
				$color7[ 'r' ] = "153";
				$color7[ 'g' ] = "222";
				$color7[ 'b' ] = "121";
			} else {
				$a = explode ( '.' , r::g ( 10 ) );
				$color7[ 'r' ] = $a[ 0 ];
				$color7[ 'g' ] = $a[ 1 ];
				$color7[ 'b' ] = $a[ 2 ];
			}
			if(!$act){
				$act = '0';
			}else{
				$act = '1';
			}
			$md5 = l::$lang.$act.'_day_cpu_'.$id.md5(
					$color1[ 'r' ].
					$color1[ 'g' ].
					$color1[ 'b' ].
					$color2[ 'r' ].
					$color2[ 'g' ].
					$color2[ 'b' ].
					$color3[ 'r' ].
					$color3[ 'g' ].
					$color3[ 'b' ].
					$color4[ 'r' ].
					$color4[ 'g' ].
					$color4[ 'b' ].
					$color5[ 'r' ].
					$color5[ 'g' ].
					$color5[ 'b' ].
					$color6[ 'r' ].
					$color6[ 'g' ].
					$color6[ 'b' ].
					$color7[ 'r' ].
					$color7[ 'g' ].
					$color7[ 'b' ]
				);
			$a = m::g ($md5);
			if ( empty($a)){
				$time = api::gettime ();
				if(!$act){
					$title = l::t ( "Нагрузка на процессор за сутки" );
				}else{
					$title = l::t ( "Нагрузка на процессор за неделю" );
				}


				$title3 = l::t ( "Средняя нагрузка на процессор:" );
				$title4 = l::t ( "Игровой хостинг:" ).' ' . $_SERVER[ 'HTTP_HOST' ];
				$title5 = $row[ 'name' ] . " (" . servers::ip_server($row['box']) . ':' . $row[ 'port' ] . ")";
				$MyData = new pData();
				$i = 0;
				$keys = array ();
				$values = array ();
				$all = 0;
				$data = array();
				if(!$act) {
					$time = $time - 600 * 144;
				}else{
					$time = $time - 600 * 144*7;
				}
				$sql = db::q ( 'SELECT * FROM gh_monitoring_cpu_time where sid="' . $id . '" and time>"'.$time.'" order by id asc' );
				while ( $row = db::r ( $sql ) ) {
					$data[ $row[ 'time' ] ] = $row[ 'cpu' ];
				}
				while ( true ) {
					if(!$act) {
						if ( $i > 144 ) {
							break;
						}
						if($data[ $time ]){
							$on = $data[ $time];
						}else{
							$on = 0;
						}
					}else{
						if ( $i > 336 ) {
							break;
						}
						$on = 0;
						$t1 = (int)$data[ $time ];
						$t2 = (int)$data[ $time-600];
						$t3 = (int)$data[ $time-1200 ];
						$on = (int)(($t1+$t2+$t3)/3);
					}
					$MyData->addPoints ( abs ( $on ) , "Inbound" );
					$MyData->addPoints ( $time , "TimeStamp" );
					$all = $all + $on;
					if(!$act) {
						$time = $time + 600;
					}else{
						$time = $time + 1800;
					}
					$i ++;
				}
				if(!$act) {
					$title3 = $title3 . " " . (int) ( $all / 144 );
				}else{
					$title3 = $title3 . " " . (int) ( $all / 336 );
				}
				$MyData->setAxisName ( 0 , "" );
				$MyData->setAxisDisplay ( 0 , AXIS_FORMAT_DEFAULT );
				$MyData->setSerieDescription ( "TimeStamp" , "time" );
				$MyData->setAbscissa ( "TimeStamp" );
				if(!$act) {
					$MyData->setXAxisDisplay ( AXIS_FORMAT_TIME , "H:00" );
				}else{
					$MyData->setXAxisDisplay (AXIS_FORMAT_CUSTOM,"XAxisFormat");
				}


				/* Create the pChart object */
				$myPicture = new pImage( 705 , 235 , $MyData );

				/* Turn of Antialiasing */
				$myPicture->Antialias = false;

				/* Draw a background */
				$Settings = array ( "R" => $color2[ 'r' ] , "G" => $color2[ 'g' ] , "B" => $color2[ 'b' ] , "Dash" => 1 , "DashR" => $color1[ 'r' ] , "DashG" => $color1[ 'g' ] , "DashB" => $color1[ 'b' ] );
				$myPicture->drawFilledRectangle ( 0 , 0 , 705 , 235 , $Settings );

				/* Overlay with a gradient */
				$Settings = array ( "StartR" => $color3[ 'r' ] , "StartG" => $color3[ 'g' ] , "StartB" => $color3[ 'b' ] , "EndR" => $color4[ 'r' ] , "EndG" => $color4[ 'g' ] , "EndB" => $color4[ 'b' ] , "Alpha" => 50 );
				$myPicture->drawGradientArea ( 0 , 0 , 705 , 270 , DIRECTION_VERTICAL , $Settings );

				/* Add a border to the picture */
				$myPicture->drawRectangle ( 0 , 0 , 704 , 234 , array ( "R" => 0 , "G" => 0 , "B" => 0 ) );

				/* Write the chart title */
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 11 , "R" => $color5[ 'r' ] , "G" => $color5[ 'g' ] , "B" => $color5[ 'b' ] ) );
				$myPicture->drawText ( 353 , 30 , $title , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 353 , 205 , $title5 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 10 , 225 , $title3 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 695 , 225 , $title4 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMRIGHT ) );

				/* Set the default font */
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 8 ) );

				/* Define the chart area */
				$myPicture->setGraphArea ( 40 , 40 , 680 , 170 );

				/* Draw the scale */
				if(!$act) {
					$scaleSettings = array ( "XMargin" => 10 , "YMargin" => 10 , "Floating" => true , "GridR" => $color6[ 'r' ] , "GridG" => $color6[ 'g' ] , "GridB" => $color6[ 'b' ] , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>8,"DrawSubTicks"=>false);
				}else{
					$scaleSettings = array ( "XMargin" => 10 , "YMargin" => 10 , "Floating" => true , "GridR" => $color6[ 'r' ] , "GridG" => $color6[ 'g' ] , "GridB" => $color6[ 'b' ] , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>23,"DrawSubTicks"=>false);
				}

				$myPicture->drawScale ( $scaleSettings );


				/* Turn on Antialiasing */
				$myPicture->Antialias = true;
				$Threshold = "";
				$Threshold[] = array("Min"=>0,"Max"=>1000,"R"=>$color7['r'],"G"=>$color7['g'],"B"=>$color7['b'],"Alpha"=>60);
				$myPicture->drawAreaChart(array("Threshold"=>$Threshold));
				/* Draw the line chart */


				/* Write a label over the chart */
				$myPicture->writeLabel ( "Inbound" , 720 );

				ob_start ();
				$myPicture->stroke ();
				$a = ob_get_contents ();
				ob_end_clean ();
				m::s ($md5, $a , 3600 );
			}
			header ('Content-Type: image/png');
			echo $a;
			die;
		}
	}
	public static function ram ($act=false)
	{
		$id = (int) r::g ( 3 );
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () != 0 ) {
			$row = db::r ();
			api::inc ( "chart/pData" );
			api::inc ( "chart/pDraw" );
			api::inc ( "chart/pImage" );
			$font = ROOT . '/engine/classes/chart/font.ttf';
			$font2 = ROOT . '/engine/classes/chart/font2.ttf';
			$font3 = ROOT . '/engine/classes/chart/font3.ttf';
			$font4 = ROOT . '/engine/classes/chart/font4.ttf';
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 4 ) ) ) {
				$color1[ 'r' ] = "120";
				$color1[ 'g' ] = "120";
				$color1[ 'b' ] = "120";
			} else {
				$a = explode ( '.' , r::g ( 4 ) );
				$color1[ 'r' ] = $a[ 0 ];
				$color1[ 'g' ] = $a[ 1 ];
				$color1[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 5 ) ) ) {
				$color2[ 'r' ] = "90";
				$color2[ 'g' ] = "90";
				$color2[ 'b' ] = "90";
			} else {
				$a = explode ( '.' , r::g ( 5 ) );
				$color2[ 'r' ] = $a[ 0 ];
				$color2[ 'g' ] = $a[ 1 ];
				$color2[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 6 ) ) ) {
				$color3[ 'r' ] = "0";
				$color3[ 'g' ] = "0";
				$color3[ 'b' ] = "0";
			} else {
				$a = explode ( '.' , r::g ( 6 ) );
				$color3[ 'r' ] = $a[ 0 ];
				$color3[ 'g' ] = $a[ 1 ];
				$color3[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 7 ) ) ) {
				$color4[ 'r' ] = "0";
				$color4[ 'g' ] = "0";
				$color4[ 'b' ] = "0";
			} else {
				$a = explode ( '.' , r::g ( 7 ) );
				$color4[ 'r' ] = $a[ 0 ];
				$color4[ 'g' ] = $a[ 1 ];
				$color4[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 8 ) ) ) {
				$color5[ 'r' ] = "255";
				$color5[ 'g' ] = "255";
				$color5[ 'b' ] = "255";
			} else {
				$a = explode ( '.' , r::g ( 8 ) );
				$color5[ 'r' ] = $a[ 0 ];
				$color5[ 'g' ] = $a[ 1 ];
				$color5[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 9 ) ) ) {
				$color6[ 'r' ] = "250";
				$color6[ 'g' ] = "250";
				$color6[ 'b' ] = "250";
			} else {
				$a = explode ( '.' , r::g ( 9 ) );
				$color6[ 'r' ] = $a[ 0 ];
				$color6[ 'g' ] = $a[ 1 ];
				$color6[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 10 ) ) ) {
				$color7[ 'r' ] = "153";
				$color7[ 'g' ] = "222";
				$color7[ 'b' ] = "121";
			} else {
				$a = explode ( '.' , r::g ( 10 ) );
				$color7[ 'r' ] = $a[ 0 ];
				$color7[ 'g' ] = $a[ 1 ];
				$color7[ 'b' ] = $a[ 2 ];
			}
			if(!$act){
				$act = '0';
			}else{
				$act = '1';
			}
			$md5 = l::$lang.$act.'_day_ram_'.$id.md5(
					$color1[ 'r' ].
					$color1[ 'g' ].
					$color1[ 'b' ].
					$color2[ 'r' ].
					$color2[ 'g' ].
					$color2[ 'b' ].
					$color3[ 'r' ].
					$color3[ 'g' ].
					$color3[ 'b' ].
					$color4[ 'r' ].
					$color4[ 'g' ].
					$color4[ 'b' ].
					$color5[ 'r' ].
					$color5[ 'g' ].
					$color5[ 'b' ].
					$color6[ 'r' ].
					$color6[ 'g' ].
					$color6[ 'b' ].
					$color7[ 'r' ].
					$color7[ 'g' ].
					$color7[ 'b' ]
				);
			$a = m::g ($md5);
			if ( empty($a)){
				$time = api::gettime ();
				if(!$act){
					$title = l::t ( "Потребление памяти за сутки" );
				}else{
					$title = l::t ( "Потребление памяти за неделю" );
				}


				$title3 = l::t ( "Среднее потребление памяти:" );
				$title4 = l::t ( "Игровой хостинг:" ).' ' . $_SERVER[ 'HTTP_HOST' ];
				$title5 = $row[ 'name' ] . " (" . servers::ip_server($row['box']) . ':' . $row[ 'port' ] . ")";
				$MyData = new pData();
				$i = 0;
				$keys = array ();
				$values = array ();
				$all = 0;
				$data = array();
				if(!$act) {
					$time = $time - 600 * 144;
				}else{
					$time = $time - 600 * 144*7;
				}
				$sql = db::q ( 'SELECT * FROM gh_monitoring_ram_time where sid="' . $id . '" and time>"'.$time.'" order by id asc' );
				while ( $row = db::r ( $sql ) ) {
					$data[ $row[ 'time' ] ] = $row[ 'ram' ];
				}
				while ( true ) {
					if(!$act) {
						if ( $i > 144 ) {
							break;
						}
						if($data[ $time ]){
							$on = $data[ $time];
						}else{
							$on = 0;
						}
					}else{
						if ( $i > 336 ) {
							break;
						}
						$on = 0;
						$t1 = (int)$data[ $time ];
						$t2 = (int)$data[ $time-600];
						$t3 = (int)$data[ $time-1200 ];
						$on = (int)(($t1+$t2+$t3)/3);
					}
					$MyData->addPoints ( abs ( $on ) , "Inbound" );
					$MyData->addPoints ( $time , "TimeStamp" );
					$all = $all + $on;
					if(!$act) {
						$time = $time + 600;
					}else{
						$time = $time + 1800;
					}
					$i ++;
				}
				if(!$act) {
					$title3 = $title3 . " " . (int) ( $all / 144 );
				}else{
					$title3 = $title3 . " " . (int) ( $all / 336 );
				}
				$MyData->setAxisName ( 0 , "" );
				$MyData->setAxisDisplay ( 0 , AXIS_FORMAT_DEFAULT );
				$MyData->setSerieDescription ( "TimeStamp" , "time" );
				$MyData->setAbscissa ( "TimeStamp" );
				if(!$act) {
					$MyData->setXAxisDisplay ( AXIS_FORMAT_TIME , "H:00" );
				}else{
					$MyData->setXAxisDisplay (AXIS_FORMAT_CUSTOM,"XAxisFormat");
				}


				/* Create the pChart object */
				$myPicture = new pImage( 705 , 235 , $MyData );

				/* Turn of Antialiasing */
				$myPicture->Antialias = false;

				/* Draw a background */
				$Settings = array ( "R" => $color2[ 'r' ] , "G" => $color2[ 'g' ] , "B" => $color2[ 'b' ] , "Dash" => 1 , "DashR" => $color1[ 'r' ] , "DashG" => $color1[ 'g' ] , "DashB" => $color1[ 'b' ] );
				$myPicture->drawFilledRectangle ( 0 , 0 , 705 , 235 , $Settings );

				/* Overlay with a gradient */
				$Settings = array ( "StartR" => $color3[ 'r' ] , "StartG" => $color3[ 'g' ] , "StartB" => $color3[ 'b' ] , "EndR" => $color4[ 'r' ] , "EndG" => $color4[ 'g' ] , "EndB" => $color4[ 'b' ] , "Alpha" => 50 );
				$myPicture->drawGradientArea ( 0 , 0 , 705 , 270 , DIRECTION_VERTICAL , $Settings );

				/* Add a border to the picture */
				$myPicture->drawRectangle ( 0 , 0 , 704 , 234 , array ( "R" => 0 , "G" => 0 , "B" => 0 ) );

				/* Write the chart title */
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 11 , "R" => $color5[ 'r' ] , "G" => $color5[ 'g' ] , "B" => $color5[ 'b' ] ) );
				$myPicture->drawText ( 353 , 30 , $title , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 353 , 205 , $title5 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 10 , 225 , $title3 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 695 , 225 , $title4 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMRIGHT ) );

				/* Set the default font */
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 8 ) );

				/* Define the chart area */
				$myPicture->setGraphArea ( 40 , 40 , 680 , 170 );

				/* Draw the scale */
				if(!$act) {
					$scaleSettings = array ( "XMargin" => 10 , "YMargin" => 10 , "Floating" => true , "GridR" => $color6[ 'r' ] , "GridG" => $color6[ 'g' ] , "GridB" => $color6[ 'b' ] , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>8,"DrawSubTicks"=>false);
				}else{
					$scaleSettings = array ( "XMargin" => 10 , "YMargin" => 10 , "Floating" => true , "GridR" => $color6[ 'r' ] , "GridG" => $color6[ 'g' ] , "GridB" => $color6[ 'b' ] , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>23,"DrawSubTicks"=>false);
				}

				$myPicture->drawScale ( $scaleSettings );


				/* Turn on Antialiasing */
				$myPicture->Antialias = true;
				$Threshold = "";
				$Threshold[] = array("Min"=>0,"Max"=>1000,"R"=>$color7['r'],"G"=>$color7['g'],"B"=>$color7['b'],"Alpha"=>60);
				$myPicture->drawAreaChart(array("Threshold"=>$Threshold));
				/* Draw the line chart */


				/* Write a label over the chart */
				$myPicture->writeLabel ( "Inbound" , 720 );

				ob_start ();
				$myPicture->stroke ();
				$a = ob_get_contents ();
				ob_end_clean ();
				m::s ($md5, $a , 3600 );
			}
			header ('Content-Type: image/png');
			echo $a;
			die;
		}
	}
	public static function hdd ($act=false)
	{
		$id = (int) r::g ( 3 );
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		if ( db::n () != 0 ) {
			$row = db::r ();
			api::inc ( "chart/pData" );
			api::inc ( "chart/pDraw" );
			api::inc ( "chart/pImage" );
			$font = ROOT . '/engine/classes/chart/font.ttf';
			$font2 = ROOT . '/engine/classes/chart/font2.ttf';
			$font3 = ROOT . '/engine/classes/chart/font3.ttf';
			$font4 = ROOT . '/engine/classes/chart/font4.ttf';
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 4 ) ) ) {
				$color1[ 'r' ] = "120";
				$color1[ 'g' ] = "120";
				$color1[ 'b' ] = "120";
			} else {
				$a = explode ( '.' , r::g ( 4 ) );
				$color1[ 'r' ] = $a[ 0 ];
				$color1[ 'g' ] = $a[ 1 ];
				$color1[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 5 ) ) ) {
				$color2[ 'r' ] = "90";
				$color2[ 'g' ] = "90";
				$color2[ 'b' ] = "90";
			} else {
				$a = explode ( '.' , r::g ( 5 ) );
				$color2[ 'r' ] = $a[ 0 ];
				$color2[ 'g' ] = $a[ 1 ];
				$color2[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 6 ) ) ) {
				$color3[ 'r' ] = "0";
				$color3[ 'g' ] = "0";
				$color3[ 'b' ] = "0";
			} else {
				$a = explode ( '.' , r::g ( 6 ) );
				$color3[ 'r' ] = $a[ 0 ];
				$color3[ 'g' ] = $a[ 1 ];
				$color3[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 7 ) ) ) {
				$color4[ 'r' ] = "0";
				$color4[ 'g' ] = "0";
				$color4[ 'b' ] = "0";
			} else {
				$a = explode ( '.' , r::g ( 7 ) );
				$color4[ 'r' ] = $a[ 0 ];
				$color4[ 'g' ] = $a[ 1 ];
				$color4[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 8 ) ) ) {
				$color5[ 'r' ] = "255";
				$color5[ 'g' ] = "255";
				$color5[ 'b' ] = "255";
			} else {
				$a = explode ( '.' , r::g ( 8 ) );
				$color5[ 'r' ] = $a[ 0 ];
				$color5[ 'g' ] = $a[ 1 ];
				$color5[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 9 ) ) ) {
				$color6[ 'r' ] = "250";
				$color6[ 'g' ] = "250";
				$color6[ 'b' ] = "250";
			} else {
				$a = explode ( '.' , r::g ( 9 ) );
				$color6[ 'r' ] = $a[ 0 ];
				$color6[ 'g' ] = $a[ 1 ];
				$color6[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 10 ) ) ) {
				$color7[ 'r' ] = "153";
				$color7[ 'g' ] = "222";
				$color7[ 'b' ] = "121";
			} else {
				$a = explode ( '.' , r::g ( 10 ) );
				$color7[ 'r' ] = $a[ 0 ];
				$color7[ 'g' ] = $a[ 1 ];
				$color7[ 'b' ] = $a[ 2 ];
			}
			if(!$act){
				$act = '0';
			}else{
				$act = '1';
			}
			$md5 = l::$lang.$act.'_day_hdd_'.$id.md5(
					$color1[ 'r' ].
					$color1[ 'g' ].
					$color1[ 'b' ].
					$color2[ 'r' ].
					$color2[ 'g' ].
					$color2[ 'b' ].
					$color3[ 'r' ].
					$color3[ 'g' ].
					$color3[ 'b' ].
					$color4[ 'r' ].
					$color4[ 'g' ].
					$color4[ 'b' ].
					$color5[ 'r' ].
					$color5[ 'g' ].
					$color5[ 'b' ].
					$color6[ 'r' ].
					$color6[ 'g' ].
					$color6[ 'b' ].
					$color7[ 'r' ].
					$color7[ 'g' ].
					$color7[ 'b' ]
				);
			$a = m::g ($md5);
			if ( empty($a)){
				$time = api::gettime ();
				if(!$act){
					$title = l::t ( "Потребление дисковой квоты за сутки" );
				}else{
					$title = l::t ( "Потребление дисковой квоты за неделю" );
				}


				$title3 = l::t ( "Среднее потребление дисковой квоты:" );
				$title4 = l::t ( "Игровой хостинг:" ).' ' . $_SERVER[ 'HTTP_HOST' ];
				$title5 = $row[ 'name' ] . " (" . servers::ip_server($row['box']) . ':' . $row[ 'port' ] . ")";
				$MyData = new pData();
				$i = 0;
				$keys = array ();
				$values = array ();
				$all = 0;
				$data = array();
				if(!$act) {
					$time = $time - 600 * 144;
				}else{
					$time = $time - 600 * 144*7;
				}
				$sql = db::q ( 'SELECT * FROM gh_monitoring_hdd_time where sid="' . $id . '" and time>"'.$time.'" order by id asc' );
				while ( $row = db::r ( $sql ) ) {
					$data[ $row[ 'time' ] ] = $row[ 'hdd' ];
				}
				$oa = 0;
				while ( true ) {
					if(!$act) {
						if ( $i > 144 ) {
							break;
						}
						if($data[ $time ]){
							$on = $data[ $time];
							$oa = $on;
						}else{
							$on = $oa;
						}
					}else{
						if ( $i > 336 ) {
							break;
						}
						$on = 0;
						if($data[ $time ]){
							$t1 = (int)$data[ $time ];
							$t2 = (int)$data[ $time-600];
							$t3 = (int)$data[ $time-1200 ];
							$on = (int)(($t1+$t2+$t3)/3);
							$oa = $on;
						}else{
							$on = $oa;
						}
					}
					$MyData->addPoints ( abs ( $on ) , "Inbound" );
					$MyData->addPoints ( $time , "TimeStamp" );
					$all = $all + $on;
					if(!$act) {
						$time = $time + 600;
					}else{
						$time = $time + 1800;
					}
					$i ++;
				}
				if(!$act) {
					$title3 = $title3 . " " . (int) ( $all / 144 );
				}else{
					$title3 = $title3 . " " . (int) ( $all / 336 );
				}
				$MyData->setAxisName ( 0 , "" );
				$MyData->setAxisDisplay ( 0 , AXIS_FORMAT_DEFAULT );
				$MyData->setSerieDescription ( "TimeStamp" , "time" );
				$MyData->setAbscissa ( "TimeStamp" );
				if(!$act) {
					$MyData->setXAxisDisplay ( AXIS_FORMAT_TIME , "H:00" );
				}else{
					$MyData->setXAxisDisplay (AXIS_FORMAT_CUSTOM,"XAxisFormat");
				}


				/* Create the pChart object */
				$myPicture = new pImage( 705 , 235 , $MyData );

				/* Turn of Antialiasing */
				$myPicture->Antialias = false;

				/* Draw a background */
				$Settings = array ( "R" => $color2[ 'r' ] , "G" => $color2[ 'g' ] , "B" => $color2[ 'b' ] , "Dash" => 1 , "DashR" => $color1[ 'r' ] , "DashG" => $color1[ 'g' ] , "DashB" => $color1[ 'b' ] );
				$myPicture->drawFilledRectangle ( 0 , 0 , 705 , 235 , $Settings );

				/* Overlay with a gradient */
				$Settings = array ( "StartR" => $color3[ 'r' ] , "StartG" => $color3[ 'g' ] , "StartB" => $color3[ 'b' ] , "EndR" => $color4[ 'r' ] , "EndG" => $color4[ 'g' ] , "EndB" => $color4[ 'b' ] , "Alpha" => 50 );
				$myPicture->drawGradientArea ( 0 , 0 , 705 , 270 , DIRECTION_VERTICAL , $Settings );

				/* Add a border to the picture */
				$myPicture->drawRectangle ( 0 , 0 , 704 , 234 , array ( "R" => 0 , "G" => 0 , "B" => 0 ) );

				/* Write the chart title */
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 11 , "R" => $color5[ 'r' ] , "G" => $color5[ 'g' ] , "B" => $color5[ 'b' ] ) );
				$myPicture->drawText ( 353 , 30 , $title , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 353 , 205 , $title5 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 10 , 225 , $title3 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 695 , 225 , $title4 , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMRIGHT ) );

				/* Set the default font */
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 8 ) );

				/* Define the chart area */
				$myPicture->setGraphArea ( 40 , 40 , 680 , 170 );

				/* Draw the scale */
				if(!$act) {
					$scaleSettings = array ( "XMargin" => 10 , "YMargin" => 10 , "Floating" => true , "GridR" => $color6[ 'r' ] , "GridG" => $color6[ 'g' ] , "GridB" => $color6[ 'b' ] , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>8,"DrawSubTicks"=>false);
				}else{
					$scaleSettings = array ( "XMargin" => 10 , "YMargin" => 10 , "Floating" => true , "GridR" => $color6[ 'r' ] , "GridG" => $color6[ 'g' ] , "GridB" => $color6[ 'b' ] , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>23,"DrawSubTicks"=>false);
				}

				$myPicture->drawScale ( $scaleSettings );


				/* Turn on Antialiasing */
				$myPicture->Antialias = true;
				$Threshold = "";
				$Threshold[] = array("Min"=>0,"Max"=>1000,"R"=>$color7['r'],"G"=>$color7['g'],"B"=>$color7['b'],"Alpha"=>60);
				$myPicture->drawAreaChart(array("Threshold"=>$Threshold));
				/* Draw the line chart */


				/* Write a label over the chart */
				$myPicture->writeLabel ( "Inbound" , 720 );

				ob_start ();
				$myPicture->stroke ();
				$a = ob_get_contents ();
				ob_end_clean ();
				m::s ($md5, $a , 3600 );
			}
			header ('Content-Type: image/png');
			echo $a;
			die;
		}
	}
	public static $asd = 0;
	public static function banner ($act=false)
	{
		$id = (int) r::g ( 3 );
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		api::inc ( "chart/pData" );
		api::inc ( "chart/pDraw" );
		api::inc ( "chart/pImage" );
		$font = ROOT . '/engine/classes/chart/font.ttf';
		if ( db::n () != 0 ) {
			$row = db::r ();

			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 4 ) ) ) {
				$color1[ 'r' ] = "255";
				$color1[ 'g' ] = "154";
				$color1[ 'b' ] = "55";
			} else {
				$a = explode ( '.' , r::g ( 4 ) );
				$color1[ 'r' ] = $a[ 0 ];
				$color1[ 'g' ] = $a[ 1 ];
				$color1[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 5 ) ) ) {
				$color2[ 'r' ] = "255";
				$color2[ 'g' ] = "255";
				$color2[ 'b' ] = "255";
			} else {
				$a = explode ( '.' , r::g ( 5 ) );
				$color2[ 'r' ] = $a[ 0 ];
				$color2[ 'g' ] = $a[ 1 ];
				$color2[ 'b' ] = $a[ 2 ];
			}
			if ( ! preg_match ( "/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/i" , r::g ( 6 ) ) ) {
				$color3[ 'r' ] = "153";
				$color3[ 'g' ] = "222";
				$color3[ 'b' ] = "121";
			} else {
				$a = explode ( '.' , r::g ( 6 ) );
				$color3[ 'r' ] = $a[ 0 ];
				$color3[ 'g' ] = $a[ 1 ];
				$color3[ 'b' ] = $a[ 2 ];
			}
			if(!$act){
				$act = '0';
			}else{
				$act = '1';
			}
			$md5 = l::$lang.'_banner_'.$act.'_day_online_'.$id.md5(
					$color1[ 'r' ].
					$color1[ 'g' ].
					$color1[ 'b' ].
					$color2[ 'r' ].
					$color2[ 'g' ].
					$color2[ 'b' ].
					$color3[ 'r' ].
					$color3[ 'g' ].
					$color3[ 'b' ]
			);
			$a = m::g ($md5);
			if ( empty($a)){
				$time = api::gettime ();


				$MyData = new pData();
				$i = 0;
				$keys = array ();
				$values = array ();
				$all = 0;
				$data = array();
				if(!$act) {
					$time = $time - 600 * 144;
				}else{
					$time = $time - 600 * 144*7;
				}
				$sql = db::q ( 'SELECT * FROM gh_monitoring_time where sid="' . $id . '" and time>"'.$time.'" order by id asc' );
				while ( $row3 = db::r ( $sql ) ) {
					$data[ $row3[ 'time' ] ] = $row3[ 'online' ];
				}
				while ( true ) {
					if ( $i > 144 ) {
						break;
					}
					if($data[ $time ]){
						$on = $data[ $time];
					}else{
						$on = 0;
					}
					$MyData->addPoints ( abs ( $on ) , "Inbound" );
					$MyData->addPoints ( $time , "TimeStamp" );
					$all = $all + $on;
						$time = $time + 600;
					$i ++;
				}
				$MyData->setAxisName ( 0 , "" );
				$MyData->setAxisDisplay ( 0 , AXIS_FORMAT_DEFAULT );
				$MyData->setSerieDescription ( "TimeStamp" , "time" );
				$MyData->setAbscissa ( "TimeStamp" );
				$MyData->setXAxisDisplay (AXIS_FORMAT_CUSTOM,"XAxisFormat2");

				$myPicture = new pImage( 560 , 95 , $MyData );
				$myPicture->drawFromPNG(0,0,ROOT."/img/chart/bg1.png");
				$myPicture->drawFromPNG(20,8,ROOT."/img/chart/".$row['game'].".png");
				$myPicture->Antialias = true;


				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 6 , "R" => $color1[ 'r' ] , "G" => $color1[ 'g' ] , "B" => $color1[ 'b' ] ) );
				$myPicture->drawText ( 490 , 20 , l::t('Онлайн за 24 часа') , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );

				$myPicture->drawText ( 120 , 26 , $row['name'] , array ( "FontSize" => 12 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 180 , 86 , $row['online'].'/'.$row['slots'] , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 120 , 62 , servers::ip_server($row['box']) , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );

				$myPicture->drawText ( 230 , 62 , $row['port'] , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				if($row['status']==1){
					$status = l::t("Работает");
				}else{
					$status = l::t("Выключен");
				}
				$myPicture->drawText ( 290 , 62 , $status, array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );

				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 6 , "R" => $color2[ 'r' ] , "G" => $color2[ 'g' ] , "B" => $color2[ 'b' ] ) );
				$myPicture->drawText ( 290 , 48 , l::t('Статус') , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );

				$myPicture->drawText ( 120 , 86 , l::t('Онлайн') , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 230 , 48 , l::t('Порт') , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 120 , 48 , l::t('Адрес') , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->drawText ( 230 , 86 , l::t('Хостинг:').' '.$_SERVER['HTTP_HOST'] , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMLEFT ) );
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 6 ) );
				$myPicture->setGraphArea ( 440 , 30 , 550 , 80 );
				$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 6 , "R" => 255 , "G" => 255 , "B" =>255 ) );

				$scaleSettings = array ( "XMargin" => 0 , "YMargin" => 0 , "AxisR"=>255,"AxisG"=>255,"AxisB"=>255, "Floating" => true , "GridR" => 255 , "GridG" => 255, "GridB" => 255 , "RemoveSkippedAxis" => true , "Mode" => SCALE_MODE_START0 , "LabelingMethod" => LABELING_ALL ,"LabelSkip"=>150,"DrawSubTicks"=>false);

				$myPicture->drawScale ( $scaleSettings );

				/* Turn on Antialiasing */
				$myPicture->Antialias = true;

				/* Draw the line chart */


				$Threshold = "";
				$Threshold[] = array("Min"=>0,"Max"=>1000,"R"=>$color3['r'],"G"=>$color3['g'],"B"=>$color3['b'],"Alpha"=>60);
				$myPicture->drawAreaChart(array("Threshold"=>$Threshold));


				$myPicture->writeLabel ( "Inbound" , 720 );

				ob_start ();
				$myPicture->stroke ();
				$a = ob_get_contents ();
				ob_end_clean ();
				m::s ($md5, $a , 3600 );
			}
			header ('Content-Type: image/png');
			echo $a;
			die;
		}else{
			$MyData = new pData();
			$myPicture = new pImage( 560 , 95 , $MyData );
			$myPicture->drawFromPNG(0,0,ROOT."/img/chart/off.png");
			$myPicture->setFontProperties ( array ( "FontName" => $font , "FontSize" => 6 , "R" => 255 , "G" => 255 , "B" => 255 ) );

			$myPicture->drawText ( 280 , 60 , l::t('Сервер не найден') , array ( "FontSize" => 24 , "Align" => TEXT_ALIGN_BOTTOMMIDDLE ) );
			$myPicture->drawText ( 550 , 80 , l::t('Иговой хостинг:').' '.$_SERVER['HTTP_HOST'] , array ( "FontSize" => 10 , "Align" => TEXT_ALIGN_BOTTOMRIGHT ) );
			$myPicture->stroke ();

			die;
		}
	}
}
function XAxisFormat2($value) {

}
function XAxisFormat($value) {
	$r = 0;
	if(chart::$asd==0){
		$r = 1;
	}
	if(chart::$asd==24){
		$r = 2;
	}
	if(chart::$asd==48){
		$r = 1;
	}
	if(chart::$asd==72){
		$r = 2;
	}
	if(chart::$asd==96){
		$r = 1;
	}
	if(chart::$asd==120){
		$r = 2;
	}
	if(chart::$asd==144){
		$r = 1;
	}
	if(chart::$asd==168){
		$r = 2;
	}
	if(chart::$asd==192){
		$r = 1;
	}
	if(chart::$asd==216){
		$r = 2;
	}
	if(chart::$asd==240){
		$r = 1;
	}
	if(chart::$asd==264){
		$r = 2;
	}
	if(chart::$asd==288){
		$r = 1;
	}
	if(chart::$asd==312){
		$r = 2;
	}
	chart::$asd++;
	if($r){
		if($r==1){
			return  api::langdate ( "j F" , $value );
		}else{
			return  api::langdate ( "H:i" , $value );
		}

	}else{
		return false;
	}
}
?>