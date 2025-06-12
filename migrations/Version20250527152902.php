<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527152902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD customer_id INT NOT NULL, ADD worker_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE9395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE6B20BA36 FOREIGN KEY (worker_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E00CEDDE9395C3F3 ON booking (customer_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E00CEDDE6B20BA36 ON booking (worker_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE want_newsletter want_newsletter TINYINT(1) DEFAULT 0 NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE want_newsletter want_newsletter TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE9395C3F3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE6B20BA36
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E00CEDDE9395C3F3 ON booking
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E00CEDDE6B20BA36 ON booking
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP customer_id, DROP worker_id
        SQL);
    }
}
