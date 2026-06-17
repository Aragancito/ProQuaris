<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProQuaris - Gestión de Producción y Calidad</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">ProQuaris</div>
        <div class="nav-buttons">
            <a href="login.php" class="btn-nav btn-login">Iniciar Sesión</a>
            <a href="registro.php" class="btn-nav btn-register">Registrarse</a>
        </div>
    </nav>

    <div class="carrusel-container">
        <div class="carrusel-slides" id="carruselSlides">
            <div class="slide">
                <img src="img/imagenproquaris1.jpg" alt="Producción">
                <div class="slide-overlay"><h2 class="slide-title">Gestión de Producción</h2><p class="slide-desc">Controla y optimiza tus líneas de producción.</p></div>
            </div>
            <div class="slide">
                <img src="img/imagenproquaris2.jpg" alt="Calidad">
                <div class="slide-overlay"><h2 class="slide-title">Control de Calidad</h2><p class="slide-desc">Registra defectos y mejora la calidad.</p></div>
            </div>
            <div class="slide">
                <img src="img/imagenproquaris3.jpg" alt="Inventario">
                <div class="slide-overlay"><h2 class="slide-title">Inventario Inteligente</h2><p class="slide-desc">Alertas de stock bajo y control de insumos.</p></div>
            </div>
            <div class="slide">
                <img src="img/imagenproquaris4.jpg" alt="Reportes">
                <div class="slide-overlay"><h2 class="slide-title">Reportes y Dashboards</h2><p class="slide-desc">Visualiza indicadores clave.</p></div>
            </div>
            <div class="slide">
                <img src="img/imagenproquaris5.jpg" alt="Analítica">
                <div class="slide-overlay"><h2 class="slide-title">Análisis Predictivo</h2><p class="slide-desc">Predice fallos y emite alertas.</p></div>
            </div>
        </div>
        <button class="carrusel-btn btn-prev" id="btnPrev">❮</button>
        <button class="carrusel-btn btn-next" id="btnNext">❯</button>
        <div class="carrusel-dots" id="carruselDots"></div>
    </div>

    <div class="features">
        <h2 class="features-title">¿Por qué elegir ProQuaris?</h2>
        <p class="features-subtitle">La solución completa para tu empresa industrial</p>
        <div class="features-grid">
            <div class="feature-card"><div class="feature-icon">📊</div><h3>Dashboards en Tiempo Real</h3><p>Métricas de producción y calidad al instante.</p></div>
            <div class="feature-card"><div class="feature-icon">🔍</div><h3>Trazabilidad Completa</h3><p>Seguimiento desde materia prima hasta producto final.</p></div>
            <div class="feature-card"><div class="feature-icon">⚡</div><h3>Alertas Inteligentes</h3><p>Notificaciones ante desperdicios o defectos críticos.</p></div>
            <div class="feature-card"><div class="feature-icon">📱</div><h3>Acceso Móvil</h3><p>Gestión desde cualquier dispositivo.</p></div>
        </div>
    </div>

    <div class="stats">
        <div class="stats-grid">
            <div><div class="stat-number">99.5%</div><div class="stat-label">Disponibilidad</div></div>
            <div><div class="stat-number">＜3s</div><div class="stat-label">Tiempo respuesta</div></div>
            <div><div class="stat-number">100k+</div><div class="stat-label">Registros</div></div>
            <div><div class="stat-number">24/7</div><div class="stat-label">Soporte</div></div>
        </div>
    </div>

    <footer>
        <p>© 2025 ProQuaris - Gestión de Producción y Calidad</p>
        <p style="margin-top:10px;font-size:12px;">SENA - Tecnología Análisis y Desarrollo de Software</p>
    </footer>

    <script>
        const slides = document.getElementById('carruselSlides');
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const dotsContainer = document.getElementById('carruselDots');
        let currentIndex = 0;
        const totalSlides = document.querySelectorAll('.slide').length;
        let autoInterval;
        for (let i=0; i<totalSlides; i++) {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if(i===0) dot.classList.add('active');
            dot.addEventListener('click', ()=>goToSlide(i));
            dotsContainer.appendChild(dot);
        }
        const dots = document.querySelectorAll('.dot');
        function updateDots() { dots.forEach((dot,idx)=>{ dot.classList.toggle('active', idx===currentIndex); }); }
        function goToSlide(index) { currentIndex = index; slides.style.transform = `translateX(-${currentIndex*100}%)`; updateDots(); resetAutoPlay(); }
        function nextSlide() { currentIndex = (currentIndex+1)%totalSlides; goToSlide(currentIndex); }
        function prevSlide() { currentIndex = (currentIndex-1+totalSlides)%totalSlides; goToSlide(currentIndex); }
        function resetAutoPlay() { clearInterval(autoInterval); autoInterval = setInterval(nextSlide, 5000); }
        btnNext.addEventListener('click', ()=>{ nextSlide(); resetAutoPlay(); });
        btnPrev.addEventListener('click', ()=>{ prevSlide(); resetAutoPlay(); });
        autoInterval = setInterval(nextSlide, 5000);
    </script>
</body>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/06/17/03/20260617035538-JZYJE355.js" defer></script>
    
</html>