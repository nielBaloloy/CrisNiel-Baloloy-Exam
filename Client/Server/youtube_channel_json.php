<?php

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require('./connectionDB.php');

$channelId ='UC_x5XG1OV2P6uZZ5FSM9Ttw';
$jsonData = [];

// Check database connection
    if ($conn->connect_error) {
        $jsonData = ['error' => 'Database connection failed: ' . $conn->connect_error];
        echo json_encode($jsonData);
        exit();
    }

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $itemsPerPage = 20;
    $offset = ($page - 1) * $itemsPerPage;

//  Fetch channel information

    $stmt = $conn->prepare("SELECT * FROM youtube_channels WHERE channel_id = ?");
    $stmt->bind_param("s", $channelId);
    $stmt->execute();
    $result = $stmt->get_result();
    $channel = $result->fetch_assoc();
    $stmt->close();
// Fetch videos
    $stmt = $conn->prepare("SELECT * FROM youtube_channel_videos WHERE channel_id = ? LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $channelId, $itemsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $videos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

//  Count total pages
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM youtube_channel_videos WHERE channel_id = ?");
    $stmt->bind_param("s", $channelId);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalVideos = $result->fetch_assoc()['count'];
    $totalPages = ceil($totalVideos / $itemsPerPage);
    $stmt->close();

$conn->close();

// Create JSON feed
    $jsonData = [
        'channel' => $channel,
        'videos' => $videos,
        'totalPages' => $totalPages
    ];

    header('Content-Type: application/json');
    echo json_encode($jsonData);


?>
