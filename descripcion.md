# Especificaciones generales del proyecto

Desarrollar una aplicación web dinámica que resuelva una problemática real o simulada, aplicando:
* HTML5 y CSS3
* JavaScript y AJAX/fetch
* PHP orientado a objetos
* PDO con consultas preparadas
* MySQL
* Manejo de sesiones y seguridad
* Consumo o publicación de servicios web
* Diseño responsivo y experiencia de usuario

## Temáticas sugeridas

La temática es libre, pero conviene que permita implementar muchos módulos. Algunas buenas opciones serían:
* Sistema de control escolar
* Sistema de citas médicas
* Tienda en línea
* Sistema de reservaciones
* Plataforma de cursos
* Sistema de biblioteca
* Control de inventarios
* Sistema de ventas
* Agenda empresarial
* Sistema de gimnasio
* Bolsa de trabajo
* Sistema de veterinaria
* Plataforma de eventos

## Especificaciones mínimas del proyecto

### 1. Interfaz Web (HTML + CSS)
Debe incluir:
* Diseño responsivo
* Menú de navegación
* Formularios bien estructurados
* Uso de CSS externo
* Tablas estilizadas
* Componentes visuales modernos

Se recomienda usar:
* Bootstrap
* Tailwind
* DataTables
* Font Awesome

### 2. Programación del lado cliente (JavaScript)
Debe implementar:
* Validaciones dinámicas
* Eventos JS
* Manipulación del DOM
* AJAX o fetch()
* Actualización parcial de contenido sin recargar página

Ejemplos:
* Búsquedas dinámicas
* Formularios automáticos
* Filtros instantáneos
* Alertas y mensajes dinámicos

### 3. Programación del lado servidor (PHP)
Debe desarrollarse usando:
* PHP orientado a objetos
* Clases
* Métodos
* Inclusión modular de archivos
* Reutilización de código

Debe incluir:
* CRUD completos
* Validaciones en servidor
* Manejo de errores
* Sanitización de datos

### 4. Base de datos MySQL
La aplicación debe tener:
* Modelo relacional
* Llaves primarias y foráneas
* Relaciones entre tablas
* Consultas SQL
* Integridad referencial

Obligatorio:
* PDO
* Consultas preparadas

### 5. Sistema de autenticación y sesiones
Debe existir:
* Login
* Logout
* Sesiones
* Roles de usuario

Ejemplo:
* Administrador
* Usuario normal
* Invitado

Seguridad mínima:
* password_hash()
* password_verify()
* Protección de rutas

### 6. Funcionalidades dinámicas obligatorias
Se recomienda incluir al menos:
* Paginación
* Búsquedas
* Filtros
* Ordenamiento
* Operaciones CRUD completas
* Subida de imágenes o archivos
* Dashboard o panel administrativo

### 7. AJAX / fetch()
Debe utilizarse en varias partes del sistema.

Ejemplos:
* Consultas automáticas
* Actualización de tablas
* Eliminación sin recargar
* Formularios dinámicos
* Búsquedas en tiempo real

### 8. Consumo de servicios web
El sistema debe consumir al menos un API pública.

Ejemplos:
* Clima
* Mapas
* Geolocalización
* Tipo de cambio
* Noticias
* APIs propias

### 9. Seguridad mínima requerida
Debe contemplar:
* Validaciones cliente/servidor
* Protección contra SQL Injection
* Manejo de sesiones
* Restricción de acceso
* Sanitización de entradas

### 10. Arquitectura recomendada
Aunque no es obligatorio, sería ideal trabajar con una estructura similar a MVC:
* /proyecto
  * /config
  * /models
  * /controllers
  * /views
  * /assets
    * /css
    * /js
    * /img

## Funcionalidades opcionales (para mayor calificación)

### Nivel intermedio
* Recuperación de contraseña
* Exportar PDF o Excel
* Gráficas estadísticas
* Notificaciones
* Tema oscuro
* Bitácora de acciones

### Nivel avanzado
* API REST propia
* WebSockets
* Panel administrativo avanzado
* Chat en tiempo real
* Implementación en la nube
* Docker
* Framework PHP

## Entregables sugeridos

### 1. Documentación
Debe incluir:
* Portada
* Introducción
* Objetivos
* Planteamiento del problema
* Requerimientos
* Diagrama de base de datos
* Casos de uso
* Capturas
* Explicación técnica

### 2. Código fuente
Organizado y comentado.

### 3. Base de datos
Archivo .sql

### 4. Manual de usuario
Breve explicación del uso del sistema.

### 5. Video demostrativo (opcional)
Mostrando:
* Login
* CRUD
* AJAX
* Servicios web
* Seguridad

## Propuesta de rúbrica de evaluación

| Criterio | Porcentaje |
| :--- | :--- |
| Diseño e interfaz | 15% |
| Funcionalidad | 25% |
| PHP orientado a objetos | 15% |
| Base de datos y PDO | 15% |
| AJAX/fetch() | 10% |
| Seguridad y sesiones | 10% |
| Servicios web | 5% |
| Documentación | 5% |