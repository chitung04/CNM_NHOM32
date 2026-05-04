<?php
session_start();
session_destroy();
session_unset();
setcookie(session_name(), '', time() - 3600, '/');

header('Location: index.php?page=login');
exit;
