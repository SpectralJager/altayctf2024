<?php
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=UTF-8');
//setlocale(LC_COLLATE | LC_CTYPE | LC_TIME, 'ru_RU.UTF-8', 'ru_RU', 'ru', 'russian');
define('MAIN_DIR', __DIR__.'/');
require_once MAIN_DIR.'defines.php';
require_once MAIN_DIR.'credentials.php';

//подключаем функции из файлов includes/functions/*-functions.php
require_once MAIN_DIR.'includes/functions/files-functions.php';
$files = file_list(MAIN_DIR.'includes/functions/', 'file', '.php', '^.*?-functions');
for($i=0; $i<sizeof($files); $i++){
	if($files[$i] != 'files-functions.php')
		require_once MAIN_DIR.'includes/functions/'.$files[$i];
}

require_once MAIN_DIR.'includes/vendor/autoload.php';

if(defined('USE_ERROR_LOG') && USE_ERROR_LOG){
	\Core\ErrorLog::instance(MAIN_DIR.'../logs/');
}

$DB = new \Core\DB(['db' => MAIN_DIR.SQLITE_DB_PATH]);

$USER = \Core\User::current();

$URL = new \Core\Url(\Pages\InfTrain\Main::class, \Pages\InfTrain\_404::class);


\Core\PageStorage::instance()->prepare_current_page();

\Core\CookieStorage::instance()->send_cookies();

\Core\PageStorage::instance()->view_current_page();