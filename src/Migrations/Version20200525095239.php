<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200525095239 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE file ADD attempt_to_change_by_id INT DEFAULT NULL, DROP confirn_to_change');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610567008DA FOREIGN KEY (attempt_to_change_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8C9F3610567008DA ON file (attempt_to_change_by_id)');
        $this->addSql('ALTER TABLE log CHANGE author_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile CHANGE author_id author_id INT DEFAULT NULL, CHANGE file_id file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription CHANGE profile_id profile_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610567008DA');
        $this->addSql('DROP INDEX IDX_8C9F3610567008DA ON file');
        $this->addSql('ALTER TABLE file ADD confirn_to_change TINYINT(1) NOT NULL, DROP attempt_to_change_by_id');
        $this->addSql('ALTER TABLE log CHANGE author_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile CHANGE author_id author_id INT DEFAULT NULL, CHANGE file_id file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription CHANGE profile_id profile_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
