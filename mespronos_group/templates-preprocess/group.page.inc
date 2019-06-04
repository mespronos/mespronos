<?php

use Drupal\Core\Render\Element;
use Drupal\mespronos_group\Entity\Group;

function template_preprocess_group(&$variables) {
  /**@var Group $group */
  $group = $variables['elements']['#group'];
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
  $variables['has_description'] = $variables['elements']['#view_mode'] === 'full' && NULL !== $group->get('description')->value;
  $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
  $variables['member'] = t('@nb members', ['@nb' => $group->getMemberNumber()]);
  $variables['created'] = t('Created on @date', ['@date' => date('d/m/Y', $group->getCreatedTime())]);
  $variables['is_member'] = $group->isMemberOf($user);
  $variables['display_join_link'] = \Drupal::currentUser()->isAuthenticated();
  $variables['betters'] = $group->getMemberNumber();
  $variables['join_url'] = \Drupal\Core\Url::fromRoute('mespronos_group.group.join', ['group' => $group->id()]);
  $variables['leave_url'] = \Drupal\Core\Url::fromRoute('mespronos_group.group.leave', ['group' => $group->id()]);
  $variables['#cache'] = ['context' => 'user'];
}