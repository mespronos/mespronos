<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosGroupCreationTest.
 */

namespace Drupal\mespronos_group\Tests;

use Drupal\mespronos_group\Entity\Group;
use Drupal\user\Entity\User;
use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the mespronos module.
 * @group mespronos
 */
class MespronosGroupCreationTest extends WebTestBase {
  public $group;
  public $user;

  public static $modules = ['mespronos_group'];
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "MesPronos Group testing",
      'description' => 'Test Group creation',
      'group' => 'MesPronos',
    );
  }


  public function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser();
    $this->drupalLogin($this->user);
  }

  public function testCreationGroup() {
    $group = Group::create([
        'name'=> 'test',
        'code'=> 'test',
    ]);
    $this->assertTrue($group->save(),t('Group saving return true'));
  }

  public function testCreationFormErrors() {
    $this->drupalGet('/mespronos/group/add');
    $this->assertResponse(200);

    $this->assertFieldByName('name[0][value]', '', 'Form - name input is empty');
    $this->assertFieldByName('code[0][value]', '', 'Form - code input is empty');
    
    $this->drupalPostForm('mespronos/group/add', array(
      'name[0][value]' => '',
      'code[0][value]' => '',
    ), t('Create my group !'));
    $this->assertText('Group name field is required.', 'The form validation correctly failed.');
    $this->assertText('Access code field is required.', 'The form validation correctly failed.');

    $this->drupalPostForm('mespronos/group/add', array(
      'name[0][value]' => '',
      'code[0][value]' => 'test',
    ), t('Create my group !'));
    $this->assertText('Group name field is required.', 'The form validation correctly failed.');

    $this->drupalPostForm('mespronos/group/add', array(
      'name[0][value]' => 'test',
      'code[0][value]' => '',
    ), t('Create my group !'));
    $this->assertText('Access code field is required.', 'The form validation correctly failed.');
  }

  public function testUserCreateGroupGoodBeahvior() {
    $this->drupalPostForm('mespronos/group/add', array(
      'name[0][value]' => 'TestNomGroup',
      'code[0][value]' => 'testCode',
    ), t('Create my group !'));
    $this->assertUrl('mespronos/group/1');
    $group = Group::load(1);
    $this->assertEqual($group->getOwnerId(),$this->user->id(),t('Group creator is correctly set'));
    $u = User::load($this->user->id());
    $user_group = Group::getUserGroup($u);
    $user_group = array_pop($user_group);
    $this->assertEqual($user_group->id(),$group->id(),t('Group creator automatically join the group'));
    $members = $group->getMembers(false);
    $this->assertTrue(in_array($this->user->id(),$members),t('The method GetMembers is working properly'));


    $this->drupalPostForm('mespronos/group/add', array(
      'name[0][value]' => 'TestNomGroup2',
      'code[0][value]' => 'testCode2',
    ), t('Create my group !'));
    $this->assertUrl('mespronos/group/2');

    $group1 = Group::load(1);
    $members_group_1 = $group1->getMembers(false);
    $this->assertTrue(in_array($this->user->id(),$members_group_1),t('When a user create a new group he doesn\'t leave the old one now :)'));

    $group2 = Group::load(2);
    $members_group_2 = $group2->getMembers(false);
    $this->assertTrue(in_array($this->user->id(),$members_group_2),t('When a user create a new group he join the new one too'));

    $u = User::load($this->user->id());
    $user_groups = Group::getUserGroup($u);
    $user_groups = array_map(function($g) {return $g->id();},$user_groups);
    $this->assertTrue(in_array($group1->id(),$user_groups),t('The method Group::getUserGroup contain group 1'));
    $this->assertTrue(in_array($group2->id(),$user_groups),t('The method Group::getUserGroup contain group 2'));

  }

}
