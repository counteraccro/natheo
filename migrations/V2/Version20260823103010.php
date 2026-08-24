<?php

declare(strict_types=1);

namespace DoctrineMigrations\V2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale de Natheo CMS
 */
final class Version20260823103010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration initiale - NatheoCMS v2.0';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, roles JSON NOT NULL, comment LONGTEXT DEFAULT NULL, disabled TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, comment LONGTEXT NOT NULL, status INT NOT NULL, disabled TINYINT NOT NULL, moderation_comment LONGTEXT DEFAULT NULL, ip VARCHAR(255) NOT NULL, user_agent LONGTEXT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, page_id INT NOT NULL, user_moderation_id INT DEFAULT NULL, INDEX IDX_9474526CC4663E4 (page_id), INDEX IDX_9474526C89731557 (user_moderation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE faq (id INT AUTO_INCREMENT NOT NULL, disabled TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_E8FF75CCA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE faq_category (id INT AUTO_INCREMENT NOT NULL, disabled TINYINT NOT NULL, render_order INT NOT NULL, faq_id INT NOT NULL, INDEX IDX_FAEEE0D681BEC8C2 (faq_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE faq_category_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, title LONGTEXT NOT NULL, faq_category_id INT NOT NULL, INDEX IDX_5493B0FCF689B0DB (faq_category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE faq_question (id INT AUTO_INCREMENT NOT NULL, disabled TINYINT NOT NULL, render_order INT NOT NULL, faq_category_id INT NOT NULL, INDEX IDX_4A55B059F689B0DB (faq_category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE faq_question_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, title LONGTEXT NOT NULL, answer LONGTEXT NOT NULL, faq_question_id INT NOT NULL, INDEX IDX_C2D1AFAE8DB86 (faq_question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE faq_statistique (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, faq_id INT NOT NULL, INDEX IDX_976F159481BEC8C2 (faq_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE faq_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, title LONGTEXT NOT NULL, faq_id INT NOT NULL, INDEX IDX_50A6685681BEC8C2 (faq_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE mail (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, key_words LONGTEXT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE mail_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, title LONGTEXT NOT NULL, content LONGTEXT NOT NULL, mail_id INT NOT NULL, INDEX IDX_B638B2C8776F01 (mail_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(50) NOT NULL, extension VARCHAR(50) NOT NULL, size INT NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, path LONGTEXT NOT NULL, web_path LONGTEXT NOT NULL, disabled TINYINT NOT NULL, trash TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, media_folder_id INT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_6A2CA10C334C0ACC (media_folder_id), INDEX IDX_6A2CA10CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE media_folder (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, path LONGTEXT NOT NULL, disabled TINYINT NOT NULL, trash TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, parent_id INT DEFAULT NULL, INDEX IDX_50DB9313727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type INT NOT NULL, position INT NOT NULL, render_order INT NOT NULL, default_menu TINYINT NOT NULL, disabled TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_7D053A93A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE menu_element (id INT AUTO_INCREMENT NOT NULL, column_position INT NOT NULL, row_position INT NOT NULL, link_target VARCHAR(100) NOT NULL, disabled TINYINT NOT NULL, menu_id INT NOT NULL, parent_id INT DEFAULT NULL, page_id INT DEFAULT NULL, INDEX IDX_C99B4387CCD7E912 (menu_id), INDEX IDX_C99B4387727ACA70 (parent_id), INDEX IDX_C99B4387C4663E4 (page_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE menu_element_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, text_link VARCHAR(255) NOT NULL, external_link LONGTEXT DEFAULT NULL, menu_element_id INT NOT NULL, INDEX IDX_9C0EC81D3EB29EF6 (menu_element_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, `level` INT NOT NULL, `read` TINYINT NOT NULL, created_at DATETIME NOT NULL, parameters LONGTEXT DEFAULT NULL, category VARCHAR(100) NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE option_system (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, value LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, update_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE option_user (id INT AUTO_INCREMENT NOT NULL, `key` LONGTEXT NOT NULL, value LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_659D7CAAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, render INT NOT NULL, status INT NOT NULL, disabled TINYINT NOT NULL, category INT NOT NULL, landing_page TINYINT NOT NULL, is_open_comment TINYINT NOT NULL, nb_comment INT NOT NULL, rule_comment INT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, header_img LONGTEXT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_140AB620A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page_tag (page_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_CF36BF12C4663E4 (page_id), INDEX IDX_CF36BF12BAD26311 (tag_id), PRIMARY KEY (page_id, tag_id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page_menu (page_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_FAC12649C4663E4 (page_id), INDEX IDX_FAC12649CCD7E912 (menu_id), PRIMARY KEY (page_id, menu_id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page_content (id INT AUTO_INCREMENT NOT NULL, render_block INT NOT NULL, render_order INT NOT NULL, type INT NOT NULL, type_id INT DEFAULT NULL, page_id INT NOT NULL, INDEX IDX_4A5DB3CC4663E4 (page_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page_content_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, text LONGTEXT NOT NULL, page_content_id INT NOT NULL, INDEX IDX_C51473728F409273 (page_content_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page_meta (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, page_id INT NOT NULL, INDEX IDX_503608EFC4663E4 (page_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page_meta_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, value LONGTEXT NOT NULL, page_meta_id INT NOT NULL, INDEX IDX_7DA93944C39C4D6C (page_meta_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page_statistique (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, page_id INT NOT NULL, INDEX IDX_641C66DFC4663E4 (page_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE page_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, titre VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, page_id INT NOT NULL, INDEX IDX_A3D51B1DC4663E4 (page_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE sidebar_element (id INT AUTO_INCREMENT NOT NULL, icon LONGTEXT NOT NULL, label VARCHAR(255) NOT NULL, route VARCHAR(255) NOT NULL, disabled TINYINT NOT NULL, role VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, `lock` TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, parent_id INT DEFAULT NULL, INDEX IDX_2420A342727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE sql_manager (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, query LONGTEXT NOT NULL, disabled TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_44D502A5A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, color VARCHAR(7) NOT NULL, disabled TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE tag_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, label VARCHAR(255) NOT NULL, tag_id INT NOT NULL, INDEX IDX_A8A03F8FBAD26311 (tag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, login VARCHAR(100) DEFAULT NULL, firstname VARCHAR(100) DEFAULT NULL, lastname VARCHAR(100) DEFAULT NULL, disabled TINYINT NOT NULL, anonymous TINYINT NOT NULL, founder TINYINT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE user_data (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, created_at DATETIME NOT NULL, update_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_D772BFAAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4',
        );
        $this->addSql(
            'ALTER TABLE comment ADD CONSTRAINT FK_9474526CC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)',
        );
        $this->addSql(
            'ALTER TABLE comment ADD CONSTRAINT FK_9474526C89731557 FOREIGN KEY (user_moderation_id) REFERENCES user (id)',
        );
        $this->addSql('ALTER TABLE faq ADD CONSTRAINT FK_E8FF75CCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql(
            'ALTER TABLE faq_category ADD CONSTRAINT FK_FAEEE0D681BEC8C2 FOREIGN KEY (faq_id) REFERENCES faq (id)',
        );
        $this->addSql(
            'ALTER TABLE faq_category_translation ADD CONSTRAINT FK_5493B0FCF689B0DB FOREIGN KEY (faq_category_id) REFERENCES faq_category (id)',
        );
        $this->addSql(
            'ALTER TABLE faq_question ADD CONSTRAINT FK_4A55B059F689B0DB FOREIGN KEY (faq_category_id) REFERENCES faq_category (id)',
        );
        $this->addSql(
            'ALTER TABLE faq_question_translation ADD CONSTRAINT FK_C2D1AFAE8DB86 FOREIGN KEY (faq_question_id) REFERENCES faq_question (id)',
        );
        $this->addSql(
            'ALTER TABLE faq_statistique ADD CONSTRAINT FK_976F159481BEC8C2 FOREIGN KEY (faq_id) REFERENCES faq (id)',
        );
        $this->addSql(
            'ALTER TABLE faq_translation ADD CONSTRAINT FK_50A6685681BEC8C2 FOREIGN KEY (faq_id) REFERENCES faq (id)',
        );
        $this->addSql(
            'ALTER TABLE mail_translation ADD CONSTRAINT FK_B638B2C8776F01 FOREIGN KEY (mail_id) REFERENCES mail (id)',
        );
        $this->addSql(
            'ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C334C0ACC FOREIGN KEY (media_folder_id) REFERENCES media_folder (id)',
        );
        $this->addSql(
            'ALTER TABLE media ADD CONSTRAINT FK_6A2CA10CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)',
        );
        $this->addSql(
            'ALTER TABLE media_folder ADD CONSTRAINT FK_50DB9313727ACA70 FOREIGN KEY (parent_id) REFERENCES media_folder (id) ON DELETE CASCADE',
        );
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql(
            'ALTER TABLE menu_element ADD CONSTRAINT FK_C99B4387CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)',
        );
        $this->addSql(
            'ALTER TABLE menu_element ADD CONSTRAINT FK_C99B4387727ACA70 FOREIGN KEY (parent_id) REFERENCES menu_element (id) ON DELETE CASCADE',
        );
        $this->addSql(
            'ALTER TABLE menu_element ADD CONSTRAINT FK_C99B4387C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)',
        );
        $this->addSql(
            'ALTER TABLE menu_element_translation ADD CONSTRAINT FK_9C0EC81D3EB29EF6 FOREIGN KEY (menu_element_id) REFERENCES menu_element (id)',
        );
        $this->addSql(
            'ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)',
        );
        $this->addSql(
            'ALTER TABLE option_user ADD CONSTRAINT FK_659D7CAAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)',
        );
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql(
            'ALTER TABLE page_tag ADD CONSTRAINT FK_CF36BF12C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE',
        );
        $this->addSql(
            'ALTER TABLE page_tag ADD CONSTRAINT FK_CF36BF12BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE',
        );
        $this->addSql(
            'ALTER TABLE page_menu ADD CONSTRAINT FK_FAC12649C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE',
        );
        $this->addSql(
            'ALTER TABLE page_menu ADD CONSTRAINT FK_FAC12649CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE',
        );
        $this->addSql(
            'ALTER TABLE page_content ADD CONSTRAINT FK_4A5DB3CC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)',
        );
        $this->addSql(
            'ALTER TABLE page_content_translation ADD CONSTRAINT FK_C51473728F409273 FOREIGN KEY (page_content_id) REFERENCES page_content (id)',
        );
        $this->addSql(
            'ALTER TABLE page_meta ADD CONSTRAINT FK_503608EFC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)',
        );
        $this->addSql(
            'ALTER TABLE page_meta_translation ADD CONSTRAINT FK_7DA93944C39C4D6C FOREIGN KEY (page_meta_id) REFERENCES page_meta (id)',
        );
        $this->addSql(
            'ALTER TABLE page_statistique ADD CONSTRAINT FK_641C66DFC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)',
        );
        $this->addSql(
            'ALTER TABLE page_translation ADD CONSTRAINT FK_A3D51B1DC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)',
        );
        $this->addSql(
            'ALTER TABLE sidebar_element ADD CONSTRAINT FK_2420A342727ACA70 FOREIGN KEY (parent_id) REFERENCES sidebar_element (id) ON DELETE CASCADE',
        );
        $this->addSql(
            'ALTER TABLE sql_manager ADD CONSTRAINT FK_44D502A5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)',
        );
        $this->addSql(
            'ALTER TABLE tag_translation ADD CONSTRAINT FK_A8A03F8FBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)',
        );
        $this->addSql(
            'ALTER TABLE user_data ADD CONSTRAINT FK_D772BFAAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)',
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CC4663E4');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C89731557');
        $this->addSql('ALTER TABLE faq DROP FOREIGN KEY FK_E8FF75CCA76ED395');
        $this->addSql('ALTER TABLE faq_category DROP FOREIGN KEY FK_FAEEE0D681BEC8C2');
        $this->addSql('ALTER TABLE faq_category_translation DROP FOREIGN KEY FK_5493B0FCF689B0DB');
        $this->addSql('ALTER TABLE faq_question DROP FOREIGN KEY FK_4A55B059F689B0DB');
        $this->addSql('ALTER TABLE faq_question_translation DROP FOREIGN KEY FK_C2D1AFAE8DB86');
        $this->addSql('ALTER TABLE faq_statistique DROP FOREIGN KEY FK_976F159481BEC8C2');
        $this->addSql('ALTER TABLE faq_translation DROP FOREIGN KEY FK_50A6685681BEC8C2');
        $this->addSql('ALTER TABLE mail_translation DROP FOREIGN KEY FK_B638B2C8776F01');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C334C0ACC');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10CA76ED395');
        $this->addSql('ALTER TABLE media_folder DROP FOREIGN KEY FK_50DB9313727ACA70');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93A76ED395');
        $this->addSql('ALTER TABLE menu_element DROP FOREIGN KEY FK_C99B4387CCD7E912');
        $this->addSql('ALTER TABLE menu_element DROP FOREIGN KEY FK_C99B4387727ACA70');
        $this->addSql('ALTER TABLE menu_element DROP FOREIGN KEY FK_C99B4387C4663E4');
        $this->addSql('ALTER TABLE menu_element_translation DROP FOREIGN KEY FK_9C0EC81D3EB29EF6');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE option_user DROP FOREIGN KEY FK_659D7CAAA76ED395');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620A76ED395');
        $this->addSql('ALTER TABLE page_tag DROP FOREIGN KEY FK_CF36BF12C4663E4');
        $this->addSql('ALTER TABLE page_tag DROP FOREIGN KEY FK_CF36BF12BAD26311');
        $this->addSql('ALTER TABLE page_menu DROP FOREIGN KEY FK_FAC12649C4663E4');
        $this->addSql('ALTER TABLE page_menu DROP FOREIGN KEY FK_FAC12649CCD7E912');
        $this->addSql('ALTER TABLE page_content DROP FOREIGN KEY FK_4A5DB3CC4663E4');
        $this->addSql('ALTER TABLE page_content_translation DROP FOREIGN KEY FK_C51473728F409273');
        $this->addSql('ALTER TABLE page_meta DROP FOREIGN KEY FK_503608EFC4663E4');
        $this->addSql('ALTER TABLE page_meta_translation DROP FOREIGN KEY FK_7DA93944C39C4D6C');
        $this->addSql('ALTER TABLE page_statistique DROP FOREIGN KEY FK_641C66DFC4663E4');
        $this->addSql('ALTER TABLE page_translation DROP FOREIGN KEY FK_A3D51B1DC4663E4');
        $this->addSql('ALTER TABLE sidebar_element DROP FOREIGN KEY FK_2420A342727ACA70');
        $this->addSql('ALTER TABLE sql_manager DROP FOREIGN KEY FK_44D502A5A76ED395');
        $this->addSql('ALTER TABLE tag_translation DROP FOREIGN KEY FK_A8A03F8FBAD26311');
        $this->addSql('ALTER TABLE user_data DROP FOREIGN KEY FK_D772BFAAA76ED395');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE faq');
        $this->addSql('DROP TABLE faq_category');
        $this->addSql('DROP TABLE faq_category_translation');
        $this->addSql('DROP TABLE faq_question');
        $this->addSql('DROP TABLE faq_question_translation');
        $this->addSql('DROP TABLE faq_statistique');
        $this->addSql('DROP TABLE faq_translation');
        $this->addSql('DROP TABLE mail');
        $this->addSql('DROP TABLE mail_translation');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE media_folder');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_element');
        $this->addSql('DROP TABLE menu_element_translation');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE option_system');
        $this->addSql('DROP TABLE option_user');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE page_tag');
        $this->addSql('DROP TABLE page_menu');
        $this->addSql('DROP TABLE page_content');
        $this->addSql('DROP TABLE page_content_translation');
        $this->addSql('DROP TABLE page_meta');
        $this->addSql('DROP TABLE page_meta_translation');
        $this->addSql('DROP TABLE page_statistique');
        $this->addSql('DROP TABLE page_translation');
        $this->addSql('DROP TABLE sidebar_element');
        $this->addSql('DROP TABLE sql_manager');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_translation');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
