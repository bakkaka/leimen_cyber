<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260612104556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', entity_id INT NOT NULL, entity_type VARCHAR(50) NOT NULL, is_approved TINYINT(1) NOT NULL, INDEX IDX_9474526CF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_read TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(200) NOT NULL, slug VARCHAR(200) NOT NULL, description LONGTEXT NOT NULL, short_description VARCHAR(300) DEFAULT NULL, level VARCHAR(20) NOT NULL, price INT NOT NULL, image_url VARCHAR(500) DEFAULT NULL, duration INT NOT NULL, is_published TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sort_order INT DEFAULT NULL, has_certificate TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_169E6FB9989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enrollment (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, course_id INT NOT NULL, enrolled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL, progress INT NOT NULL, payment_method VARCHAR(50) NOT NULL, payment_id VARCHAR(255) DEFAULT NULL, is_completed TINYINT(1) NOT NULL, INDEX IDX_DBDCD7E1CB944F1A (student_id), INDEX IDX_DBDCD7E1591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, public_id VARCHAR(255) NOT NULL, url VARCHAR(500) NOT NULL, alt VARCHAR(255) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, INDEX IDX_C53D045F591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, module_id INT NOT NULL, title VARCHAR(200) NOT NULL, teaser LONGTEXT DEFAULT NULL, content LONGTEXT DEFAULT NULL, video_url VARCHAR(500) NOT NULL, video_platform VARCHAR(20) NOT NULL, duration INT NOT NULL, order_number INT NOT NULL, is_free_preview TINYINT(1) NOT NULL, key_takeaways JSON DEFAULT NULL, action_item LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F87474F3AFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE module (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, title VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, order_number INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C242628591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prerequisite (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(190) NOT NULL, content LONGTEXT NOT NULL, excerpt VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_published TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_4594A238989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, text LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, options JSON DEFAULT NULL, correct_answer VARCHAR(255) NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, module_id INT NOT NULL, title VARCHAR(255) NOT NULL, passing_score INT NOT NULL, is_published TINYINT(1) NOT NULL, INDEX IDX_A412FA92AFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(150) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, facebook_id VARCHAR(255) DEFAULT NULL, avatar_url VARCHAR(500) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login DATETIME DEFAULT NULL, is_active TINYINT(1) NOT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, age INT DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_lesson_progress (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, lesson_id INT NOT NULL, completed TINYINT(1) NOT NULL, completed_at DATETIME DEFAULT NULL, video_position INT DEFAULT NULL, last_watched_at DATETIME DEFAULT NULL, INDEX IDX_789AD4D0CB944F1A (student_id), INDEX IDX_789AD4D0CDF80196 (lesson_id), UNIQUE INDEX unique_user_lesson (student_id, lesson_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_module_progress (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, module_id INT NOT NULL, completed TINYINT(1) NOT NULL, completed_at DATETIME DEFAULT NULL, quiz_passed TINYINT(1) NOT NULL, quiz_score INT DEFAULT NULL, INDEX IDX_541D4D37A76ED395 (user_id), INDEX IDX_541D4D37AFC2B591 (module_id), UNIQUE INDEX unique_user_module (user_id, module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_quiz_attempt (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, quiz_id INT NOT NULL, score INT NOT NULL, passed TINYINT(1) NOT NULL, attempted_at DATETIME NOT NULL, INDEX IDX_8A7AD3C4A76ED395 (user_id), INDEX IDX_8A7AD3C4853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C242628591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE user_lesson_progress ADD CONSTRAINT FK_789AD4D0CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_lesson_progress ADD CONSTRAINT FK_789AD4D0CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE user_module_progress ADD CONSTRAINT FK_541D4D37A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_module_progress ADD CONSTRAINT FK_541D4D37AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE user_quiz_attempt ADD CONSTRAINT FK_8A7AD3C4A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_quiz_attempt ADD CONSTRAINT FK_8A7AD3C4853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1CB944F1A');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1591CC992');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F591CC992');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3AFC2B591');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C242628591CC992');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92AFC2B591');
        $this->addSql('ALTER TABLE user_lesson_progress DROP FOREIGN KEY FK_789AD4D0CB944F1A');
        $this->addSql('ALTER TABLE user_lesson_progress DROP FOREIGN KEY FK_789AD4D0CDF80196');
        $this->addSql('ALTER TABLE user_module_progress DROP FOREIGN KEY FK_541D4D37A76ED395');
        $this->addSql('ALTER TABLE user_module_progress DROP FOREIGN KEY FK_541D4D37AFC2B591');
        $this->addSql('ALTER TABLE user_quiz_attempt DROP FOREIGN KEY FK_8A7AD3C4A76ED395');
        $this->addSql('ALTER TABLE user_quiz_attempt DROP FOREIGN KEY FK_8A7AD3C4853CD175');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE contact_message');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE enrollment');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE module');
        $this->addSql('DROP TABLE prerequisite');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_lesson_progress');
        $this->addSql('DROP TABLE user_module_progress');
        $this->addSql('DROP TABLE user_quiz_attempt');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
