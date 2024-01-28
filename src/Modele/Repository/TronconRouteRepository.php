<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\TronconRoute;

/**
 * Cette classe contient toutes les méthodes gérant la persistance des données des tronçons de route.
 */
class TronconRouteRepository extends AbstractRepository
{

    /** Construit un objet TronconRoute à partir d'un tableau donné en paramètre.
     * @param array $noeudRoutierTableau
     * @return TronconRoute
     */
    public function construireDepuisTableau(array $noeudRoutierTableau): TronconRoute
    {
        return new TronconRoute(
            $noeudRoutierTableau["gid"],
            $noeudRoutierTableau["id_rte500"],
            $noeudRoutierTableau["sens"],
            $noeudRoutierTableau["num_route"] ?? "",
            (float)$noeudRoutierTableau["longueur"],
            null
        );
    }

    /** Retourne le nom de la table contenant les données des tronçons de route.
     * @return string
     */
    protected function getNomTable(): string
    {
        return 'troncon_route';
    }

    /** Retourne la clé primaire de la table troncon_route.
     * @return string
     */
    protected function getNomClePrimaire(): string
    {
        return 'gid';
    }

    /** Retourne le nom de tous les attributs de la table troncon_route.
     * @return string[] le tableau contenant tous les noms des attributs
     */
    protected function getNomsColonnes(): array
    {
        return ["gid", "id_rte500", "sens", "num_route", "longueur"];
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

    public function recupererGeomParClesPrimaire($troncon_gid)
    {
        $requeteSQL = <<<SQL
                        SELECT ST_AsText(geom) FROM troncon_route WHERE gid = :gidTag;
                    SQL;

        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gidTag" => $troncon_gid
        ));
        return $pdoStatement->fetch()[0];
    }

    public function recupererClesPrimaireParNoeuds($noeud_depart, $noeud_arrivee)
    {
        $requeteSQL = <<<SQL
                        SELECT troncon_gid FROM noeud_troncon WHERE gid = :gidDepart AND noeud_routier_gid = :gidArrivee;
                    SQL;

        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gidDepart" => $noeud_depart,
            "gidArrivee" => $noeud_arrivee
        ));
        return $pdoStatement->fetch()[0];
    }


    public function recupererInfosParClesPrimaire($troncon_gid)
    {
        $requeteSQL = <<<SQL
                        SELECT class_adm AS typeRoute, longueur FROM troncon_route WHERE gid = :gidTag;
                    SQL;

        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gidTag" => $troncon_gid
        ));
        return $pdoStatement->fetch();
    }


    /**
     * @param $noeudDepart
     * @param $noeudArrive
     * @return array
     */
    public function calculerTrajet($noeudDepart, $noeudArrive): array{
        $requeteSQL = <<<SQL
                        SELECT * FROM pgr_aStar(
                            'SELECT troncon_gid AS id, gid AS source, noeud_routier_gid AS target, longueur AS cost, x1, y1, x2, y2 
                            FROM noeud_troncon_astar',
                            :noeudDepartTag::int,
                            :noeudArriveTag::int,
                            false,
                            2
                        )
                    SQL;

        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "noeudDepartTag" => $noeudDepart,
            "noeudArriveTag" => $noeudArrive
        ));
        return $pdoStatement->fetchAll();
    }



}
