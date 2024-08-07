<?php

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rename from TYPO3 and/or Phoenix to Neos as needed
 */
class Version20121030103851 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("ALTER TABLE typo3_typo3_domain_model_site RENAME TO typo3_neos_domain_model_site");
        $this->addSql("ALTER TABLE typo3_typo3_domain_model_domain RENAME TO typo3_neos_domain_model_domain");
        $this->addSql("ALTER TABLE typo3_typo3_domain_model_userpreferences RENAME TO typo3_neos_domain_model_userpreferences");
        $this->addSql("ALTER TABLE typo3_typo3_domain_model_user RENAME TO typo3_neos_domain_model_user");

        $this->addSql("UPDATE typo3_party_domain_model_abstractparty SET dtype = 'typo3_neos_user' WHERE dtype = 'typo3_typo3_user'");

        $schemaManager = $this->connection->createSchemaManager();
        $hasTables = $schemaManager->tablesExist(['typo3_typo3cr_domain_model_contentobjectproxy']);
        if ($hasTables) {
            $this->addSql("UPDATE typo3_typo3cr_domain_model_contentobjectproxy SET targettype = 'TYPO3\\Neos\\Domain\\Model\\Site' WHERE targettype = 'TYPO3\\TYPO3\\Domain\\Model\\Site'");
        }
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));

        $this->addSql("ALTER TABLE typo3_neos_domain_model_site RENAME TO typo3_typo3_domain_model_site");
        $this->addSql("ALTER TABLE typo3_neos_domain_model_domain RENAME TO typo3_typo3_domain_model_domain");
        $this->addSql("ALTER TABLE typo3_neos_domain_model_userpreferences RENAME TO typo3_typo3_domain_model_userpreferences");
        $this->addSql("ALTER TABLE typo3_neos_domain_model_user RENAME TO typo3_typo3_domain_model_user");

        $this->addSql("UPDATE typo3_party_domain_model_abstractparty SET dtype = 'typo3_typo3_user' WHERE dtype = 'typo3_neos_user'");
        $schemaManager = $this->connection->createSchemaManager();
        $hasTables = $schemaManager->tablesExist(['typo3_typo3cr_domain_model_contentobjectproxy']);
        if ($hasTables) {
            $this->addSql("UPDATE typo3_typo3cr_domain_model_contentobjectproxy SET targettype = 'TYPO3\\TYPO3\\Domain\\Model\\Site' WHERE targettype = 'TYPO3\\Neos\\Domain\\Model\\Site'");
        }
    }
}
