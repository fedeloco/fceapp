<?php
$dbname="juc2013";
$server="localhost";
$username="root";
$password="webfce123";
$db = mysql_connect($server,$username,$password);
mysql_query("use $dbname");
mysql_query("SET NAMES utf8");
?>
