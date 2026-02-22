<?php
session_start();
require_once "../config/db.php";
require_once "../functions/helpers.php";

if (!isset($_SESSION['user_id'])) {
    redirect("login.php");
}

if (!isset($_POST['emotion_text']) || empty(trim($_POST['emotion_text']))) {
    $_SESSION['error'] = "Please enter how you feel.";
    redirect("emotion_input.php");
}

$user_id = $_SESSION['user_id'];
$inputText = trim($_POST['emotion_text']);

/*
|--------------------------------------------------
| Call Gradio Emotion API
|--------------------------------------------------
| Replace the URL with your deployed Gradio app
*/
$gradio_url = "https://paac-emotion-api-dimrror.hf.space/run/predict_flower";

$payload = [
    "data" => [$inputText, 0.3, 3] // text, threshold, top_k
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $gradio_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($curl);
$curl_error = curl_error($curl);
curl_close($curl);

if ($response === false) {
    $_SESSION['error'] = "Failed to connect to the emotion detection service. Error: $curl_error";
    redirect("emotion_input.php");
}

// Gradio returns JSON like: { "data": ["Dominant Emotion", 0.95] }
$data = json_decode($response, true);

if (!isset($data['data'][0])) {
    $_SESSION['error'] = "Could not detect your emotion. Please try again.";
    redirect("emotion_input.php");
}

$detectedEmotion = $data['data'][0]; // Dominant Flower Emotion

/*
|--------------------------------------------------
| Get emotion_id from database
|--------------------------------------------------
*/
$stmt = $conn->prepare("SELECT emotion_id FROM emotions WHERE emotion_name = ?");
$stmt->bind_param("s", $detectedEmotion);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "The detected emotion '$detectedEmotion' is not available in our system.";
    redirect("emotion_input.php");
}

$emotionId = $result->fetch_assoc()['emotion_id'];

/*
|--------------------------------------------------
| Get 2–3 random bouquets for this emotion
|--------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT b.bouquet_id, b.bouquet_name, b.description, b.image
    FROM emotion_to_bouquet etb
    JOIN bouquets b ON b.bouquet_id = etb.bouquet_id
    WHERE etb.emotion_id = ? AND b.archived = 0
    ORDER BY RAND()
    LIMIT 3
");
$stmt->bind_param("i", $emotionId);
$stmt->execute();
$bouquets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($bouquets)) {
    $_SESSION['error'] = "Sorry, we currently have no bouquets mapped to '$detectedEmotion'.";
    redirect("emotion_input.php");
}

/*
|--------------------------------------------------
| Store bouquet options in session temporarily
|--------------------------------------------------
*/
$_SESSION['bouquet_choices'] = [
    'emotion_id' => $emotionId,
    'bouquets' => $bouquets
];

redirect("result.php");
