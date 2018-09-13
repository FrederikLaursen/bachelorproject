<?php
require '../Route.php';
use API\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testCanCreateRouteClass()
    {
        $route = new Route(123, '01:00', 'Stationsvej 2A', "5250");
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testGetRouteLengthNotEmpty()
    {
        $route = new Route(1, "08:00", "Overgade 22C", "5000");
        $result = $route->getRouteLength();
        $this->assertNotEmpty($result);
    }

    public function testGetRouteLengthAcceptsMinutes()
    {
        $route = new Route(1, "00:01", "Overgade 22C", "5000");
        $result = $route->getRouteLength();
        $this->assertNotEmpty($result);
    }

    public function testGetRouteLengthAcceptsHoursAndMinutes()
    {
        $route = new Route(1, "09:25", "Overgade 22C", "5000");
        $result = $route->getRouteLength();
        $this->assertNotEmpty($result);
    }
}
