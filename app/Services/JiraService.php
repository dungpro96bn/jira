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
                'fields' => 'summary,status,assignee,duedate,priority,parent,description,attachment,labels,created',
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
        try {
            $response = $this->client->get("/rest/api/3/issue/{$id}", [
                'query' => [
                    'fields' => 'summary,status,assignee,reporter,duedate,priority,description,attachment,labels,subtasks,timetracking,comment,worklog,parent,created,updated',
                    'expand' => 'changelog'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return [];
        }
    }


    public function getIssuesByKeys($keys)
    {
        $keys = array_values(array_filter(array_unique((array)$keys)));

        if (empty($keys)) {
            return [];
        }

        $quoted = array_map(function ($key) {
            return '"' . str_replace('"', '\\"', $key) . '"';
        }, $keys);

        try {
            $response = $this->client->get('/rest/api/3/search/jql', [
                'query' => [
                    'jql' => 'key in (' . implode(',', $quoted) . ')',
                    'fields' => 'summary,status,assignee,priority,issuetype,parent',
                    'maxResults' => count($keys)
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['issues'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createSubtask($parentKey, $summary)
    {
        try {
            $response = $this->client->post('/rest/api/3/issue', [
                'json' => [
                    'fields' => [
                        'project' => ['key' => $this->projectKey],
                        'parent' => ['key' => $parentKey],
                        'summary' => $summary,
                        'issuetype' => ['name' => 'Sub-task']
                    ]
                ]
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function addComment($issueKey, $comment)
    {
        try {
            $response = $this->client->post("/rest/api/3/issue/{$issueKey}/comment", [
                'json' => [
                    'body' => [
                        'type' => 'doc',
                        'version' => 1,
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => [[
                                'type' => 'text',
                                'text' => $comment
                            ]]
                        ]]
                    ]
                ]
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
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


    public function addWorklog($issueKey, $timeSpent, $remainingEstimate = '', $comment = '')
    {
        try {
            $payload = [
                'timeSpent' => $timeSpent,
            ];

            if ($comment !== '') {
                $payload['comment'] = [
                    'type' => 'doc',
                    'version' => 1,
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [[
                            'type' => 'text',
                            'text' => $comment
                        ]]
                    ]]
                ];
            }

            $query = [
                'adjustEstimate' => $remainingEstimate !== '' ? 'new' : 'auto',
            ];

            if ($remainingEstimate !== '') {
                $query['newEstimate'] = $remainingEstimate;
            }

            $response = $this->client->post("/rest/api/3/issue/{$issueKey}/worklog", [
                'query' => $query,
                'json' => $payload
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();

            return [
                'success' => false,
                'message' => $errorBody ?: $e->getMessage()
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }



    public function deleteAttachment($attachmentId)
    {
        try {
            $this->client->delete("/rest/api/3/attachment/{$attachmentId}");

            return [
                'success' => true,
            ];
        } catch (ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();

            return [
                'success' => false,
                'message' => $errorBody ?: $e->getMessage(),
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getAllAttachments()
    {
        $issues = $this->getAllIssues();
        $attachments = [];

        foreach ($issues as $issue) {
            $fields = isset($issue['fields']) && is_array($issue['fields']) ? $issue['fields'] : [];
            $issueKey = isset($issue['key']) ? $issue['key'] : '';
            $issueSummary = !empty($fields['summary']) ? $fields['summary'] : 'Untitled issue';
            $issueAttachments = !empty($fields['attachment']) && is_array($fields['attachment']) ? $fields['attachment'] : [];

            foreach ($issueAttachments as $attachment) {
                $mime = !empty($attachment['mimeType']) ? $attachment['mimeType'] : 'application/octet-stream';
                $filename = !empty($attachment['filename']) ? $attachment['filename'] : 'attachment';
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $thumb = '';

                if (strpos($mime, 'image/') === 0) {
                    $thumb = '/attachment-proxy?id=' . urlencode($attachment['id']) . '&name=' . rawurlencode($filename);
                }

                $attachments[] = [
                    'id' => isset($attachment['id']) ? (string) $attachment['id'] : '',
                    'filename' => $filename,
                    'mimeType' => $mime,
                    'size' => isset($attachment['size']) ? (int) $attachment['size'] : 0,
                    'created' => !empty($attachment['created']) ? $attachment['created'] : '',
                    'content' => !empty($attachment['content']) ? $attachment['content'] : '',
                    'thumbnail' => !empty($attachment['thumbnail']) ? $attachment['thumbnail'] : '',
                    'preview' => $thumb,
                    'author' => !empty($attachment['author']['displayName']) ? $attachment['author']['displayName'] : 'Unknown',
                    'authorAvatar' => !empty($attachment['author']['avatarUrls']['24x24']) ? $attachment['author']['avatarUrls']['48x48'] : 'Unknown',
                    'issueKey' => $issueKey,
                    'issueSummary' => $issueSummary,
                    'extension' => $extension,
                    'isImage' => strpos($mime, 'image/') === 0,
                ];
            }
        }

        usort($attachments, function ($a, $b) {
            $aTime = !empty($a['created']) ? strtotime($a['created']) : 0;
            $bTime = !empty($b['created']) ? strtotime($b['created']) : 0;
            return $bTime <=> $aTime;
        });

        return $attachments;
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

    public function deleteTask($issueKey, $archivedBy = [])
    {
        try {
            $issue = $this->client->get("/rest/api/3/issue/{$issueKey}", [
                'query' => [
                    'fields' => 'summary,issuetype,reporter,assignee,created,updated,subtasks'
                ]
            ]);
            $data = json_decode($issue->getBody(), true);

            $this->saveArchivedWorkItem($data, $archivedBy);

            $subtasks = $data['fields']['subtasks'] ?? [];

            foreach ($subtasks as $sub) {
                $this->client->delete("/rest/api/3/issue/" . $sub['key']);
            }

            $this->client->delete("/rest/api/3/issue/{$issueKey}");

            return ['success' => true];

        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    public function getAllIssues()
    {
        try {

            $response = $this->client->get('/rest/api/3/search/jql', [
                'query' => [
                    'jql' => 'project = "' . $this->projectKey . '"',
                    'fields' => 'summary,status,assignee,duedate,priority,parent,description,attachment,labels,issuetype,created',
                    'maxResults' => 1000
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['issues'] ?? [];

        } catch (\Exception $e) {
            return [];
        }
    }

    public function updatePriority($issueKey, $priorityName)
    {
        return $this->client->put("rest/api/3/issue/{$issueKey}", [
            'json' => [
                'fields' => [
                    'priority' => [
                        'name' => $priorityName
                    ]
                ]
            ]
        ]);
    }

    public function allLabels()
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

    public function updateLabels($issueKey, $labels)
    {
        return $this->client->put("/rest/api/3/issue/{$issueKey}", [
            'json' => [
                'update' => [
                    'labels' => [
                        ['set' => array_values($labels)]
                    ]
                ]
            ]
        ]);
    }


    private function getArchiveStorePath()
    {
        return __DIR__ . '/../../storage/archive-work-items.json';
    }

    private function ensureArchiveStoreDirectory()
    {
        $dir = dirname($this->getArchiveStorePath());
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    public function getArchivedWorkItemsStore()
    {
        $path = $this->getArchiveStorePath();
        if (!file_exists($path)) {
            return [];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    public function saveArchivedWorkItem($issueData, $archivedBy = [])
    {
        $this->ensureArchiveStoreDirectory();

        $items = $this->getArchivedWorkItemsStore();
        $issueKey = $issueData['key'] ?? null;

        if (!$issueKey) {
            return false;
        }

        $fields = $issueData['fields'] ?? [];
        $reporter = $fields['reporter'] ?? [];
        $assignee = $fields['assignee'] ?? [];
        $issueType = $fields['issuetype'] ?? [];

        $record = [
            'key' => $issueKey,
            'summary' => $fields['summary'] ?? '',
            'issueType' => [
                'name' => $issueType['name'] ?? 'Task',
                'iconUrl' => $issueType['iconUrl'] ?? '',
                'subtask' => !empty($issueType['subtask']),
            ],
            'reporter' => [
                'name' => $reporter['displayName'] ?? 'Unknown',
                'avatar' => $reporter['avatarUrls']['48x48'] ?? '',
            ],
            'assignee' => [
                'name' => $assignee['displayName'] ?? 'Unassigned',
                'avatar' => $assignee['avatarUrls']['48x48'] ?? '',
            ],
            'created' => $fields['created'] ?? null,
            'updated' => $fields['updated'] ?? null,
            'archivedDate' => date('c'),
            'archivedBy' => [
                'name' => $archivedBy['name'] ?? 'Unknown',
                'avatar' => $archivedBy['avatar'] ?? '',
                'email' => $archivedBy['email'] ?? '',
            ],
        ];

        $filtered = [];
        foreach ($items as $item) {
            if (($item['key'] ?? '') !== $issueKey) {
                $filtered[] = $item;
            }
        }
        array_unshift($filtered, $record);

        file_put_contents($this->getArchiveStorePath(), json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }

}
