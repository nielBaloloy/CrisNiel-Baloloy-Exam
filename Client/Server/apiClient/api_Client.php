<?php
namespace App;

use Google\Client;
use Google\Service\YouTube;

class YouTubeClient
{
    private $service;

    public function __construct($apiKey)
    {
        $client = new Client();
        $client->setDeveloperKey($apiKey);
        $this->service = new YouTube($client);
    }

    public function getChannelDetails($channelId)
    {
        return $this->service->channels->listChannels('snippet,contentDetails', ['id' => $channelId]);
    }

    public function getPlaylistItems($playlistId, $pageToken = null)
    {
        return $this->service->playlistItems->listPlaylistItems('snippet', [
            'playlistId' => $playlistId,
            'maxResults' => 50,
            'pageToken' => $pageToken
        ]);
    }
}
?>
