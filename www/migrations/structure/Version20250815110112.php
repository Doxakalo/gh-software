<?php

declare(strict_types=1);

namespace Migrations\Sample;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250815110112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE `carts` (
            `id` int NOT NULL,
            `data` text NOT NULL,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `forms_log` (
            `id` int UNSIGNED NOT NULL,
            `type` varchar(255) NOT NULL,
            `page` varchar(255) NOT NULL,
            `form_created_time` datetime DEFAULT NULL,
            `form_submit_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `first_visit_cookie_time` datetime DEFAULT NULL,
            `remote_ip` varchar(255) NOT NULL DEFAULT '',
            `http_referer` varchar(255) NOT NULL DEFAULT '',
            `http_header_json` text NOT NULL,
            `val_json` text NOT NULL,
            `keystroke_count` int UNSIGNED NOT NULL DEFAULT '0',
            `marked_as_spam` int NOT NULL DEFAULT '0',
            `evaluated_as_spam` int DEFAULT '0',
            `form-id` text
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        $this->addSql("CREATE TABLE `lang` (
            `id` varchar(2) NOT NULL,
            `shortcut` varchar(3) DEFAULT NULL,
            `iso` varchar(3) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `log_error_request` (
            `id` int NOT NULL,
            `page` text,
            `referer` text,
            `_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `search_pages_content` (
            `id` int NOT NULL COMMENT 'ID',
            `title` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Page Title',
            `type` text COLLATE utf8mb4_unicode_ci,
            `language` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `language_content` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `headline` text COLLATE utf8mb4_unicode_ci COMMENT 'Page H1',
            `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Page Content',
            `url` text COLLATE utf8mb4_unicode_ci NOT NULL,
            `score_multiplier` int NOT NULL DEFAULT '1'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->addSql("CREATE TABLE `search_terms_log` (
            `id` int NOT NULL,
            `term` text,
            `type` varchar(100) DEFAULT NULL,
            `page` text,
            `withdraw_stats_cookie` tinyint(1) NOT NULL,
            `remote_ip` varchar(255) NOT NULL,
            `_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `skills` (
            `id` int NOT NULL,
            `skill_order` int DEFAULT NULL,
            `preview_image` varchar(100) DEFAULT NULL,
            `background_opacity` float DEFAULT '0.75',
            `background_opacity_hover` float DEFAULT '0.9',
            `preview_image_extra_styles` text,
            `preview_image_hover_extra_styles` text,
            `visible` int NOT NULL DEFAULT '0',
            `folder_image` varchar(100) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `skill_content` (
            `id` int NOT NULL,
            `url` varchar(100) NOT NULL,
            `title` varchar(200) DEFAULT NULL,
            `description` text,
            `og_title` varchar(200) DEFAULT NULL,
            `og_description` text,
            `preview_text` text NOT NULL,
            `html_content` text,
            `id_lang` varchar(2) DEFAULT NULL,
            `id_skill` int DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `sys_news` (
            `id` int NOT NULL,
            `name` varchar(200) DEFAULT NULL,
            `url` text,
            `preview_description` text,
            `preview_image_base64` longtext,
            `preview_image_ext` varchar(10) DEFAULT NULL,
            `filter_over_preview_image` int NOT NULL DEFAULT '0',
            `content` text,
            `author` text NOT NULL,
            `link_intern` text,
            `link_extern` text,
            `link_name` text NOT NULL,
            `link_name_changed` int NOT NULL DEFAULT '0',
            `shortlink_facebook` text,
            `shortlink_twitter` text,
            `shortlink_linkedin` text,
            `shortlink_direct` text,
            `publish` int DEFAULT NULL,
            `published` datetime DEFAULT NULL,
            `title` varchar(200) DEFAULT NULL,
            `meta_description` text,
            `og_title` varchar(200) DEFAULT NULL,
            `og_description` text,
            `og_video_yt_id` text,
            `mailchimp_tag` varchar(255) NOT NULL,
            `pair_id` int DEFAULT NULL,
            `lang` varchar(5) NOT NULL DEFAULT '',
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `sys_news_og_media` (
            `id` int NOT NULL,
            `type` varchar(100) NOT NULL,
            `name` text,
            `filename` text NOT NULL,
            `id_news` int NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `sys_news_tags` (
            `id` int NOT NULL,
            `id_news` int NOT NULL,
            `id_tags` int NOT NULL,
            `tag_order` int NOT NULL DEFAULT '0'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `sys_tags` (
            `id` int NOT NULL,
            `name` varchar(200) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("CREATE TABLE `sys_users` (
            `id` int NOT NULL,
            `name` varchar(40) DEFAULT NULL,
            `surname` varchar(40) DEFAULT NULL,
            `username` varchar(40) NOT NULL,
            `company_name` varchar(50) DEFAULT NULL,
            `email` varchar(60) DEFAULT NULL,
            `password` varchar(150) DEFAULT NULL,
            `type` set('administrator','developer','user','') NOT NULL DEFAULT 'user',
            `note` text,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
        ");

        $this->addSql("ALTER TABLE `carts`
            ADD PRIMARY KEY (`id`);
        ");

        $this->addSql("ALTER TABLE `forms_log`
            ADD PRIMARY KEY (`id`); 
        ");

        $this->addSql("ALTER TABLE `lang`
            ADD PRIMARY KEY (`id`);
        ");

        $this->addSql("ALTER TABLE `log_error_request`
            ADD PRIMARY KEY (`id`);
        ");

        $this->addSql("
            ALTER TABLE `search_pages_content`
            ADD PRIMARY KEY (`id`);
            ALTER TABLE `search_pages_content` ADD FULLTEXT KEY `ft_title` (`title`);
            ALTER TABLE `search_pages_content` ADD FULLTEXT KEY `ft_headline` (`headline`);
            ALTER TABLE `search_pages_content` ADD FULLTEXT KEY `ft_content` (`content`);
            ALTER TABLE `search_pages_content` ADD FULLTEXT KEY `ft_all` (`title`,`headline`,`content`);
        ");

        $this->addSql("ALTER TABLE `search_terms_log`
            ADD PRIMARY KEY (`id`);
        ");

        $this->addSql("ALTER TABLE `skills`
            ADD PRIMARY KEY (`id`);
        ");

        $this->addSql("ALTER TABLE `skill_content`
            ADD PRIMARY KEY (`id`),
            ADD KEY `id_lang` (`id_lang`),
            ADD KEY `id_skill` (`id_skill`);
        ");

        $this->addSql("ALTER TABLE `sys_news`
            ADD PRIMARY KEY (`id`);
        ");

        $this->addSql("ALTER TABLE `sys_news_og_media`
            ADD PRIMARY KEY (`id`),
            ADD KEY `id_news` (`id_news`);
        ");

        $this->addSql("ALTER TABLE `sys_news_tags`
            ADD PRIMARY KEY (`id`),
            ADD KEY `id_news` (`id_news`),
            ADD KEY `id_tags` (`id_tags`);
        ");

        $this->addSql("ALTER TABLE `sys_tags`
            ADD PRIMARY KEY (`id`);
        ");

        $this->addSql("ALTER TABLE `sys_users`
            ADD PRIMARY KEY (`id`);
        ");

        $this->addSql("ALTER TABLE `carts`
            MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `forms_log`
            MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `log_error_request`
            MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `search_pages_content`
            MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID';
        ");

        $this->addSql("ALTER TABLE `search_terms_log`
            MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `skills`
             MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `skill_content`
            MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");
        $this->addSql("ALTER TABLE `sys_news`
            MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `sys_news_og_media`
             MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `sys_news_tags`
            MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `sys_tags`
            MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `sys_users`
            MODIFY `id` int NOT NULL AUTO_INCREMENT;
        ");

        $this->addSql("ALTER TABLE `skill_content`
            ADD CONSTRAINT `skill_content_ibfk_1` FOREIGN KEY (`id_lang`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            ADD CONSTRAINT `skill_content_ibfk_2` FOREIGN KEY (`id_skill`) REFERENCES `skills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
        ");

        $this->addSql("ALTER TABLE `sys_news_tags`
            ADD CONSTRAINT `sys_news_tags_ibfk_1` FOREIGN KEY (`id_news`) REFERENCES `sys_news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `sys_news_tags_ibfk_2` FOREIGN KEY (`id_tags`) REFERENCES `sys_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
