<?php
/**
 * Created by nelsonpireshassanali on 27/09/17.
 */

/**
 * Initialize bet_private
 *
 */
function mespronos_group_post_update_user_set_bet_private(&$sandbox) {
  $query = \Drupal::entityQuery('user');
  $ids = $query->range(0,2000)->execute();
  $users = \Drupal\user\Entity\User::loadMultiple($ids);
  foreach ($users as $user) {
    $user->set('bet_private', FALSE);
    $user->save();
  }
}