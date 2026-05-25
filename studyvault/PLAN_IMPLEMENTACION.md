# StudyVault — Plan de implementación (clasificación y orden)

> Clasificación de **todas** las mejoras de [`MEJORAS.md`](./MEJORAS.md) por **dificultad** y **prioridad**, más el **orden recomendado** para implementarlas.
>
> **Nada está implementado todavía.** Este documento es el mapa de ejecución.
> Fecha: 2026-05-22.

---

## A. Metodología (cómo clasifico)

Cada mejora se evalúa en dos ejes + dependencias:

**Dificultad de implementación**
- 🟢 **Baja** — horas; cambios localizados, sin tocar el modelo de datos.
- 🟡 **Media** — 1–3 días; toca varias capas o el esquema.
- 🔴 **Alta** — varios días; arquitectura nueva, integración externa compleja o multi-feature.

**Prioridad (importancia para el proyecto y para que de verdad lo uses)**
- 🔴 **Crítica** — cimiento, deuda de seguridad, o bloquea muchas otras cosas.
- 🟠 **Alta** — núcleo del valor (progreso real, retención, tus enfoques EN/CP).
- 🟡 **Media** — mejora notable pero no esencial al inicio.
- ⚪ **Baja** — nice-to-have / escala / solo si la app se comparte.

> **La clave que añade valor a esta clasificación: las dependencias.** No basta ordenar por "fácil e importante"; hay que respetar qué desbloquea qué (ej. el motor SM-2 antes de los modos de tarjeta; el modelo de datos antes del progreso granular). Por eso el orden final (sección D) está organizado en **bloques dependientes**, no solo por puntaje.

---

## B. Matriz Prioridad × Dificultad

```
            DIFICULTAD BAJA 🟢        DIFICULTAD MEDIA 🟡        DIFICULTAD ALTA 🔴
          ┌─────────────────────┬─────────────────────────┬──────────────────────┐
PRIORIDAD │ ★ HACER YA          │ ★ PLANEAR BIEN          │ ★ APUESTAS GRANDES   │
ALTA/CRÍT │ • CSRF              │ • Modelo de datos base  │ • Progreso real      │
🔴🟠      │ • Sesión segura     │ • Flashcards + SM-2     │   (dashboard global) │
          │ • Caché diccionario │ • Unidades (libro)      │ • API Codeforces     │
          │ • Guardar palabra   │ • Metas/planes          │   (integración full) │
          │ • Modo oscuro       │ • Timer + tiempo        │ • Banco CEFR + enriq │
          │                     │ • Tracking problemas CP │                      │
          ├─────────────────────┼─────────────────────────┼──────────────────────┤
PRIORIDAD │ ◦ RELLENO RÁPIDO    │ ◦ CUANDO HAGA FALTA     │ ◦ EVALUAR DESPUÉS    │
MEDIA/BAJA│ • Editorial notes   │ • Modos de tarjeta      │ • PWA / móvil        │
🟡⚪      │ • Soft delete       │ • Streaks / heatmap     │ • Gamificación       │
          │ • Headers seguridad │ • Búsqueda full-text    │ • Social / ranking   │
          │ • Rate limit dict   │ • Import/export Anki    │ • REST API completa  │
          │ • Plantilla roadmap │ • Reportes / reminders  │ • Tests + Docker     │
          │ • .env config       │ • Plantillas públicas   │ • i18n               │
          └─────────────────────┴─────────────────────────┴──────────────────────┘
```

- **Hacer ya** (alto valor, bajo esfuerzo) → arranque inmediato.
- **Apuestas grandes** (alto valor, alto esfuerzo) → planificar; son el corazón del producto.
- **Relleno rápido** → meter entre bloques grandes para ganar momentum.
- **Evaluar después** → solo si decides escalar a más usuarios.

---

## C. Tabla maestra (todas las mejoras clasificadas)

> `Dep.` = de qué bloque/feature depende. `Bloque` = en qué fase del orden recomendado va (sección D).
> El **seguimiento de avance** (casillas para marcar completado) vive en la **sección D**; aquí solo está la clasificación de referencia.

### Seguridad y cimientos
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| Tokens CSRF en todos los POST | 🟢 | 🔴 Crítica | — | 0 |
| `session_regenerate_id` + cookies seguras | 🟢 | 🔴 Crítica | — | 0 |
| Credenciales en `.env` (no hardcode) | 🟢 | 🟠 Alta | — | 0 |
| Manejo de errores centralizado (ligero) | 🟢 | 🟡 Media | — | 0 |
| Headers de seguridad (CSP, X-Frame…) | 🟢 | 🟡 Media | — | 8 |
| Rate limiting en `dictionary.php` | 🟢 | 🟡 Media | — | 8 |
| Subida de archivos fuera del webroot | 🟡 | 🟡 Media | — | 8 |
| Recuperación de contraseña + verificación email | 🟡 | 🟡 Media | B1 | 8 |
| Validación de entrada más estricta (refuerzo) | 🟢 | 🟡 Media | — | continuo |

### Rendimiento y arquitectura
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| Caché del diccionario (tabla local) | 🟢 | 🟠 Alta | — | 0 |
| Soft delete / papelera | 🟢 | 🟡 Media | B1 | 8 |
| Paginación / carga incremental | 🟡 | 🟡 Media | — | 8 |
| Búsqueda full-text en notas | 🟡 | 🟡 Media | — | 8 |
| API REST propia bien estructurada | 🔴 | ⚪ Baja | — | 8 |
| Tests (modelos y validaciones) | 🟡 | 🟡 Media | — | continuo |
| Docker | 🟡 | ⚪ Baja | — | 8 |
| i18n (multi-idioma) | 🟡 | ⚪ Baja | — | 8 |

### Modelo de datos (fundación)
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| Esquema ampliado (units, goals, sessions, vocab, cp) + índices | 🟡 | 🔴 Crítica | — | 1 |
| Bitácora de actividad (`activity_log`) | 🟢 | 🟡 Media | B1 | 1 |

### Progreso real (núcleo)
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| Unidades dentro de recurso (capítulos/páginas) → "libro enorme" | 🟡 | 🟠 Alta | B1 | 3 |
| Metas/planes con recursos ponderados → "multi-recurso" | 🟡 | 🟠 Alta | B1 | 3 |
| Temporizador Pomodoro + registro de tiempo real | 🟡 | 🟠 Alta | B1 | 3 |
| Dashboard de progreso real (ritmo, ¿a tiempo?, heatmap) | 🔴 | 🟠 Alta | B1,B3 | 3 |

### Retención / Flashcards (núcleo)
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| **Flashcards MÍNIMO** (tarjetas + repaso) | 🟡 | 🟠 Alta | B1 | 2 |
| Botón "Guardar palabra" desde el diccionario | 🟢 | 🟠 Alta | B2 | 2 |
| **Motor SM-2** (repetición espaciada, compartido EN/CP) | 🟡 | 🟠 Alta | B1 | 2 |
| Banco CEFR (importar CEFR-J/Kelly/NGSL) → estudiar por nivel | 🟡 | 🟠 Alta | B2 | 5 |
| Enriquecimiento (Datamuse + Tatoeba + audio, cacheado) | 🟡 | 🟡 Media | B2 | 5 |
| Modos de tarjeta (reconocer/cloze/producción/escucha) | 🟡 | 🟡 Media | B2 | 5 |
| Cobertura por nivel + stats de retención | 🟢 | 🟡 Media | B5 | 5 |
| Import/export Anki (CSV/.apkg) | 🟡 | 🟡 Media | B2 | 5 |

### Inglés (extra)
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| Diccionario enriquecido (Datamuse + Tatoeba en el widget) | 🟡 | 🟡 Media | — | 5 |
| Reproductor de audio de pronunciación (usar más) | 🟢 | 🟡 Media | — | 5 |
| Tracking de listening (minutos) | 🟡 | 🟡 Media | B3 | 5 |
| Diario de escritura (conteo de palabras) | 🟡 | 🟡 Media | — | 5 |

### Programación competitiva
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| Integrar API Codeforces (rating, resueltos, debilidades) | 🔴 | 🟠 Alta | B1 | 4 |
| Tracking de problemas (todo/attempted/solved/upsolved) | 🟡 | 🟠 Alta | B1 | 4 |
| Editorial notes (insight por problema) | 🟢 | 🟡 Media | B4 | 4 |
| Repaso espaciado de problemas difíciles | 🟢 | 🟡 Media | B2,B4 | 4 |
| Análisis de debilidades (por tag/rating) | 🟡 | 🟡 Media | B4 | 4 |
| Biblioteca de plantillas/snippets | 🟡 | 🟡 Media | — | 4 |
| Sugerir "siguiente problema" (zona i+1) | 🟡 | 🟡 Media | B4 | 4 |
| Calendario de concursos | 🟡 | ⚪ Baja | — | 4 |
| Modo virtual contest (cronómetro) | 🟡 | ⚪ Baja | B4 | 8 |

### Hábitos y consistencia
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| Rachas (streaks) + meta diaria | 🟡 | 🟡 Media | B3 | 6 |
| Calendario / heatmap tipo GitHub | 🟡 | 🟡 Media | B3 | 6 |
| Recordatorios (web push / email) | 🔴 | 🟡 Media | B3 | 6 |
| Reporte semanal | 🟢 | 🟡 Media | B3 | 6 |

### Compartir / escalar
| Mejora | Dificultad | Prioridad | Dep. | Bloque |
|---|:---:|:---:|---|:---:|
| Plantillas de planes públicas + clonar | 🔴 | 🟡 Media | B3 | 7 |
| Onboarding con plantillas iniciales | 🟡 | 🟡 Media | B7 | 7 |
| Plantilla "roadmap Codeforces" / "Inglés A2→B1" (contenido) | 🟢 | 🟡 Media | B7 | 7 |
| Modo oscuro | 🟢 | 🟡 Media | — | 0 |
| Perfiles públicos | 🟡 | ⚪ Baja | B1 | 8 |
| Gamificación (XP, niveles, badges) | 🔴 | ⚪ Baja | B6 | 8 |
| Social / amigos / leaderboards | 🔴 | ⚪ Baja | B8 | 8 |
| PWA / mobile-first | 🔴 | 🟡 Media | — | 8 |

---

## D. Orden recomendado de implementación (por bloques)

> Los bloques respetan dependencias. Dentro de cada bloque, hacer primero lo 🟢 y lo 🔴 de prioridad. Cada bloque deja la app **usable y mejor que antes**.
>
> **Cómo usar el seguimiento:** marca `[x]` cada tarea al terminarla, y registra **siempre** una entrada en la [Bitácora (sección G)](#g-bitácora-de-implementación) con archivos tocados, decisiones y cómo probar/revertir. El espacio `📝 Notas:` bajo cada bloque es para apuntes rápidos.

**Estado global de bloques:**
- [x] Bloque 0 — Cimientos y quick wins ✅ (2026-05-22)
- [x] Bloque 1 — Fundación de datos ✅ (2026-05-22)
- [x] Bloque 2 — Flashcards mínimo + SM-2 ✅ (2026-05-22)
- [x] Bloque 3 — Progreso real ✅ (2026-05-25): unidades ("libro enorme"), metas/planes con pace, temporizador Pomodoro + tiempo, y dashboard de progreso real
- [x] Bloque 4 — CP automático (Codeforces) ✅ (2026-05-25): sync de rating/problemas, repaso SM-2 de problemas, sugerir-siguiente (i+1), calendario de concursos y biblioteca de plantillas
- [ ] Bloque 5 — Inglés avanzado
- [ ] Bloque 6 — Hábitos y consistencia
- [ ] Bloque 7 — Compartir / plantillas
- [ ] Bloque 8 — Escala y robustez

---

### 🧱 Bloque 0 — Cimientos y quick wins *(días, crítico)*
Arreglar deuda y cosas de alto valor / bajo esfuerzo, sin dependencias.
- [x] CSRF en todos los POST · 🟢🔴
- [x] `session_regenerate_id` + cookies seguras · 🟢🔴
- [x] Credenciales a `.env` · 🟢🟠
- [x] Caché del diccionario + manejo de error del proxy · 🟢🟠
- [x] Manejo de errores centralizado (ligero) · 🟢🟡
- [x] Modo oscuro · 🟢🟡

**Por qué primero:** tapa la deuda de seguridad (también pesa en la rúbrica), no depende de nada, y da momentum.

📝 _Notas del bloque:_ Todo centralizado en `config/init.php` (env, sesión segura, errores, autoload, CSRF, helpers). Ver bitácora §G para detalles y cómo revertir.

---

### 🗄️ Bloque 1 — Fundación de datos *(enabler, crítico)*
Migrar el esquema **una sola vez** y de forma coherente.
- [x] Tablas nuevas: `units`, `goals` + `goal_resources`, `study_sessions`, `flashcards` (+`word_bank`/`word_enrichment`), `cp_problems`, `activity_log`, `dictionary_cache` · 🟡🔴
- [x] Índices en FKs y columnas de búsqueda/`due_date` · 🟢🔴
- [x] Soft delete (`deleted_at`) — implementado en flashcards; columnas añadidas a subjects/resources · 🟢🟡

**Por qué aquí:** casi todo lo valioso (progreso real, flashcards, CP) depende de este modelo. Hacerlo mal o por partes genera retrabajo.

📝 _Notas del bloque:_ Migración en `migrations_v2.sql` (ejecutar UNA vez tras `studyvault.sql`). Probada en MariaDB 10.4: 25 sentencias OK, 0 errores. Nota: `vocab_cards` del plan se implementó como tabla **`flashcards`** genérica (sirve vocab + CP).

---

### 🎴 Bloque 2 — Flashcards MÍNIMO + SM-2 *(corazón de retención, alto valor)*
Lo que pediste como piso, y la base de retención compartida con CP.
- [x] Pantalla de estudio de tarjetas (frontal/reverso, calificar, atajos de teclado) · 🟡🟠
- [x] Crear tarjeta a mano + **"Guardar palabra" desde el diccionario** · 🟢🟠
- [x] **Motor SM-2** + cola diaria por `due_date` · 🟡🟠

**Por qué antes que el resto de EN/CP:** alto impacto, esfuerzo medio, y el **SM-2 se reutiliza** en el repaso de problemas de CP (Bloque 4) y en los modos avanzados (Bloque 5).

📝 _Notas del bloque:_ Verificado end-to-end contra la BD: 3 tarjetas demo, repaso SM-2 correcto (interval/ease/due), cola diaria y soft-delete OK. Deck genérico `vocab`/`cp` deja listo el Bloque 4.

---

### 📈 Bloque 3 — Progreso real *(resuelve tus preguntas centrales)*
- [x] **Unidades dentro de recurso** (capítulos/páginas, generar N, % de progreso) → resuelve el "libro enorme" · 🟡🟠
- [x] **Metas/planes** con recursos ponderados (% ponderado + pace/¿a tiempo?) → resuelve "varios libros/videos" · 🟡🟠
- [x] **Temporizador Pomodoro** + registro de tiempo (sesiones, racha) · 🟡🟠
- [x] **Dashboard de progreso real** (tiempo hoy/semana, racha, metas con pace, heatmap en Temporizador) · 🔴🟠

**Por qué aquí:** es el núcleo del valor que pediste; depende del modelo (B1) y alimenta hábitos (B6).

📝 _Notas del bloque:_ Unidades implementadas con TDD (función `Unit::progressPct` testeada) y verificadas en BD (5 unidades → 2 completadas = 40%, `total_units` sincronizado). Falta metas, temporizador y dashboard de ritmo.

---

### 🏆 Bloque 4 — CP automático (Codeforces) *(progreso medible sin esfuerzo manual)*
- [x] **API Codeforces**: rating + resueltos importados automáticamente · 🔴🟠
- [x] Tracking de problemas (todo/attempted/solved/upsolved) + editorial notes · 🟡🟠
- [x] Repaso espaciado de problemas difíciles (reusa SM-2 de B2) · 🟢🟡
- [x] Análisis de debilidades (resueltos por tag) + sugerir siguiente problema (i+1, catálogo cacheado) · 🟡🟡
- [x] Biblioteca de plantillas/snippets · 🟡🟡
- [x] Calendario de concursos (contest.list) · 🟡⚪

**Por qué aquí:** muy alto valor para tu enfoque CP, casi independiente, y ya tienes SM-2 listo.

📝 _Notas del bloque:_ Núcleo hecho (sync Codeforces + tracking + análisis por tag). Requiere `migrations_v3.sql`. Ver bitácora §G. Pendiente: repaso SM-2 de problemas y sugerir-siguiente (i+1).

---

### 🗣️ Bloque 5 — Inglés avanzado (por nivel + usos) *(sobre las flashcards)*
- [ ] **Banco CEFR** (importar dataset) → estudiar por nivel · 🟡🟠
- [ ] Enriquecimiento Datamuse + Tatoeba + audio (cacheado) · 🟡🟡
- [ ] Modos de tarjeta (reconocer/cloze/producción/escucha) · 🟡🟡
- [ ] Cobertura por nivel + stats de retención · 🟢🟡
- [ ] Import/export Anki, diario de escritura, tracking de listening · 🟡🟡

**Por qué después de B2:** todo esto se construye sobre las flashcards y el SM-2 ya existentes.

📝 _Notas del bloque:_ _(pendiente)_

---

### 🔥 Bloque 6 — Hábitos y consistencia *(motivación)*
- [ ] Rachas (streaks) + meta diaria · 🟡🟡
- [ ] Calendario / heatmap · 🟡🟡
- [ ] Reporte semanal · 🟢🟡
- [ ] Recordatorios (web push / email) · 🔴🟡

**Por qué aquí:** necesita datos de tiempo/actividad (B3) y de estudio (B2/B4) para ser real.

📝 _Notas del bloque:_ _(pendiente)_

---

### 🤝 Bloque 7 — Compartir / plantillas *(de personal a producto)*
- [ ] Plantillas de planes públicas + clonar · 🔴🟡
- [ ] Onboarding con plantillas iniciales · 🟡🟡
- [ ] Contenido: roadmap Codeforces, plan Inglés A2→B1 · 🟢🟡

**Por qué aquí:** depende de que existan metas/planes (B3) bien hechos.

📝 _Notas del bloque:_ _(pendiente)_

---

### 🚀 Bloque 8 — Escala y robustez *(solo si creces / endureces)*
- [ ] Endurecimiento seguridad: headers, uploads fuera del root, rate limit, recuperación de contraseña
- [ ] Rendimiento: paginación, búsqueda full-text
- [ ] Plataforma: REST API, PWA/móvil, Docker, i18n
- [ ] Crecimiento: perfiles públicos, gamificación, social/ranking, modo virtual contest

**Por qué al final:** alto esfuerzo y/o solo valen la pena cuando el núcleo ya es valioso y hay usuarios.

📝 _Notas del bloque:_ _(pendiente)_

> **Continuo (en paralelo a todo):** tests de lo nuevo, validación estricta de entradas, y registrar en `activity_log`.

---

## E. Mapa de dependencias (qué desbloquea qué)

```
Bloque 0 (seguridad/quick wins) ── independiente ──┐
                                                    │
Bloque 1 (MODELO DE DATOS) ─────────────────────────┼──> base de casi todo
   │                                                │
   ├──> Bloque 2 (Flashcards + SM-2)                │
   │        │                                       │
   │        ├──> Bloque 4 (CP: repaso usa SM-2)     │
   │        └──> Bloque 5 (Inglés avanzado)         │
   │                                                │
   ├──> Bloque 3 (Progreso real) ──> Bloque 6 (Hábitos)
   │                              └─> Bloque 7 (Compartir/plantillas)
   │
   └──> Bloque 4 (CP: tracking, Codeforces)

Bloque 8 (escala) ── depende de que el núcleo (1–5) esté sólido
```

**Reglas de oro:**
- **B1 antes que todo lo demás** (excepto B0, que es independiente).
- **B2 (SM-2) antes de** los modos de tarjeta (B5) y el repaso de problemas (B4).
- **B3 antes de** hábitos (B6) y compartir (B7).
- **B8 al final.**

---

## F. Notas de flexibilidad

- **Si tu prioridad es CP sobre inglés:** tras B0→B1→B2, salta a **B4** antes que B5. Ambos son independientes entre sí.
- **Si tienes poco tiempo y quieres máximo valor personal ya:** B0 (1,2) + B1 + B2 + B3(unidades) cubren tus preguntas centrales (libro enorme, multi-recurso, retención).
- **Si la meta es compartir/portafolio:** prioriza B0 + B1 + B3 + B7 + PWA (de B8) para que se vea y se use bien en móvil.
- **Paralelizable:** B0 puede hacerse en cualquier momento; los "🟢 relleno rápido" pueden intercalarse entre bloques grandes para no perder momentum.
- **Reevaluar tras cada bloque:** al terminar un bloque, revisa si el siguiente sigue siendo el de mayor valor según cómo lo estés usando.

---

## G. Bitácora de implementación

> **Regla:** cada vez que se implemente una tarea, se agrega aquí una entrada **antes de darla por cerrada**. Esto es lo que permite depurar errores después ("¿qué cambié?, ¿por qué?, ¿cómo lo revierto?"). Copiar la plantilla y rellenarla. Entradas en orden cronológico inverso (lo más reciente arriba).

### Plantilla de entrada (copiar y rellenar)

```markdown
### [AAAA-MM-DD] Bloque N — <nombre de la tarea>
- **Estado:** ✅ hecho / ⚠️ parcial / 🐛 con bug conocido
- **Qué se hizo:** <resumen en 1–3 líneas>
- **Archivos creados/modificados:**
  - `ruta/archivo.php` — <qué cambió>
- **Base de datos:** <tablas/columnas/índices nuevos o alterados; o "sin cambios">
- **Decisiones clave / por qué:** <elección de diseño, librería, enfoque>
- **Cómo probar (verificación):** <pasos concretos para confirmar que funciona>
- **Problemas conocidos / gotchas:** <cosas frágiles, supuestos, deuda dejada>
- **Cómo revertir:** <archivos a restaurar / migración inversa / commit a deshacer>
- **Relacionado:** <otras tareas/bloques afectados>
```

### Ejemplo (referencia de cómo llenarla)

```markdown
### [2026-05-25] Bloque 0 — CSRF en todos los POST
- **Estado:** ✅ hecho
- **Qué se hizo:** token CSRF en sesión, helper para inyectarlo en forms y validarlo en cada POST.
- **Archivos creados/modificados:**
  - `config/csrf.php` — funciones csrf_token() y csrf_verify()
  - `index.php` — valida token antes de despachar POST
  - `views/**/form.php` — campo oculto con el token
- **Base de datos:** sin cambios.
- **Decisiones clave / por qué:** token por sesión (simple) en vez de por formulario; suficiente para el alcance.
- **Cómo probar:** enviar un POST sin token → debe rechazar (403); con token válido → pasa.
- **Problemas conocidos:** las llamadas fetch() de AJAX deben incluir el token en el body.
- **Cómo revertir:** quitar la llamada a csrf_verify() en index.php y borrar config/csrf.php.
- **Relacionado:** afecta materias, recursos y resource_status (AJAX).
```

---

### Entradas reales

### [2026-05-25] Bloque 4 (completo) — Repaso CP, sugerir-siguiente, concursos y plantillas
- **Estado:** ✅ completado y verificado (TDD reusado + integración BD + HTTP). **Bloque 4 cerrado.**
- **Qué se hizo:**
  - **Repaso espaciado de problemas**: reutiliza el motor `Flashcard::sm2()` (puro) sobre `cp_problems`. Botón "Repasar" por problema resuelto + página `?page=cp&action=review` con cola y calificación (Otra vez/Difícil/Bien/Fácil).
  - **Sugerir-siguiente (i+1)**: descarga el catálogo de Codeforces (`problemset.problems`) a `cf_problemset_cache`, filtra no resueltos en `rating+100..+300` → sugiere 6.
  - **Calendario de concursos**: `contest.list` → próximos 5.
  - **Plantillas/snippets**: CRUD en `?page=cp&action=templates` (copiar al portapapeles).
- **Archivos:**
  - `models/CpProblem.php` (+repaso SM-2, solvedKeys, cacheProblemset, suggestNext); `models/CpTemplate.php` (nuevo)
  - `controllers/CpController.php` (review/reviewSubmit/scheduleReview/syncProblemset/templates/storeTemplate/destroyTemplate/upcomingContests)
  - `views/cp/review.php`, `views/cp/templates.php` (nuevos); `views/cp/index.php` (panel de práctica + botón repaso)
  - `index.php` (rutas cp ampliadas); `migrations_v4.sql` (SM-2 en cp_problems, cp_templates, cf_problemset_cache)
- **Base de datos:** **ejecutar `migrations_v4.sql`** (ya aplicada en local). 
- **Reutilización:** el repaso de problemas NO duplica SM-2 — llama a `Flashcard::sm2()` (ya testeado).
- **Cómo probar:** CP → marcar un problema resuelto con "Repasar" → ir a Repasar → calificar. "Sincronizar catálogo" → ver sugerencias. Plantillas → crear/copiar.
- **Verificación:** integración BD (repaso: interval 1d, ef 2.6, cola 1→0; plantillas CRUD); HTTP (rutas 302/403, sin fatales); 3 suites de tests pasan.
- **Problemas conocidos:** `problemset.problems` es grande (~9000); la sincronización del catálogo puede tardar unos segundos.
- **Cómo revertir:** borrar `CpTemplate.php`, las vistas review/templates, los métodos nuevos de CpProblem/CpController, las rutas nuevas; `DROP TABLE cp_templates, cf_problemset_cache` y quitar columnas SM-2 de cp_problems.

### [2026-05-25] Bloque 3 (resto) — Metas, Temporizador y Dashboard de progreso real
- **Estado:** ✅ completado y verificado (TDD + integración BD + HTTP). **Bloque 3 cerrado al 100%.**
- **Qué se hizo:**
  - **Metas/planes**: agrupan recursos con **peso**; progreso = promedio ponderado del % de cada recurso (unidades si las hay, si no por estado). **Pace**: compara avance real vs esperado según la fecha objetivo (En camino / Atrasado / vencida) y días restantes. CRUD + agregar/quitar recursos.
  - **Temporizador Pomodoro 25/5**: cuenta regresiva en JS que al completar **registra la sesión** (minutos reales) vía AJAX. Registro manual. Stats de hoy/semana, **racha** de días, **heatmap** de 84 días y sesiones recientes.
  - **Dashboard de progreso real**: tarjetas de tiempo (hoy/semana/racha) + resumen de metas con su pace.
- **Archivos creados/modificados:**
  - `models/Goal.php` — `weightedProgress()` y `pace()` (puros, testeados), CRUD, `resourcesWithProgress`, `listWithProgress`, attach/detach
  - `models/StudySession.php` — `create`, `todayMinutes`, `rangeMinutes`, `minutesByDay`, `streak`, `recent`
  - `controllers/GoalController.php`, `controllers/TimerController.php`
  - `views/goals/index.php`, `views/goals/show.php`, `views/timer/index.php`
  - `index.php` (rutas goals/timer + datos del dashboard), `views/dashboard/index.php` (sección progreso real), `views/partials/header.php` (nav Metas + Temporizador)
  - `tests/goal_test.php` — pruebas de `weightedProgress` y `pace`
- **Base de datos:** usa `goals`, `goal_resources`, `study_sessions` (de la migración v2). Sin cambios de esquema nuevos.
- **TDD:** `goal_test` escrito primero (RED: "Class Goal not found"), luego implementado (GREEN: 10/10). Pace y progreso ponderado cubiertos.
- **Cómo probar:** Metas → crear "Inglés B2 para diciembre" → Ver recursos → agregar recursos con peso → ver % y pace. Temporizador → Iniciar Pomodoro o registrar manual → ver minutos/racha/heatmap. Dashboard muestra ambos.
- **Verificación:** integración BD (meta 50% "En camino", sesión 25 min, racha 1); HTTP (rutas 302 sin sesión, 403 sin CSRF, sin fatales); 3 suites de tests pasan.
- **Problemas conocidos:** las vistas recargan tras cambios (recalcular %); el heatmap es de minutos diarios simples (no por intensidad relativa).
- **Cómo revertir:** borrar `models/Goal.php`, `models/StudySession.php`, los 2 controladores, `views/goals/`, `views/timer/`, rutas goals/timer en index.php, la sección del dashboard y los items de nav; revertir el closure del dashboard en index.php.
- **Relacionado:** depende de B1 y de las unidades (B3 previo); alimenta B6 (hábitos: racha/heatmap ya sientan la base).

### [2026-05-25] Bloque 3 (parcial) — Unidades dentro de recurso ("libro enorme")
- **Estado:** ✅ hecho y verificado (TDD + integración BD + HTTP). Resto del Bloque 3 pendiente.
- **Qué se hizo:** progreso granular de un recurso vía unidades (capítulos/lecciones). Agregar una a una, **generar N de golpe** (libro grande), marcar pending/in_progress/completed, y % calculado. Acceso desde el botón "Unidades" en cada recurso. `resources.total_units` se mantiene sincronizado.
- **Archivos creados/modificados:**
  - `models/Unit.php` — `progressPct()` (puro, testeado), `progress()`, `create/bulkCreate/setStatus/delete`, `syncCount()`, checks de propiedad
  - `controllers/UnitController.php` — index/store/bulk/setStatus/destroy
  - `views/resources/units.php` — gestión de unidades + barra de progreso (AJAX)
  - `index.php` — rutas `?page=units`; `views/resources/row.php` y `views/resources/index.php` — botón "Unidades"
  - `tests/units_test.php`, `tests/assert.php` — pruebas de `progressPct`
- **Base de datos:** usa tabla `units` (de v2). Sin cambios de esquema nuevos.
- **TDD:** test de `progressPct` escrito primero (RED: "Class Unit not found"), luego implementado (GREEN: 6/6).
- **Cómo probar:** Recursos → botón "Unidades" → Generar N → marcar completadas → ver %. Integración verificada: 5 unidades, 2 completadas = 40%.
- **Problemas conocidos:** la vista recarga tras cada cambio (recalcula el %); el % aún no se muestra en el listado de recursos (solo en la página de unidades).
- **Cómo revertir:** borrar `models/Unit.php`, `controllers/UnitController.php`, `views/resources/units.php`, rutas `units` en index.php, botones "Unidades".
- **Relacionado:** depende de B1; alimenta el futuro dashboard de progreso real.

### [2026-05-25] Bloque 2 (mejora) — SM-2 extraído a función pura + tests
- **Estado:** ✅ hecho (TDD, 11/11).
- **Qué se hizo:** se extrajo el algoritmo SM-2 a `Flashcard::sm2()` (estático, puro, sin BD) y `review()` ahora lo usa. Permite testearlo sin BD.
- **Archivos:** `models/Flashcard.php` (refactor); `tests/sm2_test.php` (nuevo).
- **TDD:** test escrito primero (RED: "undefined method sm2"), luego implementado (GREEN: 11/11 — primer/segundo/tercer acierto, fallo reinicia, piso ef 1.3, estados mature/mastered).
- **Cómo probar:** `php tests/sm2_test.php`.
- **Cómo revertir:** volver a la lógica inline previa en `review()` y borrar el test.

### [2026-05-25] Bloque 4 — Verificación en vivo (Codeforces + migración v3)
- **Estado:** ✅ núcleo verificado (MySQL/MariaDB arriba).
- **Qué se hizo:** aplicada `migrations_v3.sql` (columnas cf_* en users). Probada la API de Codeforces (`user.info` de `tourist` → rating 3428) y el modelo (`setCfHandle`, `upsertSolved` con dedup insert→update, `getStats`). Limpieza posterior.
- **Cómo probar:** página Competitiva → handle (ej. tourist) → Sincronizar.
- **Problemas conocidos:** `user.status?count=3000` puede tardar con cuentas muy prolíficas (tourist tiene miles).

### [2026-05-24] Bloque 4 (parcial) — Programación competitiva + API Codeforces
- **Estado:** 🟡 parcial — núcleo hecho; lint OK. NO probado en vivo (MySQL estaba caído al verificar).
- **Qué se hizo:** módulo CP con sincronización automática desde la **API pública de Codeforces** (sin key): importa problemas resueltos (verdict OK, dedupe por contestId-index), guarda rating actual, y muestra análisis "resueltos por tag". Tracking manual con estados todo/attempted/solved/upsolved + nota editorial.
- **Archivos creados/modificados:**
  - `models/CpProblem.php` — CRUD, `upsertSolved()`, `getStats()`, `tagBreakdown()`, soft-delete
  - `controllers/CpController.php` — index/saveHandle/sync/store/setStatus/destroy + helper `cfGet()`
  - `models/User.php` — `getCfInfo()`, `setCfHandle()`, `setCfSync()`
  - `views/cp/index.php` — panel handle+sync, stats, tabla, debilidades por tag, modal alta
  - `index.php` — rutas `?page=cp`; `views/partials/header.php` — nav "Competitiva"
  - `migrations_v3.sql` (nuevo) — añade `cf_handle`, `cf_rating`, `cf_synced_at` a `users`
- **Base de datos:** usa `cp_problems` (de v2) + 3 columnas nuevas en `users` (v3). **PENDIENTE ejecutar `migrations_v3.sql`** (no se aplicó: MySQL caído).
- **Decisiones clave:** sync server-side con `file_get_contents` a `user.info` + `user.status?count=3000`; reusa el deck `cp` de flashcards para futuro repaso. URL `problemset/problem/{contestId}/{index}`.
- **Cómo probar:** levantar MySQL → `migrations_v3.sql` → CP → poner handle (ej. `tourist`) → Sincronizar → ver rating + problemas + tags.
- **Problemas conocidos:** sin red a Codeforces el sync devuelve error controlado. `user.status?count=3000` puede ser lento para usuarios con miles de envíos.
- **Cómo revertir:** borrar `models/CpProblem.php`, `controllers/CpController.php`, `views/cp/`, métodos cf_* en User, rutas cp en index.php, nav; `ALTER TABLE users DROP COLUMN cf_handle, ...`.
- **Relacionado:** reusa SM-2 de B2 (repaso de problemas, pendiente); depende de B1.

### [2026-05-24] Bloque 2 — Flashcards mínimo + motor SM-2
- **Estado:** ✅ hecho (verificado contra BD y HTTP)
- **Qué se hizo:** sistema de flashcards con repetición espaciada SM-2, pantalla de estudio con volteo y calificación (Otra vez/Difícil/Bien/Fácil + atajos 1-4 y espacio), creación manual y "Guardar como tarjeta" desde el diccionario. Mazo genérico `vocab`/`cp`.
- **Archivos creados/modificados:**
  - `models/Flashcard.php` — modelo + algoritmo SM-2 (`review()`), `getDue()`, `getStats()`, soft-delete
  - `controllers/FlashcardController.php` — index/study/store/storeFromDictionary/review/destroy
  - `views/flashcards/index.php` — lista + stats + modal crear
  - `views/flashcards/study.php` — sesión de estudio (JS, sin recargar)
  - `assets/js/app.js` — `svSaveWord()` y botón "Guardar tarjeta" en el diccionario
  - `index.php` — rutas `?page=flashcards`; `views/partials/header.php` — nav Flashcards
- **Base de datos:** usa tabla `flashcards` (creada en migración v2). 3 tarjetas demo sembradas.
- **Decisiones clave:** tabla `flashcards` genérica (no `vocab_cards`) para reutilizar el mismo motor SM-2 en CP (Bloque 4). Calidad SM-2 mapeada a 4 botones (2/3/4/5).
- **Cómo probar:** login → Flashcards → Estudiar; o `php -r` usando `Flashcard::review()`. Verificado: "although" tras calidad 5 → interval 1d, reps 1, ease 2.6, due +1; cola 3→2.
- **Problemas conocidos:** la "due_date DEFAULT (CURRENT_DATE)" requiere MariaDB 10.2+/MySQL 8.0.13+ (OK en XAMPP actual).
- **Cómo revertir:** borrar `models/Flashcard.php`, `controllers/FlashcardController.php`, `views/flashcards/`, las rutas flashcards en `index.php`, el nav y `svSaveWord` en app.js. La tabla puede quedar (no estorba).
- **Relacionado:** Bloque 4 (repaso CP), Bloque 5 (modos de tarjeta), depende de Bloque 1.

### [2026-05-24] Bloque 1 — Fundación de datos (esquema v2)
- **Estado:** ✅ hecho (25 sentencias OK / 0 errores en MariaDB 10.4)
- **Qué se hizo:** esquema ampliado para soportar progreso real, flashcards, CP, bitácora y caché.
- **Archivos creados/modificados:** `migrations_v2.sql` (nuevo).
- **Base de datos:** tablas nuevas `goals`, `goal_resources`, `units`, `study_sessions`, `flashcards`, `word_bank`, `word_enrichment`, `cp_problems`, `activity_log`, `dictionary_cache`; columnas `deleted_at`/progreso granular en `subjects`/`resources`; índices.
- **Decisiones clave:** `flashcards` genérica (ver entrada B2). `word_bank`/`word_enrichment` quedan vacías para el Bloque 5.
- **Cómo probar:** ejecutar `migrations_v2.sql` una vez; `SHOW TABLES` debe listar 13 tablas.
- **Problemas conocidos:** `ALTER TABLE ... ADD COLUMN` fallará si se re-ejecuta (las columnas ya existen). Ejecutar una sola vez.
- **Cómo revertir:** `DROP TABLE` de las nuevas + quitar columnas `deleted_at`, `total_units`, `unit_type`, `is_habit`, `estimated_minutes`.
- **Relacionado:** habilita Bloques 2, 3, 4.

### [2026-05-24] Bloque 0 — Cimientos y quick wins (seguridad + bootstrap)
- **Estado:** ✅ hecho (verificado por HTTP)
- **Qué se hizo:** CSRF global en POST, sesión segura (`session_regenerate_id` al login + cookies HttpOnly/SameSite/Secure), credenciales en `.env`, bootstrap centralizado (errores + log + autoload + helpers), caché del diccionario, modo oscuro.
- **Archivos creados/modificados:**
  - `config/init.php` (nuevo) — env, sesión, errores, autoload, `csrf_*()`, `json_response()`, `requireLogin/Admin`, `log_activity()`
  - `.env` + `.env.example` (nuevos); `config/database.php` — lee de `env()`
  - `index.php` — usa init, `csrf_verify()` en todo POST; `controllers/AuthController.php` — `session_regenerate_id(true)`
  - `api/dictionary.php` — caché en `dictionary_cache` + reescrito sobre init; `api/search.php`, `api/resource_status.php` — sobre init (+CSRF en status)
  - vistas: `csrf_field()` en login/registro/recursos; token en AJAX (subjects, resources); `header.php` toggle oscuro; `footer.php` `CSRF_TOKEN`; `app.js`/`app.css` modo oscuro
- **Base de datos:** tabla `dictionary_cache` (en migración v2).
- **Decisiones clave:** token CSRF por sesión (simple, suficiente); AJAX manda el token en el body (`CSRF_TOKEN`) o header `X-CSRF-Token`. `APP_DEBUG` controla si se muestran errores.
- **Cómo probar:** login GET=200 con campo csrf; dashboard sin sesión=302; POST sin token=403; sin fatales en `logs/error.log`. (Todo verificado.)
- **Problemas conocidos:** todo POST exige token — cualquier form/AJAX nuevo debe incluir `csrf_field()` o `CSRF_TOKEN`, si no dará 403.
- **Cómo revertir:** quitar `csrf_verify()` de `index.php`/`resource_status.php`; volver a credenciales fijas en `database.php`; quitar toggle/CSS de modo oscuro.
- **Relacionado:** base de todos los bloques siguientes.

---

## H. Resumen en una línea

> **Orden recomendado: 0 → 1 → 2 → 3 → 4 → 5 → 6 → 7 → 8**, donde 0 es deuda/quick wins, 1 es la fundación de datos, 2–5 son el núcleo de valor (retención + progreso real + tus dos enfoques), y 6–8 son motivación, comunidad y escala.
