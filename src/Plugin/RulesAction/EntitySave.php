<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesAction\EntitySave.
 */

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Save entity' action.
 *
 * @RulesAction(
 *   id = "rules_entity_save",
 *   deriver = "Drupal\rules\Plugin\RulesAction\EntitySaveDeriver",
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntitySave extends RulesActionBase implements ContainerFactoryPluginInterface{

  /**
   * Flag that indicates if the entity should be auto-saved later.
   *
   * @var bool
   */
  protected $saveLater = TRUE;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage($plugin_definition['entity_type_id'])
    );
  }

  /**
   * Saves the Entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be saved.
   * @param bool $immediate
   *   (optional) Save the entity immediately.
   */
  protected function doExecute(EntityInterface $entity, $immediate) {
    // We only need to do something here if the immediate flag is set, otherwise
    // the entity will be auto-saved after the execution.
    if ((bool) $immediate) {
      $entity->save();
      $this->saveLater = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function autoSaveContext() {
    if ($this->saveLater) {
      return ['entity'];
    }
    return [];
  }

}
