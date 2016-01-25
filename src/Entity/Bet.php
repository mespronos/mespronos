<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Bet.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\MPNEntityInterface;
use Drupal\user\UserInterface;

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
 *     "views_data" = "Drupal\mespronos\Entity\BetViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\BetForm",
 *       "add" = "Drupal\mespronos\Entity\Form\BetForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\BetForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\BetDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\BetAccessControlHandler",
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
 *   },
 *   field_ui_base_route = "bet.settings"
 * )
 */
class Bet extends ContentEntityBase implements MPNEntityInterface {
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * @todo implement
   * @param \Drupal\mespronos\Entity\Game $game
   */
  public function definePoints(Game $game) {
    $league = $game->getLeague();
    $league_points = $league->getPoints();
    $score_t1 = $game->get('score_team_1')->value;
    $score_t2 = $game->get('score_team_2')->value;
    $bet_t1 = $this->get('score_team_1')->value;
    $bet_t2 = $this->get('score_team_2')->value;
    if($score_t1 == $bet_t1 && $score_t2 == $bet_t2){
      $points = $league_points['points_score_found'];
    }
    elseif($score_t1 > $score_t2 && $bet_t1 > $bet_t2){
      $points = $league_points['points_winner_found'];
    }
    elseif($score_t1 < $score_t2 && $bet_t1 < $bet_t2){
      $points = $league_points['points_winner_found'];
    }
    elseif($score_t1 == $score_t2 && $bet_t1 == $bet_t2){
      $points = $league_points['points_winner_found'];
    }
    else {
      $points = $league_points['points_participation'];
    }

    $this->set('points',$points);
    $this->save();
  }

  public static function getUserBetsForGames($games_ids, \Drupal\Core\Session\AccountProxyInterface $user){
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
    return t('@t1 - @t2',array('@t1'=> $this->get('score_team_1')->value,'@t2'=> $this->get('score_team_2')->value));
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
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
      $game_storage = \Drupal::entityManager()->getStorage('game');
      $game = $game_storage->load($game);
    }
    return $game;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Bet entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Bet entity.'))
      ->setReadOnly(TRUE);

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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
