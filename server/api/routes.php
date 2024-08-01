<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

/*API Endpoint Router*/

require_once "./modules/get.php";
require_once "./modules/post.php";
require_once "./modules/delete.php";
require_once "./config/database.php";
require_once __DIR__ . '/bootstrap.php';

// Initialize Get and Post objects

$get = new Get();
$post = new Post();
$delete = new Delete();


// Check if 'request' parameter is set in the request
if (isset($_REQUEST['request'])) {
    // Split the request into an array based on '/'
    $request = explode('/', $_REQUEST['request']);
} else {
    // If 'request' parameter is not set, return a 404 response
    echo "Not Found";
    http_response_code(404);
}

// Handle requests based on HTTP method
switch ($_SERVER['REQUEST_METHOD']) {
    // Handle GET requests
    case 'OPTIONS':
        // Respond to preflight requests
        http_response_code(200);
        exit();

    case 'GET':
        switch ($request[0]) {
            case 'user':
                if (count($request) > 1) {
                    echo json_encode($get->get_users($request[1]));
                } else {
                    echo json_encode($get->get_users());
                }
                break;

            default:
                echo "This is forbidden";
                http_response_code(403);
                break;
        }
        break;


    case 'POST':
        // Retrieves JSON-decoded data from php://input using file_get_contents
        $data = json_decode(file_get_contents("php://input"));
        switch ($request[0]) {

            case 'search-city':
                echo json_encode($post->searchByCity($data));
                break;
            case 'search-location':
                echo json_encode($post->searchByLocation($data));
                break;

            case 'reverse-geocode-location':
                echo json_encode($post->reverseGeocodeLocation($data));
                break;
            case 'geocode-city':
                echo json_encode($post->reverseGeocodeCity($data));
                break;


            default:
                // Return a 403 response for unsupported requests
                echo "No Such Request";
                http_response_code(403);
                break;
        }
        break;

    case 'DELETE':
        switch ($request[0]) {
            case 'deleteuser':
                if (isset($request[1])) {
                    echo json_encode($delete->delete_user($request[1]));
                } else {
                    echo "Submission ID not provided";
                    http_response_code(400);
                }
                break;


            default:
                // Return a 403 response for unsupported requests
                echo "No Such Request";
                http_response_code(403);
                break;
        }
        break;

    default:
        // Return a 404 response for unsupported HTTP methods
        echo "Unsupported HTTP method";
        http_response_code(404);
        break;
}