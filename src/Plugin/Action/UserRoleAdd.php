<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\UserRoleAdd.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Engine\RulesActionBase;

/**
 * Provides a 'Add user role' action.
 *
 * @action(
 *   id = "rules_user_role_add",
 *   label = @Translation("Adds roles to a particular user"),
 *   category = @Translation("User"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User")
 *     ),
 *     "roles" = @ContextDefinition("entity:role",
 *       label = @Translation("Entity"),
 *       multiple = TRUE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add port for rules_user_roles_options_list.
 */
class UserRoleAdd extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Adds roles to a particular user');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $account = $this->getContextValue('user');
    $roles = $this->getContextValue('roles');

    //@todo: Deal with the anonymous role.
    //@todo: Implement auto-save functionality.

    if ($account !== FALSE) {
      foreach ($roles as $role) {
        // Skip adding the role to the user if they already have it.
        if (!$account->hasRole($role->rid)) {
          // For efficiency manually save the original account before applying
          // any changes.
          $account->original = clone $account;
          $account->addRole($role->rid);
          $account->save();
        }
      }
    }
  }
}
