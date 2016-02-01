<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\RankingLeague.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\Controller\RankingController;
use Drupal\mespronos\MPNEntityInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Database\Database;

/**
 * Defines the RankingLeague entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "ranking_league",
 *   label = @Translation("RankingLeague entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\RankingLeagueListController",
 *     "views_data" = "Drupal\mespronos\Entity\ViewsData\RankingLeagueViewsData",
 *     "access" = "Drupal\mespronos\ControlHandler\RankingLeagueAccessControlHandler",
 *   },
 *   base_table = "mespronos__ranking_league",
 *   admin_permission = "administer RankingLeague entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   field_ui_base_route = "ranking_league.settings"
 * )
 */
class RankingLeague extends ContentEntityBase implements MPNEntityInterface {

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
    return $this->get('better')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('better')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('better', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('better', $account->id());
    return $this;
  }

  /**
   * @return mixed
   */
  public function getLeagueiD() {
    return $this->get('league')->target_id;
  }

  /**
   * @return League
   */
  public function getLeague() {
    $day_storage = \Drupal::entityManager()->getStorage('league');
    $day = $day_storage->load($this->get('league')->target_id);
    return $day;
  }

  public function setGameBetted($nb_games_betted) {
    $this->set('games_betted', $nb_games_betted);
    return $this;
  }

  public function getGameBetted() {
    return $this->get('games_betted')->value;
  }

  public function setPoints($points) {
    $this->set('points', $points);
    return $this;
  }

  public function getPoints() {
    return $this->get('points')->value;
  }

  public static function createRanking(League $league) {
    self::removeRanking($league);
    $data = self::getData($league);
    RankingController::sortRankingDataAndDefinedPosition($data);
    foreach($data as $row) {
      $rankingLeague = self::create([
        'better' => $row->better,
        'league' => $league->id(),
        'games_betted' => $row->nb_bet,
        'points' => $row->points,
        'position' => $row->position,
      ]);
      $rankingLeague->save();
    }
    return count($data);
  }

  public static function getData(League $league) {
    $injected_database = Database::getConnection();
    $query = $injected_database->select('mespronos__ranking_day','rd');
    $query->addField('rd','better');
    $query->addExpression('sum(rd.points)','points');
    $query->addExpression('count(rd.id)','nb_bet');
    $query->join('mespronos__day','d','d.id = rd.day');
    $query->groupBy('rd.better');
    $query->orderBy('points','DESC');
    $query->orderBy('nb_bet','DESC');
    $query->condition('d.league',$league->id());
    //$query->isNotNull('b.points');
    $results = $query->execute()->fetchAllAssoc('better');

    return $results;
  }

  public static function removeRanking(League $league) {

    $storage = \Drupal::entityManager()->getStorage('ranking_league');
    $query = \Drupal::entityQuery('ranking_league');
    $query->condition('league',$league->id());
    $ids = $query->execute();

    $rankings = $storage->loadMultiple($ids);
    $nb_deleted = count($rankings);
    foreach ($rankings as $ranking) {
      $ranking->delete();
    }

    return $nb_deleted;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the RankingLeague entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the RankingLeague entity.'))
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

    $fields['league'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('League'))
      ->setDescription(t('League entity reference'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'league')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'settings' => array(),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['games_betted'] = BaseFieldDefinition::create('integer')
      ->setLabel('Games betted')
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
