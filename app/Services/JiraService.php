<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

class JiraService
{
    private $client;
    private $projectKey;

    private $email;
    private $apiToken;

    public function __construct()
    {
        $configPath = __DIR__ . '/../Config/jira.php';

        if (!file_exists($configPath)) {
            throw new \Exception('Jira config file not found.');
        }

        $config = require $configPath;

        if (
            empty($config['base_url']) ||
            empty($config['email']) ||
            empty($config['api_token']) ||
            empty($config['project_key'])
        ) {
            throw new \Exception('Jira config is incomplete.');
        }

        $this->email = $config['email'];

        $this->apiToken = $config['api_token'];

        $this->projectKey = $config['project_key'];

        $this->client = new Client([
            'base_uri' => rtrim($config['base_url'], '/') . '/',
            'timeout'  => 15,
            'headers'  => [
                'Authorization' => 'Basic ' . base64_encode(
                        $config['email'] . ':' . $config['api_token']
                    ),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getApiToken()
    {
        return $this->apiToken;
    }

    public function getAuthString()
    {
        return $this->email . ":" . $this->apiToken;
    }

    /*
    |--------------------------------------------------------------------------
    | GET ASSIGNABLE USERS
    |--------------------------------------------------------------------------
    */
    public function getAssignableUsers()
    {
        try {

            $response = $this->client->get('/rest/api/3/user/assignable/search', [
                'query' => [
                    'project' => $this->projectKey,
                ],
            ]);

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {

            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE TASK
    |--------------------------------------------------------------------------
    */
    public function createTask($data)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Handle Description Format
            |--------------------------------------------------------------------------
            */

            if (is_array($data['description'])) {
                // Nếu đã là ADF JSON thì dùng luôn
                $description = $data['description'];
            } else {
                // Nếu là text thường → convert sang ADF
                $description = [
                    'type' => 'doc',
                    'version' => 1,
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [[
                            'type' => 'text',
                            'text' => $data['description']
                        ]]
                    ]]
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Build Payload
            |--------------------------------------------------------------------------
            */

            $payload = [
                'fields' => [
                    'project' => [
                        'key' => $this->projectKey
                    ],
                    'summary' => $data['summary'],
                    'description' => $description,
                    'issuetype' => [
                        'name' => 'Task'
                    ]
                ]
            ];

            if (!empty($data['assignee'])) {
                $payload['fields']['assignee'] = [
                    'accountId' => $data['assignee']
                ];
            }

            if (!empty($data['labels'])) {
                $payload['fields']['labels'] = array_values($data['labels']);
            }

            if (!empty($data['priority'])) {
                $payload['fields']['priority'] = [
                    'id' => $data['priority']
                ];
            }

            if (!empty($data['duedate'])) {
                $payload['fields']['duedate'] = $data['duedate'];
            }

            /*
            |--------------------------------------------------------------------------
            | Send Request
            |--------------------------------------------------------------------------
            */

            $response = $this->client->post('/rest/api/3/issue', [
                'json' => $payload
            ]);

            return json_decode($response->getBody(), true);

        } catch (ClientException $e) {

            $errorBody = $e->getResponse()->getBody()->getContents();

            return [
                'error' => true,
                'status' => $e->getResponse()->getStatusCode(),
                'jira_error' => json_decode($errorBody, true)
            ];

        } catch (RequestException $e) {

            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getTasks()
    {
        try {
            $response = $this->client->get('/rest/api/3/search', [
                'query' => [
                    'jql' => 'project=' . $this->projectKey . ' ORDER BY created DESC',
                    'maxResults' => 20
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['issues'] ?? [];

        } catch (\Exception $e) {
            return [];
        }
    }

    public function getBoardTasks()
    {
        $response = $this->client->get('/rest/api/3/search/jql', [
            'query' => [
                'jql' => "project = {$this->projectKey} ORDER BY Rank ASC",
                'fields' => 'summary,status,assignee,duedate,priority,parent,description,attachment,labels',
                'maxResults' => 100
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['issues'] ?? [];
    }

    public function transitionIssue($issueKey, $transitionId)
    {
        try {
            $this->client->post("/rest/api/3/issue/{$issueKey}/transitions", [
                'json' => [
                    'transition' => [
                        'id' => $transitionId
                    ]
                ]
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getTaskById($id)
    {
        $url = "https://dev-scvweb.atlassian.net/rest/api/3/issue/" . $id;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->email . ":" . $this->apiToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response, true);
    }

    public function getTransitions($issueKey)
    {
        try {
            $response = $this->client->get("/rest/api/3/issue/{$issueKey}/transitions");

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            return [];
        }
    }

    public function assignIssue($issueKey, $accountId)
    {
        try {
            $response = $this->client->put("/rest/api/3/issue/{$issueKey}/assignee", [
                'auth' => [$this->email, $this->apiToken],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'accountId' => $accountId
                ]
            ]);

            return true;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateIssue($issueId, $data)
    {
        try {
            $response = $this->client->put("/rest/api/3/issue/{$issueId}", [
                'json' => $data
            ]);

            return json_decode($response->getBody(), true);

        } catch (ClientException $e) {

            $errorBody = $e->getResponse()->getBody()->getContents();

            return [
                'error' => true,
                'status' => $e->getResponse()->getStatusCode(),
                'jira_error' => json_decode($errorBody, true)
            ];

        } catch (RequestException $e) {

            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function uploadAttachment($issueKey, $filePath, $fileName)
    {
        try {

            $response = $this->client->post(
                "/rest/api/3/issue/{$issueKey}/attachments",
                [
                    'headers' => [
                        'X-Atlassian-Token' => 'no-check'
                    ],
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => fopen($filePath, 'r'),
                            'filename' => $fileName
                        ]
                    ]
                ]
            );

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAllLabels()
    {
        $cacheFile = __DIR__ . '/../../storage/cache/labels.json';
        $cacheTime = 3600; // 1 giờ

        // Nếu có cache và chưa hết hạn → dùng luôn
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        // Nếu không có cache → gọi API
        $allLabels = [];
        $startAt = 0;
        $maxResults = 50;

        try {
            do {
                $response = $this->client->get('/rest/api/3/label', [
                    'query' => [
                        'startAt' => $startAt,
                        'maxResults' => $maxResults
                    ]
                ]);

                $data = json_decode($response->getBody(), true);

                $labels = $data['values'] ?? [];
                $allLabels = array_merge($allLabels, $labels);

                $startAt += $maxResults;

            } while (!empty($labels));

            // Lưu cache
            if (!empty($allLabels)) {
                // tạo folder nếu chưa có
                if (!file_exists(dirname($cacheFile))) {
                    mkdir(dirname($cacheFile), 0777, true);
                }

                file_put_contents($cacheFile, json_encode($allLabels));
            }

        } catch (\Exception $e) {
            // nếu API lỗi → fallback cache cũ (nếu có)
            if (file_exists($cacheFile)) {
                return json_decode(file_get_contents($cacheFile), true);
            }
            return [];
        }

        return $allLabels;
    }


    public function updateDueDate($issueKey, $duedate)
    {
        try {
            $response = $this->client->put("/rest/api/3/issue/{$issueKey}", [
                'json' => [
                    'fields' => [
                        'duedate' => $duedate
                    ]
                ]
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getPriorities()
    {
        try {
            $response = $this->client->get('/rest/api/3/priority');
            $data = json_decode($response->getBody(), true);

            return $data ?? [];

        } catch (\Exception $e) {
            return [];
        }
    }

    public function deleteTask($issueKey)
    {
        try {
            // 1. get issue
            $issue = $this->client->get("/rest/api/3/issue/{$issueKey}");
            $data = json_decode($issue->getBody(), true);

            $subtasks = $data['fields']['subtasks'] ?? [];

            // 2. delete subtasks
            foreach ($subtasks as $sub) {
                $this->client->delete("/rest/api/3/issue/" . $sub['key']);
            }

            // 3. delete parent
            $this->client->delete("/rest/api/3/issue/{$issueKey}");

            return ['success' => true];

        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }



}