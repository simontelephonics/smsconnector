<?php
include '/etc/freepbx.conf';

if ($_SERVER['REQUEST_METHOD'] === "GET") {
    if (empty($_GET['id']) || empty($_GET['name'])) { //shenanigans
        http_response_code(404);
    }
    $id = $_GET['id'];
    $name = $_GET['name'];

    $sql = 'SELECT * FROM sms_media WHERE id = :id AND name = :name';
    $stmt = FreePBX::Database()->prepare($sql);
    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetchObject();

    $finfo = new \finfo(FILEINFO_MIME);
    header('Content-Type: ' . $finfo->buffer($row->raw));
    echo $row->raw;
} else {
    http_response_code(405);
}

