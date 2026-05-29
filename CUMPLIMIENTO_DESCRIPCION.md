# Cumplimiento de `descripcion.md` — StudyVault

Análisis punto por punto de los requisitos de `descripcion.md` frente a lo realmente implementado en `studyvault/`. Para cada punto: estado, evidencia (dónde está) y, en los **parciales/no cumplidos**, qué implementar que **de verdad sirva al propósito** del proyecto (gestor de estudio de inglés y programación competitiva).

**Leyenda:** ✅ Cumple · 🟡 Parcial · ❌ No cumple

> Fecha: 2026-05-27 · Documento de análisis, **sin cambios al proyecto**.
>
> **Actualización 2026-05-28:** se implementaron 7 de los 8 puntos parciales/❌ que se podían cubrir al 100% sin nueva infraestructura (DataTables, Chart.js, Rol Invitado, Panel Admin, Recuperación de contraseña con token, Notificaciones in-app + Notification API, Exportar PDF vía `window.print()` + CSS de impresión). Detalles en `studyvault/PLAN_IMPLEMENTACION.md` (entrada 2026-05-28). Migración requerida: `migrations_v8.sql`.

---

## Tabla resumen

| # | Requisito | Estado |
|---|---|---|
| Gen | Especificaciones generales (8 puntos) | ✅ |
| 1 | Interfaz Web (HTML+CSS) | ✅ (DataTables incluido pero sin usar) |
| 2 | Cliente (JavaScript) | ✅ |
| 3 | Servidor (PHP OOP) | ✅ |
| 4 | Base de datos MySQL | ✅ |
| 5 | Autenticación, sesiones y **roles** | ✅ (rol Invitado en plantillas públicas + panel admin) |
| 6 | Funcionalidades dinámicas obligatorias | ✅ (DataTables en Flashcards/CP/Recursos/Admin; panel admin con CRUD de estado) |
| 7 | AJAX / fetch | ✅ |
| 8 | Consumo de servicios web | ✅ (4 APIs públicas) |
| 9 | Seguridad mínima | ✅ (supera lo pedido) |
| 10 | Arquitectura MVC | ✅ |
| Opc-Int | Recuperación de contraseña | ✅ (token + expiración 1h, enlace en pantalla sin SMTP) |
| Opc-Int | Exportar PDF o Excel | ✅ (CSV + PDF de reporte/meta vía `window.print()` + CSS print) |
| Opc-Int | Gráficas estadísticas | ✅ (6 gráficas Chart.js en `?page=stats`) |
| Opc-Int | Notificaciones | ✅ (centro in-app + Notification API + recordatorios automáticos) |
| Opc-Int | Tema oscuro | ✅ |
| Opc-Int | Bitácora de acciones | ✅ |
| Opc-Av | API REST propia | ❌ |
| Opc-Av | WebSockets / Chat | ❌ |
| Opc-Av | Panel administrativo avanzado | 🟡 (mini-panel implementado: usuarios + uso global; falta auditoría granular) |
| Opc-Av | Nube / Docker / Framework | ❌ |
| Entreg | Documentación formal | 🟡 (falta llenar doc + capturas + diagrama BD) |
| Entreg | Código / BD .sql / Manual | ✅ |
| Entreg | Video demostrativo (opcional) | ❌ (opcional) |

---

## Especificaciones generales — ✅
- **HTML5/CSS3** ✅ Bootstrap 5 + `assets/css/app.css`.
- **JavaScript y AJAX/fetch** ✅ `assets/js/app.js` + scripts por vista.
- **PHP orientado a objetos** ✅ `models/`, `controllers/`.
- **PDO con consultas preparadas** ✅ `config/database.php` + todos los modelos.
- **MySQL** ✅ 16 tablas.
- **Manejo de sesiones y seguridad** ✅ sesiones, CSRF, hashing, throttling, cabeceras, CSP.
- **Consumo O publicación de servicios web** ✅ por **consumo** (4 APIs). *La "publicación" (API REST propia) sería el extra avanzado — ver Opcionales.*
- **Diseño responsivo y UX** ✅ Bootstrap responsive + modo oscuro.

---

## Mínimas

### 1. Interfaz Web — ✅ (con un detalle)
Cumple: responsive, menú (navbar + sidebar), formularios estructurados, **CSS externo** (`app.css`), tablas estilizadas, componentes modernos. **Bootstrap** ✅ y **Font Awesome** ✅.
- 🟡 **DataTables está cargado en `header.php` pero no se usa** (la app usa búsqueda/filtros AJAX propios). No es un incumplimiento (es "recomendado"), pero conviene **aprovecharlo** para el ordenamiento (ver punto 6).

### 2. Cliente (JavaScript) — ✅
Validaciones dinámicas (formularios, coincidencia de contraseña), eventos, manipulación del DOM, fetch, actualización parcial sin recargar, búsquedas/filtros instantáneos (Recursos), alertas dinámicas.
- *Mejora opcional (no requisito):* sustituir `alert()`/`confirm()` por toasts/modales de Bootstrap para una UX más pulida.

### 3. Servidor (PHP OOP) — ✅
MVC con clases/métodos, **inclusión modular** (`config/init.php` + autoloader), **reutilización** (motor SM-2 compartido entre flashcards y CP). CRUD completos (materias, recursos, unidades, metas, flashcards, problemas CP, plantillas). Validación en servidor, **manejo de errores** centralizado (`set_exception_handler` + log), sanitización (`htmlspecialchars` + preparadas).

### 4. Base de datos MySQL — ✅
Modelo relacional (16 tablas), PK/FK, relaciones (1:N y N:M con `goal_resources`), integridad referencial (`ON DELETE CASCADE`), índices. PDO + consultas preparadas obligatorias ✅.

### 5. Autenticación, sesiones y roles — 🟡
Cumple: **login, logout, sesiones, roles** (`admin`/`student`), `password_hash`/`password_verify`, **protección de rutas** (`requireLogin`/`requireAdmin`). Supera el mínimo con CSRF, throttling y regeneración de sesión.
- 🟡 **Los roles existen pero casi no se diferencian**: `admin` no tiene privilegios ni panel propio distintos del `student`, y **no existe el rol "Invitado"** del ejemplo.
- **Qué implementar (útil al propósito):**
  - **Rol "Invitado" de solo lectura** que pueda **explorar las plantillas de estudio públicas** sin crear cuenta (encaja con la función de compartir ya existente: un invitado ve y "previsualiza" planes públicos, y se registra para clonarlos). Aporta valor real (descubrimiento/onboarding).
  - **Privilegios reales de admin**: un mini-panel para administrar usuarios (listar/activar) y ver uso global (totales de tarjetas/sesiones). Hace que el rol `admin` signifique algo.

### 6. Funcionalidades dinámicas obligatorias — 🟡
- Paginación ✅ (Recursos) · Búsquedas ✅ · Filtros ✅ · CRUD ✅ · **Subida de archivos** ✅ (PDF/imágenes en recursos) · Dashboard ✅.
- 🟡 **Ordenamiento: NO implementado.** Las tablas no permiten ordenar por columna.
- 🟡 **Panel administrativo**: hay dashboard de usuario, pero no panel de administración (ver punto 5).
- **Qué implementar (útil y de bajo esfuerzo):**
  - **Activar DataTables** (ya está cargado) en las tablas de **Recursos**, **Competitiva** y **Flashcards** → ordenamiento por columna (rating, fecha de repaso, estado, nivel CEFR) y paginación/orden del lado cliente. Para CP es muy útil ordenar por **rating** o por **próxima fecha de repaso**; para vocabulario, por **nivel** o **vencimiento**. Cierra el punto "ordenamiento" con mínimo código.
  - *(Nota: si activas DataTables, conviene unificarlo con la paginación server-side actual de Recursos para no duplicar paginación.)*

### 7. AJAX / fetch — ✅
Consultas automáticas, actualización de tablas (búsqueda en vivo de Recursos), eliminación sin recargar, formularios dinámicos (modales), búsqueda en tiempo real, diccionario, repaso de flashcards/CP. Uso extenso y en varias partes.

### 8. Consumo de servicios web — ✅ (supera el mínimo)
Consume **4 APIs públicas gratuitas sin key**: Free Dictionary (definición/audio), Datamuse (colocaciones), Tatoeba (frases), **Codeforces** (rating/problemas/concursos). Todas vía **proxy PHP** (evita CORS + caché + rate limit).

### 9. Seguridad mínima — ✅ (supera lo pedido)
Validaciones cliente/servidor, anti-SQL Injection (PDO preparado), manejo de sesiones, restricción de acceso (por dueño/`user_id` e IDOR probado con tests), sanitización. **Extras:** CSRF en todo POST, `password_hash`, `session_regenerate_id`, cabeceras (`X-Frame-Options`, `nosniff`, `Referrer-Policy`, **CSP**), **rate limiting**, **throttling de login**, `.htaccess` que bloquea ejecución en `uploads/`.

### 10. Arquitectura MVC — ✅
`config/`, `models/`, `controllers/`, `views/`, `assets/{css,js,img}` + `api/` para los endpoints AJAX. Coincide con la estructura recomendada.

---

## Funcionalidades opcionales (mayor calificación)

### Nivel intermedio
- **Recuperación de contraseña — ❌.** No existe.
  - **Qué implementar:** flujo con **token** (tabla `password_resets` con token + expiración). Como no hay servidor de correo configurado en XAMPP, una variante válida para entrega: generar el enlace/token y **mostrarlo en pantalla** (o enviarlo por correo si configuras SMTP/Mailtrap). Útil de cara a un uso real multiusuario.
- **Exportar PDF o Excel — 🟡.** Hay **exportación CSV** de flashcards (compatible con Excel/Anki) → cubre "Excel" parcialmente. **No hay PDF.**
  - **Qué implementar (útil):** **reporte de progreso en PDF** (con DOMPDF): el reporte semanal o el avance de una **meta** exportable/imprimible; o la lista de problemas de CP resueltos. Aporta algo tangible para revisar/compartir tu progreso.
- **Gráficas estadísticas — 🟡.** Hay barras de progreso y un **heatmap** de constancia, pero **no gráficas reales**.
  - **Qué implementar (ALTO valor para el propósito):** **Chart.js** para visualizar el avance real:
    - **Curva de rating de Codeforces** en el tiempo (línea) — el "progreso real" de CP.
    - **Minutos de estudio por día/semana** (barras) — hábito.
    - **Problemas resueltos por tag** (radar/barras) — mapa de **debilidades**.
    - **Vocabulario por nivel CEFR / por estado** (nuevas/aprendiendo/maduras) — avance en inglés.
    Es la mejora opcional que **más refuerza el objetivo** ("ver avance real").
- **Notificaciones — 🟡.** Hay avisos in-app (badge de "tarjetas por repasar", alertas de acción), pero no notificaciones/recordatorios.
  - **Qué implementar (útil):** un **centro de notificaciones** o la **Notification API del navegador** que recuerde "tienes N repasos pendientes hoy" o "vas atrasado en tu meta X". Encaja con la repetición espaciada (el motor del propósito).
- **Tema oscuro — ✅.** Implementado (toggle persistente).
- **Bitácora de acciones — ✅.** Tabla `activity_log` + helper `log_activity()`.

### Nivel avanzado (todos ❌ — opcionales, no requeridos)
- **API REST propia — ❌.** Hay endpoints JSON internos (`api/*.php`, `?page=...`) pero no una API REST documentada.
  - **Qué implementar (si quieres el extra avanzado):** exponer rutas REST versionadas (`/api/v1/...`) con verbos HTTP y, a futuro, una app móvil/PWA que las consuma. Cubriría también la parte de "publicación de servicios web".
- **WebSockets / Chat en tiempo real — ❌.** Poco alineados con el propósito (estudio individual); no los recomiendo salvo que quieras "grupos de estudio".
- **Panel administrativo avanzado — ❌.** Ver punto 5 (un panel admin básico ya sería un buen paso).
- **Nube / Docker / Framework PHP — ❌.** Mejoras de despliegue/escala; opcionales. Docker daría reproducibilidad si lo subes a la nube.

---

## Entregables
- **Documentación — 🟡.** Tienes `README.md`, `MANUAL_USUARIO.md` y `DESCRIPCION_PROYECTO.md`; falta **llenar `ENTREGA/DOCUMENTACION.md`** (portada, objetivos, problema, requerimientos, **diagrama de BD**, casos de uso, **capturas**, explicación técnica). El esqueleto ya está listo para rellenar.
- **Código fuente — ✅** organizado y comentado.
- **Base de datos `.sql` — ✅** (`ENTREGA/studyvault_completo.sql`, esquema limpio en un archivo).
- **Manual de usuario — ✅** (`MANUAL_USUARIO.md`).
- **Video demostrativo — ❌** (opcional).

---

## Qué implementar para "completar" — priorizado por valor (propósito + rúbrica)

1. **Gráficas con Chart.js** (Opc-Intermedio + refuerza "Funcionalidad" 25%). *Alto valor:* visualiza rating CF, tiempo de estudio, debilidades por tag y vocabulario por nivel. Es lo que más sirve al objetivo de "ver avance real".
2. **Ordenamiento con DataTables** (cierra el punto 6, esfuerzo bajo — ya está cargado). Ordenar problemas por rating/fecha de repaso y tarjetas por nivel/vencimiento.
3. **Rol "Invitado" + mini-panel admin** (cierra el punto 5). Invitado que explora **plantillas públicas** (encaja con compartir); admin con gestión de usuarios/uso global.
4. **Exportar PDF de progreso** (Opc-Intermedio). Reporte semanal o de meta en PDF (DOMPDF).
5. **Recuperación de contraseña** (Opc-Intermedio) con token.
6. **Notificaciones/recordatorios** de repasos pendientes (Opc-Intermedio), ligado a SM-2.
7. *(Avanzado, opcional)* **API REST propia** documentada → habilita "publicación de servicios web" y una futura PWA.

> **Conclusión:** todas las **especificaciones mínimas obligatorias se cumplen** (con dos parciales menores: **ordenamiento** y **diferenciación de roles/panel admin**, ambos de bajo esfuerzo). En opcionales ya tienes **tema oscuro** y **bitácora**; las de mayor impacto pendientes son **gráficas estadísticas** y **ordenamiento**. Completar esos dos elevaría notablemente "Funcionalidad" y "Diseño" sin mucho esfuerzo.
