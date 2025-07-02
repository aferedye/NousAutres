<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250702214112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD confirmation_code VARCHAR(6) DEFAULT NULL, ADD is_verified TINYINT(1) NOT NULL, ADD date_inscription DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', DROP bio, CHANGE pseudo pseudo VARCHAR(255) NOT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1D1C63B386CC499D ON utilisateur (pseudo)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_1D1C63B386CC499D ON utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD bio LONGTEXT DEFAULT NULL, DROP confirmation_code, DROP is_verified, DROP date_inscription, CHANGE pseudo pseudo VARCHAR(50) NOT NULL, CHANGE statut statut VARCHAR(30) NOT NULL
        SQL);
    }
}
