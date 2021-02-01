<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210201120731 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE flair (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review_flair (review_id INT NOT NULL, flair_id INT NOT NULL, INDEX IDX_F10F70B93E2E969B (review_id), INDEX IDX_F10F70B9300CED32 (flair_id), PRIMARY KEY(review_id, flair_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE review_flair ADD CONSTRAINT FK_F10F70B93E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review_flair ADD CONSTRAINT FK_F10F70B9300CED32 FOREIGN KEY (flair_id) REFERENCES flair (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review_flair DROP FOREIGN KEY FK_F10F70B9300CED32');
        $this->addSql('DROP TABLE flair');
        $this->addSql('DROP TABLE review_flair');
    }
}
