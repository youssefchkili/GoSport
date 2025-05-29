<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529065429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE subtotal subtotal NUMERIC(10, 10) NOT NULL, CHANGE tax tax NUMERIC(10, 10) NOT NULL, CHANGE shipping shipping NUMERIC(10, 10) NOT NULL, CHANGE total total NUMERIC(10, 10) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE subtotal subtotal NUMERIC(10, 2) NOT NULL, CHANGE tax tax NUMERIC(10, 2) NOT NULL, CHANGE shipping shipping NUMERIC(10, 2) NOT NULL, CHANGE total total NUMERIC(10, 2) NOT NULL
        SQL);
    }
}
