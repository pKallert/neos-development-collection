<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add postgresql migration to set asset columns not nullable
 */
class Version20150131172631 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("ALTER TABLE typo3_media_domain_model_imagevariant ALTER originalasset SET NOT NULL");
        $this->addSql("ALTER TABLE typo3_media_domain_model_thumbnail ALTER originalasset SET NOT NULL");
        $this->addSql("ALTER TABLE typo3_media_domain_model_thumbnail ALTER resource SET NOT NULL");
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("ALTER TABLE typo3_media_domain_model_imagevariant ALTER originalasset DROP NOT NULL");
        $this->addSql("ALTER TABLE typo3_media_domain_model_thumbnail ALTER originalasset DROP NOT NULL");
        $this->addSql("ALTER TABLE typo3_media_domain_model_thumbnail ALTER resource DROP NOT NULL");
    }
}
