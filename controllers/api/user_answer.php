<?php
  $user_answer = (int)$_POST["user_answer"];
  $user_id = $user["id"];

  $result = $BR_roundsModel->round_answer($user_id);

  if($user_answer == $result->answer_number){
    $score = 30 - (time() - $result->timestamp);
    if($score < 0){
      $score = 0;
    }
  }else{
    $score = 0;
  }

  // Add user answer and score to database
  echo $BR_roundsModel->user_answer($user_answer,$score,$user_id);