<?php
namespace Test;

require '../Route.php';
use PHPUnit\Framework\TestCase;

class testTest extends TestCase
{
    public function testFlow(){
        $routeMe = new \API\Route("123","Revningevej 74","Kerteminde", "5300");
        $result = $routeMe->getRouteLength();
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('totalDistance',$result);
        $this->assertArrayHasKey('totalTime',$result);
    }
}
