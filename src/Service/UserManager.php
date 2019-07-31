<?php
namespace Drupal\mespronos\Service;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\mespronos_group\Entity\Group;
use Drupal\user\Entity\User;

/**
 * Class MespronosDomainManager.
 */
class UserManager {

  protected $domainEnabled = FALSE;
  protected $groupEnabled = FALSE;

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
    if(\Drupal::moduleHandler()->moduleExists('mespronos_group')) {
      $this->groupEnabled = TRUE;
    }
    else {
      $this->groupEnabled = FALSE;
    }
    if(\Drupal::moduleHandler()->moduleExists('domain')) {
      $this->domainEnabled = TRUE;
      $this->domainNegotiator = \Drupal::service('domain.negotiator');
    }
    else {
      $this->domainEnabled = FALSE;
    }

    $this->moduleHandler = $module_handler;
  }

  public function getUserGroups(AccountInterface $user = NULL) {
    if($this->groupEnabled) {
      if(NULL === $user) {
        $user = \Drupal::currentUser();
      }
      $user = User::load($user->id());
      return Group::getUserGroup($user);
    }
    return [];
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
