<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class fs
{
	public static function load_ftp ( $ip , $pass , $login )
	{
		include_once ( ROOT . '/engine/classes/elfinder/elFinderConnector.class.php' );
		include_once ( ROOT . '/engine/classes/elfinder/elFinder.class.php' );
		include_once ( ROOT . '/engine/classes/elfinder/elFinderVolumeDriver.class.php' );
		include_once ( ROOT . '/engine/classes/elfinder/elFinderVolumeLocalFileSystem.class.php' );
		include_once ( ROOT . '/engine/classes/elfinder/elFinderVolumeFTP.class.php' );
		$opts = array (
			'locale' => 'en_US.UTF-8' ,
			'roots'  => array (
				array (
					'alias'     => 'Home' ,
					'driver'    => 'FTP' ,
					'host'      => $ip ,
					'user'      => $login ,
					'pass'      => $pass ,
					'path'      => '/' ,
					'tmpPath'   => ROOT . '/files/tmp/' ,
					'utf8fix'   => true ,
					'separator' => "/"
				)
			)
		);
		$connector = new elFinderConnector( new elFinder( $opts ) );
		$connector->run ();
		exit;
	}
}

?>