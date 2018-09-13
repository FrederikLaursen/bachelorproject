<?php
namespace Test;
require '../Cost.php';

use API\Cost;
use PHPUnit\Framework\TestCase;

class CostTest extends TestCase
{

    public function testCalculateCost()
    {
        $this->assertEquals(Cost::calculateCost(1,"02:00",date("Y-m-d H:i:s", strtotime("2018-01-01 10:00:00"))), 240);
        $this->assertEquals(Cost::calculateCost(1,"02:00",date("Y-m-d H:i:s", strtotime("2018-01-01 05:00:00"))), 300);
        $this->assertEquals(Cost::calculateCost(1,"02:00",date("Y-m-d H:i:s", strtotime("2018-01-01 18:00:00"))), 360);
    }

    public function testGetEmployeeSalary()
    {
        $this->assertEquals(Cost::getEmployeeSalary(1),120);
    }
}
