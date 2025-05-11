<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511124327 extends AbstractMigration
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
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE shipping_address_id_id shipping_address_id_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADEF7EE744
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D34A04ADEF7EE744 ON product
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP wish_list_id_id, DROP options, DROP created_at, DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image DROP created_at
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
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE shipping_address_id_id shipping_address_id_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD wish_list_id_id INT DEFAULT NULL, ADD options JSON DEFAULT NULL COMMENT '(DC2Type:json)', ADD created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04ADEF7EE744 FOREIGN KEY (wish_list_id_id) REFERENCES wish_list (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D34A04ADEF7EE744 ON product (wish_list_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image ADD created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }
}
