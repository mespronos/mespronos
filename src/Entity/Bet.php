<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Bet.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\MPNEntityInterface;

/**
 * Defines the Bet entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "bet",
 *   label = @Translation("Bet entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\BetListController",
 *     "views_data" = "Drupal\mespronos\Entity\ViewsData\BetViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\BetForm",
 *       "add" = "Drupal\mespronos\Entity\Form\BetForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\BetForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\MPNDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\ControlHandler\BetAccessControlHandler",
 *   },
 *   base_table = "mespronos__bet",
 *   admin_permission = "administer Bet entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/bet/{bet}",
 *     "edit-form" = "/admin/bet/{bet}/edit",
 *     "delete-form" = "/admin/bet/{bet}/delete"
 *   }
 * )
 */
class Bet extends MPNContentEntityBase implements MPNEntityInterface {

  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  public static function getUserBetsForGames($games_ids, \Drupal\user\Entity\User $user){
    $bet_storage = \Drupal::entityManager()->getStorage('bet');
    $query = \Drupal::entityQuery('bet');
    $query->condition('game',$games_ids,'IN');
    $query->condition('better',$user->id());
    $ids = $query->execute();
    $bets = $bet_storage->loadMultiple($ids);
    $bets_keyed_as_game = [];
    foreach($bets as $b) {
      $bets_keyed_as_game[$b->getGame()] = $b;
    }
    return $bets_keyed_as_game;
  }

  public function labelBet() {
    $game = $this->getGame(true);
    $day = $game->getDay();
    $league = $day->getLeague();
    if($league->getBettingType(true) == 'score') {
      return t('@t1 - @t2',array('@t1'=> $this->getScoreTeam1(),'@t2'=> $this->getScoreTeam2()));
    }
    else {
      switch($this->getScoreTeam1() - $this->getScoreTeam2()) {
        case 0 : return t('Draw');
        case 1 : return $game->getTeam1()->label();
        case -1 : return $game->getTeam2()->label();
      }
    }
  }

  public function setOwnerId($uid) {
    $this->set('better', $uid);
    return $this;
  }

  public function getScoreTeam1() {
    return $this->get('score_team_1')->value;
  }

  public function getScoreTeam2() {
    return $this->get('score_team_2')->value;
  }

  /**
   * @param bool|FALSE $asEntity
   * @return \Drupal\mespronos\Entity\Game
   */
  public function getGame($asEntity = false) {
    $game =  $this->get('game')->target_id;
    if($asEntity) {
      $game = Game::load($game);
    }
    return $game;
  }

  /**
   * @return bool
   */
  public function isAllowed() {
    $game = $this->getGame(true);
    if($game->isPassed()) {
      return false;
    }
    $league = $game->getLeague();
    if(!$league->isActive()) {
      return false;
    }
    if($this->getOwnerId() == 0) {
      return false;
    }

    return true;
  }

  public function getPoints() {
    return $this->get('points')->value;
  }

  public function setPoints($points) {
    $this->set('points', $points);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['better'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Better'))
      ->setDescription(t('The user ID of the Bet entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['game'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Game'))
      ->setDescription(t('Game reference'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'game')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ),
        'weight' => -3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['score_team_1'] = BaseFieldDefinition::create('integer')
      ->setLabel('Score Team 1')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 4,
      ));

    $fields['score_team_2'] = BaseFieldDefinition::create('integer')
      ->setLabel('Score Team 2')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 5,
      ));


    $fields['points'] = BaseFieldDefinition::create('integer')
      ->setLabel('Points won')
      ->setRevisionable(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 6,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 6,
      ));

    return $fields;
  }

}
