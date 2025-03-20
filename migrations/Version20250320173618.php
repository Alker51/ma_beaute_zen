<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320173618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, taxe_id_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(2000) NOT NULL, price_ht DOUBLE PRECISION NOT NULL, promo_active TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, promo_percent DOUBLE PRECISION DEFAULT NULL, INDEX IDX_29A5EC27B5B5E27C (taxe_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27B5B5E27C FOREIGN KEY (taxe_id_id) REFERENCES tax (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27B5B5E27C');
        $this->addSql('DROP TABLE produit');
    }
}
