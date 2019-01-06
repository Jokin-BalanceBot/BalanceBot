# BalanceBot
Coinforum Workshop Binance-BalanceBot

Siehe hier für weitere Informationen:
https://coinforum.de/topic/15985-workshop-wir-basteln-uns-einen-tradingbot-lektion-1

Wer den BalanceBot nutzt möge bitte so fair sein und sich dafür einen Binance-Account über meinen Ref-Link https://www.binance.com/?ref=28506673  anlegen, damit seine BalanceBot-Assets von allem anderen Vermögen getrennt ist und ich eine kleine Aufmerksamkeit für meinen Aufwand erhalte :-)

Das Tool ist ansonsten absolut gratis und darf von jedem zu nicht-kommerziellen Zwecken verwendet werden.

Die hochgeladenen Dateien folgen den einzelnen Lektionen:

Lektion 1: Hallo Welt
- Lektion 1/index.php

https://coinforum.de/topic/15985-workshop-wir-basteln-uns-einen-tradingbot-lektion-1

Lektion 2: Hallo Datenbank
- Lektion 2/config.php
- Lektion 2/index.php
- Lektion 2/show_messages.php

https://coinforum.de/topic/15993-workshop-wir-basteln-uns-einen-tradingbot-lektion-2

Lektion 3. Hallo Binance-API
- Lektion 3/config.php
- Lektion 3/index.php
- Lektion 3/show_messages.php
(Link zum Coinforum mit Anleitung folgt)

Lektion 4. Hallo Cryptobestand
- Lektion 4/config.php
- Lektion 4/index.php
- Lektion 4/show_messages.php
(Link zum Coinforum mit Anleitung folgt)

Lektion 5. Hallo Strategie
- Lektion 5/config.php
- Lektion 5/index.php
- Lektion 5/show_messages.php
(Link zum Coinforum mit Anleitung folgt)

Lektion 6. Hallo Orderbook
- Lektion 6/config.php
- Lektion 6/index.php
- Lektion 6/show_messages.php
(Link zum Coinforum mit Anleitung folgt)

Lektion 7. Hallo Cronjob
- Lektion 7/config.php
- Lektion 7/index.php
- Lektion 7/show_messages.php
(Link zum Coinforum mit Anleitung folgt)

Fertiges Programm "BalanceBot" (wird voraussichtlich ab Ende Januar 2019 verfügbar sein):
- index.php
- config.php
- show_messages.php (<- hier wird die show_messages.php erweitert)
(Link zum Coinforum mit Anleitung folgt)

Noch ein paar Worte zur Trading-Strategie (siehe auch den Coinforum-Link zur Lektion 1):
Der Bot bekommt als Parameter wie groß der Anteil am gesamten Coinwert sein soll, den er als Basiswährung (USDT) an der Seitenlinie liegen lassen soll.
Bei einem USDT-Wert der Coins BTC, ETH und ADA von 500 Euro und der Vergabe "100%" an der Seitenlinie zu parken, wird der Bot versuchen 500 USDT an der Seitenlinie zu parken. Ist das Gesamtportfolio jedoch lediglich 800 USDT wert, wird der Bot Coins verkaufen um eine Seitenlinie von 400 USDT aufzubauen bei einem Gesamtwert aller Coins von 400 USDT:
Steigt jedoch der Kurs und das Portfolio ist 1.000 Euro wert, dann wird der Bot Coins im Wert von 50 USDT verkaufen und Gewinne mitnehmen.
Bei sinkenden Kursen wird der Bot Coins nachkaufen indem er Limit-Order so anlegt, dass er möglichst im Dip kauft.

Ich empfehle den Bot nicht einfach nur zu laden und zu nutzen sondern sich durch die Lektionen zu arbeiten um nachzuvollziehen was der Bot tut und wie er funktioniert.

Fragen stellt Ihr bitte direkt im Coinforum in den einzelnen Lektionen.

Freundliche Grüße, Johann
