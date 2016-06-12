<?php

  class iTunesRSSModel
  {
    public $db;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function update()
    {
      // Get data
      $xml = simplexml_load_string(file_get_contents("https://itunes.apple.com/fr/rss/topsongs/limit=50/explicit=true/xml"));
      $json = json_encode($xml);
      $array = json_decode($json,TRUE);

      // Edit data
      $i = 0;
      while($i != 50){
        preg_match('/i=[0-9]*/', $array["entry"][$i]["id"], $matches);
        $new_array[$i]["id"] = substr($matches[0], 2);

        $new_array[$i]["title"] =
          preg_replace("/  +/", " ",
          preg_replace("/\([^)]*\)/", "",
          preg_replace("/\[[^\]]*\]/", "", $array["entry"][$i]["title"])));

        $new_array[$i]["preview_url"] = $array["entry"][$i]["link"][1]["@attributes"]["href"];
        $i++;
      }

      // Save data in database
      $query = $this->db->query('TRUNCATE songs');
      foreach ($new_array as $_new_array) {
        $prepare = $this->db->prepare('INSERT INTO songs(id, title, preview_url) VALUES (:id, :title, :preview_url)');
        $prepare->bindValue('id', $_new_array['id']);
        $prepare->bindValue('title', $_new_array['title']);
        $prepare->bindValue('preview_url', $_new_array['preview_url']);
        $prepare->execute();
      }
    }
  }