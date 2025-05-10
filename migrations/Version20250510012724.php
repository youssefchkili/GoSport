<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250510012724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADEF7EE744
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D34A04ADEF7EE744 ON product
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP wish_list_id_id, DROP options, DROP created_at, DROP updated_at
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD wish_list_id_id INT DEFAULT NULL, ADD options JSON DEFAULT NULL COMMENT '(DC2Type:json)', ADD created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04ADEF7EE744 FOREIGN KEY (wish_list_id_id) REFERENCES wish_list (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D34A04ADEF7EE744 ON product (wish_list_id_id)
        SQL);
    }
}
