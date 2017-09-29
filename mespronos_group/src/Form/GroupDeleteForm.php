<?php

namespace Drupal\mespronos_group\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a form for deleting Group entities.
 *
 * @ingroup mespronos_group
 */
class GroupDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    $entity = $this->getEntity();
    return $this->t('Le groupe  %label a été supprimé.', [
      '%label' => $entity->label(),
    ]);
  }

  public function getCancelUrl() {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity();
    return $entity->urlInfo('canonical');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity();

    return $this->t('Êtes-vous sûr de vouloir supprimer le groupe %label?', [
      '%label' => $this->getEntity()->label(),
    ]);

  }

}
