<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Allow thumbnails without resources for asynchronous thumbnail generation.
 */
class Version20151216054040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("ALTER TABLE typo3_media_domain_model_thumbnail ALTER resource DROP NOT NULL");
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("ALTER TABLE typo3_media_domain_model_thumbnail ALTER resource SET NOT NULL");
    }
}
