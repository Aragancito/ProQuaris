<?php

define("HOST", "smtp.gmail.com");
define("USERNAME", "juandatamayo14@gmail.com");
// Pon aquí tus 16 letras de Google seguidas, sin espacios
define("PASSWORD", "lmhrmixvhjsuwjps"); 

define("SMTP_SECURE", "TLS");
define("TIEMPO_VIDA", time() + 60);
  // define("TIEMPO_VIDA", time() + 60); 1 minuto (1x60 = 60 segundos)
  // define("TIEMPO_VIDA", time() + 600); 10 minutos (10x60 = 600 segundos)
  // define("TIEMPO_VIDA", time() + 3600); 1 hora (60x60 = 3600 segundos)
  // define("TIEMPO_VIDA", time() + 18000); 5 horas (5x60x60 = 18000 segundos)
  // define("TIEMPO_VIDA", time() + 86400); 1 día (24x60x60 = 86400 segundos)
  // define("TIEMPO_VIDA", time() + 604800); 1 semana (7x24x60x60 = 604800 segundos)
  // define("TIEMPO_VIDA", time() + 2592000); 1 mes (30x24x60x60 = 2592000 segundos)