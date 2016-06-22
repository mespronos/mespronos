<?php

$json_source = file_get_contents('./ligue1.json');

$json_data = json_decode($json_source);

echo '<pre>';
$nb_game = 0;
$journee = 0;
$league_name = 'Ligue 1 2016/2017';
$sport = 'Football';
$status = 'active';
$betting_type = 'score';

$games = $json_data->VCALENDAR[0]->VEVENT;

$output_games = [];

foreach ($games as $game) {
  $nb_game++;
  if($nb_game%10 == 1) {
    $journee++;
    $output_games[$journee] = [];
  }
  $game->SUMMARY = str_replace(' - ',' | ',$game->SUMMARY);
  $dt = new DateTime($game->DTSTART);

  $dt->setTimeZone( new DateTimezone('Europe/Berlin') );
  $game->date = $dt->format('c');

  $output_games[$journee][] = $game;
}
var_dump($output_games);