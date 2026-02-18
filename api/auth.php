<?php
function requireAuth($conn) {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["error" => "Authorization header missing"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $headers['Authorization']);

    $sql = "SELECT user_id FROM crisnil_users WHERE api_token = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $rider = mysqli_fetch_assoc($result);

    if (!$rider) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid token"]);
        exit;
    }

    return $rider['user_id'];
}
