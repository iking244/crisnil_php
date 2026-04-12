<?php
// models/ActionLog.php

class ActionLog {

    private $db;

    public function __construct($db) {
        $this->db = $db;  // receives $databaseconn from your connection file
    }

    /**
     * Save a new action log entry
     *
     * @param int|null $user_id      User ID (null = system/guest)
     * @param string   $action       Short action keyword (e.g. 'login', 'logout', 'upload_photo')
     * @param string   $description  Optional longer details
     * @return bool                  true = success, false = failed
     */
    public function create($user_id, $action, $description = '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $user_id     = ($user_id === null) ? null : (int)$user_id;
        $action      = trim($action);
        $description = trim($description);

        $stmt = $this->db->prepare("
            INSERT INTO action_logs 
            (user_id, action, description, ip_address)
            VALUES (?, ?, ?, ?)
        ");

        if ($stmt === false) {
            error_log("ActionLog prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("isss", $user_id, $action, $description, $ip);

        $success = $stmt->execute();

        if (!$success) {
            error_log("ActionLog execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $success;
    }
}