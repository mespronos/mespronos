<?php
namespace Drupal\Tests\mespronos\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\mespronos\Entity\Bet;
use Drupal\mespronos\Entity\Day;
use Drupal\mespronos\Entity\League;
use Drupal\mespronos\Entity\Sport;
use Drupal\mespronos\Entity\Team;

/**
 * Test mespronos league manager service
 *
 * @group mespronos
 */
class NotificationManagerTest extends MespronosKernelTestBase {


  /**
   * @var \Drupal\mespronos\Service\NotificationManager
   */
  public $notificationManager;

  public function setUp() {
    parent::setUp();

    $this->createSport();
    $this->createLeague();
    $this->createDays(2);
    $this->createTeams(2);

    $this->users[0] = $this->createUser();
    $this->users[1] = $this->createUser();

    $this->notificationManager = \Drupal::service('mespronos.notification_manager');
  }

  public function testDayGetUpcommingMethodWithUpcomingGame() {
    $hours = 10;
    $upcomming = $this->notificationManager->getUpcomming($hours);
    $this->assertInternalType('array', $upcomming, t('The method is returning an array'));
    $this->assertEqual(\count($upcomming),0,t('The returned array of games is empty when no game is set'));

    $dateO = new \DateTime(NULL, new \DateTimeZone('UTC'));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1, $this->teams[0], $this->teams[1], $this->days[0], $date);

    $upcomming = $this->notificationManager->getUpcomming($hours);
    $this->assertEqual(\count($upcomming),1, t('The returned array contain one game when a game is set'));
  }

  public function testDayGetUpcommingMethodWithUpcomingGameButUnderNbHours() {
    $hours = 5;
    $dateO = new \DateTime(NULL, new \DateTimeZone('UTC'));
    $dateO->add(new \DateInterval('PT10H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1, $this->teams[0], $this->teams[1], $this->days[0], $date);

    $upcomming = $this->notificationManager->getUpcomming($hours);
    $this->assertTrue(is_array($upcomming),t('The method is returning an array'));
    $this->assertEqual(count($upcomming),0,t('The returned array is empty when the game is set later'));
  }

  public function testDayGetUpcommingMethodWithUpcomingGames() {
    $hours = 10;
    $dateO = new \DateTime(NULL, new \DateTimeZone('UTC'));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1, $this->teams[0], $this->teams[1], $this->days[0], $date);
    $this->setUpGame(2, $this->teams[0], $this->teams[1], $this->days[0], $date);

    $upcomming = $this->notificationManager->getUpcomming($hours);
    $this->assertEqual(count($upcomming),2,t('The returned array contains two games when two games exists'));
  }

  public function testDayGetUpcommingMethodWithUpcomingGamesFromTwoDaysWithOnlyOneUpcommingGame() {
    $hours = 10;
    $dateO = new \DateTime(NULL, new \DateTimeZone('UTC'));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1, $this->teams[0], $this->teams[1], $this->days[0], $date);

    $dateO->add(new \DateInterval('PT10H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(1, $this->teams[0], $this->teams[1], $this->days[1], $date);

    $upcomming = $this->notificationManager->getUpcomming($hours);
    $this->assertEqual(count($upcomming),1,t('The returned array contains one days when there is only one game'));
  }

  public function testGetUserWithEnabledReminder() {
    $user_ids = $this->notificationManager->getUserWithEnabledReminder();
    $this->assertInternalType('array', $user_ids, t('The method is returning an array'));
    $this->assertEqual(count($user_ids), 2, t('By default user has reminder activated'));
  }

  public function testGetUserWithEnabledReminderWithOneEnabledUser() {
    $this->users[0]->set("field_reminder_enable", 0);
    $this->users[0]->save();
    $user_ids = $this->notificationManager->getUserWithEnabledReminder();
    $this->assertEqual(count($user_ids),1,t('The returned array contains one element'));
  }

  public function testDoUserHasMissingBets() {
    $hours = 10;
    $dateO = new \DateTime(NULL, new \DateTimeZone('UTC'));
    $dateO->add(new \DateInterval('PT5H'));
    $date = $dateO->format('Y-m-d\TH:i:s');

    $this->setUpGame(0, $this->teams[0], $this->teams[1], $this->days[0], $date);

    $upcomming = $this->notificationManager->getUpcomming($hours);

    $this->assertEqual(count($upcomming),1,t('The returned array contains one game when there is only one game'));
    $this->assertInternalType('boolean', $this->notificationManager->doUserHasMissingBets($this->users[0]->id(), $upcomming),t('The static function ReminderController::doUserHasMissingBets return a boolean'));
    $this->assertTrue($this->notificationManager->doUserHasMissingBets($this->users[0]->id(), $upcomming), t('The static function ReminderController::doUserHasMissingBets return true when there is a bet to be done'));

    $bet = Bet::create(array(
      'better' => $this->users[0]->id(),
      'game' => $this->games[0]->id(),
      'score_team_1' => 1,
      'score_team_2' => 1,
      'points' => 10,
    ));

    $bet->save();

    $this->assertFalse($this->notificationManager->doUserHasMissingBets($this->users[0]->id(), $upcomming), t('The static function ReminderController::doUserHasMissingBets return False when there is no bet to be done'));
  }

}
