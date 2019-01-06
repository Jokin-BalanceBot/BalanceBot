<?php

/***
* Lektion 2: Hallo Datenbank
* ... wir widmen uns der Datenbank
* ... wir legen eine Datenbank an
* ... wir lernen phpmyadmin kennen um eine MySQL-Datenbank zu verwalten
* ... wir erzeugen die Tabellen, die wir brauchen, indem wir eine Tabelle anlegen, 
*     ihr einen Primärschlüssel geben und diesen automatisch mit jedem neuen 
*     Datensatz um 1 hochzählen lassen:

CREATE TABLE `tblMessages` (
  `id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `message` text NOT NULL
);

ALTER TABLE `tblMessages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tblMessages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  
* ... wir gliedern diese Programmcode-Datei in Abschnitte, die in späteren Lektionen gefüllt werden.
*     Auf diese Weise behalten wir die Orientierung wann welcher Code wie zu aktiualisieren ist.
* ... wir lagern den Konfigurations-Code in eine externe Datei "config.php" aus, so trennen wir 
*     Einstellungen von Programmcode und können einfacher Updates einspielen indem wir nur
*     den Programmcode austauschen und sämtliche Konfigurationen beibehalten können.
* ... Wir erstellen eine Funktion "update_messages()" um Statusmeldungen in die Datenbank zu schreiben
*     Das ist nötig um später im automatischen Betrieb den Status des Bots abzufragen
* ... wir lesen die Inhalte aus der Datenbank aus (Datei show_messages.php")
*     Ebenso wie wir die Konfiguration abtrennen werden wir auch Programmcode und 
*     Status-Ausgabe voneinander trennen.
* ... und nach X Tagen läschen wir die Datenbankeinträge wieder um zu vermeiden, dass 
*     die Datenbank irgendwann zu voll wird (Abschnitt 14).
*
* Weiterführende Informationen:
*   Google "mysql select" um nachzulesen was der MYSQL-Befehl "select" macht
*   Google "php manual date" um nachzulesen was "date()" in unserer Funktion update_message() anstellt.
*   Google "php manual real_escape_string" um nachzulesen was "real_escape_string()" in unserer Funktion update_message() anstellt.
*   Google "php manual require_once" um nachzulesen wie "require_once()" den Code weiterer Dateien einbindet.
*
* Dokumentation:
*  https://coinforum.de/topic/15993-workshop-wir-basteln-uns-einen-tradingbot-lektion-2/
*  https://github.com/Jokin-BalanceBot/BalanceBot
*/

// Abschnitt index.php 1: Datei mit den Konfigurationsparametern einlesen
require_once("config.php");

// Abschnitt index.php 2: Datenbankverbindung aufbauen und einen DB-Handler erzeugen
$mysqli = new mysqli($__db_server, $__db_user, $__db_passwort, $__db);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit(); // Skript beenden, wenn keine DB-Verbindung zustande kommt.
}

echo "DB-Verbindung hat offenbar geklappt<br>";

// Abschnitt index.php 3: 
// (Platzhalter)

// Abschnitt index.php 4: 
// Da wir zukünftig öfters Statusmeldungen in die Datenbank schreiben werden, machen wir eine Funktion draus 
function update_messages($message){

    global $mysqli;    // Datenbank-Handler innerhalb der Funktion zugänglich machen

    // Mit "real_escape_string" werden Sonderzeichen umgewandelt um sie in der DB zu speichern
    $sql_query = "INSERT INTO `tblMessages` (`date_created`, `message`) 
        VALUES ('".date("Y-m-d H:i:s")."', '".$mysqli->real_escape_string($message)."')";

    echo "MESSAGE: $message <br>"; // Was in die DB kommt, kann auch gleich mal ausgegeben werden, 

    // Minimales Fehlerhandling
    if (!$mysqli->query($sql_query)) {
        echo $mysqli->error;
    }
}


// Abschnitt index.php 4.1: 
// Nun benutzen wir die Funktion um eine Nachricht in die Datenbank zu schreiben. 
update_messages("Hallo Welt!"); 

// Nun wollen wir auslesen was in der Datenbank drin steht. 
// das machen wir aber in der Datei show_messages.php, denn der Code des Bots sollte der Ausgabe der Status-Meldungen getrennt sein.
	
// Abschnitt index.php 5:
// (Platzhalter)

// Abschnitt index.php 6:
// (Platzhalter)

// Abschnitt index.php 7:
// (Platzhalter)

// Abschnitt index.php 8:
// (Platzhalter)

// Abschnitt index.php 9:
// (Platzhalter)

// Abschnitt index.php 10:
// (Platzhalter)

// Abschnitt index.php 11:
// (Platzhalter)

// Abschnitt index.php 12:
// (Platzhalter)

// Abschnitt index.php 13:
// (Platzhalter)

// Abschnitt index.php 14: 
// Irgendwann ist die Datenbank voll, daher löschen wir Einträge, die älter als 7 Tage sind. (aus Teil 2)
$sql_query = "DELETE FROM `tblMessages` where `date_created`  < DATE_ADD(now(),INTERVAL -7 DAY)";

// Und auch obiges SQL-Statement an die Datenbank senden (aus Teil 2)
$mysqli->query($sql_query);



?>



