<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512005351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE252720AEF35F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE2527DE18E50B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F0FE252720AEF35F ON cart_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F0FE2527DE18E50B ON cart_item
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD cart_id INT NOT NULL, DROP product_id_id, CHANGE cart_id_id product_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F0FE25274584665A ON cart_item (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F0FE25271AD5CDBF ON cart_item (cart_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE shipping_address_id_id shipping_address_id_id INT DEFAULT NULL, CHANGE billing_address_id billing_address_id INT DEFAULT NULL
        SQL);
        //$this->addSql(<<<'SQL'
            //ALTER TABLE product_image DROP FOREIGN KEY FK_64617F03DE18E50B
        //SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_64617F03DE18E50B ON product_image
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image CHANGE product_id_id product_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64617F034584665A ON product_image (product_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F0FE25274584665A ON cart_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F0FE25271AD5CDBF ON cart_item
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD cart_id_id INT NOT NULL, ADD product_id_id INT DEFAULT NULL, DROP product_id, DROP cart_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE252720AEF35F FOREIGN KEY (cart_id_id) REFERENCES cart (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE2527DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F0FE252720AEF35F ON cart_item (cart_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F0FE2527DE18E50B ON cart_item (product_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE shipping_address_id_id shipping_address_id_id INT NOT NULL, CHANGE billing_address_id billing_address_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_64617F034584665A ON product_image
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image CHANGE product_id product_id_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image ADD CONSTRAINT FK_64617F03DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64617F03DE18E50B ON product_image (product_id_id)
        SQL);
    }
}
