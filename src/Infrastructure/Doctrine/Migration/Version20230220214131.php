<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230220214131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', "Migration can only be executed safely on 'postgresql'.");

        $this->addSql('CREATE TABLE budget_options (budget_id UUID NOT NULL, user_id UUID NOT NULL, position SMALLINT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(budget_id, user_id))');
        $this->addSql('CREATE INDEX IDX_12B6B06636ABA6B8 ON budget_options (budget_id)');
        $this->addSql('CREATE INDEX IDX_12B6B066A76ED395 ON budget_options (user_id)');
        $this->addSql("COMMENT ON COLUMN budget_options.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("CREATE TABLE budgets (id UUID NOT NULL, user_id UUID NOT NULL, name VARCHAR(64) NOT NULL, amount NUMERIC(19, 2) NOT NULL, icon VARCHAR(64) NOT NULL, carry_over BOOLEAN DEFAULT 'false' NOT NULL, carry_over_negative BOOLEAN DEFAULT 'false' NOT NULL, carry_over_start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, exclude_tags BOOLEAN DEFAULT 'false' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql('CREATE INDEX IDX_DCAA9548A76ED395 ON budgets (user_id)');
        $this->addSql("COMMENT ON COLUMN budgets.carry_over_start_date IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN budgets.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql('CREATE TABLE budget_access (budget_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(budget_id, user_id))');
        $this->addSql('CREATE INDEX IDX_52DC6DE836ABA6B8 ON budget_access (budget_id)');
        $this->addSql('CREATE INDEX IDX_52DC6DE8A76ED395 ON budget_access (user_id)');
        $this->addSql('CREATE TABLE budget_categories (budget_id UUID NOT NULL, category_id UUID NOT NULL, PRIMARY KEY(budget_id, category_id))');
        $this->addSql('CREATE INDEX IDX_ECF4892836ABA6B8 ON budget_categories (budget_id)');
        $this->addSql('CREATE INDEX IDX_ECF4892812469DE2 ON budget_categories (category_id)');
        $this->addSql('CREATE TABLE budget_tags (budget_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(budget_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_1578967636ABA6B8 ON budget_tags (budget_id)');
        $this->addSql('CREATE INDEX IDX_15789676BAD26311 ON budget_tags (tag_id)');
        $this->addSql('ALTER TABLE budget_options ADD CONSTRAINT FK_12B6B06636ABA6B8 FOREIGN KEY (budget_id) REFERENCES budgets (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_options ADD CONSTRAINT FK_12B6B066A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budgets ADD CONSTRAINT FK_DCAA9548A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_access ADD CONSTRAINT FK_52DC6DE836ABA6B8 FOREIGN KEY (budget_id) REFERENCES budgets (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_access ADD CONSTRAINT FK_52DC6DE8A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_categories ADD CONSTRAINT FK_ECF4892836ABA6B8 FOREIGN KEY (budget_id) REFERENCES budgets (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_categories ADD CONSTRAINT FK_ECF4892812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_tags ADD CONSTRAINT FK_1578967636ABA6B8 FOREIGN KEY (budget_id) REFERENCES budgets (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_tags ADD CONSTRAINT FK_15789676BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', "Migration can only be executed safely on 'postgresql'.");

        $this->addSql('ALTER TABLE budget_options DROP CONSTRAINT FK_12B6B06636ABA6B8');
        $this->addSql('ALTER TABLE budget_access DROP CONSTRAINT FK_52DC6DE836ABA6B8');
        $this->addSql('ALTER TABLE budget_categories DROP CONSTRAINT FK_ECF4892836ABA6B8');
        $this->addSql('ALTER TABLE budget_tags DROP CONSTRAINT FK_1578967636ABA6B8');
        $this->addSql('DROP TABLE budget_options');
        $this->addSql('DROP TABLE budgets');
        $this->addSql('DROP TABLE budget_access');
        $this->addSql('DROP TABLE budget_categories');
        $this->addSql('DROP TABLE budget_tags');
    }
}
