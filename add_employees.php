<?php
require_once 'client.php';


/**
 * Create Multiple Employees
 */
$newEmployeesData = [
    [
        "strFirstName" => "Amer",
        "strLastName" => "Chaudhuri",
        "displayName" => "Amer Amer",
        "intCompanyId" => 1
    ],
    [
        "strFirstName" => "Test",
        "strLastName" => "Employee",
        "displayName" => "Test Employee",
        "intCompanyId" => 1
    ]
];

/**
 * API endpoint for creating a new employee
 */
$endpoint = "https://353f6527123646.as.deputy.com/api/v1/supervise/employee";  // Replace with the actual endpoint

/**
 * Iterate over employees and create one by one
 */

foreach ($newEmployeesData as $employee) {

    /**
     * Create a new Employee
     */
    createNewEmployee($endpoint, $employee);
}


/**
 * @param $endpoint
 * @param array $newEmployeesData
 * @return void
 */
function createNewEmployee($endpoint, array $newEmployeesData)
{
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
//        echo "<pre>";
//        print_r($responseData);
//        echo "</pre> <br>";
        if ($responseData['isNew']) {
            echo "Employee with the name of ". $responseData['DisplayName'] . " Added Successfully <br>";

        } else {

            echo "Employee with the name of ". $responseData['DisplayName'] . " Updated Successfully <br>";

        }
    }
}