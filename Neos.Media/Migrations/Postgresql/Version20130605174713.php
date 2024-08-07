<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add caption and lastModified to Asset
 */
class Version20130605174713 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("ALTER TABLE typo3_media_domain_model_asset ADD caption TEXT NOT NULL");
        $this->addSql("ALTER TABLE typo3_media_domain_model_asset ADD lastmodified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL");
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("ALTER TABLE typo3_media_domain_model_asset DROP lastmodified");
        $this->addSql("ALTER TABLE typo3_media_domain_model_asset DROP caption");
    }
}
