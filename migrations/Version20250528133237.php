<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528133237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE coupon DROP description, DROP discount_type, DROP min_purchase, DROP usage_limit, DROP valid_from, DROP valid_to, DROP is_active, DROP created_at
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE coupon ADD description VARCHAR(255) DEFAULT NULL, ADD discount_type VARCHAR(255) NOT NULL, ADD min_purchase NUMERIC(10, 2) NOT NULL, ADD usage_limit INT DEFAULT NULL, ADD valid_from DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD valid_to DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD is_active TINYINT(1) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }
}
