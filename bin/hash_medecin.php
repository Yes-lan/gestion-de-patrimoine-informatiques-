<?php
$password = 'test1234';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash for '$password': $hash\n";
