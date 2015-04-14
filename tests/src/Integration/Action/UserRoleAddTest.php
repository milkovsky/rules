<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\UserRoleAddTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;
use Drupal\Tests\rules\Integration\RulesUserIntegrationTestTrait;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\UserRoleAdd
 * @group rules_actions
 */
class UserRoleAddTest extends RulesEntityIntegrationTestBase {

  use RulesUserIntegrationTestTrait;

  /**
   * The action that is being tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->enableModule('user');
    $this->action = $this->actionManager->createInstance('rules_user_role_add');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Adds roles to a particular user', $this->action->summary());
  }

  /**
   * Tests adding of one role to user.
   *
   * @covers ::execute()
   */
  public function testAddOneRole() {
    // Set-up a mock user.
    $account = $this->getMockUser();
    $account->expects($this->once())
      ->method('addRole');
    $account->expects($this->once())
      ->method('save');

    // Mock the 'administrator' user role.
    $administrator = $this->getMockUserRole('administrator');

    // Test adding of one role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$administrator])
      ->execute();
  }

  /**
   * Tests adding of three roles to user.
   *
   * @covers ::execute()
   */
  public function testAddThreeRoles() {
    // Set-up a mock user.
    $account = $this->getMockUser();
    $account->expects($this->exactly(3))
      ->method('addRole');
    $account->expects($this->once())
      ->method('save');

    // Mock user roles.
    $manager = $this->getMockUserRole('manager');
    $editor = $this->getMockUserRole('editor');
    $administrator = $this->getMockUserRole('administrator');

    // Test adding of three roles role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$manager, $editor, $administrator])
      ->execute();
  }

  /**
   * Tests adding of existing role to user.
   *
   * @covers ::execute()
   */
  public function testAddExistingRole() {
    // Set-up a mock user with role 'administrator'.
    $account = $this->getMockUser();
    $account->expects($this->once())
      ->method('hasRole')
      ->with($this->equalTo('administrator'))
      ->will($this->returnValue(TRUE));

    // We do not expect call of 'save' and 'addRole' methods.
    $account->expects($this->never())
      ->method('addRole');
    $account->expects($this->never())
      ->method('save');

    // Mock the 'administrator' user role.
    $administrator = $this->getMockUserRole('administrator');

    // Test adding one role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$administrator])
      ->execute();
  }

  /**
   * Tests adding of one existing and one nonexistent role to user.
   *
   * @covers ::execute()
   */
  public function testAddExistingAndNonexistentRole() {
    // Set-up a mock user with role 'administrator' but without 'editor'.
    $account = $this->getMockUser();
    $account->expects($this->exactly(2))
      ->method('hasRole')
      ->with($this->logicalOr(
        $this->equalTo('editor'),
        $this->equalTo('administrator')
      ))
      ->will($this->returnCallback(
        function($id) {
          if ($id == 'administrator') {
            return TRUE;
          }
          else {
            return FALSE;
          }
        }
      ));

    // We expect only one call of 'save' and 'addRole' methods.
    $account->expects($this->once())
      ->method('addRole');
    $account->expects($this->once())
      ->method('save');

    // Mock user roles.
    $editor = $this->getMockUserRole('editor');
    $administrator = $this->getMockUserRole('administrator');

    // Test adding one role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$administrator, $editor])
      ->execute();
  }

}
