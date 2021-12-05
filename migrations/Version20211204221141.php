<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211204221141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chats_dialogs ADD unread_owner_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE chats_dialogs ADD unread_recipient_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE chats_dialogs DROP unread_count');
        $this->addSql('CREATE INDEX IDX_9C58E95DAFDD6F7 ON chats_dialogs (last_message_date)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_9C58E95DAFDD6F7');
        $this->addSql('ALTER TABLE chats_dialogs ADD unread_count INT NOT NULL');
        $this->addSql('ALTER TABLE chats_dialogs DROP unread_owner_count');
        $this->addSql('ALTER TABLE chats_dialogs DROP unread_recipient_count');
    }
}
