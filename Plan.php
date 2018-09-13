<?php
require_once '../Cost.php';
require_once '../Route.php';
require_once '../DatabaseHandler.php';
class Plan
{
    private $maxAttempts = 10;

    public $address; // The location of the task
    public $zipcode; // The zipcode of the task
    public $timeSpan; // The time in which the task can be done
    public $startTime; // Earliest start time for the task
    public $duration; // Duration of the task

    function __construct($address, $zipcode, $startTime, $duration)
    {
        $this->address = $address;
        $this->zipcode = $zipcode;
        $this->startTime = $startTime;
        $this->duration = $duration;
    }

    public function getScheduledTask(){
        $neighbours = $this->getNeighbours($this->startTime);
        $currentNode = (object)['ID'=> $neighbours->neighbours[0], 'time' => $neighbours->time];

        $nextEval = INF; // Set lowest possible starting value
        $nextNode = null;
        $iterations = 0;
        do{
            $neighbouringSolutions = $this->getNeighbours($currentNode->time);
            foreach($neighbouringSolutions->neighbours as $workerID){
                $currVal = $this->evaluateSolution($this->startTime, $workerID);
                if($currVal < $nextEval){
                    $nextNode = (object)['ID'=> $workerID, 'time' => $this->startTime];
                    $nextEval = $currVal;
                }
            }

            if($nextEval >= $this->evaluateSolution($currentNode->time, $currentNode->ID)){
                return $currentNode;
            }
            else
                $currentNode = $nextNode;

            $iterations++;
        }while($iterations < $this->maxAttempts);
    }

    // Returns the next available time for task
    public function getNextTime($time, $duration){
        $workers = $this->getAvailableWorkers($time);

        if($workers !== null)
            $availableTime = $time;
        else if($this->startTime->format('H') + 1 < 24)
            $this->startTime->add(new DateInterval('PT'. $duration .'H'));

        return $availableTime;
    }

    public function getAvailableWorkers($time){
        $db = new \API\DatabaseHandler();
        $busyEmployees = $db->selectQuery("SELECT EmployeeID FROM JobAssignments WHERE WorkStartTime < '". $time->add(new DateInterval('PT'. $this->duration .'H'))->format('H:i'). "'")->fetchAll(PDO::FETCH_COLUMN);
        $busyEmployees = implode(',', $busyEmployees);
        $available = $db->selectQuery("SELECT EmployeeID FROM Employees WHERE EmployeeID NOT IN (". $busyEmployees.")")->fetchAll(PDO::FETCH_COLUMN);
        return $available;
    }

    public function getNeighbours($time){
        $time = $this->getNextTime($time, $this->duration);
        $neighbours = $this->getAvailableWorkers($this->startTime);
        return (object) ['neighbours'=>$neighbours, 'time' => $time];
    }

    // Returns a number representing the fitness of the solution
    public function evaluateSolution($time, $employeeID){
        $totalCost = 0;
        $totalCost += API\Cost::calculateCost($employeeID, $this->duration, $time->format("Y-m-d H:i:s"));
        $route = new API\Route($employeeID, $time->format("H:i:s"), $this->address, $this->zipcode);
        $totalCost += $route->getRouteLength();
        //var_dump($employeeID. ": " .$totalCost);
        return $totalCost;
    }
}