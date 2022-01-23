<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_cssold
{
	public static $engine = false;
	public static $rules = array (
		'on'        => 'Включение сервера' ,
		'off'       => 'Выключение сервера' ,
		'restart'   => 'Перезагрузка сервера' ,
		'settings'  => 'Управление настройками' ,
		'reinstall' => 'Переустановка сервера' ,
		'buy'       => 'Продление сервера' ,
		'ftp'       => 'Управление FTP' ,
		'modules'   => 'Управление модулями' ,
		'maps'      => 'Управление картами' ,
		'fastdl'    => 'Управление Fast DL' ,
		'eac'       => 'Управление EAC' ,
		'rise'      => 'Управление раскрутками' ,
		'friends'   => 'Управление друзьями' ,
		'console'   => 'Управление консолью',
		'sale'		=> 'Управление админами',
		'slots'		=> 'Изменение слотов'
	);

	public static function engine ()
	{
		if ( ! self::$engine ) {
			self::$engine = true;
			include_once ( ROOT . '/engine/classes/source-engine.php' );
		}
	}
	public static function admins_reload($id){
		self::engine ();
		source_engine::admins_reload($id,'/cstrike/addons/sourcemod/configs/admins.cfg');
	}
	public static function info ( $data )
	{
		global $conf;
		$cfg[ 'rcon' ] = 1;
		$cfg[ 'update' ] = 0;
		$cfg[ 'online' ] = 1;
		$cfg[ 'gadget' ] = 1;
		$cfg[ 'repository' ] = 1;
		$cfg[ 'fastdl' ] = 1;
		$cfg[ 'fps' ] = 0;
		$cfg[ 'sale' ] = 1;
		$cfg[ 'reinstall' ] = 1;
		$cfg[ 'friends' ] = 1;
		$cfg[ 'ftp' ] = 1;
		$cfg[ 'settings' ] = 1;
		$cfg[ 'settings2' ] = 1;
		$cfg[ 'settings_servercfg' ] = "/cstrike/cfg/server.cfg";
		$cfg[ 'settings_motd' ] = "/cstrike/motd.txt";
		$cfg[ 'tv' ] = 1;
		$cfg[ 'eac' ] = 0;
		$cfg[ 'maps' ] = 1;
		$cfg[ 'console' ] = 1;
		$cfg[ 'maps3' ] = 1;
		$cfg[ 'rcon_kb' ] = 0;
		$cfg['maps2'] = 'cstrike/maps/';
		$cfg[ 'ftp_root' ] = "/cstrike/";
			$cfg[ 'tv_dir' ] = "cstrike/";
		return $cfg[ $data ];
	}

	public static function install ( $id )
	{
		$data[ 'map' ] = 'de_dust2';
		$data[ 'rcon' ] = api::generate_password ( '10' );
		$data[ 'tickrate' ] = '66';
		servers::configure ( $data , $id );
	}

	public static function on ( $id )
	{
		db::q ( 'SELECT * FROM gh_servers where id="' . $id . '"' );
		$server = db::r ();
		$cfg = servers::cfg ( $id );
		$sid = $server[ 'sid' ];
		$sql = db::q('SELECT * FROM gh_rates where id="' . $server['rate'] . '"');
		$rate = db::r($sql);
		if ( $cfg[ 'slots' ] ) {
			$slots = $cfg[ 'slots' ];
		} else {
			$slots = $server[ 'slots' ];
		}
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "chmod 755 srcds_run;";
		$exec .= 'sed -i "/rcon_password/d" "cstrike/cfg/server.cfg";';
		$exec .= "screen -dmS server_" . $sid . " sudo -u s" . $sid . " ";
		$exec .= "./srcds_run -console -debug -game cstrike -localcser -nomaster ";
		$exec .= " +servercfgfile server.cfg +sv_lan 0  -norestart +map " . $cfg[ 'map' ] . "";
		$exec .= " +maxplayers " . $slots;
		$exec .= " +ip " . servers::ip_server2($server['box']) . " -port " . $server[ 'port' ] . " +clientport ".( $server[ 'port' ]+15000)." ";
		if ( $cfg[ 'pass' ] ) {
			$exec .= " +sv_password " . $cfg[ 'pass' ] . "";
		}
		if ( $cfg[ 'rcon' ] ) {
			$exec .= " +rcon_password " . $cfg[ 'rcon' ] . "";
		}
		if ($cfg[ 'vac' ]) {
			$exec .= " -insecure";
		}
		if ( $rate[ 'fps' ] ) {
			$exec .= "  +sys_ticrate " . ( $rate[ 'fps' ] + 100 ) . " -sys_ticrate " . ( $rate[ 'fps' ] + 100 ) . "";
		}
		if ( $cfg[ 'tv' ] == 0 ) {
			$exec .= " -nohltv";
			$exec .= " -tvdisable";
			$exec .= " +tv_enable 0";
		} else {
			$exec .= " +tv_enable 1";
			$exec .= " +tv_port " . ( $server[ 'port' ] + 10000 );
			$exec .= " +tv_maxclients " . $rate[ 'tv_slots' ];
		}
		$exec .= " ".$rate['plus'];
		ssh::exec_cmd ( $exec );
		sleep ( '2' );
		$pid = self::get_pid ( $sid );
		if ( $pid ) {
			servers::set_cpu ( $sid , $server[ 'slots' ] , $pid , $server[ 'rate' ] , $server[ 'game' ] );
			sleep ( '2' );
			servers::get_pid_screen ( $sid );
		}
	}

	public static function get_pid ( $id )
	{
		ssh::exec_cmd ( "ps -ef  | grep s" . $id . " | grep -v sudo | grep -v screen | grep -v srcds_run | grep srcds | awk '{ print $3}';" );
		$data = trim ( ssh::get_output () );
		$data = explode ( "\n" , $data );
		if ( count ( $data ) > 1 ) {
			servers::kill_pid ( $data );

			return false;
		} else {
			return $data[ 0 ];
		}
	}

	public static function mon ( $data )
	{
		self::engine ();
		self::admins_reload($data['id']);
		return source_engine::mon ( $data );
	}

	public static function settings ( $data )
	{
		self::engine ();

		return source_engine::settings ( $data , "/cstrike/maps/" );
	}

	public static function fastdl_on ()
	{
		fastdl::data ( 'cstrike/sound' , 'sound' );
		fastdl::data ( 'cstrike/models' , 'models' );
		fastdl::data ( 'cstrike/materials' , 'materials' );
		fastdl::data ( 'cstrike/maps' , 'maps' );
	}

	public static function admins ( $data )
	{
		self::engine ();
		source_engine::admins ( $data );
	}

	public static function rcon ( $data )
	{
		self::engine ();
		source_engine::rcon ( $data );
	}
	public static function maps ( $data )
	{
		self::engine ();

		return source_engine::maps ( $data , "/cstrike/maps/" );
	}

	public static function maps_go ( $map )
	{
		return 'changelevel ' . $map;
	}
	public static function rcon_bk ( $data )
	{
		self::engine ();
		source_engine::rcon_bk ( $data );
	}
	public static $servercfg     = array (
		array (
			'name' => 'Название вашего сервера, это название будет отображаться во вкладке Интернет в игре.' ,
			'var'  => 'hostname' ,
			'default' => 'Counter-Strike: Source',
			'type' => '2'
		) ,
		array (
			'name' => 'Определите адрес вашего веб-сайта для использования быстрой загрузки с сервера.' ,
			'var'  => 'sv_downloadurl' ,
			'default' => '',
			'type' => '2'
		) ,
		array (
			'name' => 'Контакт для связи с вами, можно указать как e-mail адрес, так и адрес сайта.' ,
			'var'  => 'sv_contact' ,
			'default' => '',
			'type' => '2'
		) ,
		array (
			'name' => 'Регион, место расположения вашего Сервера.' ,
			'var'  => 'sv_region' ,
			'type' => '1' ,
			'default' => '255',
			'val'  => array (
				'0'  	=> 'Us Eastcoast' ,
				'1'  	=> 'US Westcoast' ,
				'2'  	=> 'South America' ,
				'3'  	=> 'Europe' ,
				'4'  	=> 'Asia' ,
				'5'  	=> 'Australia' ,
				'6'  	=> 'Middle East' ,
				'7'  	=> 'Africa' ,
				'255' 	=> 'World'
			)
		) ,
		array (
			'name' => 'Время карты в минутах.' ,
			'var'  => 'mp_timelimit' ,
			'default' => '30',
			'type' => '2'
		) ,
		array (
			'name' => 'Время раунда в минутах.' ,
			'var'  => 'mp_roundtime' ,
			'default' => '5',
			'type' => '2'
		) ,
		array (
			'name' => 'Время в минутах, в течение которого доступна покупка оружия.' ,
			'var'  => 'mp_buytime' ,
			'default' => '1',
			'type' => '2'
		) ,
		array (
			'name' => 'Деньги (800-16000), устанавливаемые зашедшему игроку (или если был рестарт раунда).' ,
			'var'  => 'mp_startmoney' ,
			'default' => '800',
			'type' => '2'
		) ,
		array (
			'name' => 'Время таймера установленной бомбы (planted_c4) в секундах (от 10 до 90).' ,
			'var'  => 'mp_c4timer' ,
			'default' => '30',
			'type' => '2'
		) ,
		array (
			'name' => 'Игроки могут использовать фонарик.' ,
			'var'  => 'mp_flashlight' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Слышны звуки шагов игроков.' ,
			'var'  => 'mp_footsteps' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Если игрок умер, его экран станет чёрным.' ,
			'var'  => 'mp_fadetoblack' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Отверстия в стенах от пуль, осколки, кровь и тд, максимальное их число на карте.' ,
			'var'  => 'mp_decals' ,
			'default' => '200',
			'type' => '2'
		) ,
		array (
			'name' => 'Сколько Террорист должен убить заложников, чтобы его кикнуло с сообщением.' ,
			'var'  => 'mp_hostagepenalty' ,
			'default' => '0',
			'type' => '2'
		) ,
		array (
			'name' => 'Разрешить автоприцеливание.' ,
			'var'  => 'mp_autocrosshair' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Сколько секунд игроки могут общаться, после окончания игры.' ,
			'var'  => 'mp_chattime' ,
			'default' => '10',
			'type' => '2'
		) ,
		array (
			'name' => 'Мертвые игроки не могут наблюдать за противоположной командой.' ,
			'var'  => 'mp_forcecamera' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Спектаторы (наблюдение, SPEC) разрешены.' ,
			'var'  => 'mp_allowspectators' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Баланс команд по количеству игроков.' ,
			'var'  => 'mp_autoteambalance' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'На сколько человек в одной команде может быть больше игроков, чем в другой.' ,
			'var'  => 'mp_limitteams' ,
			'default' => '1',
			'type' => '2'
		) ,
		array (
			'name' => 'Какие имена игрок может видеть.' ,
			'var'  => 'mp_playerid' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'0'  => 'все' ,
				'1'  => 'только имена игроков своей команды' ,
				'2' => 'без имён'
			)
		) ,
		array (
			'name' => 'Количество побед одной из команд, после чего произойдёт смена карты.' ,
			'var'  => 'mp_winlimit' ,
			'default' => '0',
			'type' => '2'
		) ,
		array (
			'name' => 'Количество фрагов (убийств) у игрока, после которых автоматически сменится карта.' ,
			'var'  => 'mp_fraglimit' ,
			'default' => '0',
			'type' => '2'
		) ,
		array (
			'name' => 'Количество сыгранных раундов для авто-смены карты.' ,
			'var'  => 'mp_maxrounds' ,
			'default' => '0',
			'type' => '2'
		) ,
		array (
			'name' => 'Сколько секунд (от 0 до 60) игрок будет заморожен после начала раунда.' ,
			'var'  => 'mp_freezetime' ,
			'default' => '5',
			'type' => '2'
		) ,
		array (
			'name' => 'Стрельба по своим разрешена.' ,
			'var'  => 'mp_friendlyfire' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Игрок, убивший товарища по команде, будет убит в следующем раунде.' ,
			'var'  => 'mp_tkpunish' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Неактивные игроки/тимклиллеры будут кикаться.' ,
			'var'  => 'mp_autokick' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Если тимкилл был после старта раунда в течение N секунд, то игрока кикнет.' ,
			'var'  => 'mp_spawnprotectiontime' ,
			'default' => '5',
			'type' => '2'
		) ,
		array (
			'name' => 'Если нет ответа от клиента (игрок повис), он будет отключен после N секунд.' ,
			'var'  => 'sv_timeout' ,
			'default' => '30',
			'type' => '2'
		) ,
		array (
			'name' => 'Игроки могут использовать микрофон.' ,
			'var'  => 'sv_voiceenable' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Игроки могут использовать чит-команды, например, noclip.' ,
			'var'  => 'sv_cheats' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Нет никаких ограничений между переговорами игроков, все всех слышат.' ,
			'var'  => 'sv_alltalk' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Гравитация игроков.' ,
			'var'  => 'sv_gravity' ,
			'default' => '800',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальная скорость игрока.' ,
			'var'  => 'sv_maxspeed' ,
			'default' => '320',
			'type' => '2'
		) ,
		array (
			'name' => 'Трение.' ,
			'var'  => 'sv_friction' ,
			'default' => '4',
			'type' => '2'
		) ,
		array (
			'name' => 'Поддержка старого стиля (HL1) запросов.' ,
			'var'  => 'sv_enableoldqueries' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Ускорение в режиме наблюдения (спектатор).' ,
			'var'  => 'sv_specaccelerate' ,
			'default' => '5',
			'type' => '2'
		) ,
		array (
			'name' => 'Спектатор может пролетать сквозь стены и другие объекты на карте.' ,
			'var'  => 'sv_specnoclip' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Скорость передвижения спектаторов.' ,
			'var'  => 'sv_specspeed' ,
			'default' => '3',
			'type' => '2'
		) ,
		array (
			'name' => 'Сбор статистики использования процессора (CPU).' ,
			'var'  => 'sv_stats' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Длина шага игрока.' ,
			'var'  => 'sv_stepsize' ,
			'default' => '18',
			'type' => '2'
		) ,
		array (
			'name' => 'Минимальная скорость остановки, когда игрок на земле.' ,
			'var'  => 'sv_stopspeed' ,
			'default' => '75',
			'type' => '2'
		) ,
		array (
			'name' => 'Игроки могут скачивать файлы с сервера.' ,
			'var'  => 'sv_allowdownload' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Игроки могут загружать файлы на сервер (например, спреи).' ,
			'var'  => 'sv_allowupload' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'На сколько минут банить того, кто несколько раз ввел неверный rcon_password.' ,
			'var'  => 'sv_rcon_banpenalty' ,
			'default' => '60',
			'type' => '2'
		) ,
		array (
			'name' => 'Количество секунд для определения неверной RCON аутентификации.' ,
			'var'  => 'sv_rcon_minfailuretime' ,
			'default' => '15',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальное число попыток (от 1 до 20) для правильного ввода RCON пароля.' ,
			'var'  => 'sv_rcon_maxfailures' ,
			'default' => '5',
			'type' => '2'
		) ,
		array (
			'name' => 'Количество попыток для правильного ввода RCON пароля в течение sv_rcon_minfailuretime.' ,
			'var'  => 'sv_rcon_minfailures' ,
			'default' => '5',
			'type' => '2'
		) ,
		array (
			'name' => 'Вести лог сервера.' ,
			'var'  => 'log' ,
			'type' => '1' ,
			'default' => 'on',
			'val'  => array (
				'on'  => 'Да' ,
				'off' => 'Нет'
			)
		) ,
		array (
			'name' => 'Записывать в лог баны.' ,
			'var'  => 'sv_logbans' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Отображать логи в консоле сервера.' ,
			'var'  => 'sv_logecho' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Записывать логи сервера в файл.' ,
			'var'  => 'sv_logfile' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Лог атак.' ,
			'var'  => 'mp_logdetail' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'0'  => 'выключено' ,
				'1'  => 'от противников' ,
				'2'  => 'от своих' ,
				'3' => 'от противников и от своих'
			)
		) ,
		array (
			'name' => 'Разрешить распрыжку.' ,
			'var'  => 'sv_enablebunnyhopping' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить распрыжку.' ,
			'var'  => 'sv_enablebunnyhopping' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Ускорение на земле.' ,
			'var'  => 'sv_accelerate' ,
			'default' => '5',
			'type' => '2'
		) ,
		array (
			'name' => 'Ускорение в воздухе.' ,
			'var'  => 'sv_airaccelerate' ,
			'default' => '100',
			'type' => '2'
		) ,
		array (
			'name' => 'Ускорение в воде.' ,
			'var'  => 'sv_wateraccelerate' ,
			'default' => '100',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимум bytes/sec, с которой хост может получать данные.' ,
			'var'  => 'rate' ,
			'default' => '30000',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальная пропускная скорость, 0 = неограниченная.' ,
			'var'  => 'sv_maxrate' ,
			'default' => '30000',
			'type' => '2'
		) ,
		array (
			'name' => 'Минимальная пропускная скорость, 0 = неограниченная.' ,
			'var'  => 'sv_minrate' ,
			'default' => '3500',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальное число обновлений данных сервера в секунду.' ,
			'var'  => 'sv_maxupdaterate' ,
			'default' => '66',
			'type' => '2'
		) ,
		array (
			'name' => 'Минимальное число обновлений данных сервера в секунду.' ,
			'var'  => 'sv_minupdaterate' ,
			'default' => '10',
			'type' => '2'
		) ,
		array (
			'name' => 'Устанавливает минимальное значение cl_cmdrate, 0 = без ограничений.' ,
			'var'  => 'sv_mincmdrate' ,
			'default' => '10',
			'type' => '2'
		) ,
		array (
			'name' => 'Если sv_mincmdrate > 0, то это устанавливает максимальное cl_cmdrate игрока.' ,
			'var'  => 'sv_maxcmdrate' ,
			'default' => '66',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальное количество (от 10 до 100) командных пакетов, отправляемых на сервер в секунду.' ,
			'var'  => 'cl_cmdrate' ,
			'default' => '66',
			'type' => '2'
		) ,
		array (
			'name' => 'Количество пакетов в секунду, которое сервер вам отправляет.' ,
			'var'  => 'cl_updaterate' ,
			'default' => '66',
			'type' => '2'
		) ,
		array (
			'name' => 'Допустимая разница значений cmdrate сервера и клиента.' ,
			'var'  => 'sv_client_cmdrate_difference' ,
			'default' => '5',
			'type' => '2'
		) ,
		array (
			'name' => 'Разрешить игрокам ставить паузу.' ,
			'var'  => 'sv_pausable' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить игрокам использовать свои модели, текстуры и т.д.' ,
			'var'  => 'sv_consistency' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Игроки могут использовать.' ,
			'var'  => 'sv_pure' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'0'  => 'любые скины, модели и звуки' ,
				'1' => 'только тот контент, который разрешен в pure_server_whitelist.txt',
				'2' => 'только оригинальные файлы Steam'
			)
		) ,
		array (
			'name' => 'Если файлы игрока не соответствуют серверным, он будет кикнут.' ,
			'var'  => 'sv_pure_kick_clients' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Cервер будет выдавать сообщение о том, что файлы клиента проверяются.' ,
			'var'  => 'sv_pure_trace' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить клиентам использовать цветокоррекцию.' ,
			'var'  => 'sv_allow_color_correction' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Получить голосовой ввод от voice_input.wav, а не от микрофона.' ,
			'var'  => 'voice_inputfromfile' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить игрокам использовать voice_inputfromfile.' ,
			'var'  => 'sv_allow_voice_from_file' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить использовать команду wait.' ,
			'var'  => 'sv_allow_wait_command' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить использовать команду cl_minmodels.' ,
			'var'  => 'sv_allowminmodels' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Автосохранение игры на уровне перехода (level transition). Не влияет на автосохранение триггеров.' ,
			'var'  => 'sv_autosave' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Cервер только имитирует объекты (entities).' ,
			'var'  => 'sv_alternateticks' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Скорость движения игрока назад.' ,
			'var'  => 'sv_backspeed' ,
			'default' => '0.6',
			'type' => '2'
		) ,
		array (
			'name' => 'Множитель отскока для физически моделируемого столкновения объектов.' ,
			'var'  => 'sv_bounce' ,
			'default' => '0',
			'type' => '2'
		) ,
		array (
			'name' => 'Количество ботов на сервере.' ,
			'var'  => 'bot_quota' ,
			'default' => '0',
			'type' => '2'
		) ,
		array (
			'name' => 'Сложность ботов.' ,
			'var'  => 'bot_difficulty' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'0'  => 'новичок' ,
				'1'  => 'средний' ,
				'2'  => 'трудный' ,
				'3' => 'эксперт'
			)
		) ,
		array (
			'name' => 'Боты подключаются только после входа игрока на сервер.' ,
			'var'  => 'bot_join_after_player' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Приставка перед именем бота.' ,
			'var'  => 'bot_prefix' ,
			'default' => '[ BOT ]',
			'type' => '2'
		) ,
		array (
			'name' => 'Боты могут выходить из повиновения и не следовать задаче карты.' ,
			'var'  => 'bot_allow_rogues' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Как часто бот разговаривает.' ,
			'var'  => 'bot_chatter' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'off'  => 'off' ,
				'radio'  => 'radio' ,
				'minimal'  => 'minimal' ,
				'normal' => 'normal'
			)
		) ,
		array (
			'name' => 'Боты будут освобождать место (кик) для игроков.' ,
			'var'  => 'bot_auto_vacate' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Боты могут передвигаться рядом с игроком.' ,
			'var'  => 'bot_auto_follow' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Имя файла в папке cstrike с профилями ботов (их имена и тд).' ,
			'var'  => 'bot_profile_db' ,
			'default' => 'botprofile.db',
			'type' => '2'
		) ,
		array (
			'name' => 'Если в команде нет человека, бот не будет следовать задаче текущей карты.' ,
			'var'  => 'bot_defer_to_human' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => '' ,
			'var'  => 'bot_quota_mode' ,
			'type' => '1' ,
			'default' => 'fiil',
			'val'  => array (
				'fiil'  => 'fiil' ,
				'match'  => 'match' ,
				'normal' => 'normal'
			)
		) ,
	);
}

?>
