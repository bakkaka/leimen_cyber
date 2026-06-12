<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260612095400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE contact_message CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE course CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE enrollment CHANGE enrolled_at enrolled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lesson CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE module CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE prerequisite CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD reset_token VARCHAR(255) DEFAULT NULL, ADD reset_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE contact_message CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE course CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1CB944F1A');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1591CC992');
        $this->addSql('ALTER TABLE enrollment CHANGE enrolled_at enrolled_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F591CC992');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3AFC2B591');
        $this->addSql('ALTER TABLE lesson CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C242628591CC992');
        $this->addSql('ALTER TABLE module CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE prerequisite CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92AFC2B591');
        $this->addSql('ALTER TABLE `user` DROP reset_token, DROP reset_token_expires_at, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_lesson_progress DROP FOREIGN KEY FK_789AD4D0CB944F1A');
        $this->addSql('ALTER TABLE user_lesson_progress DROP FOREIGN KEY FK_789AD4D0CDF80196');
        $this->addSql('ALTER TABLE user_module_progress DROP FOREIGN KEY FK_541D4D37A76ED395');
        $this->addSql('ALTER TABLE user_module_progress DROP FOREIGN KEY FK_541D4D37AFC2B591');
        $this->addSql('ALTER TABLE user_quiz_attempt DROP FOREIGN KEY FK_8A7AD3C4A76ED395');
        $this->addSql('ALTER TABLE user_quiz_attempt DROP FOREIGN KEY FK_8A7AD3C4853CD175');
    }
}
