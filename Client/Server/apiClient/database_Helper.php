<?php


namespace App;

class DatabaseHelper
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function saveChannel($channelData)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO youtube_channels (channel_id, channel_name, channel_description, channel_image)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE channel_name = VALUES(channel_name), channel_description = VALUES(channel_description), channel_image = VALUES(channel_image)
        ");
        $stmt->bind_param("ssss", $channelData['channel_id'], $channelData['name'], $channelData['description'], $channelData['profile_picture']);
        $stmt->execute();
        $stmt->close();
    }

    public function saveVideos($videos)
    {
        $stmt = $this->conn->prepare("
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
}
?>
