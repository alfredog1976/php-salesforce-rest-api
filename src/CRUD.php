<?php

namespace bjsmasth\Salesforce;

use GuzzleHttp\Client;

class CRUD
{
    protected $instance_url;
    protected $access_token;

    public function __construct()
    {
        if (!isset($_SESSION) and !isset($_SESSION['salesforce'])) {
            throw new \Exception('Access Denied', 403);
        }

        $this->instance_url = $_SESSION['salesforce']['instance_url'];
        $this->access_token = $_SESSION['salesforce']['access_token'];
    }

    public function query($query)
    {
        $url = "$this->instance_url/services/data/v39.0/query";

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token"
            ],
            'query' => [
                'q' => $query
            ]
        ]);

        return json_decode($request->getBody(), true);
    }

    public function create($object, array $data)
    {
        $url = "$this->instance_url/services/data/v39.0/sobjects/$object/";

        $client = new Client();

        $request = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 201) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        $response = json_decode($request->getBody(), true);
        $id = $response["id"];

        return $id;

    }

    public function update($object, $id, array $data)
    {
        $url = "$this->instance_url/services/data/v39.0/sobjects/$object/$id";

        $client = new Client();

        $request = $client->request('PATCH', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 204) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        return $status;
    }

    public function delete($object, $id)
    {
        $url = "$this->instance_url/services/data/v39.0/sobjects/$object/$id";

        $client = new Client();
        $request = $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
            ]
        ]);

        $status = $request->getStatusCode();

        if ($status != 204) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        return true;
    }
    
    public function search($search_string)
    {
    	// Replace characters with backslash + char so SF can search correctly
    	$search  = array('?', '&', '|', '!', '{', '}', '[', ']', '(', ')', '^', '~', '*', ':', '+', '-', '"', "'");
		$replace = array('\?', '\&', '\|', '\!', '\{', '\}', '\[', '\]', '\(', '\)', '\^', '\~', '\*', '\:', '\+', '\-', '\"', "\'");

		// Check backslash special char first so they don't get replaced twice
		$search_string = str_replace($search, $replace, str_replace('\\', '\\\\', $search_string));
    	
        $url = "$this->instance_url/services/data/v39.0/search";

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token"
            ],
            'query' => [
                'q' => 'FIND {' . $search_string . '}'
            ]
        ]);
        return json_decode($request->getBody(), true);
    }

    public function sobjects()
    {
        $url = "$this->instance_url/services/data/v39.0/sobjects";

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token"
            ]
        ]);
        return json_decode($request->getBody(), true);
    }

    public function getSobjectFields($sobject, $id, $fields)
    {
        $url = "$this->instance_url/services/data/v39.0/sobjects/$sobject/$id";

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token"
            ],
            'query' => [
                'fields' => $fields
            ]
        ]);
        return json_decode($request->getBody(), true);
    }    
}
