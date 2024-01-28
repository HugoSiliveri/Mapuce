<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use PDO;

/**
 * Cette classe contient toutes les méthodes gérant la persistance des données des noeuds routiers.
 */
class NoeudRoutierRepository extends AbstractRepository
{

    /** Construit un objet NoeudRoutier à partir d'un tableau donné en paramètre.
     * @param array $noeudRoutierTableau
     * @return NoeudRoutier
     */
    public function construireDepuisTableau(array $noeudRoutierTableau): NoeudRoutier
    {
        return new NoeudRoutier(
            $noeudRoutierTableau["gid"],
            $noeudRoutierTableau["id_rte500"],
            $this
        );
    }

    /** Retourne le nom de la table contenant les données des noeuds routiers.
     * @return string
     */
    protected function getNomTable(): string
    {
        return 'noeud_routier';
    }

    /** Retourne la clé primaire de la table noeud_routier.
     * @return string
     */
    protected function getNomClePrimaire(): string
    {
        return 'gid';
    }

    /** Retourne le nom de tous les attributs de la table noeud_routier.
     * @return string[] le tableau contenant tous les noms des attributs
     */
    protected function getNomsColonnes(): array
    {
        return ["gid", "id_rte500"];
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

    /**
     * Renvoie le tableau des voisins d'un noeud routier
     *
     * Chaque voisin est un tableau avec les 3 champs
     * `noeud_routier_gid`, `troncon_gid`, `longueur`
     *
     * @param int $noeudRoutierGid
     * @return String[][]
     **/
    public function getVoisins(int $noeudRoutierGid): array
    {
        $requeteSQL = <<<SQL
                (SELECT nr2.gid AS noeud_routier_gid, tr.gid AS troncon_gid, tr.longueur
                FROM noeud_routier nr
                JOIN troncon_route tr ON ST_DWithin(nr.geom, tr.geom, 0.00002087987)
                JOIN noeud_routier nr2 ON (ST_DWithin(nr2.geom, ST_StartPoint(tr.geom), 0.00002087987) OR ST_DWithin(nr2.geom, ST_EndPoint(tr.geom), 0.00002087987))
                WHERE nr.gid = :gidTag AND nr2.gid != :gidTag
                );
        SQL;

        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gidTag" => $noeudRoutierGid
        ));
        return $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVoisinsV2(int $noeudRoutierGid): array
    {
        $requeteSQL = <<<SQL
                (SELECT noeud_routier_gid, troncon_gid, longueur
                FROM noeud_troncon
                WHERE gid = :gidTag
                );
        SQL;
        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gidTag" => $noeudRoutierGid
        ));
        return $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGeom(int $noeudRoutierGid): string
    {
        $requeteSQL = <<<SQL
                        SELECT ST_AsText(geom) FROM noeud_routier WHERE gid = :gidTag;
                    SQL;

        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gidTag" => $noeudRoutierGid
        ));
        return $pdoStatement->fetch()[0];
    }


    public function getLongitudeLatitudeParId($noeud): array
    {
        $requeteSQL = <<<SQL
                        SELECT ST_X(geom) AS x1, ST_Y(geom) AS y1 FROM noeud_routier WHERE gid = :gidTag;
                    SQL;
        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "gidTag" => $noeud,
        ));

        return $pdoStatement->fetch();
    }


    /**
     * Récupère le noeud routier le plus proche des coordonnées en paramètres
     *
     * @param $longitude
     * @param $latitude
     * @return string
     */
    public function getGidFromCoord($longitude, $latitude): string
    {

        $point = 'POINT(' . $longitude . ' ' . $latitude . ')';
        $requeteSQL = <<<SQL
                        SELECT gid
                        FROM noeud_routier nr 
                        ORDER BY ST_Distance(ST_GeogFromText(:pointTag), ST_GeogFromText(ST_AsText(geom)))
                        LIMIT 1;
                    SQL;
        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute(array(
            "pointTag" => $point,
        ));
        return $pdoStatement->fetch()[0];

    }


    /**
     * Récupère la latitute et la longitude du résultat en string d'une requete SQL renvoyant les coordonnés d'un point
     *
     * @param string $geom le point géométrique écrit littéralement comme résultat de la requete SQL
     * @return array contenant uniquement la latitude et la longitude respectivement en [0] et [1] du tableau
     */
    public function getLongitudeLatitude(string $geom): array
    {
        $sansPoint = substr($geom, 6);
        $sansDerniereP = substr($sansPoint, 0, strlen($sansPoint) - 1);
        return explode(" ", $sansDerniereP);
    }


}
