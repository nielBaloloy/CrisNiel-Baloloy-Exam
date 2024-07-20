<?php
require 'vendor/autoload.php';
require './connectionDB.php';

use Google\Client;
use Google\Service\YouTube;

// Configs
$apiKey = 'AIzaSyC9mGTx0NwPsJj2cm-AwUE61YcGvLyKtrA';
$channelIds = ['UCWv7vMbMWH4-V0ZXdmDpPBA', 'UC_x5XG1OV2P6uZZ5FSM9Ttw']; 

// Initialize YouTube API client
$client = new Client();
$client->setDeveloperKey($apiKey);
$service = new YouTube($client);

foreach ($channelIds as $channelId) {
    // Get channel details
    $response = $service->channels->listChannels('snippet,contentDetails', ['id' => $channelId]);
    $channel = $response->items[0];
    $channelData = [
        'channel_id' => $channel->id,
        'name' => $channel->snippet->title,
        'description' => $channel->snippet->description,
        'profile_picture' => $channel->snippet->thumbnails->default->url
    ];

    // Save channel details 
    $stmt = $conn->prepare("
        INSERT INTO youtube_channels (channel_id, channel_name, channel_description, channel_image)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE channel_name = VALUES(channel_name), channel_description = VALUES(channel_description), channel_image = VALUES(channel_image)
    ");
    $stmt->bind_param("ssss", $channelData['channel_id'], $channelData['name'], $channelData['description'], $channelData['profile_picture']);
    $stmt->execute();
    $stmt->close();

    // Get channel uploads playlist ID
    $uploadsPlaylistId = $channel->contentDetails->relatedPlaylists->uploads;

    // Get videos from the uploads
    $videos = [];
    $pageToken = null;

    do {
        $response = $service->playlistItems->listPlaylistItems('snippet', [
            'playlistId' => $uploadsPlaylistId,
            'maxResults' => 500,
            'pageToken' => $pageToken
        ]);

        foreach ($response->items as $item) {
            if (count($videos) < 100) {
                $videos[] = [
                    'channel_id' => $channelId,
                    'video_id' => $item->snippet->resourceId->videoId,
                    'title' => $item->snippet->title,
                    'description' => $item->snippet->description,
                    'thumbnail' => $item->snippet->thumbnails->default->url,
                    'video_link' => 'https://www.youtube.com/watch?v=' . $item->snippet->resourceId->videoId
                ];
            } else {
                break;
            }
        }

        $pageToken = $response->nextPageToken ?? null;
    } while ($pageToken && count($videos) < 100);

    // Save videos
    $stmt = $conn->prepare("
        INSERT INTO youtube_channel_videos (channel_id, video_id, title, description, thumbnail_url, video_link)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), thumbnail_url = VALUES(thumbnail_url), video_link = VALUES(video_link)
    ");

    foreach ($videos as $video) {
        $stmt->bind_param("ssssss", $video['channel_id'], $video['video_id'], $video['title'], $video['description'], $video['thumbnail'], $video['video_link']);
        $stmt->execute();
    }

    $stmt->close();
}

$conn->close();

echo "Channels and videos synchronized successfully.";
?>
