<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019190718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post CHANGE createdAt created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post RENAME INDEX idx_fab8c3b312469de2 TO IDX_5A8A6C8D12469DE2');
        $this->addSql('ALTER TABLE post RENAME INDEX idx_fab8c3b3a76ed395 TO IDX_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE user CHANGE isVerified is_verified TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE is_verified isVerified TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE post CHANGE created_at createdAt DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post RENAME INDEX idx_5a8a6c8d12469de2 TO IDX_FAB8C3B312469DE2');
        $this->addSql('ALTER TABLE post RENAME INDEX idx_5a8a6c8da76ed395 TO IDX_FAB8C3B3A76ED395');
    }
}
