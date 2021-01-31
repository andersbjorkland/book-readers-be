<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210129110128 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE current_read (id INT AUTO_INCREMENT NOT NULL, book_id INT NOT NULL, UNIQUE INDEX UNIQ_57E4280116A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_current_read (user_id INT NOT NULL, current_read_id INT NOT NULL, INDEX IDX_D7285403A76ED395 (user_id), INDEX IDX_D72854039DB9279B (current_read_id), PRIMARY KEY(user_id, current_read_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE current_read ADD CONSTRAINT FK_57E4280116A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE user_current_read ADD CONSTRAINT FK_D7285403A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_current_read ADD CONSTRAINT FK_D72854039DB9279B FOREIGN KEY (current_read_id) REFERENCES current_read (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_current_read DROP FOREIGN KEY FK_D72854039DB9279B');
        $this->addSql('DROP TABLE current_read');
        $this->addSql('DROP TABLE user_current_read');
    }
}
