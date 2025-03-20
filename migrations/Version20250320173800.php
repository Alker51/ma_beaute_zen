<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320173800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, link VARCHAR(2000) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_produit (image_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_BCB5BBFB3DA5256D (image_id), INDEX IDX_BCB5BBFBF347EFB (produit_id), PRIMARY KEY(image_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image_produit ADD CONSTRAINT FK_BCB5BBFB3DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image_produit ADD CONSTRAINT FK_BCB5BBFBF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image_produit DROP FOREIGN KEY FK_BCB5BBFB3DA5256D');
        $this->addSql('ALTER TABLE image_produit DROP FOREIGN KEY FK_BCB5BBFBF347EFB');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE image_produit');
    }
}
