<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Configuration\Configuration;
use App\PlusCourtChemin\Modele\DataObject\Utilisateur;
use App\PlusCourtChemin\Modele\Repository\UtilisateurRepository;

/**
 * VerificationEmail est une classe qui va permettre à l'utilisateur de vérifier son email
 */
class VerificationEmail
{

    /**
     * Méthode qui va envoyer à l'utlisateur <code>$utlisateur</code> un email de validation
     *
     * @param Utilisateur $utilisateur
     * @return void
     */
    public static function envoiEmailValidation(Utilisateur $utilisateur): void
    {
        $loginURL = rawurlencode($utilisateur->getLogin());
        $nonceURL = rawurlencode($utilisateur->getNonce());
        $absoluteURL = Configuration::getAbsoluteURL();
        $lienValidationEmail = "$absoluteURL?action=validerEmail&controleur=utilisateur&login=$loginURL&nonce=$nonceURL";
        $corpsEmail = "<a href=\"$lienValidationEmail\">Validation</a>";

        // Temporairement avant d'envoyer un vrai mail
        MessageFlash::ajouter("success", $corpsEmail);

        // mail(
        //     $utilisateur->getEmailAValider(),
        //     "Validation de votre adresse mail",
        //     "<a href=\"$lienValidationEmail\">Validation</a>"
        // );
    }

    /**
     * Méthode qui va traiter la validation de l'email par l'utilisateur
     *
     * @param $login
     * @param $nonce
     * @return bool
     */
    public static function traiterEmailValidation($login, $nonce): bool
    {
        $utilisateurRepository = new UtilisateurRepository();
        /** @var Utilisateur $utilisateur */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($login);

        if ($utilisateur === null)
            return false;

        if ($utilisateur->getNonce() !== $nonce) {
            return false;
        }

        $utilisateur->setEmail($utilisateur->getEmailAValider());
        $utilisateur->setEmailAValider("");
        $utilisateur->setNonce("");

        $utilisateurRepository->mettreAJour($utilisateur);
        return true;
    }

    /**
     * Méthode qui va vérifier si l'utilisateur <code>$utilisateur</code> à validé son email
     *
     * @param Utilisateur $utilisateur
     * @return bool <code>true</code> si l'email est valide, <code>false</code> sinon
     */
    public static function aValideEmail(Utilisateur $utilisateur): bool
    {
        return $utilisateur->getEmail() !== "";
    }
}
