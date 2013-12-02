<?php

namespace FabriceKabongo\Util\Ranking;
/**
 * Cette classe permet de faire les différentes opérations en rapport avec la méthode
 * de classement Elo.
 * 
 * Cette classe est inspiré de l'article si clair, publié à cette adresse:
 * http://www.lemondedudev.com/2011/09/30/classer-des-personnes-calculer-des-cotes-algorithme-elo/
 *
 * @author Fabrice Kabongo <fabrice.k.kabongo at gmail.com>
 */
class EloRanking 
{
    
    /**
     * Cette methode permet de renvoyer les estimations de victoire des deux protagonistes.
     * 
     * @param double $rankingA les points du premier candidat
     * @param double $rankingB les points du deuxieme candidat
     * 
     * @return array Les estimations des deux protagonistes.<br>
     *  indice A: l'estimation du permier protagoniste <br>
     * indeice B: l'estimation du deuxieme protagoniste.
     */
    protected static function getEstimations($rankingA,$rankingB){
        $estimationA = 1/(1+(pow(10,($rankingB - $rankingA))));
        $estimationB = 1/(1+(pow(10,($rankingB - $rankingA))));
        
        return array('A'=> $estimationA,'B'=>$estimationB);
    }
    
    /**
     * Renvoi la constante à appliquer à un joueur selon ses points
     * 
     * @param double $ranking
     * @return int la constante à appliquer au joueur selon ses points
     */
    protected static function getConstant($ranking){
        if($ranking<2000)
            return 64;
        if($ranking<2401)
            return 32;
        return 16;
    }
    
    /**
     * Renvoi les nouveaux points des joueurs apres un combat
     * 
     * @param double $rankingA les points du premier joueur avant le combat.
     * @param double $rankingB les points du deuxieme joueur avant le combat.
     * @param boolean $victoryA si c'est le premier joueurs qui a gagner ou pas.
     * @return array les nouveaux points des protagonistes. <br>
     * A: les points du premier joueur<br>
     * B: les points du deuxieme joueur
     */
    public static function getNewRanking($rankingA,$rankingB,$victoryA=true){
        $estimations = self::getEstimations($rankingA, $rankingB);
        $newRankings['A'] = $rankingA + (self::getConstant($rankingA)*(($victoryA?1:0)-$estimations['A']));
        $newRankings['B'] = $rankingB + (self::getConstant($rankingB)*(($victoryA?0:1)-$estimations['B']));
        
        return $newRankings;
    }
}

?>
