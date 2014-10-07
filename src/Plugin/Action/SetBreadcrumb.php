<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\SetBreadcrumb.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Engine\RulesActionBase;

/**
 * Provides a 'Set breadcrumb' action.
 *
 * @Action(
 *   id = "rules_breadcrumb_set",
 *   label = @Translation("Set breadcrumb"),
 *   context = {
 *     "titles" = @ContextDefinition("string",
 *       label = @Translation("Titles"),
 *       description = @Translation("A list of titles for the breadcrumb links."),
 *       multiple = TRUE
 *     ),
 *     "paths" = @ContextDefinition("string",
 *       label = @Translation("Paths"),
 *       description = @Translation("A list of Drupal paths for the breadcrumb links, matching the order of the titles."),
 *       multiple = TRUE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
