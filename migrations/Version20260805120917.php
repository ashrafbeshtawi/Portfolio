<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260805120917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add project.description';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD description TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP description');
    }
}
