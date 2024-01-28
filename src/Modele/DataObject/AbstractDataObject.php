<?php

namespace App\PlusCourtChemin\Modele\DataObject;

use JsonSerializable;

/**
 * Cette classe contient les méthodes communes à toutes les classes métier via héritage.
 */
abstract class AbstractDataObject implements JsonSerializable
{

    /** Cette méthode doit être obligatoirement redéfinie dans les sous-classes de AbstractDataObject.
     * Retourne un objet sous forme de tableau à utiliser pour les requêtes préparées
     * @return array
     */
    public abstract function exporterEnFormatRequetePreparee(): array;

    public abstract function jsonSerialize(): mixed;
}