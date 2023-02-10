<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230207232139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE constants CHANGE value value VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE form_answers CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE log CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP pp, DROP rank, DROP country_rank, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE unique_id unique_id VARCHAR(11) NOT NULL, CHANGE scoresaber_id scoresaber_id BIGINT DEFAULT NULL, CHANGE active active SMALLINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE constants CHANGE value value VARCHAR(1512) DEFAULT NULL');
        $this->addSql('ALTER TABLE form_answers CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE log CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD pp INT DEFAULT NULL, ADD rank INT DEFAULT NULL, ADD country_rank INT DEFAULT NULL, CHANGE unique_id unique_id VARCHAR(11) DEFAULT NULL, CHANGE scoresaber_id scoresaber_id BIGINT DEFAULT 0, CHANGE active active SMALLINT DEFAULT 0 NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
