<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511170348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP image_url, DROP sort_order, DROP certificate_name, DROP max_students, DROP average_rating, DROP total_reviews, CHANGE slug slug VARCHAR(200) NOT NULL');
        $this->addSql('ALTER TABLE enrollment DROP expires_at, DROP payment_id, DROP last_accessed_at, DROP certificate_score');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lesson DROP resources, DROP transcript, DROP updated_at, DROP has_quiz, DROP quiz_id, CHANGE video_url video_url VARCHAR(500) DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL, CHANGE is_free_preview is_free_preview TINYINT NOT NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE module DROP updated_at, DROP learning_objectives');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C242628591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE user DROP facebook_id, DROP avatar_url, DROP last_login, DROP subscription_level, DROP subscription_expires_at, DROP purchased_courses');
        $this->addSql('ALTER TABLE user_lesson_progress ADD CONSTRAINT FK_789AD4D0CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_lesson_progress ADD CONSTRAINT FK_789AD4D0CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course ADD image_url VARCHAR(500) DEFAULT NULL, ADD sort_order INT DEFAULT NULL, ADD certificate_name VARCHAR(255) DEFAULT NULL, ADD max_students INT DEFAULT NULL, ADD average_rating DOUBLE PRECISION DEFAULT NULL, ADD total_reviews INT DEFAULT NULL, CHANGE slug slug VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1CB944F1A');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1591CC992');
        $this->addSql('ALTER TABLE enrollment ADD expires_at DATETIME DEFAULT NULL, ADD payment_id VARCHAR(255) DEFAULT NULL, ADD last_accessed_at DATETIME DEFAULT NULL, ADD certificate_score DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3AFC2B591');
        $this->addSql('ALTER TABLE lesson ADD resources JSON DEFAULT NULL, ADD transcript LONGTEXT DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD has_quiz TINYINT DEFAULT 0 NOT NULL, ADD quiz_id INT DEFAULT NULL, CHANGE video_url video_url VARCHAR(500) NOT NULL, CHANGE duration duration INT NOT NULL, CHANGE is_free_preview is_free_preview TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C242628591CC992');
        $this->addSql('ALTER TABLE module ADD updated_at DATETIME DEFAULT NULL, ADD learning_objectives JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD facebook_id VARCHAR(255) DEFAULT NULL, ADD avatar_url VARCHAR(255) DEFAULT NULL, ADD last_login DATETIME DEFAULT NULL, ADD subscription_level VARCHAR(50) DEFAULT \'free\' NOT NULL, ADD subscription_expires_at DATETIME DEFAULT NULL, ADD purchased_courses JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE user_lesson_progress DROP FOREIGN KEY FK_789AD4D0CB944F1A');
        $this->addSql('ALTER TABLE user_lesson_progress DROP FOREIGN KEY FK_789AD4D0CDF80196');
    }
}
