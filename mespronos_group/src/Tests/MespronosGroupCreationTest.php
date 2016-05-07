<?php

/**
 * @file
 * Contains Drupal\mespronos\Tests\MespronosGroupCreationTest.
 */

namespace Drupal\mespronos_group\Tests;

use Drupal\mespronos_group\Entity\Group;
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

  public function testUserCanAccessGroupCreationForm() {
    $this->drupalGet('/mespronos/group/add');
    $this->assertResponse(200);

    $this->assertFieldByName('name[0][value]', '', 'Form - name input is empty');
    $this->assertFieldByName('code[0][value]', '', 'Form - code input is empty');

    $this->drupalPostForm('mespronos/group/add', array(
      'name[0][value]' => 'TestNomGroup',
      'code[0][value]' => 'testCode',
    ), t('Create my group !'));

  }

  public function testCreationFormErrors() {
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

  public function testUserCreateGroupRedirectedToItsPage() {
    $this->drupalPostForm('mespronos/group/add', array(
      'name[0][value]' => 'TestNomGroup',
      'code[0][value]' => 'testCode',
    ), t('Create my group !'));
    $this->assertUrl('mespronos/group/1');
  }

}
