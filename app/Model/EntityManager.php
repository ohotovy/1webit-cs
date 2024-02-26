<?php

namespace App\Model;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\ORMSetup;

class EntityManager extends DoctrineEntityManager
{
    public function __construct()
    {
        // Create a simple "default" Doctrine ORM configuration for Attributes
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: array(__DIR__."/Entity"),
            isDevMode: true,
        );

        // configuring the database connection
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_pgsql',
            'user' => 'postgres',
            'password' => 'elephantGoesToot',
            'host' => 'db',
            'port' => 5432,
            'dbname' => 'doctrine_attempt'
        ], $config);

        // obtaining the entity manager
        parent::__construct($connection, $config);
    }
}