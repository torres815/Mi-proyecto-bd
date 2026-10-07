Actúa como un desarrollador Full-Stack Senior experto en PHP, MySQL, JavaScript moderno, HTML5 y CSS3. 

Necesito que me ayudes a desarrollar el código completo para un "Sistema de Login y Panel de Administración para el Módulo de Inscripción" del CENTRO EDUCATIVO NONOGASTA (CEN). El proyecto debe ser compatible con un entorno local como XAMPP/MAMP y listo para subir a GitHub.

### 1. ESTRUCTURA DE ARCHIVOS SUGERIDA
Organiza el proyecto siguiendo esta estructura limpia y modular:

modulo-inscripcion/
│
├── config/
│   └── conexion.php        # Conexión a la base de datos MySQL (PDO)
│
├── assets/
│   ├── css/
│   │   └── styles.css      # Estilos modernos, Glassmorphism, CSS Variables y animaciones
│   ├── js/
│   │   ├── particles.js    # Fondo interactivo de red/partículas en Canvas
│   │   └── main.js         # Validaciones AJAX, toggle de contraseña y microinteracciones
│   └── img/                # Carpeta para logotipos e íconos
│
├── auth/
│   ├── login_process.php   # Procesamiento de login (validación PHP + Sesiones)
│   └── logout.php          # Cierre de sesión y destrucción de variables
│
├── database/
│   └── moduloIncrip.sql    # Script SQL para la BD y tablas
│
├── index.php               # Vista principal de Login (UI interactiva)
└── dashboard.php           # Panel de Preces (Protegido por sesión)

---

### 2. ESTRUCTURA DE LA BASE DE DATOS (MySQL)
Proporciona el script SQL para la base de datos `moduloIncrip` basándote exactamente en este esquema:

- Tabla `rol`:
  - `id_rol` INT PRIMARY KEY
  - `nombre` VARCHAR(50) NOT NULL
- Tabla `usuario`:
  - `id_usuario` INT PRIMARY KEY AUTO_INCREMENT
  - `nombre` VARCHAR(100) NOT NULL
  - `apellido` VARCHAR(100) NOT NULL
  - `email` VARCHAR(150) NOT NULL UNIQUE
  - `password_hash` VARCHAR(255) NOT NULL
  - `id_rol` INT NOT NULL (FOREIGN KEY que referencia a `rol(id_rol)`)
  - `activo` BOOLEAN DEFAULT TRUE
- Incluye registros de prueba (INSERTs) con contraseñas encriptadas usando `password_hash()` de PHP (por ejemplo, para el rol de preceptoria y una usuaria "Silvana").

---

### 3. REQUERIMIENTOS TÉCNICOS DE BACKEND (PHP)
- Conexión segura mediante PDO a MySQL (`conexion.php`).
- Procesamiento del formulario de inicio de sesión (`login_process.php`): verificación de credenciales con `password_verify()`.
- Respuestas estructuradas en JSON para peticiones AJAX.
- Manejo de sesiones sanitizadas para proteger las páginas (redirección a `index.php` si no hay sesión activa).
- Sistema de cierre de sesión (`logout.php`).

---

### 4. DISEÑO Y FRONTEND (Moderno, Interactivo y con Efectos en JS)
Quiero rediseñar la interfaz actual manteniendo la temática institucional del Centro Educativo Nonogasta, pero dándole un estilo Ultra Moderno (Glassmorphism / Neumorphism suave / gradientes elegantes en tonos marrones y dorados).

Requisitos de UI/UX y JavaScript:
- **Vista 1: Pantalla de Login (`index.php`)**
  - Tarjeta contenedora con efecto Glassmorphism (cristal esmerilado con `backdrop-filter`).
  - Animaciones de entrada suaves al cargar la página (fade-in, slide-up con CSS/JS).
  - Efecto interactivo de partículas en movimiento en el fondo (Canvas JS) que reaccione al cursor.
  - Campos de texto dinámicos con etiquetas flotantes (floating labels), validación en tiempo real con JS y botón para mostrar/ocultar contraseña con animación.
  - Botón "Entrar" con microinteracciones (efecto glow/resplandor y efecto ripple al hacer clic).
  - Manejo de errores dinámico mediante Fetch/AJAX (que no recargue la página si las credenciales son incorrectas, mostrando un toast/alerta flotante animada).

- **Vista 2: Panel de Preces / Dashboard (`dashboard.php`)**
  - Sidebar dinámico con avatar de usuario ("Silvana - Preceptora • CEN"), navegación interactiva y estados activos.
  - Banner superior estilizado e informativo.
  - Tarjetas principales (Registro de novedades, Seguimiento grupal, Entregas digitales) con efecto hover 3D (tilt effect) y microanimaciones al pasar el cursor.
  - Mantenimiento de la estética institucional moderna.

---

### 5. ENTREGABLES
Proporciona el código fuente completo archivo por archivo siguiendo exactamente la estructura planteada, listo para ser copiado en el proyecto.
