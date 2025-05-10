<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250510010316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE product_wish_list (product_id INT NOT NULL, wish_list_id INT NOT NULL, INDEX IDX_35278F304584665A (product_id), INDEX IDX_35278F30D69F3311 (wish_list_id), PRIMARY KEY(product_id, wish_list_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_wish_list ADD CONSTRAINT FK_35278F304584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_wish_list ADD CONSTRAINT FK_35278F30D69F3311 FOREIGN KEY (wish_list_id) REFERENCES wish_list (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product_wish_list DROP FOREIGN KEY FK_35278F304584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_wish_list DROP FOREIGN KEY FK_35278F30D69F3311
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_wish_list
        SQL);
    }
}
