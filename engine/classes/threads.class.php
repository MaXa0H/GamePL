<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class Threads
{
	public static $phpPath = '';
	private static $lastId         = 0;
	private static $descriptorSpec = array (
		0 => array ( 'pipe' , 'r' ) ,
		1 => array ( 'pipe' , 'w' )
	);
	private static $handles        = array ();
	private static $streams        = array ();
	private static $results        = array ();
	private static $pipes          = array ();
	private static $timeout        = 10;
	public static function newThread ( $filename , $params = array () )
	{
		if(!self::$phpPath){
			self::$phpPath = trim(file_get_contents ( ROOT . '/data/cron' ));
		}
		$params = addcslashes ( serialize ( $params ) , '"' );
		$command = self::$phpPath . ' -q ' . $filename . ' --params "' . $params . '"';
		++ self::$lastId;
		self::$handles[ self::$lastId ] = proc_open ( $command , self::$descriptorSpec , $pipes );
		self::$streams[ self::$lastId ] = $pipes[ 1 ];
		self::$pipes[ self::$lastId ] = $pipes;
		return self::$lastId;
	}
	public static function iteration ()
	{
		if ( ! count ( self::$streams ) ) {
			return false;
		}
		$read = self::$streams;
		@stream_select ( $read , $write = null , $except = null , self::$timeout );
		$stream = current ( $read );
		$id = array_search ( $stream , self::$streams );
		$result = stream_get_contents ( self::$pipes[ $id ][ 1 ] );
		if ( feof ( $stream ) ) {
			fclose ( self::$pipes[ $id ][ 0 ] );
			fclose ( self::$pipes[ $id ][ 1 ] );
			proc_close ( self::$handles[ $id ] );
			unset( self::$handles[ $id ] );
			unset( self::$streams[ $id ] );
			unset( self::$pipes[ $id ] );
		}
		return $result;
	}
	public static function getParams ()
	{
		foreach ( $_SERVER[ 'argv' ] as $key => $argv ) {
			if ( $argv == '--params' && isset( $_SERVER[ 'argv' ][ $key + 1 ] ) ) {
				return unserialize ( $_SERVER[ 'argv' ][ $key + 1 ] );
			}
		}
		return false;
	}
}
?>