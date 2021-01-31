<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210129113957 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book CHANGE current_read_id current_read_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A3319DB9279B FOREIGN KEY (current_read_id) REFERENCES current_read (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CBE5A3319DB9279B ON book (current_read_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A3319DB9279B');
        $this->addSql('DROP INDEX UNIQ_CBE5A3319DB9279B ON book');
        $this->addSql('ALTER TABLE book CHANGE current_read_id current_read_id INT NOT NULL');
    }
}
