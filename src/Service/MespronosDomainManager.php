<?php
namespace Drupal\mespronos\Service;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\domain\Entity\Domain;
use Drupal\mespronos_group\Entity\Group;
use Drupal\user\Entity\User;

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

  public function getGroupFromDomain(Domain $domain = NULL) {
    if($this->domainEnabled) {
      if (!$domain) {
        $domain = $this->domainNegotiator->getActiveDomain();
      }
      return Group::loadForDomaine($domain);
    }
    return FALSE;
  }

  /**
   * @param \Drupal\user\Entity\User $user
   *
   * @return \Drupal\domain\Entity\Domain|null
   */
  public function getUserMainDomain(User $user) {
    if($this->domainEnabled) {
      $groups = Group::getUserGroup($user);
      if (is_array($groups)) {
        foreach ($groups as $group) {
          if ($domaine = $group->getDomain()) {
            if ($domaine->status()) {
              return $domaine;
            }
          }
        }
      }
    }
    return NULL;
  }

}
