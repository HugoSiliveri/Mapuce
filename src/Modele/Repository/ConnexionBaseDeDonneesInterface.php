<?php

namespace App\PlusCourtChemin\Modele\Repository;


use PDO;

/**
 * Cette classe permet de créer la connexion à la base de données.
 */
interface ConnexionBaseDeDonneesInterface
{
    /** Retourne l'attribut pdo
     * @return PDO
     */
    public function getPdo(): PDO;
}