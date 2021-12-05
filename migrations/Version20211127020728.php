<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211127020728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE chats_dialogs_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE chats_messages_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE chats_dialogs (id INT NOT NULL, owner_id UUID NOT NULL, recipient_id UUID NOT NULL, last_message_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_message_text TEXT NOT NULL, unread_count INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C58E95D7E3C61F9 ON chats_dialogs (owner_id)');
        $this->addSql('CREATE INDEX IDX_9C58E95DE92F8F78 ON chats_dialogs (recipient_id)');
        $this->addSql('CREATE INDEX IDX_9C58E95D8B8E8428 ON chats_dialogs (created_at)');
        $this->addSql('CREATE INDEX IDX_9C58E95D7E3C61F9E92F8F78 ON chats_dialogs (owner_id, recipient_id)');
        $this->addSql('COMMENT ON COLUMN chats_dialogs.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN chats_dialogs.recipient_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE chats_messages (id INT NOT NULL, author_id UUID NOT NULL, recipient_id UUID NOT NULL, text TEXT NOT NULL, is_recipient_read BOOLEAN DEFAULT \'false\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_864EAAD3F675F31B ON chats_messages (author_id)');
        $this->addSql('CREATE INDEX IDX_864EAAD3E92F8F78 ON chats_messages (recipient_id)');
        $this->addSql('CREATE INDEX IDX_864EAAD38B8E8428 ON chats_messages (created_at)');
        $this->addSql('CREATE INDEX IDX_864EAAD3F675F31BE92F8F78 ON chats_messages (author_id, recipient_id)');
        $this->addSql('COMMENT ON COLUMN chats_messages.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN chats_messages.recipient_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE chats_dialogs ADD CONSTRAINT FK_9C58E95D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chats_dialogs ADD CONSTRAINT FK_9C58E95DE92F8F78 FOREIGN KEY (recipient_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chats_messages ADD CONSTRAINT FK_864EAAD3F675F31B FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chats_messages ADD CONSTRAINT FK_864EAAD3E92F8F78 FOREIGN KEY (recipient_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE chats_dialogs_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE chats_messages_id_seq CASCADE');
        $this->addSql('DROP TABLE chats_dialogs');
        $this->addSql('DROP TABLE chats_messages');
    }
}
