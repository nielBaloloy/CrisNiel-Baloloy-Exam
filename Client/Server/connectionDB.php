<?php

// Database configuration
$host = 'localhost'; 
$dbname = 'youtube_db'; 
$username = 'root'; 
$password = ''; 

    $conn = new mysqli($host, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

            $sqlCreateChannelsTable = "
                CREATE TABLE IF NOT EXISTS youtube_channels (
                    channel_id VARCHAR(255) PRIMARY KEY,
                    channel_name VARCHAR(255),
                    channel_description TEXT,
                    channel_image VARCHAR(255)
                )
            ";


            $sqlCreateVideosTable = "
                CREATE TABLE IF NOT EXISTS youtube_channel_videos (
                    video_id VARCHAR(255) PRIMARY KEY,
                    channel_id VARCHAR(255),
                    title VARCHAR(255),
                    description TEXT,
                    thumbnail_url VARCHAR(255),
                    video_link VARCHAR(255),
                    published_at DATETIME,
                    FOREIGN KEY (channel_id) REFERENCES youtube_channels(channel_id)
                )
            ";

        if ($conn->query($sqlCreateChannelsTable) === TRUE && $conn->query($sqlCreateVideosTable) === TRUE) {
          
        } else {
            echo "Error creating tables: " . $conn->error;
        }



?>
