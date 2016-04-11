<?php

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageBase;
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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entity = $this->prophesizeEntity(EntityInterface::class);

    // Prepare mocked entity storage.
    $entity_type_storage = $this->prophesize(EntityStorageBase::class);

    // Return the mocked storage controller.
    $this->entityTypeManager->getStorage('test')
      ->willReturn($entity_type_storage->reveal());

    // Instantiate the action we are testing.
    $this->action = $this->actionManager->createInstance('rules_entity_save:test');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Save test', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {
    $this->entity->save()->shouldBeCalledTimes(1);

    $this->action->setContextValue('entity', $this->entity->reveal())
      ->setContextValue('immediate', TRUE);

    $this->action->execute();
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
