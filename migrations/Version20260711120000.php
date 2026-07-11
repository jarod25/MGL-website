<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add HelloAsso payment reconciliation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lan__helloasso_payment (id INT AUTO_INCREMENT NOT NULL, participant_id INT DEFAULT NULL, subscription_id INT DEFAULT NULL, payment_id VARCHAR(190) NOT NULL, order_id VARCHAR(190) DEFAULT NULL, organization_slug VARCHAR(190) DEFAULT NULL, form_type VARCHAR(50) DEFAULT NULL, form_slug VARCHAR(190) DEFAULT NULL, tier_id VARCHAR(190) DEFAULT NULL, payer_email VARCHAR(255) DEFAULT NULL, payer_firstname VARCHAR(255) DEFAULT NULL, payer_lastname VARCHAR(255) DEFAULT NULL, payment_state VARCHAR(80) DEFAULT NULL, amount INT DEFAULT NULL, receipt_url LONGTEXT DEFAULT NULL, match_status VARCHAR(255) NOT NULL, mismatch_reason LONGTEXT DEFAULT NULL, payload_hash VARCHAR(64) DEFAULT NULL, received_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4C41B9599D1C3019 (participant_id), INDEX IDX_4C41B9599A1887DC (subscription_id), INDEX idx_lan_helloasso_payment_match_status (match_status), UNIQUE INDEX uniq_lan_helloasso_payment_payment_id (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lan__helloasso_payment ADD CONSTRAINT FK_4C41B9599D1C3019 FOREIGN KEY (participant_id) REFERENCES lan__participant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lan__helloasso_payment ADD CONSTRAINT FK_4C41B9599A1887DC FOREIGN KEY (subscription_id) REFERENCES lan__subscription (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lan__helloasso_payment DROP FOREIGN KEY FK_4C41B9599D1C3019');
        $this->addSql('ALTER TABLE lan__helloasso_payment DROP FOREIGN KEY FK_4C41B9599A1887DC');
        $this->addSql('DROP TABLE lan__helloasso_payment');
    }
}
