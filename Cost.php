<?php
namespace API;
require_once '../DatabaseHandler.php';

class Cost
{
    public static function calculateCost($employeeId, $duration, $time)
    {
        $employeeSalary = self::getEmployeeSalary($employeeId);
        $cost = 0;
        $dt = \DateTime::createFromFormat("Y-m-d H:i:s", $time);
        $hours = $dt->format('H');

        $var = floatval($duration) + floatval($hours);

        if ($var > 18)
        {
            $overTimeDuration = $var - 18;
            $overTime = $overTimeDuration * ($employeeSalary * 1.5);
            $timeTmp = floatval($duration) - $overTimeDuration;
            $normalWage = $timeTmp * $employeeSalary;
            $cost = $overTime + $normalWage;
        }
        else if ($hours < 6)
        {
            $overTimeWindow = 6 - $hours;
            if (floatval($duration) > $overTimeWindow)
            {
                $overTime = ($overTimeWindow * $employeeSalary) * 1.5;
                $normalWage = (floatval($duration) - $overTimeWindow) * $employeeSalary;
                $cost = $overTime + $normalWage;
            }
            else
            {
                $cost = ($employeeSalary * floatval($duration)) * 1.5;
            }

        }
        else
        {
            $cost = $employeeSalary * floatval($duration);
        }

        return $cost;
    }

    public static function getEmployeeSalary($employeeId)
    {
        $db = new \API\DatabaseHandler();
        $selectStatement = "SELECT HourlyWage FROM Employees WHERE EmployeeID = " . $employeeId;
        $result = $db->selectQuery($selectStatement);
        $employeeSalary = 0;
        foreach($result as $row)
        {
            $employeeSalary = floatval($row['HourlyWage']);
        }

        return $employeeSalary;
    }
}