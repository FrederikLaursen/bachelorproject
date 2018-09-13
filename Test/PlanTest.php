<?php
namespace Test;
require_once '../myclass.php';
require_once '../Plan.php';
require_once '../Route.php';

use API\myclass;
use Plan;
use PHPUnit\Framework\TestCase;

class planTest extends TestCase
{
    public function testAlgorithm()
    {
        $plan = new Plan("Nørregade 28B", "5000", \DateTime::createFromFormat('Y-m-d H:i:s', '2018-05-27 10:00:00'), 1);
        $result = $plan->getScheduledTask();
        $this->assertNotNull($result);
        $this->assertEquals("2", $result->ID);
    }
}
