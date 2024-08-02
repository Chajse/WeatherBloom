<?php

require_once 'global.php';

class Post extends GlobalMethods
{
    private $pdo;
    private $openWeatherKey;

    public function __construct()
    {
        $this->openWeatherKey = 'a185773a207b0315d9aac60d7dd7cb42';
    }

    public function searchByCity($data)
    {
        $validMetricSystems = ['&units=standard', '&units=metric', '&units=imperial'];
        if (!in_array($data->metricSystem, $validMetricSystems)) {
            return $this->sendPayload(null, "failed", "Invalid metric system..", 400);
        }
        if (!isset($data->city) || empty($data->city)) {
            return $this->sendPayload(null, "failed", "Please enter a City.", 400);
        }

        $city = $data->city;
        $metricSystem = $data->metricSystem;
        $api_key = $this->openWeatherKey;
        $api = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$api_key&$metricSystem";


        // Initialize cURL session
        $ch = curl_init();

        // Set the URL and other options
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Start curl session.
        $result = curl_exec($ch);

        // Check for curl errors
        if ($result === false) {
            curl_close($ch);
            return $this->sendPayload(null, "failed", "Unable to fetch weather data.", 500);
        }

        // Get HTTP status code.
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close curl session.
        curl_close($ch);


        // If city is not found.
        if ($http_status == 404) {
            return $this->sendPayload(null, "failed", "Please enter a valid city.", 404);
        } elseif ($http_status != 200) {
            return $this->sendPayload(null, "failed", "An error occurred while fetching weather data.", $http_status);
        }

        // Decode received json from api to insert to payload.
        $resultArray = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendPayload(null, "failed", "Invalid JSON response.", 500);
        }

        // Send results.
        return $this->sendPayload($resultArray, "success", "Request Success!", 200);
    }

    public function searchByLocation($data)
    {
        $validMetricSystems = ['&units=standard', '&units=metric', '&units=imperial'];
        if (!in_array($data->metricSystem, $validMetricSystems)) {
            return $this->sendPayload(null, "failed", "Invalid metric system..", 400);
        }
        if (!isset($data->lat) || !isset($data->lon)) {
            return $this->sendPayload(null, "failed", "Please enter a Location.", 400);
        }

        $lat = $data->lat;
        $lon = $data->lon;
        $metricSystem = $data->metricSystem;
        $api_key = $this->openWeatherKey;
        $api = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=$api_key&$metricSystem";

        $result = file_get_contents($api);

        if ($result === false) {
            return $this->sendPayload(null, "failed", "Unable to fetch weather data.", 500);
        }

        $resultArray = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendPayload(null, "failed", "Invalid JSON response.", 500);
        }

        return $this->sendPayload($resultArray, "success", "Request Success!", 200);
    }

    public function reverseGeocodeLocation($data)
    {
        if (!isset($data->lat) || !isset($data->lon)) {
            return $this->sendPayload(null, "failed", "Please enter a Location.", 400);
        }

        $lat = $data->lat;
        $lon = $data->lon;
        $limit = 5;
        $api_key = $this->openWeatherKey;

        $api = "http://api.openweathermap.org/geo/1.0/reverse?lat=$lat&lon=$lon&limit=$limit&appid=$api_key";

        $result = file_get_contents($api);

        if ($result === false) {
            return $this->sendPayload(null, "failed", "Unable to fetch weather data.", 500);
        }

        $resultArray = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendPayload(null, "failed", "Invalid JSON response.", 500);
        }

        return $this->sendPayload($resultArray, "success", "Request Success!", 200);
    }

    public function reverseGeocodeCity($data)
    {
        if (!isset($data->city) || empty($data->city)) {
            return $this->sendPayload(null, "failed", "Please enter a City.", 400);
        }

        $city = $data->city;
        $limit = 5;
        $api_key = $this->openWeatherKey;

        $api = "http://api.openweathermap.org/geo/1.0/direct?q=$city&limit=$limit&appid=$api_key";

        $result = file_get_contents($api);

        if ($result === false) {
            return $this->sendPayload(null, "failed", "Unable to fetch weather data.", 500);
        }

        $resultArray = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendPayload(null, "failed", "Invalid JSON response.", 500);
        }

        return $this->sendPayload($resultArray, "success", "Request Success!", 200);
    }

    public function getForecastByLocation($data)
    {
        $validMetricSystems = ['&units=standard', '&units=metric', '&units=imperial'];
        if (!in_array($data->metricSystem, $validMetricSystems)) {
            return $this->sendPayload(null, "failed", "Invalid metric system..", 400);
        }
        if (!isset($data->lat) || !isset($data->lon)) {
            return $this->sendPayload(null, "failed", "Please enter a Location.", 400);
        }

        $lat = $data->lat;
        $lon = $data->lon;
        $metricSystem = $data->metricSystem;
        $api_key = $this->openWeatherKey;
        $api = "https://api.openweathermap.org/data/2.5/forecast?lat=$lat&lon=$lon&appid=$api_key&$metricSystem";

        $result = file_get_contents($api);

        if ($result === false) {
            return $this->sendPayload(null, "failed", "Unable to fetch weather data.", 500);
        }

        $resultArray = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendPayload(null, "failed", "Invalid JSON response.", 500);
        }

        return $this->sendPayload($resultArray, "success", "Request Success!", 200);
    }

}