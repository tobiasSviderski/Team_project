<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200522101611 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, profile_id INT NOT NULL, author_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, created DATETIME NOT NULL, INDEX IDX_8F3F68C5CCFA12B8 (profile_id), INDEX IDX_8F3F68C5F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created DATETIME NOT NULL, file VARCHAR(255) NOT NULL, enable TINYINT(1) NOT NULL, for_all TINYINT(1) NOT NULL, INDEX IDX_8157AA0FF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile_user (profile_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3B5B59DDCCFA12B8 (profile_id), INDEX IDX_3B5B59DDA76ED395 (user_id), PRIMARY KEY(profile_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, created DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE profile_user ADD CONSTRAINT FK_3B5B59DDCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_user ADD CONSTRAINT FK_3B5B59DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5CCFA12B8');
        $this->addSql('ALTER TABLE profile_user DROP FOREIGN KEY FK_3B5B59DDCCFA12B8');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5F675F31B');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FF675F31B');
        $this->addSql('ALTER TABLE profile_user DROP FOREIGN KEY FK_3B5B59DDA76ED395');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE profile_user');
        $this->addSql('DROP TABLE user');
    }
}
