<?php
require_once(__DIR__.'/../CITest.php');

class BadgeTest extends CITestCase
{
    //protected $badge_test;

    public function setUp()
    {

    }

    public function testAllBadges()
    {
        $badge_model = new Badge_model();
        $clientSite = $this->getClientSite();

        $return = $badge_model->getAllBadges(array(
            'client_id' => $clientSite['client_id'],
            'site_id' => $clientSite['site_id'],
        ));

        foreach ($return as $val){
            $this->assertGreaterThanOrEqual(2, count($val));
            $this->assertArrayHasKey('badge_id', $val);
            $this->assertArrayHasKey('name', $val);
        }

        // Return last badge element to use next test
        return $val;
    }

    /**
     * @depends testAllBadges
     */
    public function testGetBadge(array $badge)
    {

        $badge_model = new Badge_model();
        $clientSite = $this->getClientSite();

        $return = $badge_model->getBadge(array(
            'client_id' => $clientSite['client_id'],
            'site_id' => $clientSite['site_id'],
            'badge_id' => new MongoId($badge['badge_id']),
        ));
        $this->assertGreaterThanOrEqual(2, count($return));
        $this->assertArrayHasKey('badge_id', $return);
        $this->assertArrayHasKey('name', $return);
    }

    /**
     * @depends testAllBadges
     */
    public function testGetBadgeName(array $badge)
    {
        $badge_model = new Badge_model();
        $clientSite = $this->getClientSite();

        $return = $badge_model->getBadgeName($clientSite['client_id'], $clientSite['site_id'], $badge['badge_id']);
        $this->assertEquals($badge['name'], $return);
    }

    public function testGetBadgeNotificationFlag()
    {
        // TODO : Create test case.
    }

    /**
     * @depends testAllBadges
     */
    public function testGeBadgeIDByName(array $badge)
    {
        $badge_model = new Badge_model();
        $clientSite = $this->getClientSite();

        $return = $badge_model->getBadgeIDByName($clientSite['client_id'], $clientSite['site_id'], $badge['name']);
        $this->assertEquals($badge['badge_id'], $return);
    }
}