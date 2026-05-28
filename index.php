<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Банкетам.Нет - выбор площадки для банкета</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --pink: #F4A0B0;
            --light-pink: #FFD1DC;
            --pastel-pink: #FFF5F5;
            --soft-rose: #B8A9C9;
            --muted-pink: #E5989B;
            --dark-pink: #D4A5A5;
        }
        body {
            font-family: 'Oswald', sans-serif;
            background: linear-gradient(135deg, var(--soft-rose) 0%, var(--dark-pink) 100%);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background: rgba(200, 160, 170, 0.95);
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap;
            gap: 15px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo img { height: 40px; width: auto; }
        .logo span { color: var(--pink); font-size: 24px; font-weight: 700; letter-spacing: 2px; }
        .nav-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .nav-buttons a {
            padding: 8px 20px;
            border: 2px solid var(--pink);
            border-radius: 25px;
            color: var(--pink);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .nav-buttons a:hover {
            background: var(--pink);
            color: white;
            transform: translateY(-2px);
        }
        .main-content {
            flex: 1;
            position: relative;
            padding: 40px 20px;
        }
        .main-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('images/fon.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: 0.35;
            z-index: 0;
        }
        .main-content::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(200, 160, 170, 0.2);
            z-index: 1;
        }
        .main-content > * { position: relative; z-index: 2; }
        .slideshow-container {
            max-width: 1000px;
            position: relative;
            margin: 0 auto 30px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .mySlides { display: none; }
        .mySlides img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        @media (max-width: 768px) { .mySlides img { height: 250px; } }
        .slide-text {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.7);
            padding: 10px 20px;
            border-radius: 10px;
            color: var(--pink);
            font-weight: 500;
        }
        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.6);
            color: var(--pink);
            padding: 12px 18px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            transition: 0.3s;
        }
        .prev:hover, .next:hover { background: var(--pink); color: white; }
        .prev { left: 10px; }
        .next { right: 10px; }
        .dot-container { text-align: center; padding: 15px; }
        .dot {
            height: 12px;
            width: 12px;
            background: #bbb;
            border-radius: 50%;
            display: inline-block;
            margin: 0 5px;
            cursor: pointer;
        }
        .dot.active, .dot:hover { background: var(--pink); }
        .features {
            max-width: 1200px;
            margin: 40px auto;
        }
        .features h2 {
            text-align: center;
            color: var(--pink);
            font-size: 32px;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        .feature-card {
            background: rgba(255,255,255,0.9);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            transition: 0.3s;
            backdrop-filter: blur(5px);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            background: white;
        }
        .feature-card h3 { color: var(--soft-rose); font-size: 22px; margin-bottom: 10px; }
        .feature-card p { color: #555; }
        footer {
            text-align: center;
            padding: 25px;
            background: var(--dark-pink);
            color: white;
            border-top: 2px solid var(--pink);
        }
        footer p { font-size: 16px; letter-spacing: 1px; }
    </style>
</head>
<body>
<header class="header">
    <div class="nav">
        <a href="index.php" class="logo">
            <img src="images/logo.png" alt="Логотип">
            <span>Банкетам.Нет</span>
        </a>
        <div class="nav-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                    <a href="admin.php">👑 Админ панель</a>
                <?php else: ?>
                    <a href="history.php">📋 Мои заявки</a>
                    <a href="create.php">🎉 Новая заявка</a>
                <?php endif; ?>
                <a href="logout.php">🚪 Выход</a>
            <?php else: ?>
                <a href="login.php">🔐 Войти</a>
                <a href="register.php">📝 Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<div class="main-content">
    <div class="slideshow-container">
        <div class="mySlides"><img src="images/zal1.jpg" alt="Банкетный зал"><div class="slide-text">🏛️ Роскошный банкетный зал</div></div>
        <div class="mySlides"><img src="images/zal2.jpg" alt="Ресторан"><div class="slide-text">🍷 Изысканный ресторан</div></div>
        <div class="mySlides"><img src="images/zal3.jpg" alt="Летняя веранда"><div class="slide-text">🌞 Летняя веранда</div></div>
        <div class="mySlides"><img src="images/zal4.jpg" alt="Закрытая веранда"><div class="slide-text">🏠 Уютная закрытая веранда</div></div>
        <a class="prev" onclick="changeSlide(-1)">❮</a>
        <a class="next" onclick="changeSlide(1)">❯</a>
    </div>
    <div class="dot-container">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
        <span class="dot" onclick="currentSlide(4)"></span>
    </div>
    <div class="features">
        <h2>✨ Почему выбирают нас?</h2>
        <div class="features-grid">
            <div class="feature-card"><h3>🏛️ Лучшие площадки</h3><p>Банкетные залы, рестораны и веранды на любой вкус</p></div>
            <div class="feature-card"><h3>💳 Удобная оплата</h3><p>Наличные, перевод, карта - выбирайте удобный способ</p></div>
            <div class="feature-card"><h3>⭐ Отзывы клиентов</h3><p>Честные отзывы о проведённых банкетах</p></div>
        </div>
    </div>
</div>
<footer><p>© 2026 Банкетам.Нет - выберите идеальное место для вашего праздника</p></footer>
<script>
    let slideIndex = 1, slideInterval;
    function showSlides(n) {
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("dot");
        if (n > slides.length) slideIndex = 1;
        if (n < 1) slideIndex = slides.length;
        for (let i = 0; i < slides.length; i++) slides[i].style.display = "none";
        for (let i = 0; i < dots.length; i++) dots[i].className = dots[i].className.replace(" active", "");
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].className += " active";
    }
    function changeSlide(n) { clearInterval(slideInterval); slideIndex += n; showSlides(slideIndex); startAutoSlide(); }
    function currentSlide(n) { clearInterval(slideInterval); slideIndex = n; showSlides(slideIndex); startAutoSlide(); }
    function startAutoSlide() { slideInterval = setInterval(() => { slideIndex++; showSlides(slideIndex); }, 3000); }
    showSlides(slideIndex); startAutoSlide();
    const container = document.querySelector('.slideshow-container');
    if (container) {
        container.addEventListener('mouseenter', () => clearInterval(slideInterval));
        container.addEventListener('mouseleave', () => startAutoSlide());
    }
</script>
</body>
</html>