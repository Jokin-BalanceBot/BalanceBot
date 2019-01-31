<?php
/***
*
* Via phpmyadmin Tabelle, Priämarschlüssel, auto_increment anlegen

CREATE TABLE `tblBalances` (
  `id` int(11) NOT NULL,
  `coin_name` varchar(10) NOT NULL,
  `pair_name` varchar(10) NOT NULL,
  `date_created` datetime NOT NULL,
  `current_balance` double NOT NULL,
  `current_price` double NOT NULL,
  `target_percentage` double NOT NULL,
  `virtual_percentage` double NOT NULL,
  `virtual_balance` double NOT NULL

);

ALTER TABLE `tblBalances`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tblBalances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
*/

require_once 'config.php'; // Config-Datei einbinden

// Abschnitt show_csv.php 1:
// Datenbankverbindung aufbauen und einen DB-Handler erzeugen
$mysqli = new mysqli($__db_server, $__db_user, $__db_passwort, $__db);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit(); // Skript beenden, wenn keine DB-Verbindung zustande kommt.
}

// Abschnitt show_csv.php 2:
// Daten aus der Datenbank auslesen
$sql_query="SELECT * FROM `tblBalances` order by date_created DESC";
$result_tblBalances = $mysqli->query($sql_query);


// Abschnitt show_csv.php 3:
// Daten aufbereiten
// Alle Einträge einer Zeile sollen einem Zeitpunkt zugeordnet werden
while($entry_tblBalances = $result_tblBalances->fetch_array(MYSQLI_BOTH)) {
	$array_csv[$entry_tblBalances['date_created']][$entry_tblBalances['coin_name']] = $entry_tblBalances;
}

// Abschnitt show_csv.php 4:
// Daten ausgeben
// Hier geben wir nicht alles aus, was in der Datenbank steht sondern nur das, 
// was derzeit in der Config.php definiert ist.
// Nach Anpassung der Coins in der config.php führt das zu Auswertungsfehler, 
// die aufgrund der Einfachheit in Kauf genommen werden.

// Abschnitt show_csv.php 4.1:
// Titel der CSV-Datei ausgeben

// Abschnitt show_csv.php 4.1.1:
echo "Datum;";
echo "virtual ".$__balanceBot_basecurrency['name']." in %;";
echo "virtual ".$__balanceBot_basecurrency['name']." in Stück;";
echo "current ".$__balanceBot_basecurrency['name']." in Stück;";

// Abschnitt show_csv.php 4.1.2:
foreach ($__balanceBot_coins as $key => $array_coin){
	echo "virtual ".$__balanceBot_coins[$key]['name']." in %;";
	echo "virtual ".$__balanceBot_coins[$key]['name']." in Stück;";
	echo "virtual ".$__balanceBot_coins[$key]['name']." in ".$__balanceBot_basecurrency['name'].";";
	echo "current ".$__balanceBot_coins[$key]['name']." in Stück;";
	echo "current ".$__balanceBot_coins[$key]['name']." in ".$__balanceBot_basecurrency['name'].";";
}
echo "virtual Gesamtportfoliowert in ".$__balanceBot_basecurrency['name'].";";
echo "current Gesamtportfoliowert in ".$__balanceBot_basecurrency['name'].";";
echo "<br>";

// Abschnitt show_csv.php 4.2:
// Daten der CSV-Datei ausgeben
foreach ($array_csv as $key => $entry_csv){

	$gesamt_virtual_portfoliowert = 0;
	$gesamt_current_portfoliowert = 0;

	// Abschnitt show_csv.php 4.2.1:
	echo $key.";";
	echo $entry_csv[$__balanceBot_basecurrency['name']]['virtual_percentage'].";";
	echo $entry_csv[$__balanceBot_basecurrency['name']]['virtual_balance'].";";
	echo $entry_csv[$__balanceBot_basecurrency['name']]['current_balance'].";";

	$gesamt_virtual_portfoliowert += $entry_csv[$__balanceBot_basecurrency['name']]['virtual_balance'];
	$gesamt_current_portfoliowert += $entry_csv[$__balanceBot_basecurrency['name']]['current_balance'];

	// Abschnitt show_csv.php 4.2.2:
	foreach ($__balanceBot_coins as $key => $array_coin){
		echo $entry_csv[$__balanceBot_coins[$key]['name']]['virtual_percentage'].";";
		echo $entry_csv[$__balanceBot_coins[$key]['name']]['virtual_balance'].";";
		echo $entry_csv[$__balanceBot_coins[$key]['name']]['virtual_balance'] * $entry_csv[$__balanceBot_coins[$key]['name']]['current_price'].";";
		echo $entry_csv[$__balanceBot_coins[$key]['name']]['current_balance'].";";
		echo $entry_csv[$__balanceBot_coins[$key]['name']]['current_balance'] * $entry_csv[$__balanceBot_coins[$key]['name']]['current_price'].";";
		$gesamt_virtual_portfoliowert += $entry_csv[$__balanceBot_coins[$key]['name']]['virtual_balance'] * $entry_csv[$__balanceBot_coins[$key]['name']]['current_price'];
		$gesamt_current_portfoliowert += $entry_csv[$__balanceBot_coins[$key]['name']]['current_balance'] * $entry_csv[$__balanceBot_coins[$key]['name']]['current_price'];
	}
	echo $gesamt_virtual_portfoliowert.";";
	echo $gesamt_current_portfoliowert.";";
	echo "<br>";
}





?>


