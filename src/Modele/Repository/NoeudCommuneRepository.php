<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\NoeudCommune;
use PDO;
use PDOException;

/**
 * Cette classe contient toutes les méthodes gérant la persistance des données des noeuds communes.
 */
class NoeudCommuneRepository extends AbstractRepository
{

    /** Construit un objet NoeudCommune à partir d'un tableau donné en paramètre.
     * @param array $noeudRoutierTableau
     * @return NoeudCommune
     */
    public function construireDepuisTableau(array $noeudRoutierTableau): NoeudCommune
    {
        return new NoeudCommune(
            $noeudRoutierTableau["gid"],
            $noeudRoutierTableau["id_rte500"],
            $noeudRoutierTableau["nom_comm"],
            $noeudRoutierTableau["id_nd_rte"]
        );
    }


    /** Retourne le nom de la table contenant les données des noeuds communes.
     * @return string
     */
    protected function getNomTable(): string
    {
        return 'noeud_commune';
    }

    /** Retourne la clé primaire de la table noeud_commune.
     * @return string
     */
    protected function getNomClePrimaire(): string
    {
        return 'gid';
    }

    /** Retourne le nom de tous les attributs de la table noeud_commune.
     * @return string[] le tableau contenant tous les noms des attributs
     */
    protected function getNomsColonnes(): array
    {
        return ["gid", "id_rte500", "nom_comm", "id_nd_rte"];
    }

    // On bloque l'ajout, la màj et la suppression pour ne pas modifier la table
    // Normalement, j'ai restreint l'accès à SELECT au niveau de la BD
    public function supprimer(string $valeurClePrimaire): bool
    {
        return false;
    }

    public function mettreAJour(AbstractDataObject $object): void
    {
        return;
    }

    public function ajouter(AbstractDataObject $object): bool
    {
        return false;
    }

    public function selectByName($name)
    {
        try {
            // préparation de la requête
            $sql = "SELECT * FROM noeud_commune WHERE nom_comm LIKE :name_tag LIMIT 5";
            $req_prep = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($sql);
            // passage de la valeur de name_tag
            $values = array("name_tag" => $name . "%");
            // exécution de la requête préparée
            $req_prep->execute($values);
            $req_prep->setFetchMode(PDO::FETCH_OBJ);
            $tabResults = $req_prep->fetchAll();
            // renvoi du tableau de résultats
            return $tabResults;
        } catch (PDOException $e) {
            echo $e->getMessage();
            die("Erreur lors de la recherche dans la base de données.");
        }
    }

}
