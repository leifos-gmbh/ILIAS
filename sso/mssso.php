<?php

$attributes = base64_encode($_SERVER['HTTP_X_TRUSTED_REMOTE_ATTR']);
$user = base64_encode($_SERVER['HTTP_X_TRUSTED_REMOTE_USER']);

header('Location: http://www.unirep-online.de/login.php?force_auth=1&mssso_attrbs='.$attributes.'&mssso_user='.$user);
exit;

?>
