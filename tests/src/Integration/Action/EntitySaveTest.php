<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntitySaveTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\Entity\EntityStorageBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntitySave
 * @group rules_actions
 */
class EntitySaveTest extends RulesEntityIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * The mocked entity used for testing.
   *
   * @var \Drupal\Core\Entity\EntityInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $entity;

  /**
   * A constant that will be used instead of an entity.
   */
  const ENTITY_REPLACEMENT = 'This is a fake entity';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entity = $this->prophesizeEntity(EntityInterface::class);

    // Prepare some mocked bundle field definitions. This is needed because
    // EntityCreateDeriver adds required contexts for required fields, and
    // assumes that the bundle field is required.
    $entity_definition = $this->prophesize(EntityDataDefinition::class);
    $bundle_field_definition = $this->prophesize(BaseFieldDefinition::class);
    $bundle_field_definition_optional = $this->prophesize(BaseFieldDefinition::class);
    $bundle_field_definition_required = $this->prophesize(BaseFieldDefinition::class);

    // The next methods are mocked because EntitySaveDeriver executes them,
    // and the mocked field definition is instantiated without the necessary
    // information.
    $bundle_field_definition->getCardinality()->willReturn(1)
      ->shouldBeCalled();
    $bundle_field_definition->getType()->willReturn('string')
      ->shouldBeCalled();
    $bundle_field_definition->getLabel()->willReturn('Bundle')
      ->shouldBeCalled();
    $bundle_field_definition->getDescription()
      ->willReturn('Bundle mock description')
      ->shouldBeCalled();

    $bundle_field_definition_required->getCardinality()->willReturn(1)
      ->shouldBeCalled();
    $bundle_field_definition_required->getType()->willReturn('string')
      ->shouldBeCalled();
    $bundle_field_definition_required->getLabel()->willReturn('Required field')
      ->shouldBeCalled();
    $bundle_field_definition_required->getDescription()
      ->willReturn('Required field mock description')
      ->shouldBeCalled();
    $bundle_field_definition_required->isRequired()
      ->willReturn(TRUE)
      ->shouldBeCalled();

    $bundle_field_definition_optional->isRequired()
      ->willReturn(FALSE)
      ->shouldBeCalled();

    // Prepare mocked entity storage.
    $entity_type_storage = $this->prophesize(EntityStorageBase::class);
    $entity_type_storage->save(['entity' => $this->entity, 'bundle' => 'test', 'field_required' => NULL])
      ->willReturn(self::ENTITY_REPLACEMENT);

    // Return the mocked storage controller.
    $this->entityTypeManager->getStorage('test')
      ->willReturn($entity_type_storage->reveal());

    // Return a mocked list of base fields definitions.
    $this->entityFieldManager->getBaseFieldDefinitions('test')
      ->willReturn([
        'bundle' => $bundle_field_definition->reveal(),
        'field_required' => $bundle_field_definition_required->reveal(),
        'field_optional' => $bundle_field_definition_optional->reveal(),
      ]);

    // Instantiate the action we are testing.
    $this->action = $this->actionManager->createInstance('rules_entity_save:test');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Save entity', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {
    $this->entity->save()->shouldBeCalledTimes(1);

    // @todo Exception: The entity context is not a valid context.
    $this->action->setContextValue('entity', $this->entity->reveal())
      ->setContextValue('immediate', TRUE);

    $this->action->execute();
    $entity = $this->action->getProvidedContext('entity')->getContextValue();
    $this->assertEquals(self::ENTITY_REPLACEMENT, $entity);
  }

  /**
   * Tests the action execution when saving immediately.
   *
   * @covers ::execute
   */
  public function testActionExecutionImmediately() {
    $this->entity->save()->shouldBeCalledTimes(1);

    $this->action->setContextValue('entity', $this->entity->reveal())
      ->setContextValue('immediate', TRUE);

    $this->action->execute();
    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the entity has been saved already.');
  }

  /**
   * Tests the action execution when saving is postponed.
   *
   * @covers ::execute
   */
  public function testActionExecutionPostponed() {
    $this->entity->save()->shouldNotBeCalled();

    $this->action->setContextValue('entity', $this->entity->reveal());
    $this->action->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['entity'], 'Action returns the entity context name for auto saving.');
  }

}
