<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\UserRoleAdd.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Core\RulesActionBase;

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
 *     "roles" = @ContextDefinition("entity:user_role",
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
    // Indicates if the user account was changed.
    $user_is_changed = FALSE;
    foreach ($roles as $role) {
      // Skip adding the role to the user if they already have it.
      if (!$account->hasRole($role->id())) {
        // For efficiency manually save the original account before applying
        // any changes.
        $account->original = clone $account;
        // If you try to add anonymous or authenticated role to user, Drupal
        // will throw an \InvalidArgumentException. Anonymous or authenticated
        // role ID must not be assigned manually.
        $account->addRole($role->id());
        $user_is_changed = TRUE;
      }
    }
    // Save user only in case of change.
    if ($user_is_changed) {
      //@todo: Implement auto-save functionality. See EntitySave action.
      $account->save();
    }
  }
}
