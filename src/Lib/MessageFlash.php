<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\HTTP\Session;

/**
 * MessageFlash est une classe qui va s'occuper de l'affichage de message sur le site
 */
class MessageFlash
{

    /**
     * @var string Les messages sont enregistrés en session et sont associés à la clé suivante
     */
    private static string $cleFlash = "_messagesFlash";

    /**
     * Méthode qui va ajouter pour un certain type <code>$type</code> un certain message <code>$message</code>
     *
     * @param string $type parmi "success", "info", "warning" ou "danger"
     * @param string $message
     * @return void
     */
    public static function ajouter(string $type, string $message): void
    {
        $session = Session::getInstance();

        $messagesFlash = [];
        if ($session->existeCle(MessageFlash::$cleFlash))
            $messagesFlash = $session->lire(MessageFlash::$cleFlash);

        $messagesFlash[$type][] = $message;
        $session->enregistrer(MessageFlash::$cleFlash, $messagesFlash);
    }

    /**
     * Méthode qui vérifie si pour un certain type <code>$type</code> il existe un message
     *
     * @param string $type parmi "success", "info", "warning" ou "danger"
     * @return bool <code>true</code> si le type contient un message, <code>false</code> sinon
     */
    public static function contientMessage(string $type): bool
    {
        $session = Session::getInstance();
        return $session->existeCle(MessageFlash::$cleFlash) &&
            array_key_exists($type, $session->lire(MessageFlash::$cleFlash)) &&
            !empty($session->lire(MessageFlash::$cleFlash)[$type]);
    }

    /**
     * Méthode qui va lire les messages lier au type <code>$type</code> et retourner la liste des messages lus.
     * S'il n'existe pas de message pour ce type, la méthode renvoie une liste vide
     *
     * @param string $type parmi "success", "info", "warning" ou "danger"
     * @return array
     */
    // Attention : la lecture doit détruire le message
    public static function lireMessages(string $type): array
    {
        $session = Session::getInstance();
        if (!MessageFlash::contientMessage($type))
            return [];

        $messagesFlash = $session->lire(MessageFlash::$cleFlash);
        $messages = $messagesFlash[$type];
        unset($messagesFlash[$type]);
        $session->enregistrer(MessageFlash::$cleFlash, $messagesFlash);

        return $messages;
    }

    /**
     * Méthode qui va lire tous les messages de chaques types puis renvoyer la liste des messages lus.
     *
     * @return array
     */
    public static function lireTousMessages(): array
    {
        $tousMessages = [];
        foreach (["success", "info", "warning", "danger"] as $type) {
            $tousMessages[$type] = MessageFlash::lireMessages($type);
        }
        return $tousMessages;
    }

}