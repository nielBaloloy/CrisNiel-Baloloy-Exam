<?php
require 'vendor/autoload.php';
require './connectionDB.php';
require './apiClient/api_Client.php';
require './apiClient/database_Helper.php';

use App\YouTubeClient;
use App\DatabaseHelper;
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get the API key from environment variables
$apiKey = $_ENV['YOUTUBE_API_KEY'] ?? null; 
if (!$apiKey) {
    die("API key not set. Check your .env file.");
}

$channelIds = ['UCWv7vMbMWH4-V0ZXdmDpPBA', 'UC_x5XG1OV2P6uZZ5FSM9Ttw'];

// Initialize clients
$youtubeClient = new YouTubeClient($apiKey);
$databaseHelper = new DatabaseHelper($conn);

foreach ($channelIds as $channelId) {
    // Get channel details
    $response = $youtubeClient->getChannelDetails($channelId);
    if (empty($response->items)) {
        die("Channel not found: $channelId");
    }

    $channel = $response->items[0];
    $channelData = [
        'channel_id' => $channel->id,
        'name' => $channel->snippet->title,
        'description' => $channel->snippet->description,
        'profile_picture' => $channel->snippet->thumbnails->default->url
    ];

    // Save channel details
    $databaseHelper->saveChannel($channelData);

    // Get channel uploads playlist ID
    $uploadsPlaylistId = $channel->contentDetails->relatedPlaylists->uploads;

    // Get videos from the uploads
    $videos = [];
    $pageToken = null;

    do {
        $response = $youtubeClient->getPlaylistItems($uploadsPlaylistId, $pageToken);

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
    $databaseHelper->saveVideos($videos);
}

$conn->close();

echo "Channels and videos synchronized successfully.";
?>
