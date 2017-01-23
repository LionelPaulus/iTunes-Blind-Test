<?php
  // Create last_update.txt if does not exist
  if(!file_exists('last_update.txt')){
    file_put_contents('last_update.txt', '');
  }

  // Update songs
  if(time() - file_get_contents('last_update.txt') > 86400){
    $iTunesRSSModel->update();
    file_put_contents('last_update.txt', time());
  }

  $user = $app['session']->get('user');
  $stats = "";
  if(isset($_GET['start'])){
    $blind_test = "start";
    $BR_roundsModel->clear_table($user['id']);
  }elseif(isset($_GET['end'])){
    $blind_test = "end";

    $stats = $BR_roundsModel->get_game_stats($user['id']);
    $user_profile = $usersModel->get($user['id']);
    $stats['score']['high_score'] = $user_profile->high_score;

    if($stats['score']['total_score'] > $stats['score']['high_score']){
      $usersModel->new_high_score($stats['score']['total_score'], $user['id']);
    }
  }else{
    $blind_test = "intro";
  }
