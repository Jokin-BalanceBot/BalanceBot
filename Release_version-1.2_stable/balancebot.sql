-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 29. Jan 2019 um 19:20
-- Server-Version: 10.1.37-MariaDB-0+deb9u1
-- PHP-Version: 7.0.33-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `balancebot`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblBalances`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Tabellenstruktur für Tabelle `tblMessages`
--

CREATE TABLE `tblMessages` (
  `id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `tblBalances`
--
ALTER TABLE `tblBalances`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `tblMessages`
--
ALTER TABLE `tblMessages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `tblBalances`
--
ALTER TABLE `tblBalances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `tblMessages`
--
ALTER TABLE `tblMessages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
