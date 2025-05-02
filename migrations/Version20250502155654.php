<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250502155654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne is_active à la table film';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film ADD COLUMN is_active BOOLEAN DEFAULT TRUE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film DROP COLUMN is_active');
    }
} 