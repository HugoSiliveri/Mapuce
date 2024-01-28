<?php

namespace App\PlusCourtChemin\Modele\DataObject;

use App\PlusCourtChemin\Lib\MotDePasse;
use JsonSerializable;

/**
 * Cette classe correspond à un utilisateur du site web, les attributs correspondant à toutes ses informations envoyées lors de la création d'un compte.
 */
class Utilisateur extends AbstractDataObject implements JsonSerializable
{

    private string $login;
    private string $nom;
    private string $prenom;
    private string $mdp;
    private bool $estAdmin;
    private string $email;
    private string $emailAValider;
    private string $nonce;
    private string $avatar;

    /** Constructeur d'Utilisateur
     * @param string $login le login de l'utilisateur
     * @param string $nom le nom de l'utilisateur
     * @param string $prenom le prénom de l'utilisateur
     * @param string $mdp le mot de passe de l'utilisateur après hachage
     * @param bool $estAdmin un attribut booléen permettant de savoir si l'utilisateur est un admin du site web
     * @param string $email l'adresse mail de l'utilisateur
     * @param string $emailAValider la nouvelle adresse mail lors de la demande de modification d'email
     * @param string $nonce chaîne secrète de caractères aléatoires permettant de vérifier l'email de l'utilisateur
     */
    public function __construct(
        string $login,
        string $nom,
        string $prenom,
        string $mdp,
        bool   $estAdmin,
        string $email,
        string $emailAValider,
        string $nonce,
        string $avatar
    )
    {
        $this->login = $login;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->mdp = $mdp;
        $this->estAdmin = $estAdmin;
        $this->email = $email;
        $this->emailAValider = $emailAValider;
        $this->nonce = $nonce;
        $this->avatar = $avatar;
    }

    /** Construit un objet Utilisateur à partir du tableau contenant les informations venant d'un formulaire envoyées par l'utilisateur, et retourne l'objet créé
     * @param array $tableauFormulaire
     * @return Utilisateur
     * @throws \Exception
     */
    public static function construireDepuisFormulaire(array $tableauFormulaire): Utilisateur
    {
        return new Utilisateur(
            $tableauFormulaire["login"],
            $tableauFormulaire["nom"],
            $tableauFormulaire["prenom"],
            MotDePasse::hacher($tableauFormulaire["mdp"]),
            isset($tableauFormulaire["estAdmin"]),
            "",
            $tableauFormulaire["email"],
            MotDePasse::genererChaineAleatoire(),
            $tableauFormulaire["avatar"]
        );
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    /** Retourne l'attribut login
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /** Met à jour l'attribut login
     * @param string $login le nouveau login
     * @return void
     */
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    /** Retourne l'attribut nom
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /** Met à jour l'attribut nom
     * @param string $nom le nouveau nom
     * @return void
     */
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getMdp(): string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdpClair)
    {
        $this->mdp = MotDePasse::hacher($mdpClair);
    }

    public function getEstAdmin(): string
    {
        return $this->estAdmin;
    }

    public function setEstAdmin(string $estAdmin): void
    {
        $this->estAdmin = $estAdmin;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmailAValider(): string
    {
        return $this->emailAValider;
    }

    public function setEmailAValider(string $emailAValider): void
    {
        $this->emailAValider = $emailAValider;
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function setNonce(string $nonce): void
    {
        $this->nonce = $nonce;
    }

    /** Héritage de la méthode présente dans AbstractDataObject. Retourne un tableau contenant les attributs de this
     * @return array
     */
    public function exporterEnFormatRequetePreparee(): array
    {
        return array(
            "login_tag" => $this->login,
            "nom_tag" => $this->nom,
            "prenom_tag" => $this->prenom,
            "mdp_tag" => $this->mdp,
            "est_admin_tag" => $this->estAdmin ? "1" : "0",
            "email_tag" => $this->email,
            "nonce_tag" => $this->nonce,
            "email_a_valider_tag" => $this->emailAValider,
            "avatar_tag" => $this->avatar
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            "login" => $this->getLogin(),
            "nom" => $this->getNom(),
            "prenom" => $this->getPrenom(),
            "estAdmin" => $this->getEstAdmin(),
            "email" => $this->getEmail(),
            "emailAValider" => $this->getEmailAValider(),
            "avatar" => $this->getAvatar()
        ];
    }
}
