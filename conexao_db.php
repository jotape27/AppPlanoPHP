<?php

// Abre uma conexao com o BD.

$host        = "host = abul.db.elephantsql.com;";
$port        = "port = 5432;";
$dbname      = "dbname = apgrsynx;";
$dbuser      = "apgrsynx";
$dbpassword     = "Feg9QwZGipsWx7MwTMPRsXwHIrcAROgd";

// dados de conexao com o railway. Usar somente caso esteja usando railway
// $host        = "host = " . getenv("PGHOST") . ";";
// $port        = "port = " . getenv("PGPORT") . ";";
// $dbname      = "dbname = " . getenv("PGDATABASE") . ";";
// $dbuser 	 = getenv("PGUSER");
// $dbpassword	 = getenv("PGPASSWORD");

// para conectar ao mysql, substitua pgsql por mysql
$db_con = new PDO('pgsql:' . $host . $port . $dbname, $dbuser, $dbpassword);

//alguns atributos de performance.
$db_con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
