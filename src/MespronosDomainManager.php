<?php

namespace Drupal\mespronos;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\mespronos_group\Entity\Group;

/**
 * Class MespronosDomainManager.
 */
class MespronosDomainManager {

  protected $domainEnabled = FALSE;

  /**
   * Drupal\domain\DomainNegotiatorInterface definition.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;
  /**
   * Drupal\Core\Extension\ModuleHandlerInterface definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;
  /**
   * Constructs a new MespronosDomainManager object.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    if(\Drupal::moduleHandler()->moduleExists('domain')) {
      $this->domainEnabled = TRUE;
      $this->domainNegotiator = \Drupal::service('domain.negotiator');
    }
    $this->moduleHandler = $module_handler;
  }

  public function getGroupFromDomain() {
    if($this->domainEnabled) {
      $domaine = $this->domainNegotiator->getActiveDomain();
      return Group::loadForDomaine($domaine);
    }
    return FALSE;
  }

}
