Actúa como un desarrollador web Full-Stack senior. Necesito que crees una aplicación web simple que incluya una pantalla de Login y un Panel Principal (Dashboard) inspirados visualmente en las imágenes adjuntas, pero con un estilo moderno, limpio y estilizado.

### 1. ESTRUCTURA DE BASE DE DATOS (Estricta)
Debes utilizar exactamente el esquema SQL proporcionado a continuación:

CREATE DATABASE moduloIncrip;
USE moduloIncrip;

CREATE TABLE rol (
    id_rol INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

-- Incluye un script SQL de datos de prueba (Insertar roles como 'Preceptor/a' y un usuario de prueba con contraseña encriptada).

### 2. ARQUITECTURA Y ESTRUCTURA DE ARCHIVOS
Genera el código con una estructura simple, directa y sin dependencias ni frameworks innecesarios (puedes usar PHP con PDO para el backend, HTML5, CSS3 nativo y JavaScript vanilla):

/proyecto
├── db.php             # Conexión PDO a la base de datos MySQL 'moduloIncrip'
├── index.php          # Vista de Login (Formulario + Estilos + JS de validación)
├── login_process.php  # Procesamiento del login, verificación de password_hash y manejo de sesiones
├── dashboard.php      # Panel de Control (Verifica sesión activa e id_rol)
├── logout.php         # Cierre de sesión y destrucción de variables
└── assets/
    ├── css/
    │   └── styles.css # Estilos globales, variables CSS y diseño de tarjetas
    └── img/           # Placeholder o referencias para logos

### 3. DISEÑO Y ESTILOS DE LA INTERFAZ (UI/UX)
Toma como referencia los componentes de las imágenes pero aplica mejoras estéticas estilizadas (colores armónicos, bordes redondeados suavemente, sombreados sutiles y buena tipografía):

A. Pantalla de Login (index.php):
- Tarjeta dividida en dos secciones:
  * Izquierda (oscura/acentuada): Mensaje de bienvenida, versión (v1.0), insignia del centro educativo y sello de "Seguridad activa / Sesión cifrada".
  * Derecha (clara): Título "Iniciar sesión", campo para Gmail/Email institucional, campo para Contraseña (con botón para mostrar/ocultar contraseña), enlace de "¿Olvidaste tu contraseña?" y botón principal de "Entrar".

B. Panel Principal / Dashboard (dashboard.php):
- Banner/Header superior con el título del módulo y chip de rol ("Preceptoría / Acceso interno").
- Barra lateral o tarjeta izquierda de usuario: Avatar con iniciales, Nombre y Apellido completos (consultados dinámicamente desde la BD), Rol y Menú de navegación ("Inicio", "Formulario", "Cerrar sesión").
- Panel central de bienvenida: Saludo personalizado ("Bienvenida/o, [Nombre]"), aviso de estado e indicadores/tarjetas rápidas ("Registro de novedades", "Seguimiento grupal", "Entregas digitales").

### 4. REQUISITOS TÉCNICOS
- Usa password_verify() en PHP para comparar la contraseña ingresada contra 'password_hash'.
- Valida que el campo 'activo' sea TRUE para permitir el acceso.
- Muestra mensajes de error claros en el formulario de login si las credenciales son incorrectas o la cuenta está inactiva.
- Asegura las páginas mediante control de sesiones ($_SESSION).

Por favor, genera el código fuente completo e individual para cada archivo de la estructura propuesta.