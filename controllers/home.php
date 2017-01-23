<?php
  if(!session_id()) {
    session_start();
  }

  $fb = new Facebook\Facebook([
    'app_id' => '****',
    'app_secret' => '****',
    'default_graph_version' => 'v2.6',
  ]);
  $helper = $fb->getRedirectLoginHelper();
  $permissions = ['email']; // Optional

  try {
    if (isset($_SESSION['facebook_access_token'])) {
        $accessToken = $_SESSION['facebook_access_token'];
    } else {
        $accessToken = $helper->getAccessToken();
    }
  } catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: '.$e->getMessage();
    exit;
  } catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: '.$e->getMessage();
    exit;
  }

  if (isset($accessToken)) {
    if (isset($_SESSION['facebook_access_token'])) {
      $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    } else {
      // Getting short-lived access token
      $_SESSION['facebook_access_token'] = (string) $accessToken;
      // OAuth 2.0 client handler
      $oAuth2Client = $fb->getOAuth2Client();
      // Exchanges a short-lived access token for a long-lived one
      $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
      $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
      // Setting default access token to be used in script
      $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }
    // Redirect the user back to the same page if it has "code" GET variable
    if (isset($_GET['code'])) {
      header('Location: ./');
    }
    // Getting basic info about user
    try {
      $profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
      $profile = $profile_request->getGraphNode()->asArray();
      $profile_picture_request = $fb->get('/me/picture?type=large&height=200&width=200&redirect=false');
      $picture = $profile_picture_request->getGraphNode()->asArray();
      $profile["picture"] = $picture['url'];
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: '.$e->getMessage();
      session_destroy();
      // 4edirecting user back to app login page
      header('Location: ./');
      exit;
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: '.$e->getMessage();
      exit;
    }

    $result = $usersModel->get($profile['id']);

    if (empty($result)) {
      $usersModel->add($profile);
    }else{
      $usersModel->update($result->id, $profile);
    }

    // Sessions
    $app['session']->set('user', array(
      'first_name' => $profile['first_name'],
      'last_name' => $profile['last_name'],
      'id' => $profile['id']
    ));

    $user = $app['session']->get('user');
    if (isset($user)) {
      // return $app->redirect($app['url_generator']->generate('blind-test')); DOESN'T WORK
      header('Location: '.$app['url_generator']->generate('blind-test'));
      die();
    }
  } else {
    // Redirection link
    $loginUrl = $helper->getLoginUrl("http://".$_SERVER['HTTP_HOST'].$app['url_generator']->generate('home'), $permissions);
  }
