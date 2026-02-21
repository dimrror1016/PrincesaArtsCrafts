<?php
session_start();
require_once "../config/db.php";
require_once "../functions/helpers.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT firstname FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$firstname = htmlspecialchars($user['firstname']);

$errorMessage = "";
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Emotion Input | Princesa Arts & Crafts</title>
<link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/bootstrap/icons/bootstrap-icons.css" rel="stylesheet">

<!-- Floral / Handwriting fonts -->
<link href="https://fonts.googleapis.com/css2?family=Parisienne&family=Great+Vibes&display=swap" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#ffeef5,#f3e8ff,#f0fff4);
    min-height:100vh;
    font-family:'Segoe UI',sans-serif;
    overflow-x:hidden;
    position: relative;
}

/* floating flowers */
.flower{
    position:fixed;
    font-size:28px;
    opacity:.15;
    animation: float 12s infinite ease-in-out;
}

/* flower positions with random delays */
.flower:nth-child(1){ top:10%; left:5%; animation-delay:0s;}
.flower:nth-child(2){ top:70%; left:90%; animation-delay:1s;}
.flower:nth-child(3){ top:40%; left:85%; animation-delay:2s;}
.flower:nth-child(4){ top:85%; left:15%; animation-delay:3s;}
.flower:nth-child(5){ top:20%; left:50%; animation-delay:4s;}
.flower:nth-child(6){ top:60%; left:10%; animation-delay:5s;}
.flower:nth-child(7){ top:35%; left:30%; animation-delay:6s;}
.flower:nth-child(8){ top:75%; left:70%; animation-delay:7s;}
.flower:nth-child(9){ top:5%; left:80%; animation-delay:8s;}
.flower:nth-child(10){ top:50%; left:95%; animation-delay:9s;}
.flower:nth-child(11){ top:30%; left:60%; animation-delay:10s;}
.flower:nth-child(12){ top:80%; left:40%; animation-delay:11s;}

@keyframes float{
    0%,100%{ transform:translateY(0);}
    50%{ transform:translateY(-30px);}
}

/* card floral background */
.card{
    border:none;
    border-radius:24px;
    box-shadow:0 15px 40px rgba(0,0,0,0.08);
    background: rgba(255,255,255,0.95);
    position: relative;
    overflow: hidden;
}

.card::before{
    content:"🌸🌼💐🌷";
    position:absolute;
    top:-20px;
    left:-20px;
    font-size:3rem;
    opacity:.05;
    pointer-events:none;
}

textarea{
    resize:none;
    border-radius:14px !important;
    padding:16px !important;
}

textarea:focus{
    border-color:#d291bc !important;
    box-shadow:0 0 0 .2rem rgba(210,145,188,.25) !important;
}

.btn-main{
    background:linear-gradient(90deg,#8ec5a4,#cdb4db);
    border:none;
    border-radius:14px;
    padding:14px;
    font-weight:600;
    font-family:'Parisienne', cursive;
    font-size:1.1rem;
}

.btn-main:hover{ opacity:.9; }

/* chips */
.chip{
    background:#fff;
    border-radius:20px;
    padding:6px 14px;
    margin:4px;
    border:1px solid #e3d5ec;
    cursor:pointer;
    transition:.2s;
}
.chip:hover{ background:#f5e8ff; }

/* emoji preview */
.emoji-preview{
    font-size:28px;
    text-align:center;
    margin-bottom:10px;
}

/* headings */
h3{
    font-family:'Great Vibes', cursive;
    color:#7a4e65;
    text-align:center;
}

/* navbar brand */
.navbar-brand{
    font-family:'Parisienne', cursive;
    font-size:1.8rem;
    color:#7a4e65;
}

/* loading overlay */
.loading{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(255,255,255,.8);
    z-index:999;
    justify-content:center;
    align-items:center;
    flex-direction:column;
}
.spinner-border{
    width:3rem;
    height:3rem;
}
</style>
</head>

<body>

<!-- floating flowers -->
<div class="flower">🌸</div>
<div class="flower">🌼</div>
<div class="flower">🌷</div>
<div class="flower">💐</div>
<div class="flower">🌹</div>
<div class="flower">🌻</div>
<div class="flower">🌺</div>
<div class="flower">🥀</div>
<div class="flower">💮</div>
<div class="flower">🌿</div>
<div class="flower">🍀</div>
<div class="flower">🌾</div>

<!-- loading overlay -->
<div class="loading" id="loadingBox">
    <div class="spinner-border text-success"></div>
    <p class="mt-3 fw-semibold">Analyzing your emotion & preparing bouquet...</p>
</div>

<nav class="navbar bg-white shadow-sm mb-4">
<div class="container">
<span class="navbar-brand fw-bold">🌸 Princesa Arts & Crafts</span>
<div>
Hello, <?= $firstname ?>!
<a href="../functions/logout.php" class="btn btn-outline-danger btn-sm ms-3">Logout</a>
</div>
</div>
</nav>

<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card p-5">

<h3 class="mb-2">How are you feeling today?</h3>
<p class="text-center text-muted mb-3">Tell us your mood and we'll pick the perfect bouquet.</p>

<?php if (!empty($errorMessage)): ?>
<div class="alert alert-danger text-center">
<?= htmlspecialchars($errorMessage) ?>
</div>
<?php endif; ?>

<div class="emoji-preview" id="emojiPreview">🙂</div>

<form method="POST" action="process_emotion.php" onsubmit="showLoading()">

<div class="mb-3">
<textarea id="emotionText"
name="emotion_text"
class="form-control form-control-lg"
rows="5"
placeholder="Type how you feel today..."
required></textarea>
</div>

<!-- suggestion chips -->
<div class="mb-3 text-center">
    <span class="chip" onclick="setText('I feel in love')">❤️ love</span>
    <span class="chip" onclick="setText('I feel sorry for what happened')">😔 sorry</span>
    <span class="chip" onclick="setText('I feel grief and loss')">💔 grief</span>
    <span class="chip" onclick="setText('I feel very happy today')">😊 happy</span>
    <span class="chip" onclick="setText('I feel sad and down')">😢 sad</span>
</div>

<button type="submit" class="btn btn-main btn-lg w-100">
<i class="bi bi-gift-fill me-2"></i> Get Bouquet Recommendation
</button>

</form>

</div>
</div>
</div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
// placeholder animation
const placeholderText = [
    "Type how you feel today...",
    "Example: I feel stressed about exams...",
    "Example: I'm really happy today...",
    "Example: I feel lonely lately..."
];
let i=0;
setInterval(()=>{
    document.getElementById("emotionText").placeholder=placeholderText[i];
    i=(i+1)%placeholderText.length;
},3000);

// emoji preview
document.getElementById("emotionText").addEventListener("input", function() {
    let text = this.value.toLowerCase();
    let emoji = "🙂";

    // Positive / Warm
    if (text.includes("admiration") || text.includes("respect") || text.includes("appreciate") || text.includes("impressed")) emoji = "👏";
    else if (text.includes("amusement") || text.includes("funny") || text.includes("laugh") || text.includes("joke")) emoji = "😆";
    else if (text.includes("approval") || text.includes("agree") || text.includes("like") || text.includes("ok")) emoji = "👍";
    else if (text.includes("caring") || text.includes("help") || text.includes("support") || text.includes("concerned")) emoji = "🤗";
    else if (text.includes("curiosity") || text.includes("wonder") || text.includes("question") || text.includes("explore")) emoji = "🧐";
    else if (text.includes("desire") || text.includes("want") || text.includes("wish") || text.includes("hope for")) emoji = "😍";
    else if (text.includes("excitement") || text.includes("thrilled") || text.includes("can't wait") || text.includes("pumped")) emoji = "🤩";
    else if (text.includes("gratitude") || text.includes("thank") || text.includes("appreciate")) emoji = "🙏";
    else if (text.includes("joy") || text.includes("happy") || text.includes("delighted") || text.includes("cheerful")) emoji = "😊";
    else if (text.includes("love") || text.includes("in love") || text.includes("adore") || text.includes("fond")) emoji = "❤️";
    else if (text.includes("optimism") || text.includes("hopeful") || text.includes("positive") || text.includes("looking forward")) emoji = "🌞";
    else if (text.includes("pride") || text.includes("accomplished") || text.includes("achieved") || text.includes("successful")) emoji = "😌";
    else if (text.includes("relief") || text.includes("phew") || text.includes("finally") || text.includes("safe")) emoji = "😌";
    else if (text.includes("surprise") || text.includes("shocked") || text.includes("unexpected") || text.includes("wow")) emoji = "😮";

    // Neutral / Complex
    else if (text.includes("realization") || text.includes("suddenly understand") || text.includes("aware") || text.includes("noticed")) emoji = "🤔";
    else if (text.includes("confusion") || text.includes("unsure") || text.includes("mixed feelings") || text.includes("don't know")) emoji = "😕";

    // Negative / Heavy
    else if (text.includes("anger") || text.includes("mad") || text.includes("furious") || text.includes("frustrated")) emoji = "😠";
    else if (text.includes("annoyance") || text.includes("irritated") || text.includes("bothered") || text.includes("fed up")) emoji = "😒";
    else if (text.includes("disapproval") || text.includes("disagree") || text.includes("unacceptable") || text.includes("wrong")) emoji = "👎";
    else if (text.includes("disgust") || text.includes("gross") || text.includes("repulsed") || text.includes("yuck")) emoji = "🤢";
    else if (text.includes("embarrassment") || text.includes("awkward") || text.includes("shy") || text.includes("humiliated")) emoji = "😳";
    else if (text.includes("fear") || text.includes("scared") || text.includes("anxious") || text.includes("panic")) emoji = "😨";
    else if (text.includes("grief") || text.includes("mourning") || text.includes("loss") || text.includes("heartbroken")) emoji = "😢";
    else if (text.includes("nervousness") || text.includes("worried") || text.includes("tense") || text.includes("apprehensive")) emoji = "😰";
    else if (text.includes("remorse") || text.includes("regret") || text.includes("sorry") || text.includes("guilty")) emoji = "😔";
    else if (text.includes("sadness") || text.includes("sad") || text.includes("down") || text.includes("unhappy")) emoji = "😔";
    else if (text.includes("disappointment") || text.includes("let down") || text.includes("frustrated") || text.includes("dissatisfied")) emoji = "😞";

    document.getElementById("emojiPreview").textContent = emoji;
});

// chips fill textarea
function setText(t){
    document.getElementById("emotionText").value=t;
    document.getElementById("emotionText").dispatchEvent(new Event('input'));
}

// show loading overlay
function showLoading(){
    document.getElementById("loadingBox").style.display="flex";
}
</script>

</body>
</html>