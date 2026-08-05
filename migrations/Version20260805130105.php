<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260805130105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add skill.description';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skill ADD description TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skill DROP description');
    }
}
