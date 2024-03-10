<?php
require_once 'client.php';


class ImportSchedule {
    public $employees;
    public $employeeShifts;

    public $intCompanyId;

    public $rostersList;
    public function __construct() {
        /**
         * Setting the time and Memory Limits
         */
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        /**
         * Setting Default Timezone
         */
        date_default_timezone_set("UTC");

        /**
         * Reading Employees and Schedule json files
         */
        $this->employees = json_decode(file_get_contents('json_files/employees.json'), 1);
        $this->employeeShifts = json_decode(file_get_contents('json_files/schedule.json'), 1);

        /**
         * Company ID
         */
        $this->intCompanyId = 1; // replace it by your company id
    }


    public function import() {

        /**
         * Get Rosters List
         */
        $this->getRosterList();
        foreach ($this->employees as $employee) {
            $employee['intCompanyId'] = $this->intCompanyId;
            $this->initEmployee($employee); // add/update and get employee
        }

    }


    /**
     * Initialize the Employee, Map the schedule and Update
     * @param array $newEmployeesData
     * @return void
     * @throws Exception
     */
    public function initEmployee(array $newEmployeesData)
    {
        /**
         * API endpoint for creating a new employee
         */
        $endpoint = "api/v1/supervise/employee";

        /**
         * Send Request
         */
        $response = Client::request('Post', $endpoint, $newEmployeesData);

        /**
         *  Response Status Code
         */
        $statusCode = $response->getStatusCode();
        if ($statusCode === 200) {
            /**
             * Decode Response Data into PHP Array
             */
            $responseData = json_decode($response->getBody(), true);
            if(isset($responseData)) {
                echo $responseData['DisplayName'] . "  Initialized \n <br>";
                $this->importEmployeeSchedule($newEmployeesData['id'], $responseData['Id'], $responseData['DisplayName']);
            }
        }
    }

    /**
     * Import Employee Schedule
     * @param $internalEmployeeId
     * @param $deputyEmployeeId
     * @param $employeeName
     * @return void
     */
    public function importEmployeeSchedule($internalEmployeeId,$deputyEmployeeId, $employeeName) {
        if(isset($this->employeeShifts[$internalEmployeeId])) {
            $shift = $this->employeeShifts[$internalEmployeeId];
            $endpoint = "api/v1/supervise/roster";  // Replace with the actual endpoint

            /**
             * Create the request payload to schedule the employee
             */
            $empShift['intStartTimestamp'] = strtotime($shift['start']);
            $empShift['intEndTimestamp'] = strtotime($shift['end']);
            $empShift['intRosterEmployee'] = $deputyEmployeeId;
            $empShift['blnPublish'] = true;
            $empShift['intOpunitId'] = 1;
            $empShift['blnForceOverwrite'] = 0;
            $empShift['strComment'] =  "Roster via API";
            $empShift['blnOpen'] = 0;
            $empShift['intConfirmStatus'] = 1;

            /**
             * Find Roster ID
             * If Roster ID exists, the schedule will be updated/replaced with existing one
             */
            $rosterId = $this->findRosterId($empShift['intStartTimestamp'], $empShift['intEndTimestamp'], $deputyEmployeeId);
            if($rosterId && $rosterId > 0) {
                $empShift['intRosterId'] = $rosterId;
            }

            try {

                $response = Client::request('Post', $endpoint, $empShift);

                /**
                 * Employee TimeSlot Updated
                 */
                if($response->getStatusCode() == 200) {
                    echo "$employeeName's schedule added \n <br>";
                }
            } catch (Exception $e) {
                /**
                 * If the employee is already working on the given timeslot
                 */
                if($e->getMessage() == 'OverlapDetected') {
                    echo "$employeeName is currently occupied during the specified time slot\n <br>";
                }
            }

        } else {
            echo "No Schedule found for $employeeName \n <br>";
        }
    }


    /**
     * Get a list of rosters from last 12 hours, and forward 36 hours for all the employees
     * @return mixed
     * @throws Exception
     */
    public function getRosterList() {
        $endpoint = "api/v1/supervise/roster";
        $response = Client::request('GET', $endpoint);
        $this->rostersList = json_decode($response->getBody());
        return $this->rostersList;
    }


    /**
     * Find Roster ID to update existing Schedule
     * @param $startTime
     * @param $endTime
     * @param $empId
     * @return void|null
     */
    public function findRosterId($startTime, $endTime, $empId) {
       $employeeRosters = $this->getRostersByEmployeeID($empId);
       $rosterId = null;
       foreach ($employeeRosters as $roster) {
           if(($startTime >= $roster->StartTime and $startTime <= $roster->EndTime) or ($endTime >= $roster->StartTime and $endTime <= $roster->EndTime) ) {
                $rosterId = $roster->Id;
           }
       }
       return $rosterId;
    }

    /**
     * Find Rosters of particular Employee
     * @param $empId
     * @return array
     */
    public function getRostersByEmployeeID($empId): array
    {
        $employeeRosters = [];
        foreach ($this->rostersList as  $roster) {
            if($roster->Employee == $empId) {
                $employeeRosters[] = $roster;
            }
        }
        return $employeeRosters;
    }
}


try {
    /**
     * Import Data
     */
    $importer = new ImportSchedule();
    $importer->import();
} catch (Throwable $e) {
    die(var_export($e->getMessage()));
}



