<?php

use Symfony\Component\HttpFoundation\Request;

$app = require_once __DIR__ . '/app/bootstrap.php';
$app['chemin_public'] = 'http://localhost/facemash/web/';
$app->get('/', function(Request $requete) use ($app) {
            if ($app['session']->has('jeuxencours') && !is_null($requete->get('choix', null)) ) {
                switch ($requete->get('choix', null)) {
                    case 'gauche':
                        //on charge les données de la base de donnée
                        $sql = "SELECT 
                                    a.vote AS vote_1, b.vote AS vote_2,a.photo AS photo_1
                                   FROM photos a, photos b
                                   WHERE a.id = :id1
                                   AND b.id = :id2;";
                        $stmt = $app['db']->prepare($sql);
                        $stmt->bindValue(':id1', $app['session']->get('gauche'));
                        $stmt->bindValue(':id2', $app['session']->get('droite'));
                        $stmt->execute();
                        $donnee = $stmt->fetch();
                        //fin du chargement
                        //on mets à jour les votes
                        $voteGauche_actuelle = $donnee['vote_1'];
                        $voteDroite_actuelle = $donnee['vote_2'];
                        $voteGauche = $voteGauche_actuelle + ($voteDroite_actuelle / (($voteGauche_actuelle==0?1:0)+$voteGauche_actuelle));
                        $voteDroite = $voteDroite_actuelle - ($voteDroite_actuelle / (($voteGauche_actuelle==0?1:0)+$voteGauche_actuelle));
                        $app['db']->update('photos', array('vote' => $voteGauche), array('id'=>$app['session']->get('gauche')));
                        $app['db']->update('photos', array('vote' => $voteDroite), array('id'=>$app['session']->get('droite')));
                        //on fini
                        //on recupere la nouvelle photo de droite
                        $sql1 = 'SELECT * FROM photos WHERE id <> :id AND id<> :id1 ORDER BY rand()  LIMIT 0,1;';
                        $stmt = $app['db']->prepare($sql1);
                        $stmt->bindValue(':id', $app['session']->get('gauche'));
                        $stmt->bindValue(':id1', $app['session']->get('droite'));
                        $stmt->execute();
                        $droite = $stmt->fetch();
                        $app['session']->set('droite', $droite['id']);
                        //fin de la recuperation
                        return $app['twig']->render('index.html.twig', array('gauche' => $donnee['photo_1'], 'droite' => $droite['photo']));
                        break;
                    case 'droite':
                        //on charge les données de la base de donnée
                        $sql = "SELECT 
                                a.vote AS vote_1, b.vote AS vote_2,b.photo AS photo_2
                               FROM photos a, photos b
                               WHERE a.id = :id1
                               AND b.id = :id2;";
                        $stmt = $app['db']->prepare($sql);
                        $stmt->bindValue(':id1', $app['session']->get('gauche'));
                        $stmt->bindValue(':id2', $app['session']->get('droite'));
                        $stmt->execute();
                        $donnee = $stmt->fetch();
                        //fin du chargement
                        //on mets à jour les votes
                        $voteGauche_actuelle = $donnee['vote_1'];
                        $voteDroite_actuelle = $donnee['vote_2'];
                        $voteGauche = $voteGauche_actuelle - ($voteGauche_actuelle / (($voteDroite_actuelle==0?1:0)+$voteDroite_actuelle));
                        $voteDroite = $voteDroite_actuelle + ($voteGauche_actuelle / (($voteDroite_actuelle==0?1:0)+$voteDroite_actuelle));
                        $app['db']->update('photos', array('vote' => $voteGauche), array('id'=>$app['session']->get('gauche')));
                        $app['db']->update('photos', array('vote' => $voteDroite), array('id'=>$app['session']->get('droite')));
                        //on fini
                        //on recupere la nouvelle photo de droite
                        $sql1 = 'SELECT * FROM photos WHERE id <> :id AND id<> :id1 ORDER BY rand()  LIMIT 0,1;';
                        $stmt = $app['db']->prepare($sql1);
                        $stmt->bindValue(':id', $app['session']->get('gauche'));
                        $stmt->bindValue(':id1', $app['session']->get('droite'));
                        $stmt->execute();
                        $gauche = $stmt->fetch();
                        $app['session']->set('gauche', $gauche['id']);
                        //fin de la recuperation
                        return $app['twig']->render('index.html.twig', array('gauche' => $gauche['photo'], 'droite' => $donnee['photo_2']));
                        break;
                }
            } else {
                $app['session']->set('jeuxencours', true);
                //on recupere la photo de droite
                $sql = 'SELECT * FROM photos ORDER BY rand() LIMIT 0,1;';
                $stmt = $app['db']->query($sql);
                $gauche = $stmt->fetch();

                //on recupere la photo de gauche
                $sql1 = 'SELECT * FROM photos WHERE id <> :id ORDER BY rand() LIMIT 0,1;';
                $stmt = $app['db']->prepare($sql1);
                $stmt->bindValue(':id', $gauche['id']);
                $stmt->execute();
                $droite = $stmt->fetch();
                $app['session']->set('gauche', $gauche['id']);
                $app['session']->set('droite', $droite['id']);
                return $app['twig']->render('index.html.twig', array('gauche' => $gauche['photo'], 'droite' => $droite['photo']));
            }
        })->assert('_method','POST|GET')->bind('facemash');
$app->match('/uploader', function(Request $requete) use ($app) {
            //on cree le formulaire
            $form = $app['form.factory']->createBuilder('form', array('file' => null))
                    ->add('file', 'file', array('label' => 'Photo', 'required' => true))
                    ->getForm();
            if ($requete->isMethod('POST')) {#si le formulaire a été envoyé
                $form->bind($requete); #on lie le formulaire à la requete

                if ($form->isValid()) {#si le formulaire est valide
                    $fichiers = $requete->files->get($form->getName()); #on recupere les images envoyé par le formulaire
                    if (!is_null($fichiers['file'])) {#si l'image a bien été chargé
                        $nouveauNom = '';
                        do {#on genere un nom de fichier unique
                            $nouveauNom = uniqid(uniqid('', true), true) . '.' . $fichiers['file']->guessExtension();
                        } while (file_exists(__DIR__ . '/web/images/' . $nouveauNom));

                        $fichiers['file']->move(__DIR__ . '/web/images/', $nouveauNom); #on deplace le fichier vers le dossier des images
                        $app['db']->insert('photos', array('photo' => $nouveauNom, 'vote' => 1)); #on insere le chemin de l'image dans bdd
                    }
                }
            }
            return $app['twig']->render('upload.html.twig', array('form' => $form->createView()));
        })->assert('_method', 'POST|GET')->bind('upload');
        
/*
 * Renvoi le top 10 des images
 */        
$app->get('/stat', function() use ($app){
    $sql = 'SELECT photo,vote FROM photos ORDER BY vote desc LIMIT 0,10;';
    $stmt = $app['db']->query($sql);
    $statistique = $stmt->fetchAll();
    return $app['twig']->render('statistique.html.twig',array('statistiques'=>$statistique));
})->bind('statistique');



$app['debug'] = true;
$app->run();
