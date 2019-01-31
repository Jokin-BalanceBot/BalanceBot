<?php

// Abschnitt show_messages.php 1: Datei mit den Konfigurationsparametern einlesen
require_once("config.php");

// Abschnitt show_messages.php 2: Datenbankverbindung aufbauen und einen DB-Handler erzeugen
$mysqli = new mysqli($__db_server, $__db_user, $__db_passwort, $__db);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit(); // Skript beenden, wenn keine DB-Verbindung zustande kommt.
}

echo "DB-Verbindung hat offenbar geklappt<br>";


// Abschnitt show_messages.php 3:
// Die Linkliste zu Beginn der Seite einbinden
// mit <?php ... lässt sich PHP-Code innerhalb von HTML-Code einfügen.
?>
<a href="<?php echo $_SERVER["PHP_SELF"] ?>?limit=600">alle Messages</a> |
<a href="<?php echo $_SERVER["PHP_SELF"] ?>?limit=600&search=Creat">Create</a> |
<a href="<?php echo $_SERVER["PHP_SELF"] ?>?limit=600&search=Order">Order</a> |
<a href="<?php echo $_SERVER["PHP_SELF"] ?>?limit=600&search=Status">Status</a> |
<a href="<?php echo $_SERVER["PHP_SELF"] ?>?limit=600&search=Error">Error</a> |
<a href="<?php echo $_SERVER["PHP_SELF"] ?>?limit=600&search=Fehler">Fehler</a> |
<a href="<?php echo $_SERVER["PHP_SELF"] ?>?limit=600&search=balance">BALANCE</a> |
<a href="<?php echo $_SERVER["PHP_SELF"] ?>?limit=600&search=csv">csv</a> |
<hr>
<?php


// Abschnitt show_messages.php 4:
// Daten, die mit der URL übergeben werden, nehmen wir hier in das Skript auf
$limit = $_GET["limit"];
$search = $_GET["search"];

// falls keine Werte übermittelt worden sind, setzen wir hier Standardwerte
if (!$limit or !is_numeric($limit)) $limit = 600; // falls "limit" nicht numerisch ist, dann wird das hier abgefangen.
if (!$search) $search = ''; // "$search" kann beliebige Suchwerte beinhalten - individuelle Auswertungen werden so möglich


// Abschnitt show_messages.php 5:
// Nun wollen wir auslesen was in der Datenbank drin steht.
// Dazu wollen wir die DB abfragen und die Ergebnisse sollen nach der ID absteigend sortiert werden (Datum ginge auch)
$sql_query="SELECT * FROM `tblMessages` WHERE `message` like '%".$mysqli->real_escape_string($search)."%' ORDER BY  `id` DESC LIMIT 0, ".$mysqli->real_escape_string($limit)." ";

$result_tblMessages = $mysqli->query($sql_query);

// in einer while-Schleife durchlaufen wir jeden Datensatz, den wir von der Datenbank zurück erhalten.
while($entry_tblMessages = $result_tblMessages->fetch_array(MYSQLI_BOTH)) {
	// ... und geben diesen einfach über einen echo-Befehl im Browser aus
    echo " ".$entry_tblMessages['date_created'].": ".$entry_tblMessages['message']." <br>";
}


?>