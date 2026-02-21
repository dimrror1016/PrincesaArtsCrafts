<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Princesa Arts & Crafts</title>

<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
  background:#fff7fb;
  font-family:'Segoe UI',sans-serif;
}

@media (max-width:768px){
  .hero{
    min-height:600px;
    text-align:center;
  }

  .hero .row{
    gap:30px;
  }
}

/* NAVBAR */
.navbar{
  background:linear-gradient(90deg,#d8a7b1,#f3c6d3);
  box-shadow:0 2px 8px rgba(0,0,0,.08);
  position:sticky;
  top:0;
  z-index:999;
}

.navbar-brand{
  font-family:"Brush Script MT","Lucida Handwriting",cursive;
  font-size:2.3rem;
  letter-spacing:1px;
}

.navbar-brand::before{
  content:"✿ ";
  font-size:2rem;
  opacity:.8;
}

/* HERO */
.hero{
  position:relative;
  overflow:hidden;
  min-height:520px;
  display:flex;
  align-items:center;
  border-bottom:1px solid #f0dbe6;
  background:#000;
}

.hero img{
  max-width:100%;
  height:auto;
}

/* carousel background fix */
.hero .carousel,
.hero .carousel-inner,
.hero .carousel-item{
  position:absolute;
  inset:0;
  background:#000;
}

/* bouquet images */
.hero .carousel-item img{
  width:100%;
  height:100%;
  object-fit:cover;
  filter:brightness(.6);
  transform:scale(1);
  transition:transform 10s ease;   /* matches slide timing */
  display:block;
}

/* zoom when active */
.carousel-item.active img{
  transform:scale(1.08);
}

/* smoother slide */
.carousel-item{
  transition:transform 1.6s cubic-bezier(.65,.05,.36,1);
}

/* romantic overlay */
.hero::after{
  content:"";
  position:absolute;
  inset:0;
  background:linear-gradient(rgba(210,145,188,.35),rgba(210,145,188,.25));
  z-index:1;
}

/* content above carousel */
.hero-content{
  position:relative;
  z-index:2;
  color:white;
}

/* optional subtle glass feel */
.hero h1,
.hero p{
  text-shadow:0 2px 10px rgba(0,0,0,.25);
}

.hero h1{
  font-weight:700;
}

.hero p{
  color:#f4eaf2;
}

.carousel-fade .carousel-item{
  transition:opacity 1.8s ease-in-out;
}

/* buttons */
.btn-success{background:#8ec5a4;border:none}
.btn-success:hover{background:#79b693}

.btn-primary{background:#d291bc;border:none}
.btn-primary:hover{background:#c27aa7}

/* cards */
.features{background:#fff}

.card{
  border:none;
  border-radius:18px;
  box-shadow:0 8px 18px rgba(0,0,0,.05);
  transition:.2s;
}

.card:hover{transform:translateY(-5px)}

.card-title{color:#7a4e65;font-weight:600}
.card-text{color:#7a6a73}

/* footer */
footer{
  background:linear-gradient(90deg,#d8a7b1,#cdb4db);
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Princesa Arts & Crafts</a>
  </div>
</nav>

<section class="hero">

  <!-- Carousel -->
  <div id="heroCarousel"
       class="carousel slide carousel-fade"
       data-bs-ride="carousel"
       data-bs-interval="5000"
       data-bs-pause="false">

    <div class="carousel-inner">

      <div class="carousel-item active">
        <img src="assets/images/bouquet1.jpg">
      </div>

      <div class="carousel-item">
        <img src="assets/images/bouquet2.jpg">
      </div>

      <div class="carousel-item">
        <img src="assets/images/bouquet3.jpg">
      </div>

    </div>
  </div>

  <!-- Content -->
  <div class="container hero-content">
    <div class="row align-items-center">

      <div class="col-md-6">
        <h1 class="display-5">Emotion-Based Bouquet Recommendation</h1>
        <p class="lead">
          Share how you feel and let our system suggest the perfect bouquet that speaks your emotion.
        </p>

        <div class="mt-4">
          <a href="login.php" class="btn btn-success btn-lg me-2">Login</a>
          <a href="user/register.php" class="btn btn-primary btn-lg">Register</a>
        </div>
      </div>

      <div class="col-md-6 text-center">
        <img src="assets/images/pca_logo2.png"
             class="img-fluid"
             style="max-height:350px;">
      </div>

    </div>
  </div>

</section>

<section class="features py-5">
  <div class="container">
    <h2 class="text-center mb-5" style="color:#7a4e65;">How it Works</h2>

    <div class="row g-4 text-center">

      <div class="col-md-4">
        <div class="card p-4 h-100">
          <h5 class="card-title">🌸 Register</h5>
          <p class="card-text">Create your account and begin exploring personalized bouquet suggestions.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card p-4 h-100">
          <h5 class="card-title">💬 Enter Message</h5>
          <p class="card-text">Write your thoughts and emotions. Our AI detects your mood instantly.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card p-4 h-100">
          <h5 class="card-title">💐 Get Recommendation</h5>
          <p class="card-text">Receive a bouquet recommendation crafted to match your feelings.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<footer class="text-white py-3">
  <div class="container text-center">
    <small>&copy; <?= date("Y") ?> Princesa Arts & Crafts. All rights reserved.</small>
  </div>
</footer>

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>