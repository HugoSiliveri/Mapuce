<?php

namespace App\PlusCourtChemin\Modele\DataObject;

use JsonSerializable;

/**
 * Cette classe dite métier récupère les données de la table noeud_commune afin d'en instancier des objets, et de pouvoir les manipuler.
 */
class NoeudCommune extends AbstractDataObject implements JsonSerializable
{
    /**
     * Constructeur de NoeudCommune
     * @param int $gid l'identifiant unique d'un objet NoeudCommune
     * @param string $id_rte500 l'identifiant de la commune correspondant au noeud sous forme de texte
     * @param string $nomCommune le nom de la commune
     * @param string $id_nd_rte les id des noeuds routiers de la commune sous forme de texte
     */
    public function __construct(
        private int    $gid,
        private string $id_rte500,
        private string $nomCommune,
        private string $id_nd_rte,
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

    /** Retourne l'attribut id_nd_rte
     * @return string
     */
    public function getId_nd_rte(): string
    {
        return $this->id_nd_rte;
    }

    /** Retourne l'attribut nomCommune
     * @return string
     */
    public function getNomCommune(): string
    {
        return $this->nomCommune;
    }

    /** Héritage de la fonction présente dans AbstractDataObject. Retourne un tableau vide.
     * @return array
     */
    public function exporterEnFormatRequetePreparee(): array
    {
        // Inutile car on ne fait pas d'ajout ni de mise-à-jour
        return [];
    }

    public function jsonSerialize(): mixed
    {
        return [
            "gid" => $this->getGid(),
            "id_rte500" => $this->getId_rte500(),
            "nomCommune" => $this->getNomCommune(),
            "noeud_routier" => [
                "id_rte500" => $this->getId_nd_rte()
            ]
        ];
    }
}
