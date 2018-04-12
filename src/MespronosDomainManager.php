<?php

namespace Drupal\mespronos;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\mespronos_group\Entity\Group;

/**
 * Class MespronosDomainManager.
 */
class MespronosDomainManager {

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
  public function __construct(DomainNegotiatorInterface $domain_negotiator, ModuleHandlerInterface $module_handler) {
    $this->domainNegotiator = $domain_negotiator;
    $this->moduleHandler = $module_handler;
  }

  public function getGroupFromDomain() {
    if(\Drupal::moduleHandler()->moduleExists('domain')) {
      $domaine = $this->domainNegotiator->getActiveDomain();
      $group = Group::loadForDomaine($domaine);
      return $group;
    }
    return FALSE;
  }

}
