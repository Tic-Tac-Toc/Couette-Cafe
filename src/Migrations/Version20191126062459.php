<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191126062459 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE unvailable_period (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
        $this->addSql('DROP TABLE unvaillable_period');
        $this->addSql('CREATE TEMPORARY TABLE __temp__reservation AS SELECT id, validate, room_id, client_id FROM reservation');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('CREATE TABLE reservation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, validate BOOLEAN NOT NULL, room_id INTEGER NOT NULL, client_id INTEGER NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL)');
        $this->addSql('INSERT INTO reservation (id, validate, room_id, client_id) SELECT id, validate, room_id, client_id FROM __temp__reservation');
        $this->addSql('DROP TABLE __temp__reservation');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE unvaillable_period (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, period VARCHAR(255) NOT NULL COLLATE BINARY --(DC2Type:dateinterval)
        , room_id INTEGER NOT NULL)');
        $this->addSql('DROP TABLE unvailable_period');
        $this->addSql('CREATE TEMPORARY TABLE __temp__reservation AS SELECT id, validate, room_id, client_id FROM reservation');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('CREATE TABLE reservation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, validate BOOLEAN NOT NULL, room_id INTEGER NOT NULL, client_id INTEGER NOT NULL, period VARCHAR(255) NOT NULL COLLATE BINARY --(DC2Type:dateinterval)
        )');
        $this->addSql('INSERT INTO reservation (id, validate, room_id, client_id) SELECT id, validate, room_id, client_id FROM __temp__reservation');
        $this->addSql('DROP TABLE __temp__reservation');
    }
}
