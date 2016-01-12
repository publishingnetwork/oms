<?php
echo shell_exec('date');
exit;
set_time_limit(0);
ignore_user_abort(true);
//var_dump(ini_get('max_execution_time'));

readfile('application/logs/2013/08/27.php');
exit;
var_dump(shell_exec('date'));
exit;


var_dump(shell_exec('/usr/local/bin/php /home/yeastinf/public_html/energizegreens/oms/index.php --task=UpdatePaypal &'));

//var_dump(shell_exec('date'));