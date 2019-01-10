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
// (Platzhalter)

// Abschnitt show_messages.php 4:
// (Platzhalter)

// Abschnitt show_messages.php 5:
// Nun wollen wir auslesen was in der Datenbank drin steht.
// Dazu wollen wir die DB abfragen und die Ergebnisse sollen nach der ID absteigend sortiert werden (Datum ginge auch)
$sql_query="SELECT * FROM `tblMessages` ORDER BY `id` DESC";

$result_tblMessages = $mysqli->query($sql_query);

// in einer while-Schleife durchlaufen wir jeden Datensatz, den wir von der Datenbank zurück erhalten.
while($entry_tblMessages = $result_tblMessages->fetch_array(MYSQLI_BOTH)) {
	// ... und geben diesen einfach über einen echo-Befehl im Browser aus
    echo " ".$entry_tblMessages['date_created'].": ".$entry_tblMessages['message']." <br>";
}

?>