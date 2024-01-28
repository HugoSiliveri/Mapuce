<?php

namespace App\PlusCourtChemin\Modele\DataObject;

use JsonSerializable;

/**
 * Cette classe dite métier récupère les données de la table troncon_route afin d'en instancier des objets, et de pouvoir les manipuler.
 */
class TronconRoute extends AbstractDataObject implements JsonSerializable
{

    /** Constructeur de TronconRoute
     * @param int $gid l'identifiant unique d'un objet TronconRoute
     * @param string $id_rte500 les identifiants des noeuds routiers se situant aux exrémités du tronçon sous forme de texte
     * @param string $sens le sens de la route (sens unique ou double sens)
     * @param string $numeroRoute le numéro de la route (exemple
     * @param float $longueur la longueur du tronçon en km
     */
    public function __construct(
        private int    $gid,
        private string $id_rte500,
        private string $sens,
        private string $numeroRoute,
        private float  $longueur,
    )
    {
    }

    /** Retourne l'attribut gid
     * @return int
     */
    public function getGid(): int
    {
        return $this->gid;
    }

    /** Retourne l'attribut id_rte500
     * @return string
     */
    public function getId_rte500(): string
    {
        return $this->id_rte500;
    }

    /** Retourne l'attribut sens
     * @return string
     */
    public function getSens(): string
    {
        return $this->sens;
    }

    /** Retourne l'attribut numeroRoute
     * @return string
     */
    public function getNumeroRoute(): string
    {
        return $this->numeroRoute;
    }

    /** Retourne l'attribut longueur
     * @return float
     */
    public function getLongueur(): float
    {
        return $this->longueur;
    }

    /** Héritage de la méthode présente dans AbstractDataObject. Retourne un tableau vide.
     * @return array
     */
    public function exporterEnFormatRequetePreparee(): array
    {
        // Inutile car pas d'ajout ni de màj
        return [];
    }

    public function jsonSerialize(): mixed
    {
        return [
            "gid" => $this->getGid(),
            "id_rte500" => $this->getId_rte500(),
            "sens" => $this->getSens(),
            "num_route" => $this->getNumeroRoute(),
            "longueur" => $this->getLongueur()
        ];
    }
}
