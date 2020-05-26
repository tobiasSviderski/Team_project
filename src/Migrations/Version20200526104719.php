<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200526104719 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE file CHANGE attempt_to_change_by_id attempt_to_change_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log CHANGE author_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile ADD for_all TINYINT(1) NOT NULL, CHANGE author_id author_id INT DEFAULT NULL, CHANGE file_id file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription CHANGE profile_id profile_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE file CHANGE attempt_to_change_by_id attempt_to_change_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log CHANGE author_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile DROP for_all, CHANGE author_id author_id INT DEFAULT NULL, CHANGE file_id file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription CHANGE profile_id profile_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
