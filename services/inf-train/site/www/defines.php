<?php
define('USE_ERROR_LOG', 1);
define('USE_API_EVENTS_LOG', 0);
define('DEBUG_SMTP', 0);
define('USE_SSL', 0);


/** 3600\*24 -- 60\*60*24 */
define('SECONDS_PER_DAY', 86400);
/** 60*60 */
define('SECONDS_PER_HOUR', 3600);
define('SECONDS_PER_MINUTE', 60);
define('BYTES_PER_KB', 1024);
define('BYTES_PER_MB', 1048576);
define('BYTES_PER_GB', 1073741824);
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DB_DATE_FORMAT', 'Y-m-d');

/** int REMEMBER_AUTH_TOKEN_LIVE_DAYS - время жизни запоминающего токена в сутках @see action_login */
define('REMEMBER_AUTH_TOKEN_LIVE_DAYS', 30);
/** int SESSION_AUTH_TOKEN_LIVE_SECONDS - время жизни сессионного токена в секундах */
define('SESSION_AUTH_TOKEN_LIVE_SECONDS', 20*SECONDS_PER_MINUTE);