<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\UserRoleAddTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\UserRoleAdd
 * @group rules_actions
 */
class UserRoleAddTest extends RulesEntityIntegrationTestBase {

  /**
   * The action that is being tested.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->enableModule('user');

    parent::setUp();

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
   * Tests evaluating the action.
   *
   * @covers ::execute()
   */
  public function testActionExecution() {
    // Set-up a mock object with roles 'authenticated' and 'editor', but not
    // 'administrator'.
    $account = $this->getMock('Drupal\user\UserInterface');
    $account->expects($this->exactly(7))
      ->method('getRoles')
      ->will($this->returnValue(['authenticated', 'editor']));

    $authenticated = $this->getMockRole('authenticated');
    $editor = $this->getMockRole('editor');
    $administrator = $this->getMockRole('administrator');

    // First test adding one role.
    $this->action
      ->setContextValue('user', $this->getMockTypedData($account))
      ->setContextValue('roles', $this->getMockTypedData([$administrator]));

    $this->action->execute();

    $this->assertTrue(array_search($administrator->rid, $account->getRoles()), 'The role ' . $administrator->rid . ' is present in the user object.');

/*
    // User doesn't have the administrator role, this should fail.
    $this->action->setContextValue('roles', $this->getMockTypedData([$authenticated, $administrator]));
    $this->assertFalse($this->action->evaluate());

    // Only one role, should succeed.
    $this->action->setContextValue('roles', $this->getMockTypedData([$authenticated]));
    $this->assertTrue($this->action->evaluate());

    // A role the user doesn't have.
    $this->action->setContextValue('roles', $this->getMockTypedData([$administrator]));
    $this->assertFalse($this->action->evaluate());

    // Only one role, the user has with OR action, should succeed.
    $this->action->setContextValue('roles', $this->getMockTypedData([$authenticated]));
    $this->action->setContextValue('operation', $this->getMockTypedData('OR'));
    $this->assertTrue($this->action->evaluate());

    // User doesn't have the administrator role, but has the authenticated,
    // should succeed.
    $this->action->setContextValue('roles', $this->getMockTypedData([$authenticated, $administrator]));
    $this->action->setContextValue('operation', $this->getMockTypedData('OR'));
    $this->assertTrue($this->action->evaluate());

    // User doesn't have the administrator role. This should fail.
    $this->action->setContextValue('roles', $this->getMockTypedData([$administrator]));
    $this->action->setContextValue('operation', $this->getMockTypedData('OR'));
    $this->assertFalse($this->action->evaluate());
*/
  }

  /**
   * Creates a mocked user role.
   *
   * @param string $id
   *   The machine-readable name of the mocked role.
   *
   * @return \PHPUnit_Framework_MockObject_MockBuilder|\Drupal\user\RoleInterface
   *   The mocked role.
   */
  protected function getMockRole($id) {
    $role = $this->getMockBuilder('Drupal\user\Entity\Role')
      ->disableOriginalConstructor()
      ->setMethods(['id'])
      ->getMock();

    $role->expects($this->any())
      ->method('id')
      ->will($this->returnValue($id));

    return $role;
  }

}
