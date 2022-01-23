<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;
class game_css
{
	public static $engine = false;

	public static $rules = array (
		'on'        => 'Включение сервера' ,
		'off'       => 'Выключение сервера' ,
		'restart'   => 'Перезагрузка сервера' ,
		'settings'  => 'Управление настройками' ,
		'reinstall' => 'Переустановка сервера' ,
		'update' 	=> 'Обновление сервера' ,
		'buy'       => 'Продление сервера' ,
		'ftp'       => 'Управление FTP' ,
		'modules'   => 'Управление модулями' ,
		'maps'      => 'Управление картами' ,
		'fastdl'    => 'Управление Fast DL' ,
		'eac'       => 'Управление EAC' ,
		'rise'      => 'Управление раскрутками' ,
		'friends'   => 'Управление друзьями' ,
		'console'   => 'Управление консолью',
		'admins'   => 'Управление администраторами',
		'slots'		=> 'Изменение слотов',
		'sale'		=> 'Управление админами'
	);

	public static function engine ()
	{
		if ( ! self::$engine ) {
			self::$engine = true;
			include_once ( ROOT . '/engine/classes/source-engine.php' );
		}
	}

	public static function info ( $data )
	{
		global $conf;
		$cfg[ 'rcon' ] = 1;
		$cfg[ 'update' ] = 1;
		$cfg[ 'online' ] = 1;
		$cfg[ 'gadget' ] = 1;
		$cfg[ 'repository' ] = 1;
		$cfg[ 'fastdl' ] = 1;
		$cfg[ 'fps' ] = 0;
		$cfg[ 'reinstall' ] = 1;
		$cfg[ 'friends' ] = 1;
		$cfg[ 'ftp' ] = 1;
		$cfg[ 'settings' ] = 1;
		$cfg[ 'settings2' ] = 1;
		$cfg[ 'settings_servercfg' ] = "/cstrike/cfg/server.cfg";
		$cfg[ 'settings_motd' ] = "/cstrike/cfg/motd.txt";
		$cfg[ 'tv' ] = 1;
		$cfg[ 'sale' ] = 1;
		$cfg[ 'eac' ] = 1;
		$cfg[ 'maps' ] = 1;
		$cfg[ 'maps3' ] = 1;
		$cfg[ 'console' ] = 1;
		$cfg[ 'maps2' ] = 'cstrike/maps/';
		$cfg[ 'rcon_kb' ] = 1;
		$cfg[ 'eac_dir' ] = "servers/hl2";
		$cfg[ 'ftp_root' ] = "/cstrike/";
		$cfg[ 'tickrate' ] = 1;
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
		$sql = db::q('SELECT * FROM gh_rates where id="' . $server['rate'] . '"');
		$rate = db::r($sql);
		$cfg = servers::cfg ( $id );
		if ( $cfg[ 'slots' ] ) {
			$slots = $cfg[ 'slots' ];
		} else {
			$slots = $server[ 'slots' ];
		}
		$sid = $server[ 'sid' ];
		$exec = "cd /host/" . $server[ 'user' ] . "/" . $sid . "/;";
		$exec .= "chmod 755 srcds_run;";
		$exec .= 'sed -i "/rcon_password/d" "cstrike/cfg/server.cfg";';
		$exec .= "screen -dmS server_" . $sid . " sudo -u s" . $sid . " ";
		$exec .= "./srcds_run";
		$exec .= " -norestart";
		$exec .= " -console";
		$exec .= " -game cstrike";
		$exec .= "  +map " . $cfg[ 'map' ] . "";
		$exec .= " +maxplayers " . $slots;
		$exec .= " +ip " . servers::ip_server2($server['box']);
		$exec .= " -port " . $server[ 'port' ];
		if ( $cfg[ 'pass' ] ) {
			$exec .= " +sv_password " . $cfg[ 'pass' ] . "";
		}
		if ( $cfg[ 'rcon' ] ) {
			$exec .= " +rcon_password " . $cfg[ 'rcon' ] . "";
		}
		if ( $cfg[ 'vac' ]) {
			$exec .= " -insecure";
		}
		$exec .= " -sv_lan 0";
		$exec .= " +sv_lan 0";
		$exec .= " ".$rate['plus'];
		$exec .= " +clientport " . ( $server[ 'port' ] + 15000 );
		if ( $cfg[ 'tv' ] == 0 ) {
			$exec .= " -nohltv";
			$exec .= " -tvdisable";
			$exec .= " +tv_enable 0";
		} else {
			$exec .= " +tv_enable 1";
			$exec .= " +tv_port " . ( $server[ 'port' ] + 10000 );
			$exec .= " +tv_maxclients " . $rate[ 'tv_slots' ];
		}
		ssh::exec_cmd ( $exec );
		sleep ( '2' );
		$pid = self::get_pid ( $sid );
		if ( $pid ) {
			servers::set_cpu ( $sid , $server[ 'slots' ] , $pid , $server[ 'rate' ] , $server[ 'game' ] );
			sleep ( '2' );
			servers::get_pid_screen ( $sid );
		}
	}

	public static function update ( $data )
	{
		self::engine ();
		source_engine::update ( $data , '232330' );
	}

	public static function get_pid ( $id )
	{
		self::engine ();

		return source_engine::get_pid ( $id );
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

	public static function admins_reload($id){
		self::engine ();
		source_engine::admins_reload($id,'/cstrike/addons/sourcemod/configs/admins.cfg');
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
			'name' => 'Здесь может находиться контакт для связи с вами, можно указать как e-mail адрес, так и адрес сайта.' ,
			'var'  => 'sv_contact' ,
			'default' => '',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальный размер для скачиваемой карты (сюда включены звуки, материалы и модели) в мегабайтах.' ,
			'var'  => 'net_maxfilesize' ,
			'default' => '50',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальный размер для скачиваемой карты (сюда включены звуки, материалы и модели) в мегабайтах.' ,
			'var'  => 'net_maxfilesize' ,
			'default' => '50',
			'type' => '2'
		) ,
		array (
			'name' => 'Разрешить игрокам загружать свои спреи на сервер.' ,
			'var'  => 'sv_allowupload' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить игрокам скачивать файлы.' ,
			'var'  => 'sv_allowdownload' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить игрокам скачивать файлы.' ,
			'var'  => 'sv_allowdownload' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Это контролирует framerate вашего сервера.' ,
			'var'  => 'host_framerate' ,
			'default' => '60',
			'type' => '2'
		) ,

		array (
			'name' => 'Количество времени в секундах после которого игрок сможет снова распылить свой спрей рисунок.' ,
			'var'  => 'decalfrequency' ,
			'default' => '60',
			'type' => '2'
		) ,

		array (
			'name' => 'Количество времени в секундах после которого игрок сможет снова распылить свой спрей рисунок.' ,
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
			'name' => 'Это заставит двери открываться быстрее, особенно de_nuke.' ,
			'var'  => 'phys_timescale' ,
			'default' => '1.0',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимально возможная пропускная способность канала вашего сервера.' ,
			'var'  => 'sv_maxrate' ,
			'default' => '50000',
			'type' => '2'
		) ,
		array (
			'name' => 'Минимально возможная пропускная способность канала вашего сервера.' ,
			'var'  => 'sv_minrate' ,
			'default' => '10000',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальное количество возможных обновлений в секунду.' ,
			'var'  => 'sv_maxupdaterate' ,
			'default' => '66',
			'type' => '2'
		) ,
		array (
			'name' => 'Минимальное количество возможных обновлений в секунду.' ,
			'var'  => 'sv_minupdaterate' ,
			'default' => '33',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальное значение cmdrate у игрока.' ,
			'var'  => 'sv_maxcmdrate' ,
			'default' => '66',
			'type' => '2'
		) ,
		array (
			'name' => 'Минимальное значение cmdrate у игрока.' ,
			'var'  => 'sv_mincmdrate' ,
			'default' => '33',
			'type' => '2'
		) ,

		array (
			'name' => 'Укажите количество фрагов у игрока после достижения которого будет произведена смена карты.' ,
			'var'  => 'mp_fraglimit' ,
			'default' => '0',
			'type' => '2'
		) ,

		array (
			'name' => 'Укажите количество сыгранных раундов после которых будет произведена смена карты.' ,
			'var'  => 'mp_maxrounds' ,
			'default' => '0',
			'type' => '2'
		) ,

		array (
			'name' => 'Эта настройка определяет по прошествии скольки раундов выигранных одной из сторон будет произведена смена карты.' ,
			'var'  => 'mp_winlimit' ,
			'default' => '0',
			'type' => '2'
		) ,

		array (
			'name' => 'Эта настройка определяет по прошествии какого количества минут игры будет произведена смена карты.' ,
			'var'  => 'mp_timelimit' ,
			'default' => '30',
			'type' => '2'
		) ,

		array (
			'name' => 'Это управляет количеством денег в начале раунда у каждого нового игрока.' ,
			'var'  => 'mp_startmoney' ,
			'default' => '800',
			'type' => '2'
		) ,

		array (
			'name' => 'Продолжительность раунда в минутах, если бомба не взорвана/обезврежена.' ,
			'var'  => 'mp_roundtime' ,
			'default' => '5',
			'type' => '2'
		) ,

		array (
			'name' => 'Выключает функцию автоматического наведения прицела.' ,
			'var'  => 'mp_autocrosshair' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Включено' ,
				'0' => 'Выключено'
			)
		) ,

		array (
			'name' => 'Эта настройка определяет как долго вы сможете покупать оружие с начала раунда (в минутах). 0.5 означает 30 секунд.' ,
			'var'  => 'mp_buytime' ,
			'default' => '0.5',
			'type' => '2'
		) ,

		array (
			'name' => 'Эта настройка определяет время в секундах по истечению которого взорвется заложенная взрывчатка C4.' ,
			'var'  => 'mp_c4timer' ,
			'default' => '45',
			'type' => '2'
		) ,

		array (
			'name' => 'Урон при падении с большой высоты.' ,
			'var'  => 'mp_falldamage' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Включено' ,
				'0' => 'Выключено'
			)
		) ,

		array (
			'name' => 'Данная настройка позволяет использовать фонарик.' ,
			'var'  => 'mp_flashlight' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Включено' ,
				'0' => 'Выключено'
			)
		) ,

		array (
			'name' => 'Воспроизводить звуки шагов при ходьбе.' ,
			'var'  => 'sv_footsteps' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Включено' ,
				'0' => 'Выключено'
			)
		) ,
		array (
			'name' => 'Воспроизводить звуки шагов при ходьбе.' ,
			'var'  => 'mp_footsteps' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Включено' ,
				'0' => 'Выключено'
			)
		) ,

		array (
			'name' => 'Настройка определяет как долго вы не сможете сдвинуться с места и выстрелить в начале раунда, при этом покупка оружия доступна.' ,
			'var'  => 'mp_freezetime' ,
			'default' => '0',
			'type' => '2'
		) ,

		array (
			'name' => 'Возможность атаковать игроков своей команды.' ,
			'var'  => 'mp_friendlyfire' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Разрешено' ,
				'0' => 'Запрещено'
			)
		) ,
		array (
			'name' => 'Если на сервере разрешено убивать игроков своей команды, то совершивший TK игрок будет автоматически убит в начале следующего раунда.' ,
			'var'  => 'mp_tkpunish' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Наказывать' ,
				'0' => 'Помиловать'
			)
		) ,
		array (
			'name' => 'Время после начала раунда, в течении которого если игрок совершит TK он будет кикнут с сервера.' ,
			'var'  => 'mp_spawnprotectiontime' ,
			'default' => '0',
			'type' => '2'
		) ,

		array (
			'name' => 'Сколько заложников должен убить Terrorist перед тем как он будет кикнут, 0 для отключения.' ,
			'var'  => 'mp_hostagepenalty' ,
			'default' => '0',
			'type' => '2'
		) ,

		array (
			'name' => 'Предел разницы в количестве игроков в командах.' ,
			'var'  => 'mp_limitteams' ,
			'default' => '1',
			'type' => '2'
		) ,
		array (
			'name' => 'Что будет видеть игрок в строке состояния при наведении прицела на игрока:' ,
			'var'  => 'mp_playerid' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'0'  => 'все имена' ,
				'1' => 'только имена игроков своей команды',
				'2' => 'без имён'
			)
		) ,

		array (
			'name' => 'Вести логи:' ,
			'var'  => 'log' ,
			'type' => '1' ,
			'default' => 'on',
			'val'  => array (
				'on'  => 'Да' ,
				'off' => 'Нет'
			)
		) ,

		array (
			'name' => 'Хранить логи в одном единственном файле. Не рекомендуется если вы используете статистику, например HLstatsX:CE' ,
			'var'  => 'sv_log_onefile' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Сохранять логи в дирикторию logs/' ,
			'var'  => 'sv_logfile' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Записывать баны в логи.' ,
			'var'  => 'sv_logbans' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Показывать или нет ход ведения логов в ГЛАВНОЙ консоли сервера.' ,
			'var'  => 'sv_logecho' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Уровень детализации логов.' ,
			'var'  => 'mp_logdetail' ,
			'default' => '0',
			'type' => '2' ,
		) ,
		array (
			'name' => 'Отключение freezecam на вашем сервере.' ,
			'var'  => 'sv_disablefreezecam' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Отключение системы доминирования и мести.' ,
			'var'  => 'sv_nonemesis' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Отключение показа самого результативного игрока в конце раунда.' ,
			'var'  => 'sv_nomvp' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Отключение сбора статистики и достижений.' ,
			'var'  => 'sv_nostats' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'В конце раунда не будет появляться панель со статистикой и лучшим игроком.' ,
			'var'  => 'sv_nowinpanel' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Включить ускорение игрока при попадании в него флешкой во время прыжка.' ,
			'var'  => 'sv_enableboost' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Включите для фикса бага брони против гранат.' ,
			'var'  => 'sv_legacy_grenade_damage' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Включение банни-хопа.' ,
			'var'  => 'sv_allowbunnyjumping' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Количество минут на которое будет забанен игрок 1 - 60 max, если он не пройдет RCON аутентификацию.' ,
			'var'  => 'sv_rcon_banpenalty' ,
			'default' => '15',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальное число попыток пользователя ввести правильный пароль 1 - 20 max, по истечении этого кол-ва попыток игрок будет забанен.' ,
			'var'  => 'sv_rcon_maxfailures' ,
			'default' => '3',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальное число попыток пользователя ввести правильный пароль 1 - 20 max, по истечении этого кол-ва попыток игрок будет заблокирован на время указанное в следующем пункте.' ,
			'var'  => 'sv_rcon_minfailures' ,
			'default' => '1',
			'type' => '2'
		) ,
		array (
			'name' => 'Количество секунд до разрешения повторно ввести RCON пароль, если сначала он был введен не правильно.' ,
			'var'  => 'sv_rcon_minfailuretime' ,
			'default' => '30',
			'type' => '2'
		) ,
		array (
			'name' => 'Записывать в логи действия связанные с RCON или нет.' ,
			'var'  => 'sv_rcon_log' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Имя для вашего STV.' ,
			'var'  => 'tv_name' ,
			'default' => 'Source TV',
			'type' => '2'
		) ,
		array (
			'name' => 'Установите максимальную пропускную способность затрачиваемую на одного клиента в bytes/second.' ,
			'var'  => 'tv_maxrate' ,
			'default' => '25000',
			'type' => '2'
		) ,
		array (
			'name' => 'Пароль для доступа к просмотру STV трансляции, так же как и с "sv_password" для сервера.' ,
			'var'  => 'tv_password' ,
			'default' => '',
			'type' => '2'
		) ,
		array (
			'name' => 'Установите пароль для подключения дополнительных STV прокси.' ,
			'var'  => 'tv_relaypassword' ,
			'default' => '',
			'type' => '2'
		) ,
		array (
			'name' => 'Установите максимальное количество клиентов для локального SourceTV сервера/прокси.' ,
			'var'  => 'tv_maxclients' ,
			'default' => '20',
			'type' => '2'
		) ,
		array (
			'name' => 'Отключить зрителям возможность видеть чат.' ,
			'var'  => 'tv_nochat' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Автоматически записывать каждую игру, название демо файла будет иметь формат auto-YYYYMMDD-hhmm-map.dem.' ,
			'var'  => 'tv_autorecord' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Сколько ботов добавить в игру, чем больше ботов, тем больше будет загружен ваш CPU.' ,
			'var'  => 'bot_quota' ,
			'default' => '0',
			'type' => '2'
		) ,
		array (
			'name' => 'Если стоит Fill, то при достижении количества игроков равного X в игре боты будут удалены.' ,
			'var'  => 'bot_quota_mode' ,
			'type' => '1' ,
			'default' => 'Fill',
			'val'  => array (
				'Fill'  => 'Fill' ,
				'normal' => 'normal'
			)
		) ,
		array (
			'name' => 'Эта настройка управляет сложностью ботов.' ,
			'var'  => 'bot_difficulty' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'0'  => 'новичок' ,
				'1' => 'средний',
				'2' => 'трудный',
				'3' => 'эксперт',
			)
		) ,
		array (
			'name' => 'Установите текст который будет содержаться в начале имени бота.' ,
			'var'  => 'bot_prefix' ,
			'default' => '',
			'type' => '2'
		) ,
		array (
			'name' => 'Уровень общения ботов. Вот доступные значения для этого параметра.' ,
			'var'  => 'bot_chatter' ,
			'type' => '1' ,
			'default' => 'Normal',
			'val'  => array (
				'Normal'  => 'Normal' ,
				'Radio' => 'Radio',
				'Minimal' => 'Minimal',
				'Off' => 'Off',
			)
		) ,
		array (
			'name' => 'Если у бота количество денег меньше чем тут, то он не будет покупать оружие пока не преодолеет этот денежный лимит.' ,
			'var'  => 'bot_eco_limit' ,
			'default' => '800',
			'type' => '2'
		) ,
		array (
			'name' => 'Пределяет уровень гравитации. Если стоит высокое значение, то вы не сможете прыгать.' ,
			'var'  => 'sv_gravity' ,
			'default' => '800',
			'type' => '2'
		) ,
		array (
			'name' => 'Устанавливает уровень трения. Отрицательные значения приведут к ускорению.' ,
			'var'  => 'sv_friction' ,
			'default' => '4',
			'type' => '2'
		) ,
		array (
			'name' => 'Кикать простаивающих или делающих TK игроков.' ,
			'var'  => 'mp_autokick' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'За кем сможет наблюдать мертвый игрок.' ,
			'var'  => 'mp_forcecamera' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Наблюдать можно только за игроками своей команды.' ,
				'0' => 'Возможность смотреть за CT и T.'
			)
		) ,
		array (
			'name' => 'Перемещать игроков в одну из команд автоматически, если команды не сбалансированы.' ,
			'var'  => 'mp_autoteambalance' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Включение общего разговора. T и CT смогут разговаривать друг с другом.' ,
			'var'  => 'sv_alltalk' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Возможность голосового общения в игре.' ,
			'var'  => 'sv_voiceenable' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Разрешить любому игроку ставить паузу на сервере (Не рекомендуется!).' ,
			'var'  => 'sv_pausable' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Принудительная проверка наличия не стандартных скинов, звуков и карт.' ,
			'var'  => 'sv_consistency' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Настройка определяет могут ли игроки использовать чит команды.' ,
			'var'  => 'sv_cheats' ,
			'type' => '1' ,
			'default' => '0',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Максимальная скорость движения игрока.' ,
			'var'  => 'sv_maxspeed' ,
			'default' => '350',
			'type' => '2'
		) ,
		array (
			'name' => 'Разрешить игрокам заходить в наблюдатели (Specators).' ,
			'var'  => 'mp_allowspectators' ,
			'type' => '1' ,
			'default' => '1',
			'val'  => array (
				'1'  => 'Да' ,
				'0' => 'Нет'
			)
		) ,
		array (
			'name' => 'Когда игра дойдет до последнего раунда и все умрут, прежде чем загружать новую карту, будет пауза в количестве указанных здесь секунд.' ,
			'var'  => 'mp_chattime' ,
			'default' => '10',
			'type' => '2'
		) ,
		array (
			'name' => 'После этого количества секунд клиент будет отсоединен от сервера, если от него не получено сообщение.' ,
			'var'  => 'sv_timeout' ,
			'default' => '60',
			'type' => '2'
		) ,
		array (
			'name' => 'Ускорение скорости игрока когда он находиться в воздухе, например падает или прыгает.' ,
			'var'  => 'sv_airaccelerate' ,
			'default' => '10',
			'type' => '2'
		) ,
		array (
			'name' => 'Максимальная скорость с которой может двигаться игрок когда нажата клавиша [SHIFT].' ,
			'var'  => 'sv_stopspeed' ,
			'default' => '75',
			'type' => '2'
		) ,
		array (
			'name' => 'Размер шага игроков.' ,
			'var'  => 'sv_stepsize' ,
			'default' => '18',
			'type' => '2'
		) ,
	);
}

?>