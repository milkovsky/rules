<?php

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageBase;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * Test auto saving of variables after Rules execution.
 *
 * @group rules
 */
class AutoSaveTest extends RulesEntityIntegrationTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Prepare mocked entity storage.
    $entity_type_storage = $this->prophesize(EntityStorageBase::class);

    // Return the mocked storage controller.
    $this->entityTypeManager->getStorage('test')
      ->willReturn($entity_type_storage->reveal());
  }

  /**
   * Tests auto saving after an action execution.
   */
  public function testActionAutoSave() {
    $rule = $this->rulesExpressionManager->createRule();
    // Just leverage the entity save action, which by default uses auto-saving.
    $rule->addAction('rules_entity_save:test', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $entity = $this->prophesizeEntity(EntityInterface::class);
    $entity->save()->shouldBeCalledTimes(1);

    RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->setContextValue('entity', $entity->reveal())
      ->execute();
  }

}
