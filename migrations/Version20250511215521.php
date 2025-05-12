<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511215521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE shipping_address_id_id shipping_address_id_id INT DEFAULT NULL
        SQL);
        //$this->addSql(<<<'SQL'
          //  ALTER TABLE product_image DROP FOREIGN KEY FK_64617F03DE18E50B
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
            ALTER TABLE `order` CHANGE shipping_address_id_id shipping_address_id_id INT NOT NULL
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
