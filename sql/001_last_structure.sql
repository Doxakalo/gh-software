-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Úte 15. dub 2025, 12:12
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
-- Struktura tabulky `carts`
--

CREATE TABLE `carts` (
                         `id` int(11) NOT NULL,
                         `data` text NOT NULL,
                         `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `cookies_log`
--

CREATE TABLE `cookies_log` (
                               `id` int(11) NOT NULL,
                               `ip_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
                               `browser` text DEFAULT NULL,
                               `cookies_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `forms_log`
--

CREATE TABLE `forms_log` (
                             `id` int(10) UNSIGNED NOT NULL,
                             `type` varchar(255) NOT NULL,
                             `page` varchar(255) NOT NULL,
                             `form_created_time` datetime DEFAULT NULL,
                             `form_submit_time` datetime NOT NULL DEFAULT current_timestamp(),
                             `first_visit_cookie_time` datetime DEFAULT NULL,
                             `remote_ip` varchar(255) NOT NULL DEFAULT '',
                             `http_referer` varchar(255) NOT NULL DEFAULT '',
                             `http_header_json` text NOT NULL,
                             `val_json` text NOT NULL,
                             `keystroke_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
                             `marked_as_spam` int(11) NOT NULL DEFAULT 0,
                             `evaluated_as_spam` int(11) DEFAULT 0,
                             `form-id` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `lang`
--

CREATE TABLE `lang` (
                        `id` varchar(2) NOT NULL,
                        `shortcut` varchar(3) DEFAULT NULL,
                        `iso` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `log_error_request`
--

CREATE TABLE `log_error_request` (
                                     `id` int(11) NOT NULL,
                                     `page` text DEFAULT NULL,
                                     `referer` text DEFAULT NULL,
                                     `_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

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
                                        `relative_url` text NOT NULL,
                                        `absolute_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `search_terms_log`
--

CREATE TABLE `search_terms_log` (
                                    `id` int(11) NOT NULL,
                                    `term` text DEFAULT NULL,
                                    `type` varchar(100) DEFAULT NULL,
                                    `page` text DEFAULT NULL,
                                    `withdraw_stats_cookie` tinyint(1) NOT NULL,
                                    `remote_ip` varchar(255) NOT NULL,
                                    `_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `skills`
--

CREATE TABLE `skills` (
                          `id` int(11) NOT NULL,
                          `skill_order` int(11) DEFAULT NULL,
                          `preview_image` varchar(100) DEFAULT NULL,
                          `background_opacity` float DEFAULT 0.75,
                          `background_opacity_hover` float DEFAULT 0.9,
                          `preview_image_extra_styles` text DEFAULT NULL,
                          `preview_image_hover_extra_styles` text DEFAULT NULL,
                          `visible` int(11) NOT NULL DEFAULT 0,
                          `folder_image` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `skill_content`
--

CREATE TABLE `skill_content` (
                                 `id` int(11) NOT NULL,
                                 `url` varchar(100) NOT NULL,
                                 `title` varchar(200) DEFAULT NULL,
                                 `description` text DEFAULT NULL,
                                 `og_title` varchar(200) DEFAULT NULL,
                                 `og_description` text DEFAULT NULL,
                                 `preview_text` text NOT NULL,
                                 `html_content` text DEFAULT NULL,
                                 `id_lang` varchar(2) DEFAULT NULL,
                                 `id_skill` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `sys_news`
--

CREATE TABLE `sys_news` (
                            `id` int(11) NOT NULL,
                            `name` varchar(200) DEFAULT NULL,
                            `url` text DEFAULT NULL,
                            `preview_description` text DEFAULT NULL,
                            `preview_image_base64` longtext DEFAULT NULL,
                            `preview_image_ext` varchar(10) DEFAULT NULL,
                            `filter_over_preview_image` int(11) DEFAULT 0,
                            `content` text DEFAULT NULL,
                            `author` text NOT NULL,
                            `link_intern` text DEFAULT NULL,
                            `link_extern` text DEFAULT NULL,
                            `link_name` text NOT NULL,
                            `link_name_changed` int(255) NOT NULL DEFAULT 0,
                            `shortlink_facebook` text DEFAULT NULL,
                            `shortlink_twitter` text DEFAULT NULL,
                            `shortlink_linkedin` text DEFAULT NULL,
                            `shortlink_direct` text DEFAULT NULL,
                            `publish` int(11) DEFAULT NULL,
                            `published` datetime DEFAULT NULL,
                            `title` varchar(200) DEFAULT NULL,
                            `meta_description` text DEFAULT NULL,
                            `og_title` varchar(200) DEFAULT NULL,
                            `og_description` text DEFAULT NULL,
                            `og_video_yt_id` text DEFAULT NULL,
                            `mailchimp_tag` varchar(255) NOT NULL,
                            `pair_id` int(11) DEFAULT NULL,
                            `lang` varchar(5) NOT NULL DEFAULT '',
                            `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `sys_news_og_media`
--

CREATE TABLE `sys_news_og_media` (
                                     `id` int(11) NOT NULL,
                                     `type` varchar(100) NOT NULL,
                                     `name` text DEFAULT NULL,
                                     `filename` text NOT NULL,
                                     `id_news` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `sys_news_tags`
--

CREATE TABLE `sys_news_tags` (
                                 `id` int(11) NOT NULL,
                                 `id_news` int(11) NOT NULL,
                                 `id_tags` int(11) NOT NULL,
                                 `tag_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `sys_tags`
--

CREATE TABLE `sys_tags` (
                            `id` int(11) NOT NULL,
                            `name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `sys_users`
--

CREATE TABLE `sys_users` (
                             `id` int(11) NOT NULL,
                             `name` varchar(40) DEFAULT NULL,
                             `surname` varchar(40) DEFAULT NULL,
                             `username` varchar(40) NOT NULL,
                             `company_name` varchar(50) DEFAULT NULL,
                             `email` varchar(60) DEFAULT NULL,
                             `password` varchar(150) DEFAULT NULL,
                             `type` set('administrator','developer','user','') NOT NULL DEFAULT 'user',
                             `note` text DEFAULT NULL,
                             `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `carts`
--
ALTER TABLE `carts`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `cookies_log`
--
ALTER TABLE `cookies_log`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `forms_log`
--
ALTER TABLE `forms_log`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `lang`
--
ALTER TABLE `lang`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `log_error_request`
--
ALTER TABLE `log_error_request`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `search_pages_content`
--
ALTER TABLE `search_pages_content`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `search_terms_log`
--
ALTER TABLE `search_terms_log`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `skills`
--
ALTER TABLE `skills`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `skill_content`
--
ALTER TABLE `skill_content`
    ADD PRIMARY KEY (`id`),
  ADD KEY `id_lang` (`id_lang`),
  ADD KEY `id_skill` (`id_skill`);

--
-- Indexy pro tabulku `sys_news`
--
ALTER TABLE `sys_news`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `sys_news_og_media`
--
ALTER TABLE `sys_news_og_media`
    ADD PRIMARY KEY (`id`),
  ADD KEY `id_news` (`id_news`);

--
-- Indexy pro tabulku `sys_news_tags`
--
ALTER TABLE `sys_news_tags`
    ADD PRIMARY KEY (`id`),
  ADD KEY `id_news` (`id_news`),
  ADD KEY `id_tags` (`id_tags`);

--
-- Indexy pro tabulku `sys_tags`
--
ALTER TABLE `sys_tags`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `sys_users`
--
ALTER TABLE `sys_users`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `carts`
--
ALTER TABLE `carts`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `cookies_log`
--
ALTER TABLE `cookies_log`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `forms_log`
--
ALTER TABLE `forms_log`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `log_error_request`
--
ALTER TABLE `log_error_request`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `search_pages_content`
--
ALTER TABLE `search_pages_content`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- AUTO_INCREMENT pro tabulku `search_terms_log`
--
ALTER TABLE `search_terms_log`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `skills`
--
ALTER TABLE `skills`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `skill_content`
--
ALTER TABLE `skill_content`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `sys_news`
--
ALTER TABLE `sys_news`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `sys_news_og_media`
--
ALTER TABLE `sys_news_og_media`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `sys_news_tags`
--
ALTER TABLE `sys_news_tags`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `sys_tags`
--
ALTER TABLE `sys_tags`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `sys_users`
--
ALTER TABLE `sys_users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `skill_content`
--
ALTER TABLE `skill_content`
    ADD CONSTRAINT `skill_content_ibfk_1` FOREIGN KEY (`id_lang`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `skill_content_ibfk_2` FOREIGN KEY (`id_skill`) REFERENCES `skills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `sys_news_tags`
--
ALTER TABLE `sys_news_tags`
    ADD CONSTRAINT `sys_news_tags_ibfk_1` FOREIGN KEY (`id_news`) REFERENCES `sys_news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sys_news_tags_ibfk_2` FOREIGN KEY (`id_tags`) REFERENCES `sys_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
