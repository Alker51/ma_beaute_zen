<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250710160456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE booking_produit (booking_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_731BA9073301C60 (booking_id), INDEX IDX_731BA907F347EFB (produit_id), PRIMARY KEY(booking_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_produit ADD CONSTRAINT FK_731BA9073301C60 FOREIGN KEY (booking_id) REFERENCES booking (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_produit ADD CONSTRAINT FK_731BA907F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_produit DROP FOREIGN KEY FK_731BA9073301C60
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_produit DROP FOREIGN KEY FK_731BA907F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE booking_produit
        SQL);
    }
}
