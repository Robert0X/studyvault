# Documentación del proyecto — StudyVault

> Esqueleto del documento de la rúbrica. Rellena los `[...]` y agrega capturas donde se indica.
> Puedes pasarlo a Word/PDF para entregar.

---

## Portada
- **Proyecto:** StudyVault — Gestor de material de estudio
- **Materia:** Programación Web
- **Alumno:** [tu nombre]
- **Matrícula / Grupo:** [...]
- **Docente:** [...]
- **Fecha:** [...]

---

## 1. Introducción
StudyVault es una aplicación web para organizar y estudiar material de aprendizaje, con enfoque en **inglés** y **programación competitiva**. Centraliza recursos (enlaces, PDFs, notas, videos), mide el **progreso real** del estudiante y aplica técnicas de aprendizaje basadas en evidencia (repetición espaciada, práctica activa, estudio por nivel).

## 2. Objetivos
- **General:** desarrollar una aplicación web dinámica y segura que ayude a estudiar de forma ordenada y medible.
- **Específicos:**
  - Aplicar PHP orientado a objetos con arquitectura MVC y PDO con consultas preparadas.
  - Implementar autenticación con roles, sesiones y medidas de seguridad.
  - Usar AJAX/fetch para una experiencia fluida sin recargas.
  - Consumir servicios web públicos (diccionario, Codeforces, etc.).
  - Medir progreso real (unidades, metas, tiempo) y favorecer la retención (SM-2).

## 3. Planteamiento del problema
Los estudiantes acumulan material disperso (libros, cursos, problemas) sin una forma clara de **medir avance** ni de **retener** lo aprendido. Marcar recursos como "hechos" no refleja el progreso real ni combate el olvido. StudyVault resuelve esto con progreso granular, repetición espaciada y metas con seguimiento de ritmo.

## 4. Requerimientos
**Funcionales:** registro/login con roles; CRUD de materias, recursos, unidades, metas, flashcards y problemas; búsqueda/filtros AJAX; subida de archivos; repaso SM-2; sincronización con Codeforces; reporte de progreso.
**No funcionales:** seguridad (CSRF, hashing, sesiones, cabeceras), diseño responsivo, rendimiento (caché de diccionario/catálogo), código mantenible (MVC).

## 5. Modelo de base de datos
Base `studyvault` (MySQL/MariaDB). Tablas principales y relaciones:
- **users** (1) → (N) **subjects**, **resources**, **goals**, **flashcards**, **cp_problems**, **study_sessions**, **activity_log**.
- **subjects** (1) → (N) **resources**.
- **resources** (1) → (N) **units**; relación N:M con **goals** vía **goal_resources** (con `weight`).
- Apoyo: **dictionary_cache**, **word_bank**/**word_enrichment**, **cf_problemset_cache**, **cp_templates**.
- Integridad referencial con FOREIGN KEY y `ON DELETE CASCADE`; índices en claves de búsqueda y `due_date`/`due_review`.

> **Diagrama:** generar en phpMyAdmin → base `studyvault` → pestaña **Diseñador** → exportar imagen y pegar aquí.
>
> `[INSERTAR DIAGRAMA DE BASE DE DATOS]`

## 6. Casos de uso (principales)
1. **Autenticarse:** el usuario inicia sesión; según su rol (admin/estudiante) accede a las funciones; las rutas están protegidas.
2. **Gestionar recursos:** crear/editar/eliminar recursos por materia, subir PDFs, buscar y filtrar en vivo, cambiar su estado.
3. **Medir progreso de un libro:** dividir un recurso en unidades, marcarlas completadas y ver el % real.
4. **Estudiar con flashcards:** repasar las tarjetas que tocan hoy (SM-2) en modo Clásico/Cloze/Producción; guardar palabras desde el diccionario.
5. **Practicar competitiva:** vincular handle de Codeforces, sincronizar problemas y rating, recibir sugerencias a su nivel, repasar problemas y guardar plantillas.
6. **Planear metas:** crear una meta con fecha objetivo, asociar recursos con peso y ver si va a tiempo.
7. **Registrar tiempo:** usar el Pomodoro, ver racha y heatmap; consultar el reporte semanal.

> `[INSERTAR DIAGRAMA DE CASOS DE USO si lo pide la rúbrica]`

## 7. Capturas de pantalla
Tomar y pegar capturas de:
- `[Login / Registro]`
- `[Dashboard con progreso y metas]`
- `[Materias y Recursos (búsqueda AJAX)]`
- `[Unidades de un recurso con %]`
- `[Estudio de flashcards (modo Cloze/Producción)]`
- `[Diccionario con definición + colocaciones + ejemplos]`
- `[Competitiva: rating, problemas, sugerencias]`
- `[Metas con pace]`
- `[Temporizador con heatmap]`
- `[Reporte semanal]`
- `[Modo oscuro]`

## 8. Explicación técnica
- **Arquitectura MVC:** `models/` (acceso a datos con PDO), `controllers/` (lógica), `views/` (presentación), enrutado en `index.php`, bootstrap centralizado en `config/init.php`.
- **Seguridad:** `password_hash`/`password_verify`, **CSRF** en todo POST, `session_regenerate_id` al iniciar sesión, cookies `HttpOnly`/`SameSite`, cabeceras (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`), **rate limiting** en proxies externos, `.htaccess` que bloquea ejecución en `uploads/`, consultas preparadas contra inyección SQL, `htmlspecialchars` en salidas.
- **AJAX/fetch:** búsquedas en vivo, CRUD sin recargar, repaso, diccionario.
- **Servicios web consumidos (sin API key):** Free Dictionary, Datamuse (colocaciones), Tatoeba (frases), Codeforces (rating/problemas/concursos).
- **Algoritmo SM-2:** repetición espaciada para flashcards y repaso de problemas (función pura testeada).
- **Pruebas:** `php tests/sm2_test.php`, `goal_test.php`, `units_test.php`.

## 9. Manual de usuario
Ver `studyvault/MANUAL_USUARIO.md` (incluir como anexo).

## 10. Conclusiones
`[Redactar: qué se logró, aprendizajes, posibles mejoras — ver PROPUESTAS_DISENO.md y PLAN_IMPLEMENTACION.md]`
