<?php
// Origin
$originLat = 14.429345;
$originLng = 121.039821;

// Destination
$destLat = 14.599512;
$destLng = 120.984222;

// IMPORTANT: output=embed is REQUIRED
$mapUrl = "https://www.google.com/maps?"
        . "saddr=$originLat,$originLng"
        . "&daddr=$destLat,$destLng"
        . "&output=embed";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Point to Point GPS</title>
    <style>
        iframe {
            width: 100%;
            height: 500px;
            border: 0;
        }
    </style>
</head>
<body>

<iframe
    src="<?php echo $mapUrl; ?>"
    loading="lazy"
    referrerpolicy="no-referrer-when-downgrade">
</iframe>

</body>
</html>
