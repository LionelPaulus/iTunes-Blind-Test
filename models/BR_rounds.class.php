<?php

  class BR_roundsModel
  {
    public $db;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function round_answer($user_id)
    {
      $user_id = (int)$user_id;

      $prepare = $this->db->prepare('
      SELECT
        answer_number, timestamp
      FROM
        BT_rounds
      WHERE
        user_id = :user_id
      ORDER BY
        id DESC
      ');
      $prepare->bindValue('user_id',$user_id);
      $prepare->execute();
      return $prepare->fetch();
    }

    public function user_answer($user_answer,$score,$user_id)
    {
      $user_answer = (int)$user_answer;
      $score = (int)$score;
      $user_id = (int)$user_id;

      $prepare = $this->db->prepare('
      UPDATE
        BT_rounds
      SET
        user_answer = :user_answer,
        score = :score
      WHERE
        user_id = :user_id
      ORDER BY
        id DESC
      LIMIT
        1
      ');
      $prepare->bindValue('user_answer',$user_answer);
      $prepare->bindValue('score',$score);
      $prepare->bindValue('user_id',$user_id);
      $prepare->execute();
    }

    public function get_songs($user_id)
    {
      $user_id = (int)$user_id;

      do {
        $query = $this->db->query('SELECT * FROM songs ORDER BY RAND() LIMIT 4');
        $songs = $query->fetchAll();

        $i = 0;
        do {
          $answer_number = rand(0, 3);
          $prepare = $this->db->prepare('SELECT * FROM BT_rounds WHERE song_id = :song_id AND user_id = :user_id');
          $prepare->bindValue('song_id', $songs[$answer_number]->id);
          $prepare->bindValue('user_id', $user_id);
          $prepare->execute();
          $song_already_played = $prepare->fetch();
          $i++;
        } while($song_already_played != false || $i == 4);
      } while ($song_already_played != false);

      $timestamp = time();

      $prepare = $this->db->prepare('INSERT INTO BT_rounds (answer_number, song_id, song_title, user_id, timestamp) VALUES (:answer_number, :song_id, :song_title, :user_id, :timestamp)');
      $prepare->bindValue('answer_number', $answer_number);
      $prepare->bindValue('song_id', $songs[$answer_number]->id);
      $prepare->bindValue('song_title', $songs[$answer_number]->title);
      $prepare->bindValue('user_id', $user_id);
      $prepare->bindValue('timestamp', $timestamp);
      $prepare->execute();

      $songs["preview_url"] = $songs[$answer_number]->preview_url;

      // Delete preview_url's
      for ($i=0; $i < 4; $i++) {
        unset($songs[$i]->preview_url);
      }

      return json_encode($songs);
    }

    public function get_game_stats($user_id){
      $user_id = (int)$user_id;

      $prepare = $this->db->prepare('
        SELECT
          *
        FROM
          BT_rounds
        WHERE
          user_id = :user_id
      ');
      $prepare->bindValue('user_id',$user_id);
      $prepare->execute();
      $answers = $prepare->fetchAll();

      $prepare = $this->db->prepare('
        SELECT
          SUM(score) AS score
        FROM
          BT_rounds
        WHERE
          user_id = :user_id
      ');
      $prepare->bindValue('user_id',$user_id);
      $prepare->execute();
      $total_score = $prepare->fetch();
      $stats['answers'] = $answers;
      $stats['score']['total_score'] = $total_score->score;

      return $stats;
    }
    public function clear_table($user_id){
      if($user_id > 1){ // SECURITY
        $prepare = $this->db->prepare('
          DELETE FROM
            BT_rounds
          WHERE
            user_id = :user_id
        ');
        $prepare->bindValue('user_id',$user_id);
        $prepare->execute();
      }
    }
  }