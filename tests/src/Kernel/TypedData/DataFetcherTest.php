<?php

/**
 * @file
 * Contains Drupal\Tests\rules\Kernel\TypedData\DataFetcherTest.
 */

namespace Drupal\Tests\rules\Kernel\TypedData;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;

/**
 * Class DataFetcherTest.
 *
 * @group rules
 *
 * @cover \Drupal\rules\TypedData\DataFetcher
 */
class DataFetcherTest extends KernelTestBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\rules\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * A node used for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * An entity type manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules', 'system', 'node', 'field', 'text', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->typedDataManager = $this->container->get('typed_data_manager');

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->entityTypeManager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    $this->node = $this->entityTypeManager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function testFetchingByBasicPropertyPath() {
    $this->assertEquals(
      $this->node->title->value,
      $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'title.0.value')
      ->getValue()
    );
  }

  /**
   * @cover fetchBySubPaths
   */
  public function testFetchingByBasicSubPath() {
    $this->assertEquals(
      $this->node->title->value,
      $this->typedDataManager->getDataFetcher()
      ->fetchBySubPaths($this->node->getTypedData(), array('title', '0', 'value'))
      ->getValue()
    );
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function testFetchingEntityReference() {
    $user = $this->entityTypeManager->getStorage('user')
      ->create([
        'name' => 'test',
        'type' => 'user',
      ]);
    $this->node->uid->target_id = $user->id();

    $fetched_user = $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'uid.entity.value')
      ->getValue();
    $this->assertEquals(TRUE, $fetched_user instanceof EntityAdapter);
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function testFetchingEntityReferenceAtPosition0() {
    $user = $this->entityTypeManager->getStorage('user')
      ->create([
        'name' => 'test',
        'type' => 'user',
      ]);
    $this->node->uid->target_id = $user->id();

    $fetched_user = $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'uid.0.entity.value')
      ->getValue();
    $this->assertEquals(TRUE, $fetched_user instanceof EntityAdapter);
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function testFetchingEntityReferenceAtPosition1() {
    $user1 = $this->entityTypeManager->getStorage('user')
      ->create([
        'name' => 'test1',
        'type' => 'user',
      ]);
    $users[]['target_id'] = $user1->id();
    $user2 = $this->entityTypeManager->getStorage('user')
      ->create([
        'name' => 'test2',
        'type' => 'user',
      ]);
    $users[]['target_id'] = $user2->id();
    $this->node->uid->setValue($users);

    $fetched_user = $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'uid.1.entity.value')
      ->getValue();
    $this->assertEquals(TRUE, $fetched_user instanceof EntityAdapter);
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function testFetchingNonExistingEntityReference() {
    $this->setExpectedException('Drupal\Core\TypedData\Exception\MissingDataException');
    $fetched_user = $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'uid.0.entity.value')
      ->getValue();
    $this->assertEquals(TRUE, $fetched_user instanceof EntityAdapter);
  }

}
