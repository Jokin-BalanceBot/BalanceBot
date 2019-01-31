Einleitung

Dieser Balancebot ist kostenlos, dennoch bitte ich Dich vor der Verwendung dies zu tun:
1. neuen eMail-Account anlegen
2. über meinen Ref-Link https://www.binance.com/?ref=28506673 neuen Binanceaccount 
   anzulegen. Mit diesem Account trennst Du das vom BalanceBot verwaltete 
   Vermögen von Deinem anderen Vermögen und stellst sicher, dass nur das
   gehandelt wird, was Du ihm zur Verfügung stellst.

Weiterhin:
3. Lade eine ausreichende Menge an Coins auf den Account. 
   Weniger als 100 USDT sind nicht sinnvoll, selbst mit 100 USDT wird der 
   Bot nur selten traden, da mit min. 10 USDT getradet werden muss (Trading-Rules 
   von Binance).
   Ich empfehle mit 10 ETH im ETH-Markt zu starten, die Trading-Rules erlauben
   minimale Order mt 0,01 ETH.


Setup-Anleitung

Der BalanceBot ist ein PHP-Projekt. Du solltest also Folgendes zur Verfügung haben:
1. Webspace mit PHP (getestet mit Version 7, Version 5 kann klappen)
2. MySQL-Datenbank mt phpmyadmin
3. Cronjob (oder Gratis-Cron-Dienst via Google, z.B. cron-jobs.org)
   Cronjob kann durchaus mal 30 Sekunden und länger laufen, nicht jeder 
   Cron-Dienstleister bietet Jobs an, die lang genug auf den Service warten.

Der BalanceBot eignet sich bestens für einen eigenen RaspberryPi im eigenen Heimnetz.

Schritt 1:
API-Keys bei Binance erzeugen und ein paar BNB kaufen um die günstigeren Gebühren
bei Binance zu haben. Dazu die Einstellung vornehmen, dass Gebühren möglichst 
in BNB bezahlt werden.
Zudem benötigt der Bot mindestens einen Coin mit Bestand um überhaupt starten zu können.

Schritt 2:
Datenbank einrichten.
Auf einem RaspberryPi oder einem VPS muss man erst einen DB-User und die 
User-Datenbank einrichten.
Im Terminal "mysql" (oder sudo mysql) eingeben und diese SQL-Befehle ausführen.
Gern das Passwort "BalanceBotPasswort" hier ändern, auch gern den 
User "balancebot" durch einen eigenen Namen ersetzen.

CREATE USER 'balancebot'@'localhost' IDENTIFIED BY 'BalanceBotPasswort';
GRANT USAGE ON *.* TO 'balancebot'@'localhost' REQUIRE NONE WITH 
MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 
MAX_USER_CONNECTIONS 0;CREATE DATABASE IF NOT EXISTS `balancebot`;
GRANT ALL PRIVILEGES ON `balancebot`.* TO 'balancebot'@'localhost';
FLUSH PRIVILEGES; EXIT;

Danach kann man sich über phpmyadmin als dieser neue User an der Datenbank anmelden.
Die User-Datenbank auswählen und über den Import die Datei "balancebot.sql" importieren.

Schritt 3:
config.php vorbereiten
Falls in Schritt 2 User und Passwort angepasst wurden, muss dies auch in der 
config.php erfolgen. Ebenfalls müssen API-Key und API-Secret aus Schritt 1 
in der config.php eingetragen werden.
Als nächstes muss die Basiswährung festgelegt werden - ich empfehle zu Beginn mit 
der Basiswährung "ETH" zu starten, da hier das geringste Ordervolumen möglich ist.
Nun werden die zu balancierenden Coins definiert. Hier dürfen nur die Coins benutzt 
werden, die bei Binance im Markt "ETH" handelbar sind. BTC und USDT fallen raus, da
deren Symbol "ETHBTC", bzw. "ETHUSDT" lauten und ETH im Markt "BTC" handelbar ist, 
nicht umgekehrt.
Unbedingt sollte BNB darin enthalten sein, denn hier werden die Gebühren mit bezahlt,
daher sollte stets BNB-Guthaben vorhanden sein.
$__trading_factor und $__max_orderAge sollten vorerst so belassen werden.

Schritt 4:
PHP-Dateien auf den Webspace laden.

Schritt 5:
Cronjob einrichten.
Es genügt wenn der Job einmal ja Stunde ausgeführt wird. Damit ist der Abstand in
dem die Order erstellt werden groß genug um ausreichend große Abstände zu erhalten.

0 * * * * root wget -O /dev/null -o /dev/null http://localhost/BalanceBot/index.php


Änderungshistorie ggü. Lektion 7 des Workshops

show_csv.php
- Sortierung in show_csv.php geändert (nun neueste Einträge oben)
- 2 weitere Spalten in der CSV-Datei (virtual+current Gesamtportfoliowert)

index.php
- "Change 25.01.2019" definiert "$create_order" um nur noch eine Aktion je Coin und je Durchlauf durchzuführen. Entweder SELL oder BUY, aber nicht mehr beides zeitgleich.
- "Update 25.01.2019" im Abschnitt 6.5: $array_coins_sorted_by_deviation[$key] wird nun korrekt ermittelt.
- Bugfix, Variable "$market" durch "$symbol" ersetzt damit die Ausgabe auch das Richtige ausgibt.
- "Update 25.01.2019" im Abschnitt 12.2.1.2.: falls mehr Ordervolumen gewünscht ist als vorhanden ist, soll genomen werden was da ist (jedoch auskommentiert)
- "Update 25.01.2019" im Abschnitt 12.2.2.2.: falls mehr Ordervolumen gewünscht ist als vorhanden ist, soll genomen werden was da ist (jedoch auskommentiert)


show_messages.php
- Neuer Filter nach "Balance" um alle Aktionen schneller überblicken zu können.


