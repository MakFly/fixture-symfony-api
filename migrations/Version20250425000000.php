<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250425000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create item table + seed two rows for ploydok smoke test';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE item (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql("INSERT INTO item (name) VALUES ('alpha')");
        $this->addSql("INSERT INTO item (name) VALUES ('beta')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE item');
    }
}
