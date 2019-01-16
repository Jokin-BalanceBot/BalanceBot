<?php

/***
* Lektion 7: Hallo Cronjob
*
* 1. Cronjob einrichten (Automatisierung)
* 2. Setup eines eigenen Raspberry Pi
* 3. Weiterführende Informationen
*
* Dokumentation:
*  https://coinforum.de/topic/16081-workshop-wir-basteln-uns-einen-tradingbot-lektion-7/
*  https://github.com/Jokin-BalanceBot/BalanceBot
*
* Bitte eine kleine Spende (BTC oder BCH) an 1JokinL8P2A5Zh9QNyD9Rv2HGCYRGLhhef
* Und bitte auch Euren neuen BalanceBot-Binance-Account über meinen Ref-Link anmelden:
*
*             =>  https://www.binance.com/?ref=28506673  <=
*
*              Danke :-)
*
*/

// Abschnitt index.php 1: Datei mit den Konfigurationsparametern einlesen
require_once("config.php");

// Abschnitt index.php 2: Datenbankverbindung aufbauen und einen DB-Handler erzeugen
$mysqli = new mysqli($__db_server, $__db_user, $__db_passwort, $__db);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit(); // Skript beenden, wenn keine DB-Verbindung zustande kommt.
}

// echo "DB-Verbindung hat offenbar geklappt<br>";

// Abschnitt index.php 3: 
// (Platzhalter)

// Abschnitt index.php 4: 
// Da wir zukünftig öfters Statusmeldungen in die Datenbank schreiben werden, machen wir eine Funktion draus 
function update_messages($message){

    global $mysqli;    // Datenbank-Handler innerhalb der Funktion zugänglich machen

    // Mit "real_escape_string" werden Sonderzeichen umgewandelt um sie in der DB zu speichern
    $sql_query = "INSERT INTO `tblMessages` (`date_created`, `message`) 
        VALUES ('".date("Y-m-d H:i:s")."', '".$mysqli->real_escape_string($message)."')";

    // echo "MESSAGE: $message <br>"; // Was in die DB kommt, kann auch gleich mal ausgegeben werden, 

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

	global $__balanceBot_coins; // der Status steht in unserem "Master"-Array zusammen mit alen anderen Daten
	global $__balanceBot_basecurrency;
	global $binance_api_handler; // den Binance-API-Handler brauchen wir in unserer Funktion.

	// Nun wollen wir wissen wieviel Coins wir insgesamt in unserem Portfolio haben (Teil 4)
	// Dazu senden wir eine entsprechende Anfrage an Binance und schauen uns an was da zurück kommt.
	$array_binance_account = $binance_api_handler->account();

	// echo "<pre> Das sind unsere Account-Daten, die Binance zurück gibt<br>";
	// print_r($array_binance_account);
	// echo "</pre>";

	// Abschnitt index.php 5.1: 
	// ok, nun wissen wir, dass in dem Array unter "balances" je Asset unser Bestand steht (Teil 4)
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

	// ... und ob wir unsere Coins finden, die der BalanceBot verwalten soll (Teil 4)
	// Da wir jedoch mehr als nur einen Coin von unserem Bot verwalten lassen, müssen wir für jeden Coin, der von Binance zurück kommt
	// in einer weiteren foreach-Schleife die zu verwaltenden Coins gegenchecken, also eine Stufe schwerer als für die Basis-Währung.
	foreach ($array_binance_account['balances'] as $key => $array_account_coin){

		foreach ($__balanceBot_coins as $key => $array_coin){
			// wenn das Asset dem Namen unserer Basis-Währung entspricht, haben wir unseren Basis-Coin gefunden
			if ($array_account_coin['asset'] == $array_coin['name']){
				// "locked" sind die Coins, für die gerade offene Order angelegt sind, "free" sind die noch verfügbaren Coins für neue Order
				$message = "Bestand: ".($array_account_coin['free'] + $array_account_coin['locked'])." ".$array_account_coin['asset'] ." ";
				update_messages($message); // Message in DB schreiben und ausgeben.
		
				// wenn wir den Bestand gefunden haben, können wir den auch gleich in unser Array zum basiscoin aufnehmen:
				$__balanceBot_coins[$key]['free'] = $array_account_coin['free'];
				$__balanceBot_coins[$key]['locked'] = $array_account_coin['locked'];
			}
		}
	}

}

// Abschnitt index.php 6: 
// wann immer sich was im Orderbuch verändert, soll der Status aktualisiert werden, das machen wir auch am Besten in einer Funktion
function update_status(){

	global $__balanceBot_coins; 				// der Status steht in unserem "Master"-Array zusammen mit allen anderen Daten
	global $__balanceBot_basecurrency;			// und auch das Array mit der Basiswährung brauchen wir hier in der Funktion
	global $array_binance_openOrders; 			// auch die offenen Order müssen wir hier in der Funktion zugänglich machen
	global $sum_coin_value; 					// hier wird später der Coin-Gesamtwert drin stehen
	global $array_coins_sorted_by_deviation;	// Nach Abweichung abwärts sortiert entsteht später eine Priorität der Abarbeitung

	$sum_coin_value = 0;	// mit jedem Update ermitteln wir die Summe aller Coins auf's Neue
	$__balanceBot_basecurrency['virtual_balance'] = 0; // auch diese Summe ermitteln wir mit jedem Durchlauf auf's Neue.
												// Die "virtual_balance" ist die Balance, die nach Erfüllung aller offenen 
												// Order vorhanden wäre - darauf bauen wir unseren Status auf.
												// die gibt es für die Basiswährung und für jeden Coin.
	
	foreach ($__balanceBot_coins as $key => $array_coin){

		$__balanceBot_coins[$key]['virtual_balance'] = 0; // und auch je Coin mit jedem Funktions-Aufruf die Summe auf's Neue ermitteln.
	
		// Abschnitt index.php 6.1:
		// nun müssen wir für jede Order entweder den Bestand der Basiswährung oder den Bestand der Coins erhöhen.
		// Dazu durchlaufen wir alle offenen Order mal wieder in einer foreach-Schleife:
		foreach ($array_binance_openOrders[$array_coin['name']] as $array_openOrder){
			if ($array_openOrder['side'] == "SELL"){
				// wenn SELL, dann Basis-Währung um den Wert der dann verkauften Coins erhöhen
				// "origQty" ist die ursprüngliche Ordermenge und "executedQty" ist das was bereits ausgeführt wurde, das ziehen wir ab, denn deren Verkauf passierte bereits.
				$__balanceBot_basecurrency['virtual_balance'] += ($array_openOrder['origQty'] - $array_openOrder['executedQty']) * $array_openOrder['price'];	
			}
		
			if ($array_openOrder['side'] == "BUY"){
				// wenn BUY, dann Basis-Währung um den Wert der dann verkauften Coins erhöhen
				// "origQty" ist die ursprüngliche Ordermenge und "executedQty" ist das was bereits ausgeführt wurde, das ziehen wir ab, denn deren Verkauf passierte bereits.
				$__balanceBot_coins[$key]['virtual_balance'] += ($array_openOrder['origQty'] - $array_openOrder['executedQty']);	
			}
		
			// Jede offene Order geben wir auch im MessageLog aus - wir wollen die ja stets im Blick haben
			$message = "OPENORDER: ".$array_openOrder['side']." ".$array_openOrder['symbol']." ".($array_openOrder['origQty'] - $array_openOrder['executedQty'])." @ ".$array_openOrder['price']."";
			update_messages($message); // Message in DB schreiben und ausgeben.
		}

	
		// nachdem für den Coin alle offenen Order abgearbeitet sind, assieren wir die "free"-Coins zur virtuellen Balance:
		$__balanceBot_coins[$key]['virtual_balance'] += $__balanceBot_coins[$key]['free'];
	}
	
	// nachdem nun alle offenen Order aller Coins abgearbeitet sind, addieren wir die "free"-Coins der Basis-Währung zur virtuellen Balance (Teil 4)
	$__balanceBot_basecurrency['virtual_balance'] += $__balanceBot_basecurrency['free'];

	// Abschnitt index.php 6.2:
	// Und nun können wir aus der virtuellen Balance einen virtuellen Wert ermitteln um darauf aufbauend zu ermitteln ob das Portfolio korrekt ausbalanciert ist (Teil 4)
	// Wieder arbeiten wir in einer foreach-Schleife jeden Coin ab und ermitteln den Wert "virtual_value":
	foreach ($__balanceBot_coins as $key => $array_coin){
		$__balanceBot_coins[$key]['virtual_value'] = $__balanceBot_coins[$key]['virtual_balance'] * $__balanceBot_coins[$key]['price'];
	}

	// Nun möchten wir den Gesamtwert aller Coins ermitteln - auch das machen wir schon wieder in einer foreach-Schleife (Teil 4)
	foreach ($__balanceBot_coins as $key => $array_coin){
		$sum_coin_value += $__balanceBot_coins[$key]['virtual_value'];
	}

	// echo "Der Gesamtwert aller Coins beträgt: ".round($sum_coin_value,2)." ".$__balanceBot_basecurrency['name']." - der Wert der Seitenlinie (Basiswährung) beträgt: ".round($__balanceBot_basecurrency['virtual_balance'],2)." ".$__balanceBot_basecurrency['name']." Gesamtportfoliowert: ".round($sum_coin_value + $__balanceBot_basecurrency['virtual_balance'],2)." ".$__balanceBot_basecurrency['name']."<br>";

	// wenn der Gesamt-Coinwert "0" beträgt, müssen wir an der Stelle nicht weitermachen, das ergibt zum Einen einen DIV/0-Fehler, andererseits 
	// macht dieses Skript überhaupt keinen Sinn mit leerem Bestand arbeiten zu lassen.
	if ($sum_coin_value == 0){
		$message = "FEHLER: Der Portfoliowert beträgt <1 - so macht der BalanceBot keinen Sinn, also bitte erstmal irgendwelche Coins aufladen, die der Bot auch verwalten soll.";
		update_messages($message); // Message in DB schreiben und ausgeben.
		exit; // Skript beenden ... macht ja nu echt keinen Sinn.
	}


	// Abschnitt index.php 6.3:
	// Zur Bestandsaufnahme gehört es nun, dass wir sowohl für Basis-Währung als auch für jeden Coin den prozentualen Anteil am Gesamt-Coinwert ermitteln 
	foreach ($__balanceBot_coins as $key => $array_coin){
		$__balanceBot_coins[$key]['current_percentage'] = $__balanceBot_coins[$key]['virtual_value'] / $sum_coin_value * 100;
	}
	// bei der Basiswährung entspricht die Balance auch dem virtual_value - logisch ...
	$__balanceBot_basecurrency['current_percentage'] = $__balanceBot_basecurrency['virtual_balance'] / $sum_coin_value * 100;

	// Abschnitt index.php 6.4:
	// Den Bestand wollen wir nun noch in Kurzform in die Datenbank schreiben 
	// round( .... ,2) meint "runde auf 2 Stellen"
	$message = "Status: ";
	$message .= "Portfolio: ".round($__balanceBot_basecurrency['virtual_balance'] + $sum_coin_value,2)." ".$__balanceBot_basecurrency['name']."; ";
	$message .= $__balanceBot_basecurrency['name'].": ".round($__balanceBot_basecurrency['virtual_balance'],2)." Soll: ".round($__balanceBot_basecurrency['target_percentage'],2)."% Ist: ".round($__balanceBot_basecurrency['current_percentage'],2)."%; ";
	foreach ($__balanceBot_coins as $key => $array_coin){
		$message .= $__balanceBot_coins[$key]['name'].": ".round($__balanceBot_coins[$key]['virtual_value'],2)." ".$__balanceBot_basecurrency['name']." Soll: ".round($__balanceBot_coins[$key]['target_percentage'],2)."% Ist: ".round($__balanceBot_coins[$key]['current_percentage'],2)."%; ";
	}
	update_messages($message); // Message in DB schreiben und ausgeben.

	// Abschnitt index.php 6.4.1:
	// Den Bestand wollen wir nun noch in CSV-Form in die Datenbank schreiben 
	// Das wird später nützlich sein um die Daten in Excel auszuwerten.
	$message = "; CSV; ";
	$message .= " ".round($__balanceBot_basecurrency['virtual_balance'] + $sum_coin_value,2)." ".$__balanceBot_basecurrency['name']."; ";
	$message .= $__balanceBot_basecurrency['name']."; ".round($__balanceBot_basecurrency['virtual_balance'],2)."; ".round($__balanceBot_basecurrency['target_percentage'],2)."%; ".round($__balanceBot_basecurrency['current_percentage'],2)."%; ";
	foreach ($__balanceBot_coins as $key => $array_coin){
		$message .= $__balanceBot_coins[$key]['name']."; ".round($__balanceBot_coins[$key]['virtual_value'],2)."; ".$__balanceBot_basecurrency['name']."; ".round($__balanceBot_coins[$key]['target_percentage'],2)."%; ".round($__balanceBot_coins[$key]['current_percentage'],2)."%; ";
	}
	update_messages($message); // Message in DB schreiben und ausgeben.


	// Abschnitt index.php 6.4.2:
	// Den Bestand wollen wir nun in die Tabelle tblBalances schreiben damit wir später die Daten bequem auswerten können.
	global $mysqli; // diese Zeile gehört eigentlich an den Anfang der Funktion nach oben

	$current_date = date("Y-m-d H:i:s"); // damit wir in diesem Duchlauf dieselbe Zeit für jeden Datensatz haben.

	// Alle Daten für die Basiswährung in die Datenbank werfen.
    $sql_query = "INSERT INTO `tblBalances` (`coin_name`, `pair_name`, `date_created`, `current_balance`, 
				`current_price`, `target_percentage`, `virtual_percentage`, `virtual_balance`) 
        VALUES (
				'".$__balanceBot_basecurrency['name']."',
				'base',
				'".$current_date."',
				".($__balanceBot_basecurrency['free'] + $__balanceBot_basecurrency['locked']).",
				1,
				".$__balanceBot_basecurrency['target_percentage'].",
				".$__balanceBot_basecurrency['current_percentage'].",
				".$__balanceBot_basecurrency['virtual_balance']."
			)";

    // Minimales Fehlerhandling
   	if (!$mysqli->query($sql_query)) {
   	    echo $mysqli->error;
    }
	foreach ($__balanceBot_coins as $key => $array_coin){
		// Alle Daten für die Coins in die Datenbank werfen.
    	$sql_query = "INSERT INTO `tblBalances` (`coin_name`, `pair_name`, `date_created`, `current_balance`, 
						`current_price`, `target_percentage`, `virtual_percentage`, `virtual_balance`) 
        	VALUES (
 	       			'".$__balanceBot_coins[$key]['name']."',
					'".$__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name']."',
					'".$current_date."',
					".($__balanceBot_coins[$key]['free'] + $__balanceBot_coins[$key]['locked']).",
					".$__balanceBot_coins[$key]['price'].",
					".$__balanceBot_coins[$key]['target_percentage'].",
					".$__balanceBot_coins[$key]['current_percentage'].",
					".$__balanceBot_coins[$key]['virtual_balance']."
				)";

	    // Minimales Fehlerhandling
    	if (!$mysqli->query($sql_query)) {
    	    echo $mysqli->error;
	    }
	}




	// Abschnitt index.php 6.5:
	// Nun müssen wir mal ermitteln wie weit weg das IST vom Ziel ist, dazu bilden wir einfach einen Faktor aus SOLL (target_percentage) und IST (current_percentage)
	// Mit "deviation_percentage" führen wir einen Wert ein, der besagt:
	//   >1 -> Ich habe zu viel davon, das muss weniger werden! ... also SELL-Order erstellen oder BUY-Order löschen
	//   <1 -> Ich habe zu wenig davon, das muss mehr werden! ... also BUY-Order erstellen oder SELL-Order löschen
	foreach ($__balanceBot_coins as $key => $array_coin){
		$__balanceBot_coins[$key]['deviation_percentage'] = $__balanceBot_coins[$key]['current_percentage'] / $__balanceBot_coins[$key]['target_percentage'];
		// Hier bauen wir uns das Hilfsarray indem alle Coins absteigend nach ihrer Soll/Ist-Abweichung drin stehen werden.
		$array_coins_sorted_by_deviation[$key] = $__balanceBot_coins[$key]['deviation_percentage'];
	}
	// bei der Basiswährung machen wir das auch
	$__balanceBot_basecurrency['deviation_percentage'] = $__balanceBot_basecurrency['current_percentage'] / $__balanceBot_basecurrency['target_percentage'];

	// nun sortieren wir das Hilfsarray auch so, dass es brauchbar ist um den am heftigsten abweichenden Coin zuerst abzuarbeiten.
	arsort($array_coins_sorted_by_deviation); // siehe php manual "krsort()" .. behält den key assoziativer Arrays bei und sortiert absteigend.

	// echo "<pre> Und das ist die Sortierung der Coins: <br>";
	// print_r($array_coins_sorted_by_deviation);
	// echo "</pre>";


	// Abschnitt index.php 6.6:
	// Der Toleranzfaktor verhindert das Schwingen des Gesamtsystems indem nicht jede 
	// kleinste Abweichung ausbalanciert wird sonder erst wenn die Abweichung groß 
	// genug ist um durch eine Order nicht sofort wieder aus der Balance zu geraten.
	foreach ($__balanceBot_coins as $key => $array_coin){
		// welchen USDT-Wert soll der Coin im ausbalancierten Zustand haben?
		$__balanceBot_coins[$key]['target_value'] = $sum_coin_value * $__balanceBot_coins[$key]['target_percentage'] / 100;
		
		// welcher Toleranz-Faktor ergibt sich daraus?
		$__balanceBot_coins[$key]['tolerance_factor'] = $__balanceBot_coins[$key]['minNotional'] / $__balanceBot_coins[$key]['target_value'] * 100;

		// eine Nachkommastelle soll genügen
		$__balanceBot_coins[$key]['tolerance_factor'] = round ($__balanceBot_coins[$key]['tolerance_factor'],1);

		if ($__balanceBot_coins[$key]['tolerance_factor'] > 50){
			$message = "WARNUNG: Toleranzfaktor für ".$__balanceBot_coins[$key]['name']." liegt über 50% bei ".$__balanceBot_coins[$key]['tolerance_factor']." ... prozentuale Aufteilung der Coins überarbeiten oder Seitenlinie reduzieren! ";
			update_messages($message); // Message in DB schreiben und ausgeben.		
		}
	}	
	
}


// Abschnitt index.php 7: 
// An mindestens zwei Stellen werden wir später Order in das Orderbuch schreiben, das rechtfertigt auch hier eine Funktion.
function create_order($side, $symbol, $amount, $price){

	global $binance_api_handler;	// API-Handler innerhalb der Funktion zugänglich machen

	// Schonmal vorbereiten was wir in die Log-Datei schreiben wollen.
	$message = "CREATE ORDER: $side $amount @ $price $symbol ";

	// Hier führen wir die Order aus, "sellTest" / "buyTest" meint, dass Binance prüft ob die Order so 
	// anlegbar sind jedoch keine Order in das Orderbuch schreiben wird. Keine Gefahr für Geldverlust!
	// in Teil 6 ersetzen wir "sellTest" durch "sell" und "buyTest" durch "buy"
	if ($side == "SELL") $result_binance_create = $binance_api_handler->sell($symbol,$amount,$price);
	if ($side == "BUY") $result_binance_create = $binance_api_handler->buy($symbol,$amount,$price);

	// falls ein Fehler zurück kommt, wollen wir das wissen
	if ($result_binance_create['code']){
		$message .= "Binance-API-Call-ERROR ".$result_binance_create['code'].": ".$result_binance_create['msg']." ";	
	}
	update_messages ($message);								
}


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

/*
echo "<pre> So sieht nun unser Array __balanceBot_basecurrency aus: <br>";
print_r($__balanceBot_basecurrency);
echo "</pre>";
echo "<pre> So sieht nun unser Array __balanceBot_coins aus: <br>";
print_r($__balanceBot_coins);
echo "</pre>";
*/

// Abschnitt index.php 9: 
// Nachdem wir nun die Binance-API eingebunden haben, nutzen wir sie auch mal und fragen die Kurse ab (Teil 3)
foreach ($__balanceBot_coins as $key => $array_coin){
	// finde den Preis zum derzeit bearbeiteten Pair 
	$array_binance_prevDay = $binance_api_handler->prevDay($__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name']);	

	// Hier machen wir eine Fehlerabfrage ob wir einen Preis für den Coin finden - wenn ein Coin gedelistet wird, gibt es keinen Preis mehr
	if ($array_binance_prevDay["lastPrice"]){
		$__balanceBot_coins[$key]["price"] = $array_binance_prevDay["lastPrice"];
		$__balanceBot_coins[$key]["price_24_high"] = $array_binance_prevDay["highPrice"];
		$__balanceBot_coins[$key]["price_24_low"] = $array_binance_prevDay["lowPrice"];
		$message = "Finde Preis für ".$__balanceBot_coins[$key]['name']."".$__balanceBot_basecurrency['name'].": ".$__balanceBot_coins[$key]["price"]." (24h high/low: ".$__balanceBot_coins[$key]["price_24_high"]." / ".$__balanceBot_coins[$key]["price_24_low"].") ";
		update_messages($message); // message in DB schreiben und ausgeben.
	} else {
		$message = "FEHLER: Keinen Preis für ".$__balanceBot_coins[$key]['name']."".$__balanceBot_basecurrency['name']." gefunden!";
		update_messages($message); // message in DB schreiben und ausgeben.
	}

	/* Hinzugefügt am 15.01.2019 - Beginn */
	if ($__balanceBot_coins[$key]["price_24_high"]-$__balanceBot_coins[$key]["price_24_low"] > $__balanceBot_coins[$key]["price"] * 0.5){
		// sollte die Spanne zwischen 24h-High und 24h-Low um $__maxPriceDrop % fallen,
		// gehen wir davon aus, dass der Kurs gerade mächtig in den Keller fällt.
		// wir wollen dann keine weitere Balancierung durchführen, geben einen Fehler aus und beenden den Bot.
		$message = "FEHLER: Spanne zwischen 24High und 24Low beträgt mehr als der halbe Kurswert bei ".$__balanceBot_coins[$key]['name']."".$__balanceBot_basecurrency['name']." - Breche das Rebalancing ab.";
		update_messages($message); // message in DB schreiben und ausgeben.
		
		// Abschnitt Index.php 9.1
		// (Platzhalter)
		
		exit;
	}
	/* Hinzugefügt am 15.01.2019 - Ende */

}


// Abschnitt index.php 10: 
// Offene Order von Binance holen
foreach ($__balanceBot_coins as $key => $array_coin){
	$array_binance_openOrders[$array_coin['name']] = $binance_api_handler->openOrders($array_coin['name'].$__balanceBot_basecurrency['name']);
}
/*
echo "<pre> So sehen die offenen Order aus:";
print_r($array_binance_openOrders);
echo "</pre>";
*/

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



// Abschnitt index.php 12: 
// nachdem wir alle offenen Order geladen haben können wir den Status für jeden Coin aktualisieren
update_status(); 

/*
echo "<pre> So sieht nun unser Array __balanceBot_basecurrency aus: <br>";
print_r($__balanceBot_basecurrency);
echo "</pre>";
echo "<pre> So sieht nun unser Array __balanceBot_coins aus: <br>";
print_r($__balanceBot_coins);
echo "</pre>";
*/

// Abschnitt index.php 12.2:
// hier durchlaufe ich die Coins nach der vorgenommenen Sortierung: Größte Abweichung zuerst
foreach ($array_coins_sorted_by_deviation as $key => $nothing){ // $nothing weil ich nur den $key brauche, nicht das Subarray.

	$already_order_deleted = 0; // Loeschmerker, falls ich für den Coin schon eine Order gelöscht habe
	
	// Abschnitt index.php 12.2.1.:
	// $__balanceBot_coins[$key]['name'] enthält den Namen des derzeit bearbeiteten Coins.
	//
	// $__balanceBot_coins[$key]['deviation_percentage'] enthält die prozentuale Abweichung von IST und SOLL-Wert des Coin-Anteils vom Coin-Gesamtwert
	// Gedanke 1: Wenn es KEINE Abweichung von IST und SOLL gibt, muss NICHT ausbalanciert werden, ansonsten wird ausbalanciert:
	// ... if ($__balanceBot_coins[$key]['deviation_percentage'] > 1) then SELL Coin
	// ... if ($__balanceBot_coins[$key]['deviation_percentage'] < 1) then BUY Coin
	// Neues Problem: Ein BUY ist unsinnig wenn meine Basiswährung ebenfalls unter dem SOLL-Wert liegt, dadurch vergrößere ich meine Abweichung bei der Basiswährung.
	//
	// $__balanceBot_basecurrency['deviation_percentage'] enthält die prozentuale Abweichung von IST und SOLL des Basiswährungs-Anteils vom Coin-Gesamtwert
	// Gedanke 2: Wenn die IST/SOLL-Abweichung des Coins GLEICH der IST/SOLL-Abweichung der Basiswährung ist, muss NICHT ausbalanciert werden, ansonsten wird ausbalanciert:
	// ... if ($__balanceBot_coins[$key]['deviation_percentage'] > $__balanceBot_basecurrency['deviation_percentage']) then SELL Coin
	// ... if ($__balanceBot_coins[$key]['deviation_percentage'] < $__balanceBot_basecurrency['deviation_percentage']) then BUY Coin
	// Neues Problem: Jede kleinste Abweichung von 0,01 USDT führt zu einer Order mit 10 USDT Mindestvolumen, 
	// die eine viel größere IST/SOLL-Abweichung zur Folge haben wird.
	//
	// $__balanceBot_coins[$key]['tolerance_factor'] enthält die tolerierte prozentuale Abweichung.
	// Gedanke 3: Wenn die Abweichung des Coins und die Abweichung der Basiswährung tolerierbar sind, wird NICHT ausbalanciert, ansonsten wird ausbalanciert:
	// ... if ($__balanceBot_coins[$key]['deviation_percentage']+$__balanceBot_coins[$key]['tolerance_factor']/100 > $__balanceBot_basecurrency['deviation_percentage']) then SELL Coin
	// ... if ($__balanceBot_coins[$key]['deviation_percentage']-$__balanceBot_coins[$key]['tolerance_factor']/100 < $__balanceBot_basecurrency['deviation_percentage']) then BUY Coin
	// Neues Problem: Die Toleranzschwelle liegt bei geringem Portfoliowert von 100 USDT recht hoch, fast schon zu hoch für vernünftiges Ausbalancieren
	//
	// Gedanke 4: mo' money helps a lot ...
	
	$message = "BALANCE SELL: ".$__balanceBot_coins[$key]['name']." ".round($__balanceBot_coins[$key]['deviation_percentage'],2)."-".($__balanceBot_coins[$key]['tolerance_factor']/100)."  = ".round($__balanceBot_coins[$key]['deviation_percentage']-$__balanceBot_coins[$key]['tolerance_factor']/100,2)." > ".round($__balanceBot_basecurrency['deviation_percentage'],2)." ";
	if ($__balanceBot_coins[$key]['deviation_percentage'] - $__balanceBot_coins[$key]['tolerance_factor']/100 <= $__balanceBot_basecurrency['deviation_percentage']){
		$message .= " => do nothing";
		update_messages($message); // Message in DB schreiben und ausgeben.
	} else { // ... die Abweichung des Coins ist > der Abweichung der Basiswährung

		$message .= " => SELL ... ";
		update_messages($message); // Message in DB schreiben und ausgeben.

		// Abschnitt index.php 12.2.1.1.:
		// Bevor wir eine neue SELL-Order anlegen, suchen wir eine vorhandene BUY-order und löschen die einfach mal aus dem Orderbuch raus.
		// Dabei ist uns jetzt erstmal egal wie weit weg die Order vom aktuellen Kurs ist oder was für ein Trade-Volumen die hat.
		foreach ($array_binance_openOrders[$__balanceBot_coins[$key]['name']] as $array_openOrder){
			if ($array_openOrder['side'] == "BUY" AND $already_order_deleted == 0){

				$message = " ... Order zum Löschen gefunden!  ";

				$result_binance_cancel = $binance_api_handler->cancel($__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name'], $array_openOrder["orderId"]);

				if ($result_binance_cancel['code']){
					$message .= "Binance-API-Call-ERROR ".$result_binance_cancel['code'].": ".$result_binance_cancel['msg']." ";
					update_messages($message); // Message in DB schreiben und ausgeben.
				} else {
					$message .= " ... gelöscht.";
					update_messages($message); // Message in DB schreiben und ausgeben.
					$already_order_deleted = 1; // Löschmerker auf 1 setzen damit ich nicht noch mehr Order weglösche

					// nachdem sich nun was an den offenen Order getan hat, aktualisieren wir unseren Status.
					// zuerst laden wir unsere neuen Accountinfo, dann alle offenen Order zu dem aktuell bearbeiteten Coin und dann aktualisieren wir den Status	
					update_accountinfo();			
					$array_binance_openOrders[$__balanceBot_coins[$key]['name']] = $binance_api_handler->openOrders($__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name']);
					update_status(); 

				}

			}
		}	

		
		// Abschnitt index.php 12.2.1.2.:
		// falls ich nix zum Löschen gefunden hab, muss ich eine neue Order anlegen
		if (!$already_order_deleted){


			// Der Preis kann auch aus dem Mittelwert von aktuellem Kurs und dem Min (buy) oder Max (sell) gebildet werden.
			// Das hat den Vorteil, dass Order, die nahe dem unteren Minimum sind nahe am aktuellen Kurs eingestellt werden und 
			// Order, die weit weg vom unteren Minimum mit entsprechend weit entferntem Kurs eingestellt werden, damit lässt sich der Ertrag
			// verbessern, denn das Ganze ist eh "langfristig".
			$price = round(($__balanceBot_coins[$key]['price']+$__balanceBot_coins[$key]['price_24_high'])/2,$__balanceBot_coins[$key]['tickSizePrecision']);
			
			// die Menge ergibt sich aus dem Gesamt-Portfoliowert und dem trading_factor
			$amount = round(($sum_coin_value + $__balanceBot_basecurrency['virtual_balance']) * $__trading_factor/100 / $price,$__balanceBot_coins[$key]['minQtyPrecision']);
			
			// Der Minimum-Betrag darf natürlich nicht unterschritten werden
			// das "+ $__balanceBot_coins[$key]['minQty']" ist dazu da um Abrundungen zu vermeiden
			$min_amount = round($__balanceBot_coins[$key]['minNotional'] / $price, $__balanceBot_coins[$key]['minQtyPrecision']) + $__balanceBot_coins[$key]['minQty'];
			if ($amount < $min_amount) $amount = $min_amount;			
			
			$symbol = $__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name'];
			$side = "SELL";
		
			// mit number_format stelle ich sicher, dass cih keine "2.4E-5" anstatt "0.00025" angezeigt bekomme
			$message = " ... Erstelle Order: $side ".number_format($amount, $__balanceBot_coins[$key]['minQtyPrecision'], '.', '')." @ $price $symbol (aktueller Kurs: ".$__balanceBot_coins[$key]['price'].") ";
			update_messages($message); // Message in DB schreiben und ausgeben.

			// Abschnitt index.php 12.2.1.2.1:
			// prüfen ob ich überhaupt genug Coins zur Verfügung habe
			if ($__balanceBot_coins[$key]['free'] > $amount){
				// Im oberen Abschnitt haben wir die Funktion "create_order()" deklariert.
				create_order($side, $symbol, $amount, $price);

				// nachdem sich nun was an den offenen Order getan hat, aktualisieren wir unseren Status.
				// zuerst laden wir unsere neuen Accountinfo, dann alle offenen Order zu dem aktuell bearbeiteten Coin und dann aktualisieren wir den Status	
				update_accountinfo();			
				$array_binance_openOrders[$__balanceBot_coins[$key]['name']] = $binance_api_handler->openOrders($__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name']);
				update_status(); 

			} else {
				$message = " ... FEHLER: Nicht genug frei verfügbare ".$__balanceBot_coins[$key]['name']." vorhanden";
				update_messages($message); // Message in DB schreiben und ausgeben.
			}

		}

			
	}
	
	
	// Abschnitt index.php 12.2.2.:
	// Beispielwert: BTC-Deviation 0,3 + (10/100) > 0,6 ist ok - ansonsten: Das führt zum BUY der BTC
	$message = "BALANCE BUY: ".$__balanceBot_coins[$key]['name']." ".round($__balanceBot_coins[$key]['deviation_percentage'],2)."+".($__balanceBot_coins[$key]['tolerance_factor']/100)." = ".round($__balanceBot_coins[$key]['deviation_percentage']+$__balanceBot_coins[$key]['tolerance_factor']/100,2)." < ".round($__balanceBot_basecurrency['deviation_percentage'],2)." ";
	if ($__balanceBot_coins[$key]['deviation_percentage'] + $__balanceBot_coins[$key]['tolerance_factor']/100 >= $__balanceBot_basecurrency['deviation_percentage']){
		$message .= " => do nothing";
		update_messages($message); // Message in DB schreiben und ausgeben.
	} else { // ... die Abweichung des Coins ist < der Abweichung der Basiswährung
		$message .= " => BUY ... ";
		update_messages($message); // Message in DB schreiben und ausgeben.

		// Abschnitt index.php 12.2.2.1.:
		// Bevor wir eine neue SELL-Order anlegen, suchen wir eine vorhandene BUY-order und löschen die einfach mal aus dem Orderbuch raus.
		// Dabei ist uns jetzt erstmal egal wie weit weg die Order vom aktuellen Kurs ist oder was für ein Trade-Volumen die hat.
		foreach ($array_binance_openOrders[$__balanceBot_coins[$key]['name']] as $array_openOrder){
			if ($array_openOrder['side'] == "SELL" AND $already_order_deleted == 0){

				$message = " Order zum Löschen gefunden!  ";

				$result_binance_cancel = $binance_api_handler->cancel($__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name'], $array_openOrder["orderId"]);

				if ($result_binance_cancel['code']){
					$message .= "Binance-API-Call-ERROR ".$result_binance_cancel['code'].": ".$result_binance_cancel['msg']." ";
					update_messages($message); // Message in DB schreiben und ausgeben.
				} else {
					$message .= " ... gelöscht.";
					update_messages($message); // Message in DB schreiben und ausgeben.
					$already_order_deleted = 1; // Löschmerker auf 1 setzen damit ich nicht noch mehr Order weglösche

					// nachdem sich nun was an den offenen Order getan hat, aktualisieren wir unseren Status.
					// zuerst laden wir unsere neuen Accountinfo, dann alle offenen Order zu dem aktuell bearbeiteten Coin und dann aktualisieren wir den Status	
					update_accountinfo();			
					$array_binance_openOrders[$__balanceBot_coins[$key]['name']] = $binance_api_handler->openOrders($__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name']);
					update_status(); 
				
				}

			}
		}	


		// Abschnitt index.php 12.2.2.2.:
		// falls ich nix zum Löschen gefunden hab, muss ich eine neue Order anlegen
		if (!$already_order_deleted){


			// Der Preis kann auch aus dem Mittelwert von aktuellem Kurs und dem Min (buy) oder Max (sell) gebildet werden.
			// Das hat den Vorteil, dass Order, die nahe dem unteren Minimum sind nahe am aktuellen Kurs eingestellt werden und 
			// Order, die weit weg vom unteren Minimum mit entsprechend weit entferntem Kurs eingestellt werden, damit lässt sich der Ertrag
			// verbessern, denn das Ganze ist eh "langfristig".
			$price = round(($__balanceBot_coins[$key]['price']+$__balanceBot_coins[$key]['price_24_low'])/2,$__balanceBot_coins[$key]['tickSizePrecision']);
			
			// die Menge ergibt sich aus dem Gesamt-Portfoliowert und dem trading_factor
			$amount = round(($sum_coin_value + $__balanceBot_basecurrency['virtual_balance']) * $__trading_factor/100 / $price,$__balanceBot_coins[$key]['minQtyPrecision']);
			
			// Der Minimum-Betrag darf natürlich nicht unterschritten werden
			// das "+ $__balanceBot_coins[$key]['minQty']" ist dazu da um Abrundungen zu vermeiden
			$min_amount = round($__balanceBot_coins[$key]['minNotional'] / $price, $__balanceBot_coins[$key]['minQtyPrecision']) + $__balanceBot_coins[$key]['minQty'];
			if ($amount < $min_amount) $amount = $min_amount;			

			$symbol = $__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name'];
			$side = "BUY";

			$message = " ... Erstelle Order: $side ".number_format($amount, $__balanceBot_coins[$key]['minQtyPrecision'], '.', '')." @ $price $market (aktueller Kurs: ".$__balanceBot_coins[$key]['price'].") ";
			update_messages($message); // Message in DB schreiben und ausgeben.

			// Abschnitt index.php 12.2.2.2.1.:
			// prüfen ob ich überhaupt genug Basiswährung zur Verfügung habe
			if ($__balanceBot_basecurrency['free'] > $price * $amount){
				// Im oberen Abschnitt haben wir die Funktion "create_order()" deklariert.
				create_order($side, $symbol, $amount, $price);


				// nachdem sich nun was an den offenen Order getan hat, aktualisieren wir unseren Status.
				// zuerst laden wir unsere neuen Accountinfo, dann alle offenen Order zu dem aktuell bearbeiteten Coin und dann aktualisieren wir den Status	
				update_accountinfo();			
				$array_binance_openOrders[$__balanceBot_coins[$key]['name']] = $binance_api_handler->openOrders($__balanceBot_coins[$key]['name'].$__balanceBot_basecurrency['name']);
				update_status(); 

			} else {
				$message = "FEHLER: Nicht genug ".$__balanceBot_basecurrency['name']." vorhanden (benötigt: ".($price * $amount).") ";
				update_messages($message); // Message in DB schreiben und ausgeben.
			}

		
		}			
	

			
	}
	
}


// Abschnitt index.php 13: 
// wieder arbeiten wir jeden zu verwaltenden Coin ab:
foreach ($__balanceBot_coins as $key => $array_coin){

	// und für jeden Coin arbeiten wir dessen offene Order ab
	foreach ($array_binance_openOrders[$array_coin['name']] as $array_openOrder){
			
		// Der Zeitstempel wann die Order das letzte Mal aktualisiert wurde (z.B. durch Teilverkauf) is in Millisekuden angegeben.
		// 1 Millisekunde = 1 Stunde / 1000 / 60 / 60
		// Das Alter berechne ich aus dem aktuellen Zeitstempel
		$array_openOrder['ageInHours'] = (Time() - $array_openOrder['updateTime'] / 1000) / 60 / 60;
			
		if ($array_openOrder['ageInHours'] > $__max_orderAge){

			$message = " Veraltete Order zum Löschen gefunden!  ";

			$result_binance_cancel = $binance_api_handler->cancel($array_coin['name'].$__balanceBot_basecurrency['name'], $array_openOrder["orderId"]);

			if ($result_binance_cancel['code']){
				$message .= "Binance-API-Call-ERROR ".$result_binance_cancel['code'].": ".$result_binance_cancel['msg']." ";
			} else {
				$message .= " ... gelöscht.";
			}
			update_messages($message); // Message in DB schreiben und ausgeben.

		}
	}		

}


// Abschnitt index.php 14: 
// Irgendwann ist die Datenbank voll, daher löschen wir Einträge, die älter als 7 Tage sind. (aus Teil 2)
$sql_query = "DELETE FROM `tblMessages` where `date_created`  < DATE_ADD(now(),INTERVAL -7 DAY)";

// Und auch obiges SQL-Statement an die Datenbank senden (aus Teil 2)
$mysqli->query($sql_query);



?>



