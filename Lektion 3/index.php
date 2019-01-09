<?php

/***
* Lektion 3: Hallo Binance-API
*
* 1. Binance-Account einrichten
* 2. Google-Auth und Binance-API auf Binance einrichten
* 3. Binance-API-Programmcode laden und ihn in unseren Webspace schieben
* 4. Binance-API ansprechen und eigene Balance laden
* 5. Array-Struktur
* 6. Basiswährung und Coins inkl. Parameter definieren
* 7. Kurse zu unseren Coins ermitteln
* 8. Trading-Rules ermitteln
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
// update_messages("Hallo Welt!"); 

// Nun wollen wir auslesen was in der Datenbank drin steht. 
// das machen wir aber in der Datei show_messages.php, denn der Code des Bots sollte der Ausgabe der Status-Meldungen getrennt sein.
	
// Abschnitt index.php 5: 
function update_accountinfo(){

	global $__balanceBot_coins; // der Status steht in unserem "Master"-Array zusammen mit allen anderen Daten
	global $__balanceBot_basecurrency;
	global $binance_api_handler; // den Binance-API-Handler brauchen wir in unserer Funktion.

	// Nun wollen wir wissen wieviel Coins wir insgesamt in unserem Portfolio haben (Teil 4)
	// Dazu senden wir eine entsprechende Anfrage an Binance und schauen uns an was da zurück kommt.
	$array_binance_account = $binance_api_handler->account();

	echo "<pre> Das sind unsere Account-Daten, die Binance zurück gibt<br>";
	print_r($array_binance_account);
	echo "</pre>";

	// Abschnitt index.php 5.1: 
	// ok, nun wissen wir, dass in dem Array unter "balances" je Asset unser Bestand steht
	// Also können wir in einer foreach-Schleife die zurückgegebenen Daten durchsuchen ob wir unsere Basiswährung finden
	foreach ($array_binance_account['balances'] as $key => $array_account_coin){

		// wenn das Asset dem Namen unserer Basis-Währung entspricht, haben wir unseren Basis-Coin gefunden
		if ($array_account_coin['asset'] == $__balanceBot_basecurrency['name']){
			// "locked" sind die Coins, für die gerade offene Order angelegt sind, "free" sind die noch verfügbaren Coins für neue Order
			$message = "Bestand: ".($array_account_coin['free'] + $array_account_coin['locked'])." ".$array_account_coin['asset'] ." ";
			update_messages($message); // Message in DB schreiben und ausgeben.
		
			// wenn wir den Bestand gefunden haben, können wir den auch gleich in unser Array zum basiscoin aufnehmen:
			$__balanceBot_basecurrency['free'] = $array_account_coin['free'];
			$__balanceBot_basecurrency['locked'] = $array_account_coin['locked'];
		}

	}

	// Abschnitt index.php 5.2: 

	// ... und ob wir unsere Coins finden, die der BalanceBot verwalten soll
	// Da wir jedoch mehr als nur einen Coin von unserem Bot verwalten lassen, müssen wir für jeden Coin, der von Binance zurück kommt
	// in einer weiteren foreach-Schleife die zu verwaltenden Coins gegenchecken, also eine Stufe schwerer als für die Basis-Währung.
	foreach ($array_binance_account['balances'] as $key => $array_account_coin){

		foreach ($__balanceBot_coins as $key => $array_coin){
			// wenn das Asset dem Namen unseres Coins entspricht, haben wir unseren Coin gefunden
			if ($array_account_coin['asset'] == $array_coin['name']){
				// "locked" sind die Coins, für die gerade offene Order angelegt sind, "free" sind die noch verfügbaren Coins für neue Order
				$message = "Bestand: ".($array_account_coin['free'] + $array_account_coin['locked'])." ".$array_account_coin['asset'] ." ";
				update_messages($message); // Message in DB schreiben und ausgeben.
		
				// wenn wir den Bestand gefunden haben, können wir den auch gleich in unser Array zum Coin aufnehmen:
				$__balanceBot_coins[$key]['free'] = $array_account_coin['free'];
				$__balanceBot_coins[$key]['locked'] = $array_account_coin['locked'];
			}
		}
	}

}

// Abschnitt index.php 6:
// (Platzhalter)

// Abschnitt index.php 7:
// (Platzhalter)

// Abschnitt index.php 8: 
// Binance-API anbinden
require_once ("php-binance-api.php"); // Binance-API Code laden
$binance_api_handler = new Binance\API($__binance_APIkey ,$__binance_APIsecret);
// prüfen ob die Binance-API tatsächlich angesprochen werden kann
if (!$binance_api_handler){
    update_messages ("FEHLER: 'binance_api_handler' nicht vorhanden");
}

// Abschnitt index.php 8.1: 
// nun aktualisieren wir all unsere Account-Infos ... siehe oben bei der Funktionsdeklarierung
update_accountinfo();

echo "<pre> So sieht nun unser Array __balanceBot_basecurrency aus: <br>";
print_r($__balanceBot_basecurrency);
echo "</pre>";
echo "<pre> So sieht nun unser Array __balanceBot_coins aus: <br>";
print_r($__balanceBot_coins);
echo "</pre>";

// Abschnitt index.php 9: 
// Nachdem wir nun die Binance-API eingebunden haben, nutzen wir sie auch mal und fragen die Kurse ab
foreach ($__balanceBot_coins as $key => $array_coin){
	// finde den Preis zum derzeit bearbeiteten Pair 

	$array_binance_prevDay = $binance_api_handler->prevDay($__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name']);	

	// echo "<pre> Dieses Array array_binance_prevDay gibt uns Binance zurück: <br>";
	// print_r($array_binance_prevDay);
	// echo "</pre>";
	
	// Hier machen wir eine Fehlerabfrage ob wir einen Preis für den Coin finden - wenn ein Coin gedelistet wird, gibt es keinen Preis mehr
	if ($array_binance_prevDay["lastPrice"]){
		$__balanceBot_coins[$key]["price"] = $array_binance_prevDay["lastPrice"];
		$__balanceBot_coins[$key]["price_24_high"] = $array_binance_prevDay["highPrice"];
		$__balanceBot_coins[$key]["price_24_low"] = $array_binance_prevDay["lowPrice"];
		$message = "Finde Preis für ".$__balanceBot_coins[$key]['name']."".$__balanceBot_basecurrency['name'].": ".$__balanceBot_coins[$key]["price"]." (24h high/low: ".$__balanceBot_coins[$key]["price_24_high"]." / ".$__balanceBot_coins[$key]["price_24_low"]." ";
		update_messages($message); // message in DB schreiben und ausgeben.
	} else {
		$message = "FEHLER: Keinen Preis für ".$__balanceBot_coins[$key]['name']."".$__balanceBot_basecurrency['name']." gefunden!";
		update_messages($message); // message in DB schreiben und ausgeben.
	}
}

echo "<pre> So sieht nun unser Array __balanceBot_basecurrency aus: <br>";
print_r($__balanceBot_basecurrency);
echo "</pre>";
echo "<pre> So sieht nun unser Array __balanceBot_coins aus: <br>";
print_r($__balanceBot_coins);
echo "</pre>";

// Abschnitt index.php 10:
// (Platzhalter)

// Abschnitt index.php 11: 
// Bevor wir überhaupt irgendwelche Order erstellen müssen wir die Regeln kennen wie Order erstellt werden dürfen.
// Zum Beispiel mit wieviel Dezimalstellen Preise und Coinanzahl übermittelt werden dürfen.
$array_binance_exchangeInfo = $binance_api_handler->exchangeInfo();

/*
echo "<pre> So sieht nun unser Array array_binance_exchangeInfo aus: <br>";
print_r($array_binance_exchangeInfo);
echo "</pre>";
*/

// Für jeden meiner Coins ermittle ich nun die minimale handelbare Coin-Anzahl und die Minimum-Beträge, die es zum Traden braucht
foreach ($__balanceBot_coins as $key => $array_coin){
	// Und je Coin muss ich den Kram durchwühlen, den ich von Binance bekommen habe
	foreach ($array_binance_exchangeInfo['symbols'] as $entry){
		if ($entry['symbol'] == $array_coin['name'].$__balanceBot_basecurrency['name']){

			foreach ($entry['filters'] as $filters){
				// Mindestanzahl an Coins, die getradet werden dürfen, bzw. die Anzahl der Nachkommastellen
				if ($filters['filterType'] == "LOT_SIZE"){
					$__balanceBot_coins[$key]['minQty'] = $filters['minQty'];
					// nun wird es besonders knifflig.
					// Binance gibt die Min-Quantity als "0.00000100" an, das bedeutet, ich muss später auf 6 Stellen genau runden.
					// Nun muss die "0.00000100" zur "6" werden .. das ist der Absolutwert der abgerundeten Ganzzahl des Log10 (fragt mich nicht, wie ich drauf kam ...)
					$__balanceBot_coins[$key]['minQtyPrecision'] = abs(floor(log10($__balanceBot_coins[$key]['minQty'])));
				}
				// Mindestbetrag, der getradet werden darf. (In der Regel 10.00000000)
				if ($filters['filterType'] == "MIN_NOTIONAL"){
					$__balanceBot_coins[$key]['minNotional'] = $filters['minNotional'];
				}
				// Mindestbetrag je Coin, bzw. die Rundungsgenauigkeit für Preise
				if ($filters['filterType'] == "PRICE_FILTER"){
					$__balanceBot_coins[$key]['tickSize'] = $filters['tickSize'];
					$__balanceBot_coins[$key]['tickSizePrecision'] = abs(floor(log10($__balanceBot_coins[$key]['tickSize'])));
				}
			}
		}
	}
}

echo "<pre> So sieht nun unser Array __balanceBot_coins aus: <br>";
print_r($__balanceBot_coins);
echo "</pre>";

// Abschnitt index.php 12:
// (Platzhalter)

// Abschnitt index.php 13:
// (Platzhalter)

// Abschnitt index.php 14: 
// (Platzhalter)



?>



