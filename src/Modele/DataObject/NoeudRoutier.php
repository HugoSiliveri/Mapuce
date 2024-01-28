<?php

namespace App\PlusCourtChemin\Modele\DataObject;

use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;
use Exception;
use JsonSerializable;

/**
 * Cette classe dite métier récupère les données de la table noeud_routier afin d'en instancier des objets, et de pouvoir les manipuler.
 */
class NoeudRoutier extends AbstractDataObject implements JsonSerializable
{
    private array $voisins;

    /** Constructeur de NoeudRoutier. Instancie l'attribut voisins qui prend comme valeur un tableau contenant les voisins d'un noeud routier
     * @param int $gid l'identifiant unique d'un objet NoeudRoutier
     * @param string $id_rte500 ??
     */
    public function __construct(
        private int                             $gid,
        private string                          $id_rte500,
        private readonly NoeudRoutierRepository $noeudRoutierRepository
    )
    {
        //$this->voisins = (new NoeudRoutierRepository())->getVoisins($this->getGid());
        $this->voisins = $this->noeudRoutierRepository->getVoisinsV2($this->getGid());
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

    /** Retourne l'attribut voisins
     * @return array
     */
    public function getVoisins(): array
    {
        return $this->voisins;
    }

    /** Héritage de la méthode présente dans AbstractDataObject. Retourne une exception.
     * @return array
     * @throws Exception
     */
    public function exporterEnFormatRequetePreparee(): array
    {
        // Inutile car pas d'ajout ni de màj
        throw new Exception("Vous ne devriez pas appeler cette fonction car il n'y a pas de modification des noeuds routiers");
        return [];
    }

    public function jsonSerialize(): mixed
    {
        return [
            "gid" => $this->getGid(),
            "id_rte500" => $this->getId_rte500()
        ];
    }
}
