<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use PDOException;

/**
 * Cette classe regroupe toutes les méthodes communes aux classes gérant la persistance des données (répertoire Repository).
 */
abstract class AbstractRepository implements AbstractRepositoryInterface
{

    public function __construct(private readonly ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
    }


    /**
     * Ci-dessous sont regroupées toutes les méthodes à redéfinir dans les sous-classes d'AbstractRepository.
     */
    protected abstract function getNomTable(): string;
    protected abstract function getNomClePrimaire(): string;
    protected abstract function getNomsColonnes(): array;
    protected abstract function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject;

    /** Récupère un ou plusieurs tuples d'une table dans la base de données.
     * @param int|string $limit Nombre de réponses ("ALL" pour toutes les réponses)
     * @return AbstractDataObject[] les objets construits à partir des données récupérees
     */
    public function recuperer($limit = 200): array
    {
        $nomTable = $this->getNomTable();
        $champsSelect = implode(", ", $this->getNomsColonnes());
        $requeteSQL = <<<SQL
        SELECT $champsSelect FROM $nomTable LIMIT $limit;
        SQL;

        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query($requeteSQL);

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    /**
     * Récupère les trajets du fichier json de l'utilisateur @param
     *
     * @param $login
     * @return array
     */
    public function recupererTrajetsUtilisateur($login): array{
        $nomTable = $this->getNomTable();
        $champsSelect = "trajets->'trajets'";
        $nomClePrimaire = $this->getNomClePrimaire();
        $requeteSQL = <<<SQL
                        SELECT trajet
                        FROM $nomTable, jsonb_array_elements($champsSelect) as trajet
                        WHERE $nomClePrimaire = :loginTag;
                    SQL;

        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($requeteSQL);

        $values = array(
            "loginTag" => $login
        );

        $pdoStatement->execute($values);

        return $pdoStatement->fetchAll();

    }

    /** Récupère un ou plusieurs tuples d'une table en passant par une condition
     * @param array $critereSelection ex: ["nomColonne" => valeurDeRecherche]
     * @return AbstractDataObject[] les objets construits à partir des données récupérees
     */
    public function recupererPar(array $critereSelection, $limit = 200): array
    {
        $nomTable = $this->getNomTable();
        $champsSelect = implode(", ", $this->getNomsColonnes());

        $partiesWhere = array_map(function ($nomcolonne) {
            return "$nomcolonne = :$nomcolonne";
        }, array_keys($critereSelection));
        $whereClause = join(',', $partiesWhere);

        $requeteSQL = <<<SQL
            SELECT $champsSelect FROM $nomTable WHERE $whereClause LIMIT $limit;
        SQL;
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($requeteSQL);
        $pdoStatement->execute($critereSelection);

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /** Récupère un tuple d'une table à l'aide de sa clé primaire
     * @param string $valeurClePrimaire la clé primaire de la ligne
     * @return AbstractDataObject|null l'objet construit à partir du tuple si la récupération des données a fonctionné, null sinon
     */
    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomClePrimaire();
        $sql = "SELECT * from $nomTable WHERE $nomClePrimaire=:clePrimaireTag";
        // Préparation de la requête
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        $values = array(
            "clePrimaireTag" => $valeurClePrimaire,
        );
        // On donne les valeurs et on exécute la requête
        $pdoStatement->execute($values);

        // On récupère les résultats comme précédemment
        // Note: fetch() renvoie false si pas de voiture correspondante
        $objetFormatTableau = $pdoStatement->fetch();

        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    /** Supprime un tuple à l'aide de sa clé primaire
     * @param string $valeurClePrimaire la clé primaire du tuple
     * @return bool vrai si la suppression a fonctionné, faux sinon
     */
    public function supprimer(string $valeurClePrimaire): bool
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomClePrimaire();
        $sql = "DELETE FROM $nomTable WHERE $nomClePrimaire= :clePrimaireTag;";
        // Préparation de la requête
        $pdoStatement = $this->connexionBaseDeDonnees->getPDO()->prepare($sql);

        $values = array(
            "clePrimaireTag" => $valeurClePrimaire
        );

        // On donne les valeurs et on exécute la requête
        $pdoStatement->execute($values);

        // PDOStatement::rowCount() retourne le nombre de lignes affectées par la dernière
        // requête DELETE, INSERT ou UPDATE exécutée par l'objet PDOStatement correspondant.
        // https://www.php.net/manual/fr/pdostatement.rowcount.php
        $deleteCount = $pdoStatement->rowCount();

        // Renvoie true ssi on a bien supprimé une ligne de la BDD
        return ($deleteCount > 0);
    }

    /**
     * Supprime un trajet du fichier json de l'utilisateur @param
     *
     * @param string $login
     * @param string $nomsCommunes
     * @return bool
     */
    public function supprimerTrajetUtilisateur(string $login, string $nomsCommunes) : bool{
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomClePrimaire();

        $tabCommune = explode("+", $nomsCommunes);


        $sql = <<<SQL
            UPDATE $nomTable
            SET trajets = COALESCE(JSONB_SET(trajets, '{trajets}',
                                    (
                                    SELECT jsonb_agg(elem ORDER BY idx)
                                    FROM jsonb_array_elements(trajets->'trajets') WITH ORDINALITY arr(elem, idx)
                                    where NOT (elem->>'nomCommuneDepart' = :nomCommuneDepartTag AND elem->>'nomCommuneArrivee' = :nomCommuneArriveeTag)
                                    )
                        ), '{"trajets": []}'::JSONB)
            WHERE $nomClePrimaire = :loginTag;
            SQL;

        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        echo "<p>$sql</p>";

        $values = array(
            "loginTag" => $login,
            "nomCommuneDepartTag" => $tabCommune[0],
            "nomCommuneArriveeTag" => $tabCommune[1]
        );

        return $pdoStatement->execute($values);

    }

    /** modifie un tuple d'une table en passant en paramètre l'objet qui lui correspond
     * @param AbstractDataObject $object l'objet à modifier
     * @return void
     */
    public function mettreAJour(AbstractDataObject $object): void
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomClePrimaire();
        $nomsColonnes = $this->getNomsColonnes();

        $partiesSet = array_map(function ($nomcolonne) {
            return "$nomcolonne = :{$nomcolonne}_tag";
        }, $nomsColonnes);
        $setString = join(',', $partiesSet);
        $whereString = "$nomClePrimaire = :{$nomClePrimaire}_tag";

        $sql = "UPDATE $nomTable SET $setString WHERE $whereString";
        // Préparation de la requête
        $req_prep = $this->connexionBaseDeDonnees->getPDO()->prepare($sql);

        $objetFormatTableau = $object->exporterEnFormatRequetePreparee();
        $req_prep->execute($objetFormatTableau);
    }

    /**
     * Ajoute un trajet dans le fichier json de la table utilisateur
     *
     * @param $colonneJson
     * @param $donnees
     * @param $tab
     * @param string $valeurClePrimaire
     * @return bool
     */
    public function AjouterJson($colonneJson, $donnees, $tab, string $valeurClePrimaire): bool{
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomClePrimaire();
        $sql = <<<SQL
                UPDATE $nomTable
                SET $colonneJson = jsonb_insert($colonneJson, '{trajets,-1}', :donneesTag)
                WHERE $nomClePrimaire = :clePrimaireTag AND NOT EXISTS (
                            SELECT 1 FROM jsonb_array_elements($colonneJson->'trajets') AS elem WHERE elem = :donneesTag
                             );
                SQL;


        $pdoStatement = $this->connexionBaseDeDonnees->getPDO()->prepare($sql);

        $values = array(
            "donneesTag" => $donnees,
            "clePrimaireTag" => $valeurClePrimaire
        );

        try {
            $pdoStatement->execute($values);
            return true;
        } catch (PDOException $exception) {
            if ($pdoStatement->errorCode() === "23000") {
                // Je ne traite que l'erreur "Duplicate entry"
                return false;
            } else {
                // Pour les autres erreurs, je transmets l'exception
                throw $exception;
            }
        }

    }

    /** Insère les données d'un objet dans la base de données
     * @param AbstractDataObject $object l'objet à ajouter
     * @return bool vrai si l'insertion a fonctionné, faux sinon
     */
    public function ajouter(AbstractDataObject $object): bool
    {
        $nomTable = $this->getNomTable();
        $nomsColonnes = $this->getNomsColonnes();

        $insertString = '(' . join(', ', $nomsColonnes) . ')';

        $partiesValues = array_map(function ($nomcolonne) {
            return ":{$nomcolonne}_tag";
        }, $nomsColonnes);
        $valueString = '(' . join(', ', $partiesValues) . ')';

        $sql = "INSERT INTO $nomTable $insertString VALUES $valueString";
        // Préparation de la requête
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        $objetFormatTableau = $object->exporterEnFormatRequetePreparee();

        try {
            $pdoStatement->execute($objetFormatTableau);
            return true;
        } catch (PDOException $exception) {
            if ($pdoStatement->errorCode() === "23000") {
                // Je ne traite que l'erreur "Duplicate entry"
                return false;
            } else {
                // Pour les autres erreurs, je transmets l'exception
                throw $exception;
            }
        }
    }

    /**
     * @return ConnexionBaseDeDonneesInterface
     */
    protected function getConnexionBaseDeDonnees(): ConnexionBaseDeDonneesInterface
    {
        return $this->connexionBaseDeDonnees;
    }


}
