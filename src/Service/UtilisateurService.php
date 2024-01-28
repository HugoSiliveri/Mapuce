<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Lib\ConnexionUtilisateurInterface;
use App\PlusCourtChemin\Lib\MotDePasse;
use App\PlusCourtChemin\Lib\VerificationEmail;
use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\Utilisateur;
use App\PlusCourtChemin\Modele\Repository\AbstractRepositoryInterface;
use App\PlusCourtChemin\Service\Exception\ServiceException;

class UtilisateurService implements UtilisateurServiceInterface
{
    public function __construct(
        private readonly AbstractRepositoryInterface   $utilisateurRepository,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateur)
    {
    }


    /**
     * Fonction qui permet de récupérer tous les utilisateurs présents dans la base de donnée
     * @return array
     */
    public function recupererUtilisateurs(): array
    {
        return $this->utilisateurRepository->recuperer();
    }

    /**
     * @param $login
     * @return AbstractDataObject
     * @throws ServiceException
     */
    public function recupererUtilisateur($login)
    {
        if (!isset($login)) {
            throw new ServiceException("Il manque le login !");
        } else {
            $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
            if ($utilisateur == null) {
                throw new ServiceException("Login inconnu !");
            } else {
                return $utilisateur;
            }
        }
    }

    /**
     * @param $login
     * @return void
     * @throws ServiceException
     */
    public function supprimerUtilisateur($login): void
    {
        if (!isset($login)) {
            throw new ServiceException("Il manque le login !");
        } else {
            $ancienAvatar = $this::recupererUtilisateur($login)->getAvatar();
            unlink(__DIR__ . "/../../ressources/img/utilisateurs/$ancienAvatar");

            $deleteSuccessful = $this->utilisateurRepository->supprimer($login);
            if (!$deleteSuccessful) {
                throw new ServiceException("Login inconnu !");
            }
        }
    }


    /**
     * @param $login
     * @param $mdp
     * @param $mdp2
     * @param $prenom
     * @param $nom
     * @param $email
     * @param $avatar
     * @return void
     * @throws ServiceException
     */
    public function creerUtilisateur($login, $mdp, $mdp2, $prenom, $nom, $email, $avatar)
    {
        if (!isset($login) || !isset($mdp) || !isset($mdp2) || !isset($prenom) || !isset($nom) || !isset($email) || !isset($avatar)) {
            throw new ServiceException("Login, nom, prenom, mot de passe, adresse mail ou avatar manquant !");
        } else {
            if (strcmp($mdp, $mdp2) !== 0) {
                throw new ServiceException("Mots de passe distincts");
            } else {
                if (!$this->connexionUtilisateur->estAdministrateur()) {
                    unset($_REQUEST["estAdmin"]);
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new ServiceException("Email non valide");
                } else {

                    $explosion = explode('.', $avatar['name']);
                    $fileExtension = end($explosion);
                    if (!in_array($fileExtension, ['png', 'jpg', 'jpeg'])) {
                        throw new ServiceException("La photo de profil n'est pas au bon format!");
                    }

                    $pictureName = uniqid() . '.' . $fileExtension;
                    $from = $avatar['tmp_name'];
                    $to = __DIR__ . "/../../ressources/img/utilisateurs/$pictureName";
                    move_uploaded_file($from, $to);

                    $utilisateur = Utilisateur::construireDepuisFormulaire([
                        'login' => $login,
                        'prenom' => $prenom,
                        'nom' => $nom,
                        'mdp' => $mdp,
                        'mdp2' => $mdp2,
                        'email' => $email,
                        'avatar' => $pictureName
                    ]);

                    //VerificationEmail::envoiEmailValidation($utilisateur);

                    $succesSauvegarde = $this->utilisateurRepository->ajouter($utilisateur);

                    if (!$succesSauvegarde) {
                        throw new ServiceException("Login existant !");
                    }
                }
            }
        }
    }


    /**
     * @param $login
     * @return AbstractDataObject
     * @throws ServiceException
     */
    public function droitsModifsUtilisateurs($login)
    {
        if (!isset($login)) {
            throw new ServiceException("Il manque le login !");
        } else {
            $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
            if ($utilisateur === null) {
                throw new ServiceException("Login inconnu !");
            } else {
                if (!$this->connexionUtilisateur->estUtilisateur($login) && !$this->connexionUtilisateur->estAdministrateur()) {
                    throw new ServiceException("La mise à jour n'est possible que pour l'utilisateur connecté ou un administrateur !");
                } else {
                    return $utilisateur;
                }
            }
        }
    }


    /**
     * @param $login
     * @param $prenom
     * @param $nom
     * @param $mdp
     * @param $mdp2
     * @param $mdpAncien
     * @param $email
     * @param $estAdmin
     * @param $avatar
     * @return void
     * @throws ServiceException
     */
    public function modifierUtilisateur($login, $prenom, $nom, $mdp, $mdp2, $mdpAncien, $email, $estAdmin, $avatar)
    {
        if (!isset($login) || !isset($prenom) || !isset($nom)
            || !isset($mdp) || !isset($mdp2) || !isset($mdpAncien)
            || !isset($email) || !isset($avatar)) {
            throw new ServiceException("Login, nom, prenom, email, avatar ou mot de passe manquant !");
        } else {
            if (strcmp($mdp, $mdp2) !== 0) {
                throw new ServiceException("Mots de passe distincts !");
            }
            if (!$this->connexionUtilisateur->estUtilisateur($login) && !$this->connexionUtilisateur->estAdministrateur()) {
                throw new ServiceException("La mise à jour n'est possible que pour l'utilisateur connecté ou un administrateur !");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ServiceException("Email non valide");
            }
            $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
            if ($utilisateur == null) {
                throw new ServiceException("Login inconnu !");
            }
            if (!MotDePasse::verifier($mdpAncien, $utilisateur->getMdp())) {
                throw new ServiceException("Ancien mot de passe erroné !");
            }

            $explosion = explode('.', $avatar['name']);
            $fileExtension = end($explosion);
            if (!in_array($fileExtension, ['png', 'jpg', 'jpeg'])) {
                throw new ServiceException("La photo de profil n'est pas au bon format!");
            }

            $ancienAvatar = $utilisateur->getAvatar();

            $pictureName = uniqid() . '.' . $fileExtension;
            $from = $avatar['tmp_name'];
            $to = __DIR__ . "/../../ressources/img/utilisateurs/$pictureName";
            move_uploaded_file($from, $to);
            unlink(__DIR__ . "/../../ressources/img/utilisateurs/$ancienAvatar");

            $utilisateur->setNom($nom);
            $utilisateur->setPrenom($prenom);
            $utilisateur->setMdp($mdp);
            $utilisateur->setAvatar($pictureName);

            if ($this->connexionUtilisateur->estAdministrateur()) {
                $utilisateur->setEstAdmin(isset($estAdmin));
            }

            if (strcmp($email, $utilisateur->getEmail()) !== 0) {
                $utilisateur->setEmailAValider($_REQUEST["email"]);
                $utilisateur->setNonce(MotDePasse::genererChaineAleatoire());

                VerificationEmail::envoiEmailValidation($utilisateur);
            }

            $this->utilisateurRepository->mettreAJour($utilisateur);
        }
    }

    /**
     * @param $login
     * @param $mdp
     * @return void
     * @throws ServiceException
     */
    public function droitConnexion($login, $mdp)
    {
        if (!isset($login) || !isset($mdp)) {
            throw new ServiceException("Login ou mot de passe manquant !");
        } else {
            $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
            if ($utilisateur == null) {
                throw new ServiceException("Login inconnu !");
            }
            if (!MotDePasse::verifier($mdp, $utilisateur->getMdp())) {
                throw new ServiceException("Mot de passe incorrect !");
            }

//            if (!VerificationEmail::aValideEmail($utilisateur)){
//                throw new ServiceException("Adresse email non validée !","warning");
//            }
            return $login;
        }
    }

//    /**
//     * @return void
//     * @throws ServiceException
//     */
//    public function deconnexion(){
//        if (!$this->connexionUtilisateur->estConnecte()){
//            throw new ServiceException("Utilisateur non connecté !");
//        }
//        ConnexionUtilisateurSession::deconnecter();
//    }


    public function valideEmail($login, $nonce)
    {
        if (!isset($login) || !isset($nonce)) {
            throw new ServiceException("Login ou nonce manquant !");
        } else {
            $succesValidation = VerificationEmail::traiterEmailValidation($login, $nonce);
            if (!$succesValidation) {
                throw new ServiceException("Email de validation incorrect !");
            }
        }
    }


    /**
     * @param $data
     * @return bool
     * @throws ServiceException
     */
    public function ajouterTrajetUtilisateur($data): bool
    {
        if (!isset($data)) {
            throw new ServiceException("données manquantes !");
        } else {
            $login = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
            if (!$login) {
                throw new ServiceException("Utilisateur non connecté !");
            }
            return $this->utilisateurRepository->ajouterJson("trajets", $data, '{trajets,-1}', $login);
        }
    }

    /**
     * @return array
     * @throws ServiceException
     */
    public function recupererTrajetsUtilisateur(): array
    {
        $login = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
        if (!$login) {
            throw new ServiceException("Utilisateur non connecté !");
        }
        return $this->utilisateurRepository->recupererTrajetsUtilisateur($login);
    }

    /**
     * @param $id
     * @return mixed
     * @throws ServiceException
     */
    public function supprimerTrajetUtilisateur($id)
    {
        if (!isset($id)) {
            throw new ServiceException("id manquant !");
        } else {
            $login = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
            if (!$login) {
                throw new ServiceException("Utilisateur non connecté !");
            }
            return $this->utilisateurRepository->supprimerTrajetUtilisateur($login, $id);
        }
    }
}