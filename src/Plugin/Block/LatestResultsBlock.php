<?php

namespace Drupal\mespronos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\mespronos\Entity\Game;

/**
 * Provides a 'LatestResultsBlock' block.
 *
 * @Block(
 *  id = "latest_results_block",
 *  admin_label = @Translation("Latest Results"),
 * )
 */
class LatestResultsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['number_of_results_to_display'] = array(
      '#type' => 'number',
      '#title' => $this->t('Number of results to display'),
      '#description' => $this->t(''),
      '#default_value' => isset($this->configuration['number_of_results_to_display']) ? $this->configuration['number_of_results_to_display'] : '10',
      '#weight' => '0',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['number_of_results_to_display'] = $form_state->getValue('number_of_results_to_display');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $games = Game::getLastestGamesWithMark($this->configuration['number_of_results_to_display']);
    $items = [];
    foreach ($games as $game) {
      $day_id = $game->getDayId();
      $items[] = Link::fromTextAndUrl(
        $game->labelWithScoreAndLogo(),
        new Url('entity.day.canonical', ['day' => $day_id])
      );
    }
    $build = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#list_type' => 'ul',
      '#cache' => [
        'tags' => ['games_results'],
      ]
    ];
    return $build;
  }

}
