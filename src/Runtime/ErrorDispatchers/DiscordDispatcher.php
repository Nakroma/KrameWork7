<?php

namespace KrameWork\Runtime\ErrorDispatchers;

use KrameWork\Runtime\ErrorReports\IErrorReport;

require_once(__DIR__ . '/IErrorDispatcher.php');

class DiscordDispatcher implements IErrorDispatcher
{
    /**
     * @var string
     */
    private string $webhookURL;
    /**
     * @var string|null
     */
    private ?string $threadId;
    /**
     * @var string|null
     */
    private ?string $username;
    /**
     * @var string|null
     */
    private ?string $avatarUrl;

    /**
     * DiscordDispatcher constructor.
     *
     * @api __construct
     * @param string $webhookURL Discord webhook URL to send this request to.
     * @param string|null $threadId Discord Thread ID to send this report to.
     * @param string|null $username Username to send this report as.
     * @param string|null $avatarUrl Avatar URL to send this report as.
     */
    public function __construct(string $webhookURL, string $threadId = null, string $username = null, string $avatarUrl = null)
    {
        $this->webhookURL = $webhookURL;
        $this->threadId = $threadId;
        $this->username = $username;
        $this->avatarUrl = $avatarUrl;
    }

    /**
     * Dispatch an error report.
     *
     * @api dispatch
     * @param IErrorReport|string $report Report to dispatch.
     * @return bool
     */
    public function dispatch($report): bool
    {
        // Remove multiple \n from report
        $report = preg_replace('/\n{2,}/', "\n", $report);

        $parts = str_split($report, 2000 - 6); // 2000 is the max length of a Discord message, 6 is for the code block markdown
        foreach ($parts as $part) {
            $payload = [
                'content' => '```' . $part . '```'
            ];

            if ($this->username !== null) {
                $payload['username'] = $this->username;
            }

            if ($this->avatarUrl !== null) {
                $payload['avatar_url'] = $this->avatarUrl;
            }

            // Make POST request
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n",
                    'content' => json_encode($payload)
                ]
            ]);

            $url = $this->webhookURL;
            if ($this->threadId !== null) {
                $url .= '?thread_id=' . $this->threadId;
            }
            $result = file_get_contents($url, false, $context);

            if ($result === false) {
                return false;
            }
        }

        return true;
    }
}
