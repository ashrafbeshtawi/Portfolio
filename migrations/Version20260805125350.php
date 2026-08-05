<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260805125350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add skill_category.icon';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skill_category ADD icon VARCHAR(32) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skill_category DROP icon');
    }
}
