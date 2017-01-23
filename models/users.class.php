<?php

class UsersModel
{
  public $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function get($id)
  {
    $id = (int)$id;

    $prepare = $this->db->prepare('
      SELECT
        *
      FROM
        users
      WHERE
        id = :id
    ');
    $prepare->bindValue('id',$id);
    $prepare->execute();

    return $prepare->fetch();
  }

  public function add($profile)
  {
    $prepare = $this->db->prepare('INSERT INTO users(id, first_name, last_name, email, picture) VALUES (:id, :first_name, :last_name, :email, :picture)');
    $prepare->bindValue('id', $profile['id']);
    $prepare->bindValue('first_name', $profile['first_name']);
    $prepare->bindValue('last_name', $profile['last_name']);
    $prepare->bindValue('email', $profile['email']);
    $prepare->bindValue('picture', $profile['picture']);

    $prepare->execute();
  }

  public function update($id, $profile)
  {
    $prepare = $this->db->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, picture = :picture WHERE id = :id');
    $prepare->bindValue('id', $id);
    $prepare->bindValue('first_name', $profile['first_name']);
    $prepare->bindValue('last_name', $profile['last_name']);
    $prepare->bindValue('email', $profile['email']);
    $prepare->bindValue('picture', $profile['picture']);

    $prepare->execute();
  }

  public function leaderboard(){
    $query = $this->db->query('
      SELECT
        *
      FROM
        users
      ORDER BY
        high_score DESC
      LIMIT
        10
    ');
    $users = $query->fetchAll();

    return $users;
  }
  public function new_high_score($score,$user_id){
    $score = (int)$score;
    $user_id = (int)$user_id;

    $prepare = $this->db->prepare('
      UPDATE
        users
      SET
        high_score = :high_score
      WHERE
        id = :id
    ');
    $prepare->bindValue('high_score',$score);
    $prepare->bindValue('id',$user_id);
    $prepare->execute();
  }
}
