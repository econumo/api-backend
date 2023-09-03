<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230219210615 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', "Migration can only be executed safely on 'postgresql'.");

        $addCurrencySQL =<<<'SQL'
INSERT INTO user_options (id, name, value, user_id, created_at, updated_at)
    SELECT gen_random_uuid(), 'currency', 'USD', id, created_at, updated_at FROM users
ON CONFLICT (user_id,name)  
DO NOTHING;
SQL;
        $this->addSql($addCurrencySQL);

        $addReportPeriodSQL =<<<'SQL'
INSERT INTO user_options (id, name, value, user_id, created_at, updated_at)
    SELECT gen_random_uuid(), 'report_period', 'monthly', id, created_at, updated_at FROM users
ON CONFLICT (user_id,name) 
DO NOTHING;
SQL;
        $this->addSql($addReportPeriodSQL);
        $this->addSql("DELETE FROM user_options WHERE name='report_day';");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', "Migration can only be executed safely on 'postgresql'.");


    }
}
