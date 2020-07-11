<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200711155238 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promocode_transition (id INT AUTO_INCREMENT NOT NULL, promocode_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3414CB0C76C06D9 (promocode_id), INDEX IDX_3414CB0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE promocode_transition ADD CONSTRAINT FK_3414CB0C76C06D9 FOREIGN KEY (promocode_id) REFERENCES promocode (id)');
        $this->addSql('ALTER TABLE promocode_transition ADD CONSTRAINT FK_3414CB0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE promocode_transition');
    }
}
