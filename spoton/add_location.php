<?php 
include('server.php'); 
require 'vendor/autoload.php'; // Ensure you have the library installed via Composer

use ConsoleTVs\Profanity\Builder;

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header('location: login.php');
    exit;
}

// Initialize errors array
$errors = [];

// Fetch the user's ID
$username = $_SESSION['username'];
$query = "SELECT id FROM users WHERE username='$username'";
$result = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($result);
$user_id = $user['id'];

// Check the number of locations added in the last 15 minutes
$time_limit = 15 * 60; // 15 minutes in seconds
$current_time = time();

$query = "SELECT id, UNIX_TIMESTAMP(created_at) as created_at 
          FROM locations 
          WHERE user_id='$user_id' 
          ORDER BY created_at ASC";
$result = mysqli_query($db, $query);

$locations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $locations[] = $row;
}

// Identify and delete excess spots
if (count($locations) > 5) {
    $excess_spots = [];
    $valid_spots = [];

    foreach ($locations as $location) {
        if (count($valid_spots) < 5 || ($current_time - $location['created_at']) > $time_limit) {
            $valid_spots[] = $location;
        } else {
            $excess_spots[] = $location['id'];
        }
    }

    if (!empty($excess_spots)) {
        $excess_ids = implode(',', $excess_spots);
        $delete_query = "DELETE FROM locations WHERE id IN ($excess_ids)";
        mysqli_query($db, $delete_query);
        array_push($errors, "You have exceeded the limit of 5 spots in 15 minutes. Excess spots have been removed.");
    }
}

// Handle form submission
if (isset($_POST['add_location']) && count($errors) == 0) {
    $location_name = mysqli_real_escape_string($db, $_POST['location_name']);
    $latitude = mysqli_real_escape_string($db, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($db, $_POST['longitude']);
    $image_path = '';

    // Check for profanity in the location name
    $clean_location_name = Builder::blocker($location_name)->filter();
    if ($clean_location_name !== $location_name) {
        array_push($errors, "Location name contains inappropriate language and has been sanitized.");
        $location_name = $clean_location_name; // Replace with sanitized version
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            // Resize the image to 100x100 pixels
            $source = null;
            if ($imageFileType == "jpg" || $imageFileType == "jpeg") {
                $source = imagecreatefromjpeg($_FILES["image"]["tmp_name"]);
            } elseif ($imageFileType == "png") {
                $source = imagecreatefrompng($_FILES["image"]["tmp_name"]);
            } elseif ($imageFileType == "gif") {
                $source = imagecreatefromgif($_FILES["image"]["tmp_name"]);
            }

            if ($source) {
                $resizedImage = imagecreatetruecolor(100, 100);
                $originalWidth = $check[0];
                $originalHeight = $check[1];
                imagecopyresampled($resizedImage, $source, 0, 0, 0, 0, 100, 100, $originalWidth, $originalHeight);

                // Save the resized image
                if ($imageFileType == "jpg" || $imageFileType == "jpeg") {
                    imagejpeg($resizedImage, $target_file);
                } elseif ($imageFileType == "png") {
                    imagepng($resizedImage, $target_file);
                } elseif ($imageFileType == "gif") {
                    imagegif($resizedImage, $target_file);
                }

                imagedestroy($source);
                imagedestroy($resizedImage);
            } else {
                array_push($errors, "Unsupported image format.");
            }
        } else {
            array_push($errors, "File is not an image.");
        }
    }

    // Validate form input
    if (empty($location_name) || empty($latitude) || empty($longitude)) {
        array_push($errors, "All fields are required");
    }

    // Add location to the database if no errors
    if (count($errors) == 0) {
        $query = "INSERT INTO locations (user_id, location_name, latitude, longitude, image_path, created_at) 
                  VALUES('$user_id', '$location_name', '$latitude', '$longitude', '$image_path', NOW())";
        if (mysqli_query($db, $query)) {
            $_SESSION['success'] = "Location added successfully";
            header('location: add_location.php'); // Refresh the page
            exit;
        } else {
            array_push($errors, "Failed to add location. Please try again.");
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Location</title>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let remainingTime = <?php echo isset($remaining_time) ? $remaining_time : 0; ?>;
            const timerElement = document.getElementById('timer');

            if (remainingTime > 0) {
                const updateTimer = () => {
                    if (remainingTime <= 0) {
                        timerElement.textContent = "You can add a new location now.";
                        clearInterval(interval);
                        return;
                    }
                    const minutes = Math.floor(remainingTime / 60);
                    const seconds = remainingTime % 60;
                    timerElement.textContent = `Time to add next spot: ${minutes} minutes and ${seconds} seconds`;
                    remainingTime--;
                };
                updateTimer();
                const interval = setInterval(updateTimer, 1000);
            } else {
                timerElement.textContent = "You can add a new location now.";
            }
        });
    </script>
</head>
<body>
    <div id="timer" style="font-weight: bold; margin-bottom: 20px;"></div>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error" style="color: red; font-weight: bold; margin-bottom: 20px;">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']); // Clear the error after displaying it
            ?>
        </div>
    <?php endif; ?>
    <form method="post" action="add_location.php" enctype="multipart/form-data">
        <?php include('errors.php'); ?>
        <div class="input-group">
            <label>Location Name</label>
            <input type="text" name="location_name" required>
        </div>
        <div class="input-group">
            <label>Latitude</label>
            <input type="text" name="latitude" required>
        </div>
        <div class="input-group">
            <label>Longitude</label>
            <input type="text" name="longitude" required>
        </div>
        <div class="input-group">
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <div class="input-group">
            <button type="submit" class="btn" name="add_location">Add Location</button>
        </div>
    </form>
</body>
</html>
