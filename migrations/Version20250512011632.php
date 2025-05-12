<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512011632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE252720AEF35F FOREIGN KEY (cart_id_id) REFERENCES cart (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F0FE252720AEF35F ON cart_item (cart_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item RENAME INDEX fk_f0fe2527de18e50b TO IDX_F0FE2527DE18E50B
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE252720AEF35F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F0FE252720AEF35F ON cart_item
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item RENAME INDEX idx_f0fe2527de18e50b TO FK_F0FE2527DE18E50B
        SQL);
    }
}
