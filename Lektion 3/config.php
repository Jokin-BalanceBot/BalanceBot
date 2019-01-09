<?php

// Abschnitt config.php 1: Zugangsdaten für Datenbankverbindung
$__db="balancebot";
$__db_server="localhost";
$__db_user="balancebot";
$__db_passwort="BalanceBotPasswort";

// Abschnitt config.php 2: Binance-API
$__binance_APIkey = "he5JIjiJL4QVxxxxxxxxxxxxxxxxxxxxxxxxxxxxxwzhuguNFIJXIlAoNc";
$__binance_APIsecret = "QrrtlQiErtxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxoCIfkiuZUb3128L";

// Abschnitt config.php 3: Nun erstellen wir ein Array welches später alle Informationen zu den Coins enthält, die wir brauchen.
$__balanceBot_basecurrency = array (
					"name" => "USDT",
					// "40" bedeutet, dass 40% des Coin-Gesamtwertes auch als Basis-Währung an der Seitenlinie liegen soll
					// Der "Coin-Gesamtwert" ist die Summe der USDT-Werte der Coins (BTC, ETH und ADA in unserem Beispiel)
					"target_percentage" => "40"	// Achtung, das letzte Element ohne Komma!
				);

$__balanceBot_coins = array(				
					array(
						"name" => "BTC",
						// "40" meint, dass 40% des Coin-Gesamtwertes als BTC vorhanden sein sollen
						// Summe aller Prozentwerte muss 100 ergeben, ansonsten wird es später zur Fehlermeldung kommen
						// Weiterhin sollte die Vorgabe nicht dazu führen, dass Coins einen Gegenwert von unter 20 USDT haben.
						// Berechnungsbeispiel: Wenn 30% eines Coins min. 20 USDT sein sollen, dann wird der 100%-Coin-Gesamtwert
						//                      mindestens 66,67 USDT betragen. Wenn zudem 40% dieses Wertes an der Seitenlinie liegen sollen,
						//						dann würden mindestens 26,67 USDT an der Seitenlinie liegen.
						//						... somit müssen ca. 100 USDT an Portfolio-Gesamtwert vorhanden sein.
						// Portfolio-Gesamtwert = Seitenlinie + Coin-Gesamtwert 
						// ... wird minimal "20" gewählt, also 20% eines Coinwertes müssen 20 USDT entsprechen, dann wird der 
						// Coin-Gesamtwert mindestens 100 USDT betragen. Zusätzlich 40% an der Seitenlinie zwingen zu einem
						// Gesamt-Portfoliowert von mindestens 140 USDT.
						"target_percentage" => "40"	// Achtung, das letzte Element ohne Komma!
						),
					array(
						"name" => "ETH",
						"target_percentage" => "30"	// Achtung, das letzte Element ohne Komma!
						),
					array(
						"name" => "ADA",
						"target_percentage" => "30"	// Achtung, das letzte Element ohne Komma!
						) // Achtung, das letzte Element ohne Komma!
					);


// Abschnitt config.php 4: 
// (Platzhalter)

?>
