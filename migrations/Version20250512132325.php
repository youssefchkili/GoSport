<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512132325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item CHANGE product_id product_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image CHANGE product_id product_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item CHANGE product_id product_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image CHANGE product_id product_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
