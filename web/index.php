<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../models/users.class.php';
require_once __DIR__.'/../models/itunes-rss.class.php';
require_once __DIR__.'/../models/BR_rounds.class.php';

$app = new Silex\Application();

if(strpos($_SERVER['HTTP_HOST'], "localhost") === false)
  $app['debug'] = false;
else
  $app['debug'] = true;

// Services
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options' => array (
      'driver'    => 'pdo_mysql',
      'host'      => 'localhost',
      'dbname'    => 'silex_lionel_paulus',
      'user'      => 'root',
      'password'  => 'root',
      'charset'   => 'utf8'
  ),
));
$app['db']->setFetchMode(PDO::FETCH_OBJ);
$app->register(new Silex\Provider\SessionServiceProvider());

// Models
$usersModel = new UsersModel($app['db']);
$iTunesRSSModel = new iTunesRSSModel($app['db']);
$BR_roundsModel = new BR_roundsModel($app['db']);

// Routes
$app->get('/', function() use($app,$usersModel){
  require_once __DIR__.'/../controllers/home.php';

  if(!isset($loginUrl)){
    $loginUrl;
  }
  $data = array(
    'loginUrl' => $loginUrl
  );

  return $app['twig']->render('pages/home.twig', $data);
})->bind('home');

$app->get('/blind-test', function() use($app,$iTunesRSSModel,$BR_roundsModel,$usersModel){
  $user = $app['session']->get('user');
  if(empty($user)){
    return $app->redirect($app['url_generator']->generate('home'));
  }else{
    require_once __DIR__.'/../controllers/blind-test.php';

    $data = array(
      'blind_test' => $blind_test,
      'user' => array(
        'first_name' => $user['first_name']
      ),
      'stats' => $stats
    );

    return $app['twig']->render('pages/blind-test.twig', $data);
  }
})->bind('blind-test');

$app->get('/leaderboard', function() use($app,$usersModel){
  $data = array(
    'users' => $usersModel->leaderboard()
  );

  return $app['twig']->render('pages/leaderboard.twig', $data);
})->bind('leaderboard');

$app->get('/api/get_songs', function() use($app,$BR_roundsModel){
  $user = $app['session']->get('user');
  if(empty($user)){
    return $app->redirect($app['url_generator']->generate('home'));
  }else{
    return $BR_roundsModel->get_songs($user["id"]);
  }
})->bind('api/get_songs');

$app->post('/api/user_answer', function() use($app,$BR_roundsModel){
  $user = $app['session']->get('user');
  if(empty($user)){
    return $app->redirect($app['url_generator']->generate('home'));
  }else{
    require_once __DIR__.'/../controllers/api/user_answer.php';
    return 1;
  }
})->bind('api/user_answer');

$app->get('/logout', function() use($app){
  // Unset all cookies
  if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
      $parts = explode('=', $cookie);
      $name = trim($parts[0]);
      setcookie($name, '', time()-1000);
      setcookie($name, '', time()-1000, '/');
    }
  }

  $url = $app['url_generator']->generate('home');
  return $app->redirect($url);
})->bind('logout');

$app->run();