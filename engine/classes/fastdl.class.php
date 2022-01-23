<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class fastdl
{
	public static $data   = false;
	public static $type   = false;
	public static $dir    = false;
	public static $server = false;
	public static $os     = 1;

	public static function data ( $dir , $dir2 )
	{
		if ( self::$type == 1 ) {
			self::$data .= "Alias /s" . self::$server . "/" . $dir2 . " " . self::$dir . $dir . "\n";
			self::$data .= "<Directory " . self::$dir . $dir . ">\n";
			self::$data .= "<IfModule mpm_itk_module>\n";
			self::$data .= "AssignUserID s" . self::$server . " s" . self::$server . "\n";
			self::$data .= "</IfModule>\n";
			self::$data .= "AllowOverride None\n";
			self::$data .= "<IfModule mod_php5.c>\n";
			self::$data .= "php_admin_flag engine off\n";
			self::$data .= "</IfModule>\n";
			self::$data .= "</Directory>\n";
		} elseif ( self::$type == 3 ) {
			self::$data .= "Alias /s" . self::$server . "/" . $dir2 . " " . self::$dir . $dir . "\n";
			self::$data .= "<Directory " . self::$dir . $dir . ">\n";
			self::$data .= "Options Indexes FollowSymLinks\n";
			self::$data .= "AllowOverride None\n";
			self::$data .= "Require all granted\n";
			self::$data .= "<IfModule mpm_itk_module>\n";
			self::$data .= "AssignUserID #" . self::$server . " #" . self::$server . "\n";
			self::$data .= "</IfModule>\n";
			self::$data .= "AllowOverride None\n";
			self::$data .= "<IfModule mod_php5.c>\n";
			self::$data .= "php_admin_flag engine off\n";
			self::$data .= "</IfModule>\n";
			self::$data .= "</Directory>\n";
		} else {
			self::$data .= "location /s" . self::$server . "/" . $dir2 . " {\n";
			self::$data .= "alias " . self::$dir . $dir . ";\n";
			self::$data .= "}\n";
		}
	}

	public static function on ()
	{
		if ( self::$type == 1 ) {
			if ( self::$os==1|| self::$os==2) {
				ssh::exec_cmd ( "cd /etc/apache2/fastdl/;echo '" . self::$data . "' > " . self::$server . ".conf" );
				ssh::exec_cmd ( "screen -dmS " . time () . " service apache2 restart" );
			}
			if ( self::$os==3) {
				ssh::exec_cmd ( "cd /etc/httpd/fastdl/;echo '" . self::$data . "' > " . self::$server . ".conf" );
				ssh::exec_cmd ( "screen -dmS " . time () . " service httpd restart" );
			}
		} elseif ( self::$type == 3 ) {
			if ( self::$os==1|| self::$os==2) {
				ssh::exec_cmd ( "cd /etc/apache2/fastdl/;echo '" . self::$data . "' > " . self::$server . ".conf" );
				ssh::exec_cmd ( "screen -dmS " . time () . " service apache2 restart" );
			}
			if ( self::$os==3) {
				ssh::exec_cmd ( "cd /etc/httpd/fastdl/;echo '" . self::$data . "' > " . self::$server . ".conf" );
				ssh::exec_cmd ( "screen -dmS " . time () . " service httpd restart" );
			}
		} else {
			ssh::exec_cmd ( "cd /etc/nginx/fastdl/;echo '" . self::$data . "' > " . self::$server . ".conf" );
			ssh::exec_cmd ( "screen -dmS " . time () . " service nginx reload" );
		}
	}
}

?>