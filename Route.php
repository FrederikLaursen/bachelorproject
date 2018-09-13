<?php
namespace API;
require_once '../DatabaseHandler.php';
class Route
{
    private $employeeId;
    private $address;
    private $zipCode;
    private $assignmentTime;

    function __construct($employeeId, $assignmentTime, $address, $zipCode)
    {
        $this->employeeId = $employeeId;
        $this->assignmentTime = $assignmentTime;
        $this->address = $address;
        $this->zipCode = $zipCode;
    }
    //TODO: Time error handling
    /**
     * Main loop for Route class. Takes the assignment info and tries to find a total route length in kilometers
     * @return bool|float|int
     */
    public function getRouteLength()
    {
        $totalTime = 0;
        $totalDistance = 0;
        $currentAssignments = $this->getData($this->employeeId);
        //Would be nice to throw an exception if input is >23:59
        $currentAssignments[] = [
            'Address' => $this->address,
            'ZipCode' => $this->zipCode,
            'WorkStartTime' => $this->assignmentTime,
        ];
        $currentAssignments = $this->sortRoute($currentAssignments);

        for ($i = 0; $i <= count($currentAssignments); $i++) {
            if (array_key_exists($i + 1, $currentAssignments)) {
                $reply = $this->GetInformation($currentAssignments[$i]['Address'], $currentAssignments[$i]['ZipCode'], $currentAssignments[$i + 1]['Address'], $currentAssignments[$i + 1]['ZipCode']);
                if ($this->validateAnswer($reply['status'])) {
                    $totalTime += floatval($reply['time']);
                    $totalDistance += floatval($reply['distance']);
                } else {
                    return false;
                }
            }
        }
        return $totalDistance;
    }

    /**
     * Takes addresses and queries google maps for information
     * @param $address1
     * @param $city1
     * @param $address2
     * @param $city2
     * @return array
     */
    private function GetInformation($address1, $city1, $address2, $city2)
    {
        $encodedStartAddress = urlencode($address1 . "," . $city1);
        $encodedEndAddress = urlencode($address2 . "," . $city2);
        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $encodedStartAddress . "&destination=" . $encodedEndAddress . "&mode=driving&key=insertkey";
        $response = $this->sendWebRequest($url);
        return array('distance' => $response->routes[0]->legs[0]->distance->text, 'time' => $response->routes[0]->legs[0]->duration->text, 'status' => $response->status);
    }

    /**
     * CURL web request
     * @param $url
     * @return mixed
     */
    private function sendWebRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $decodedResponse = json_decode($response);
        return $decodedResponse;
    }

    /**
     * Validates the google maps api answer
     * @param $status
     * @return bool
     */
    private function validateAnswer($status)
    {
        if ($status != 'OK') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns the cost according to kilometers
     * @param $totalDistance
     * @param $costPerKilometer
     * @return float|int
     */
    public function calculateCost($totalDistance, $costPerKilometer)
    {
        return $totalDistance * $costPerKilometer;
    }

    /**
     * Grabs assignments from DB
     * @param $employeeID
     * @return array
     */
    private function getData($employeeID)
    {
        $db = new \API\DatabaseHandler();
        $selectStatement = "SELECT Jobs.Address, Jobs.ZipCode, JobAssignments.WorkStartTime,JobAssignments.WorkDuration FROM JobAssignments 
                            INNER JOIN Jobs ON Jobs.JobID = JobAssignments.JobID WHERE JobAssignments.EmployeeID =" . $employeeID;
        $result = $db->selectQuery($selectStatement)->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Sort times from low to high
     * @param $currentAssignments
     * @return mixed
     */
    private function sortRoute($currentAssignments)
    {
        usort($currentAssignments, function ($a, $b) {
            return strtotime($a['WorkStartTime']) - strtotime($b['WorkStartTime']);
        });
        return $currentAssignments;
    }
}