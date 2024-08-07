<?php

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds indices to the event log to improve performance
 */
class Version20150724091150 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("CREATE INDEX neos_eventlog_documentnodeidentifier ON typo3_neos_eventlog_domain_model_event (documentnodeidentifier)");
        $this->addSql("CREATE INDEX neos_eventlog_eventtype ON typo3_neos_eventlog_domain_model_event (eventtype)");
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("DROP INDEX neos_eventlog_documentnodeidentifier");
        $this->addSql("DROP INDEX neos_eventlog_eventtype");
    }
}
