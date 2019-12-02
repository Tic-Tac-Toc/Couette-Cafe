<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191124221755 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE room_region (room_id INTEGER NOT NULL, region_id INTEGER NOT NULL, PRIMARY KEY(room_id, region_id))');
        $this->addSql('ALTER TABLE room ADD COLUMN owner_id INTEGER NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE room_region');
        $this->addSql('CREATE TEMPORARY TABLE __temp__room AS SELECT id, summary, description, capacity, superficy, price, address FROM room');
        $this->addSql('DROP TABLE room');
        $this->addSql('CREATE TABLE room (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, summary VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, capacity INTEGER NOT NULL, superficy INTEGER NOT NULL, price INTEGER NOT NULL, address CLOB NOT NULL)');
        $this->addSql('INSERT INTO room (id, summary, description, capacity, superficy, price, address) SELECT id, summary, description, capacity, superficy, price, address FROM __temp__room');
        $this->addSql('DROP TABLE __temp__room');
    }
}
