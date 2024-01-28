<?php

namespace App\PlusCourtChemin\Lib;

enum TypeRoute: string
{
    case AUTOROUTE = "Autoroute";
    case NATIONALE = "Nationale";
    case DEPARTEMENTALE = "Départementale";
    case NULl = "Sans objet";
}
