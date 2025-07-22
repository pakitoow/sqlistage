<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720211740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE log_item DROP FOREIGN KEY log_item_ibfk_1');
        $this->addSql('ALTER TABLE log_item ADD line_number INT DEFAULT NULL, ADD raw_line LONGTEXT DEFAULT NULL, ADD parsed TINYINT(1) NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE log_set_id log_set_id INT NOT NULL, CHANGE message message LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX log_set_id ON log_item');
        $this->addSql('CREATE INDEX IDX_E75CB6B5015EBA ON log_item (log_set_id)');
        $this->addSql('ALTER TABLE log_item ADD CONSTRAINT log_item_ibfk_1 FOREIGN KEY (log_set_id) REFERENCES log_set (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log_set ADD import_date DATETIME NOT NULL, DROP upload_time, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE uploaded_file_name filename VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE log_item DROP FOREIGN KEY FK_E75CB6B5015EBA');
        $this->addSql('ALTER TABLE log_item DROP line_number, DROP raw_line, DROP parsed, CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE log_set_id log_set_id INT UNSIGNED NOT NULL, CHANGE message message TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_e75cb6b5015eba ON log_item');
        $this->addSql('CREATE INDEX log_set_id ON log_item (log_set_id)');
        $this->addSql('ALTER TABLE log_item ADD CONSTRAINT FK_E75CB6B5015EBA FOREIGN KEY (log_set_id) REFERENCES log_set (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log_set ADD upload_time DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP import_date, CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE filename uploaded_file_name VARCHAR(255) NOT NULL');
    }
}
