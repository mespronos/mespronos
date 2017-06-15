<?php

$json_source = file_get_contents('./ligue1.json');

$json_data = json_decode($json_source);

echo '<pre>';
$nb_game = 0;
$journee = 0;
$league_name = 'Ligue 1 2017/2018';
$sport = 'Football';
$status = 'active';
$betting_type = 'score';

$games = $json_data->VCALENDAR[0]->VEVENT;

$output_games = [];

foreach ($games as $game) {
  $nb_game++;
  if ($nb_game % 10 == 1) {
    $journee++;
    $output_games[$journee] = [];
  }
  $game->SUMMARY = str_replace(' - ', ' | ', $game->SUMMARY);
  $dt = new DateTime($game->DTSTART);

  $dt->setTimeZone(new DateTimezone('Europe/Berlin'));
  $game->date = $dt->format('c');
  unset($game->URL);
  unset($game->DTEND);
  unset($game->DTEND);
  unset($game->DESCRIPTION);
  unset($game->LOCATION);
  unset($game->TRANSP);
  unset($game->DTSTART);

  $output_games[$journee][] = $game;
}

//$trans = \Drupal::service('transliteration');

$fp = fopen('../'.str_replace('/', '-', $league_name).'.yaml', 'w+');
ob_start();
$ligue_content = "league :\n  name : '$league_name'\n  sport : '$sport'\n  status : '$status'\n  classement : true\n  betting_type : '$betting_type'\n  days:\n";
fwrite($fp, $ligue_content);

foreach ($output_games as $num_journee => $matchs) {
  ob_start();
  echo "    - number : $num_journee\n      day_default_date : ".$matchs[0]->date."\n      games :\n";
  foreach ($matchs as $match) {
    $date = DateTime::createFromFormat('Y-m-d\TH:i:sP', $match->date);
    $date->setTime(20,0);
    echo "        - game : ".$match->SUMMARY."\n          game_date : ".$date->format('Y-m-d\TH:i:sP')."\n";
  }
  $journee_content = ob_get_contents();
  fwrite($fp, $journee_content);
}
