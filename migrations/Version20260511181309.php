<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511181309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course ADD image_url VARCHAR(500) DEFAULT NULL, ADD sort_order INT DEFAULT NULL, DROP requirements, DROP what_you_will_learn, DROP target_audience, DROP has_certificate');
        $this->addSql('ALTER TABLE enrollment ADD payment_id VARCHAR(255) DEFAULT NULL, CHANGE completed_at expires_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lesson DROP created_at, CHANGE video_url video_url VARCHAR(500) NOT NULL, CHANGE duration duration INT NOT NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE module DROP estimated_minutes');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C242628591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE user ADD facebook_id VARCHAR(255) DEFAULT NULL, ADD avatar_url VARCHAR(500) DEFAULT NULL, ADD last_login DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_lesson_progress ADD CONSTRAINT FK_789AD4D0CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_lesson_progress ADD CONSTRAINT FK_789AD4D0CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course ADD requirements JSON DEFAULT NULL, ADD what_you_will_learn JSON DEFAULT NULL, ADD target_audience JSON DEFAULT NULL, ADD has_certificate TINYINT NOT NULL, DROP image_url, DROP sort_order');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1CB944F1A');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1591CC992');
        $this->addSql('ALTER TABLE enrollment DROP payment_id, CHANGE expires_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3AFC2B591');
        $this->addSql('ALTER TABLE lesson ADD created_at DATETIME NOT NULL, CHANGE video_url video_url VARCHAR(500) DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C242628591CC992');
        $this->addSql('ALTER TABLE module ADD estimated_minutes INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` DROP facebook_id, DROP avatar_url, DROP last_login');
        $this->addSql('ALTER TABLE user_lesson_progress DROP FOREIGN KEY FK_789AD4D0CB944F1A');
        $this->addSql('ALTER TABLE user_lesson_progress DROP FOREIGN KEY FK_789AD4D0CDF80196');
    }
}
