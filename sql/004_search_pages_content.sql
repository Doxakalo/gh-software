-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Stř 23. dub 2025, 21:56
-- Verze serveru: 10.11.11-MariaDB-0+deb12u1
-- Verze PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `filip_www.24usoftware.com`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `search_pages_content`
--

CREATE TABLE `search_pages_content` (
                                        `id` int(11) NOT NULL COMMENT 'ID',
                                        `title` text NOT NULL COMMENT 'Page Title',
                                        `type` text DEFAULT NULL,
                                        `language` varchar(10) DEFAULT NULL,
                                        `language_content` varchar(10) DEFAULT NULL,
                                        `headline` text DEFAULT NULL COMMENT 'Page H1',
                                        `content` longtext NOT NULL COMMENT 'Page Content',
                                        `url` text NOT NULL,
                                        `score_multiplier` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `search_pages_content`
--
ALTER TABLE `search_pages_content`
    ADD PRIMARY KEY (`id`);
ALTER TABLE `search_pages_content` ADD FULLTEXT KEY `ft_title` (`title`);
ALTER TABLE `search_pages_content` ADD FULLTEXT KEY `ft_headline` (`headline`);
ALTER TABLE `search_pages_content` ADD FULLTEXT KEY `ft_content` (`content`);
ALTER TABLE `search_pages_content` ADD FULLTEXT KEY `ft_all` (`title`,`headline`,`content`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `search_pages_content`
--
ALTER TABLE `search_pages_content`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
