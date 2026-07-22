<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ========================================== -->
    <!-- META TAGS PARA EVITAR CACHÉ (LOGOUT)      -->
    <!-- ========================================== -->
    <!-- Estos meta tags evitan que el navegador guarde la página en caché,
         forzando una carga fresca desde el servidor. Esto es crítico para
         que después del logout el usuario no pueda retroceder y ver datos
         de sesiones anteriores. -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>ProQuaris - Gestión de Producción y Calidad</title>
    <!-- Fuente Inter para una tipografía moderna y legible -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Estilos principales del index (carrusel, features, stats, footer) -->
    <link rel="stylesheet" href="views/css/index.css">
</head>
<body>
    <!-- ========================================== -->
    <!-- NAVBAR - Barra de navegación superior      -->
    <!-- ========================================== -->
    <nav class="navbar">
        <!-- Logo con gradiente personalizado -->
        <div class="logo">ProQuaris</div>
        <div class="nav-buttons">
            <!-- Enlaces a login y registro visibles para usuarios no autenticados -->
            <a href="views/login.php" class="btn-nav btn-login">Iniciar Sesión</a>
            <a href="views/registro.php" class="btn-nav btn-register">Registrarse</a>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- CARRUSEL - Presentación de módulos         -->
    <!-- ========================================== -->
    <!-- Cada slide representa un módulo del sistema -->
    <div class="carrusel-container">
        <div class="carrusel-slides" id="carruselSlides">
            <!-- Slide 1: Producción -->
            <div class="slide">
                <img src="views/img/imagenproquaris1.jpg" alt="Producción">
                <div class="slide-overlay">
                    <h2 class="slide-title">Gestión de Producción</h2>
                    <p class="slide-desc">Controla y optimiza tus líneas de producción.</p>
                </div>
            </div>
            <!-- Slide 2: Calidad -->
            <div class="slide">
                <img src="views/img/imagenproquaris2.jpg" alt="Calidad">
                <div class="slide-overlay">
                    <h2 class="slide-title">Control de Calidad</h2>
                    <p class="slide-desc">Registra defectos y mejora la calidad.</p>
                </div>
            </div>
            <!-- Slide 3: Inventario -->
            <div class="slide">
                <img src="views/img/imagenproquaris3.jpg" alt="Inventario">
                <div class="slide-overlay">
                    <h2 class="slide-title">Inventario Inteligente</h2>
                    <p class="slide-desc">Alertas de stock bajo y control de insumos.</p>
                </div>
            </div>
            <!-- Slide 4: Reportes -->
            <div class="slide">
                <img src="views/img/imagenproquaris4.jpg" alt="Reportes">
                <div class="slide-overlay">
                    <h2 class="slide-title">Reportes y Dashboards</h2>
                    <p class="slide-desc">Visualiza indicadores clave.</p>
                </div>
            </div>
            <!-- Slide 5: Análisis Predictivo -->
            <div class="slide">
                <img src="views/img/imagenproquaris5.jpg" alt="Analítica">
                <div class="slide-overlay">
                    <h2 class="slide-title">Análisis Predictivo</h2>
                    <p class="slide-desc">Predice fallos y emite alertas.</p>
                </div>
            </div>
        </div>
        <!-- Controles del carrusel -->
        <button class="carrusel-btn btn-prev" id="btnPrev">❮</button>
        <button class="carrusel-btn btn-next" id="btnNext">❯</button>
        <!-- Indicadores de posición (dots) -->
        <div class="carrusel-dots" id="carruselDots"></div>
    </div>

    <!-- ========================================== -->
    <!-- SECCIÓN DE CARACTERÍSTICAS                 -->
    <!-- ========================================== -->
    <!-- Muestra 4 beneficios clave del sistema -->
    <div class="features">
        <h2 class="features-title">¿Por qué elegir ProQuaris?</h2>
        <p class="features-subtitle">La solución completa para tu empresa industrial</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Dashboards en Tiempo Real</h3>
                <p>Métricas de producción y calidad al instante.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3>Trazabilidad Completa</h3>
                <p>Seguimiento desde materia prima hasta producto final.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Alertas Inteligentes</h3>
                <p>Notificaciones ante desperdicios o defectos críticos.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Acceso Móvil</h3>
                <p>Gestión desde cualquier dispositivo.</p>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SECCIÓN DE ESTADÍSTICAS                    -->
    <!-- ========================================== -->
    <!-- Datos ficticios que transmiten confianza al usuario -->
    <div class="stats">
        <div class="stats-grid">
            <div>
                <div class="stat-number">99.5%</div>
                <div class="stat-label">Disponibilidad</div>
            </div>
            <div>
                <div class="stat-number">＜3s</div>
                <div class="stat-label">Tiempo respuesta</div>
            </div>
            <div>
                <div class="stat-number">100k+</div>
                <div class="stat-label">Registros</div>
            </div>
            <div>
                <div class="stat-number">24/7</div>
                <div class="stat-label">Soporte</div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FOOTER                                     -->
    <!-- ========================================== -->
    <footer>
        <p>© 2025 ProQuaris - Gestión de Producción y Calidad</p>
        <p style="margin-top:10px;font-size:12px;">SENA - Tecnología Análisis y Desarrollo de Software</p>
    </footer>

    <!-- ========================================== -->
    <!-- SCRIPTS DEL CARRUSEL                      -->
    <!-- ========================================== -->
    <!-- Controla la transición automática, los dots y los botones de navegación -->
    <script>
        const slides = document.getElementById('carruselSlides');
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const dotsContainer = document.getElementById('carruselDots');
        let currentIndex = 0;
        const totalSlides = document.querySelectorAll('.slide').length;
        let autoInterval;
        
        // Crea los puntos indicadores dinámicamente según el número de slides
        for (let i=0; i<totalSlides; i++) {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if(i===0) dot.classList.add('active');
            dot.addEventListener('click', ()=>goToSlide(i));
            dotsContainer.appendChild(dot);
        }
        const dots = document.querySelectorAll('.dot');
        
        // Actualiza los puntos indicadores según el slide actual
        function updateDots() { dots.forEach((dot,idx)=>{ dot.classList.toggle('active', idx===currentIndex); }); }
        
        // Navega a un slide específico
        function goToSlide(index) { currentIndex = index; slides.style.transform = `translateX(-${currentIndex*100}%)`; updateDots(); resetAutoPlay(); }
        
        // Avanza al siguiente slide
        function nextSlide() { currentIndex = (currentIndex+1)%totalSlides; goToSlide(currentIndex); }
        
        // Retrocede al slide anterior
        function prevSlide() { currentIndex = (currentIndex-1+totalSlides)%totalSlides; goToSlide(currentIndex); }
        
        // Reinicia el temporizador para mantener el ciclo automático
        function resetAutoPlay() { clearInterval(autoInterval); autoInterval = setInterval(nextSlide, 5000); }
        
        // Eventos de los botones
        btnNext.addEventListener('click', ()=>{ nextSlide(); resetAutoPlay(); });
        btnPrev.addEventListener('click', ()=>{ prevSlide(); resetAutoPlay(); });
        
        // Inicia el carrusel automático al cargar la página
        autoInterval = setInterval(nextSlide, 5000);
    </script>
</body>

<!-- ========================================== -->
<!-- BOTPRESS CHATBOT                           -->
<!-- ========================================== -->
<script type="module">
    import Chatbot from "https://cdn.jsdelivr.net/npm/flowise-embed/dist/web.js"
    Chatbot.init({
        chatflowid: "50de36ef-a39c-4cfa-a795-e95952c78ebe",
        apiHost: "https://cloud.flowiseai.com",
    })
</script>

</html>
