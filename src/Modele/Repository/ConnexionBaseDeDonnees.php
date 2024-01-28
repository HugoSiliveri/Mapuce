<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Configuration\Configuration;
use App\PlusCourtChemin\Configuration\ConfigurationBDDInterface;
use PDO;

/**
 * Cette classe permet de créer la connexion à la base de données.
 */
class ConnexionBaseDeDonnees implements ConnexionBaseDeDonneesInterface
{
    private PDO $pdo;
    private ConfigurationBDDInterface $configurationBDD;

    /** Retourne l'attribut pdo
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Constructeur de ConnexionBaseDeDonnees.
     * Récupère la configuration de la connexion à la base de données PostgreSQL, puis crée la connexion
     */
    public function __construct(ConfigurationBDDInterface $configurationBDD)
    {
        $configuration = new Configuration($configurationBDD);
        $this->configurationBDD = $configuration->getConfigurationBDD();

        // Connexion à la base de données
        $this->pdo = new PDO(
            $this->configurationBDD->getDSN(),
            $this->configurationBDD->getLogin(),
            $this->configurationBDD->getMotDePasse(),
            $this->configurationBDD->getOptions()
        );

        // On active le mode d'affichage des erreurs, et le lancement d'exception en cas d'erreur
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}