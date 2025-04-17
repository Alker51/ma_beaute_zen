<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417170445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, state_id INT NOT NULL, title VARCHAR(255) NOT NULL, detail LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, edited_time DATETIME NOT NULL, INDEX IDX_4C62E638A76ED395 (user_id), INDEX IDX_4C62E6385D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reply (id INT AUTO_INCREMENT NOT NULL, contact_reference_id INT NOT NULL, message LONGTEXT NOT NULL, replay_date DATETIME NOT NULL, INDEX IDX_FDA8C6E03AF2EC9D (contact_reference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE state (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E6385D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E03AF2EC9D FOREIGN KEY (contact_reference_id) REFERENCES contact (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638A76ED395');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E6385D83CC1');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E03AF2EC9D');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE reply');
        $this->addSql('DROP TABLE state');
    }
}
