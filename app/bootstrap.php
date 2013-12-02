<?php

require_once __DIR__.'/../vendor/autoload.php';


use Silex\Application\UrlGeneratorTrait;
use Silex\Application\TwigTrait;
use Silex\Application\FormTrait;
use Musal\Util\MusalTwigExtenxion;


$app = new Silex\Application();
//on enregistre le gestionnaire de session
$app['chemin_public'] = '/rdcontact/web/';
//on enregistre TWIG, le gestionnaire de template
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../twig',
    //'twig.options' => array('cache' => __DIR__.'/cache/twig'),
));
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'dbname' => 'facemash',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ),
));
//on enregistre le gestionnaire de generation d'url
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());


//on enregistre le validateur de formulaire et d'objet
$app->register(new Silex\Provider\ValidatorServiceProvider());

//on enregistre le gestionnaire de formulaire
$app->register(new Silex\Provider\FormServiceProvider());

//on enregistre le gestionnaire de traduction
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => 'fr',
));
$app['twig']->addFilter(new Twig_SimpleFilter('truncate', function($string, $size) {
    if(strlen($string) < $size)
        return $string;
    else
        return array_shift(str_split($string, $size)) . "...";
}));



/*$app->error(function(\Exception $e, $code) use($app)  {
    switch ($code) 
    {
        case 404:
            $message = $e->getMessage();
            $message = (empty($message))?"La ressource que vous recherchez n'existe pas ou a ete deplace.":$message;
            break;
        case 401:
            $message = $e->getMessage();
            $message = (empty($message))?"Vous n'etes pas identifier":$message;
            break;
        case 403:
            $message = $e->getMessage();
            $message = (empty($message))?"Vous n'etes pas autorise a effectuer cette action":$message;
            break;
        case 405:
            $message = $e->getMessage();
            $message = (empty($message))?"La methode http utilise n'est pas prise en compte.":$message;
            break;
        case 500:
            $message = $e->getMessage();
            $message = (empty($message))?"Le systeme a rencontre une erreur inatendue.":$message;
            break;
        case 501:
            $message = $e->getMessage();
            $message = (empty($message))?"Le systeme est dans l'incapacite de repondre a votre requete suite a une surcharge.":$message;
            break;
        case 408:
            $message = $e->getMessage();
            $message = (empty($message))?"La requete a pris trop de temps":$message;
            break;
        default:
            $message = "Une erreur a survennu lors du traitement de votre requete.";
    }

    return $app['twig']->render('error.html.twig',array('message'=>$message,"code"=>$code));
});*/

$app->register(new Silex\Provider\SessionServiceProvider(),array(
        "name" => "rdcontact",
        "cookie_lifetime" =>604800,
    ));
mb_internal_encoding("UTF-8");

return $app;
?>
