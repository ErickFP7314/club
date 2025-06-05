# Club de Ciencias - Sitio Web

![alt text](image.png)

## Instrucciones de Instalación para Windows

### Requisitos Previos

1. **XAMPP** (o WAMP)
   - Descarga e instala XAMPP desde: [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
   - Asegúrate de instalar los módulos de Apache, MySQL, PHP y phpMyAdmin

### Configuración del Entorno

1. **Coloca los archivos en el directorio correcto**:
   - Copia la carpeta del proyecto a: `C:\xampp\htdocs\sitio`
   - O usa el directorio que prefieras, pero asegúrate de ajustar las rutas en la configuración

2. **Configuración de la base de datos**:
   - Inicia los servicios de Apache y MySQL desde el Panel de Control de XAMPP
   - Abre tu navegador y ve a: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Crea una nueva base de datos llamada `club_ciencias`
   - Importa el archivo `Dump20250530.sql` ubicado en la raíz del proyecto y ejecutalo en un editor MySQL

3. **Configura el archivo de conexión**:
   - Abre `config/database.php`
   - Asegúrate de que los datos de conexión sean correctos:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'club_ciencias');
     ```

### Ejecutar el Proyecto

1. **Iniciar los servidores**:
   - Abre el Panel de Control de XAMPP
   - Inicia los módulos de Apache y MySQL

2. **Acceder al sitio web**:
   - Abre tu navegador web
   - Ingresa: [http://localhost/sitio](http://localhost/sitio)

3. **Credenciales de acceso**:
   - **Usuario administrador:** admin@clubciencias.com
   - **Contraseña:** admin123

### Estructura del Proyecto

- `/` - Páginas principales
- `/auth` - Autenticación (login, registro)
- `/config` - Archivos de configuración
- `/exposiciones` - Módulo de presentaciones
- `/imganes club` - Imágenes del sitio
- `/includes` - Clases principales
- `/css`, `/js`, `/fonts` - Recursos estáticos

### Solución de Problemas Comunes

1. **Error de conexión a la base de datos**:
   - Verifica que MySQL esté en ejecución
   - Comprueba las credenciales en `config/database.php`

2. **Página en blanco**:
   - Revisa los logs de error de Apache
   - Verifica los permisos de los directorios

3. **Problemas con las rutas**:
   - Asegúrate de que el módulo `mod_rewrite` esté habilitado
   - Verifica el archivo `.htaccess`

### Soporte

Para soporte técnico, contacta al equipo de desarrollo.

---

© 2025 Club de Ciencias. Todos los derechos reservados.

