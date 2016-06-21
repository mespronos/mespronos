<?php

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Provides a 'BetHelpBlock' block.
 *
 * @Block(
 *  id = "bet_help_block",
 *  admin_label = @Translation("Bet help block"),
 * )
 */
class BetHelpBlock extends BlockBase implements ContainerFactoryPluginInterface {


  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $current_route_match;
  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current root match service
   */
  public function __construct(array $configuration,$plugin_id,$plugin_definition,CurrentRouteMatch $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->current_route_match = $current_route_match;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Routing\CurrentRouteMatch $crm */
    $crm = $container->get('current_route_match');
    return new static($configuration,$plugin_id,$plugin_definition,$crm);
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $day = $this->current_route_match->getParameter('day');
    $league = $day->getLeague();
    $betting_type = $league->getBettingType(TRUE);
    if($betting_type == 'score') {
      $text = t('You have to enter the final score');
    }
    else {
      $text = t('You have to choose the winner, or draw');
    }
    $text .= ' '.t('at the end of the legal time (no kicks).');
    $texts[] = $text;
    $texts[] = t('You can change a bet until the game start.');
    $build = [
      '#theme' => 'item_list',
      '#items' => $texts,
      '#title' => t('Help'),
      '#list_type' => 'ul',
    ];

    return $build;
  }

}
