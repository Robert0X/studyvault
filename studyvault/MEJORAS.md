# StudyVault — Análisis profundo y hoja de ruta de mejoras

> Documento de análisis. **No** es código ni cambios implementados: es el mapa de todo lo que se puede mejorar para que StudyVault pase de ser un "gestor de enlaces" a una **herramienta real de estudio** centrada en inglés y programación competitiva.
>
> Fecha: 2026-05-22 · Estado del proyecto: entrega académica funcional.

---

## 0. Cómo leer este documento

- **Secciones 1–3**: diagnóstico y la idea grande (el "salto conceptual").
- **Secciones 4–6**: el corazón del asunto — cómo estudia un humano, la ciencia del aprendizaje y **cómo medir progreso REAL** (incluido el problema del libro enorme).
- **Secciones 7–8**: inglés y programación competitiva a fondo (tu enfoque).
- **Secciones 9–12**: features concretas (repetición espaciada, tiempo/hábitos, dashboard, compartir).
- **Secciones 13–14**: técnico / seguridad / APIs.
- **Sección 15**: preguntas abiertas para que **tú** decidas.
- **Sección 16**: roadmap priorizado por impacto/esfuerzo.
- **Sección 17**: mejoras fuera del software (tus hábitos de estudio).

---

## 1. Diagnóstico honesto: ¿qué es StudyVault HOY?

Hoy la app es, en esencia, un **gestor de marcadores con estado**:

- Guardas recursos (link / PDF / nota / video) dentro de materias.
- Cada recurso tiene 3 estados: `pending → in_progress → completed`.
- Buscas, filtras, ves un dashboard con barras de "% completado".
- Un widget de diccionario te da la definición de una palabra en inglés.

Es correcto para la entrega y cumple la rúbrica. **Pero no te hace estudiar mejor.** Es un "tablero de cosas por leer", no un sistema de aprendizaje. La diferencia es enorme:

| Lo que hace hoy | Lo que necesita un estudiante real |
|---|---|
| Marcar un recurso como "completado" | Saber si **realmente lo recuerda** semanas después |
| Estado binario por recurso | Progreso **granular** (página 247 de 600, capítulo 12 de 30) |
| Lista de links sueltos | Un **plan/meta** que conecta varios libros, videos y prácticas |
| Buscar definición de una palabra | **Construir vocabulario** que de verdad puedas usar |
| "% completado" auto-reportado | Progreso **medible y objetivo** (problemas resueltos, rating, recall) |
| Guardar un PDF de 400 páginas | Dividirlo en unidades y **retomar donde lo dejaste** |

El resto del documento es básicamente: **cómo cerrar esa columna derecha.**

---

## 2. El salto conceptual: de "guardar" a "aprender"

La pregunta clave que debe responder la app no es *"¿qué recursos tengo?"* sino:

> **"¿Qué tan cerca estoy de mi meta, y qué debo hacer HOY para avanzar?"**

Eso implica tres ideas nuevas que hoy no existen:

1. **Metas / Planes de estudio** (capa por encima de las materias): "Llegar a B2 de inglés", "Alcanzar Specialist (1400) en Codeforces", con fecha objetivo y progreso real.
2. **Unidades dentro de un recurso** (capa por debajo de los recursos): un libro = capítulos; un curso = lecciones; un problem set = problemas. El progreso se calcula sumando unidades, no marcando el recurso entero.
3. **Retención y práctica activa**: repetición espaciada, autoevaluación, tiempo invertido. El aprendizaje no es leer, es **recordar y aplicar**.

Jerarquía propuesta:

```
META  (ej. "Codeforces Specialist antes de dic-2026")
 └── TEMA / MATERIA  (ej. "Programación Dinámica")
       └── RECURSO  (libro, curso, problem set, video)
             └── UNIDAD  (capítulo, lección, problema individual)
                   └── progreso real + tiempo + repaso
```

---

## 3. El flujo de estudio de un humano (modelo mental)

Para diseñar bien, hay que modelar **el ciclo real de aprender algo**. Un humano que estudia bien repite este bucle:

```
   ┌──────────────────────────────────────────────────┐
   │ 1. PLANEAR   → ¿qué meta? ¿qué recursos? ¿cuándo?  │
   │ 2. ESTUDIAR  → input: leer/ver/escuchar (con foco) │
   │ 3. PRACTICAR → output: resolver, escribir, hablar  │
   │ 4. EVALUAR   → ¿lo recuerdo? autoevaluación/test   │
   │ 5. REPASAR   → repetición espaciada de lo débil    │
   │ 6. MEDIR     → ¿avancé? ¿voy a tiempo? ajustar plan │
   └──────────────────────────────────────────────────┘
                  (y vuelve a empezar)
```

**Hoy StudyVault solo cubre el paso 1 a medias y el 2 de forma pasiva.** Los pasos 3, 4, 5 y 6 (los que de verdad producen aprendizaje) no existen. Ahí está el 80% del valor sin explotar.

Principios de diseño que se derivan de esto:
- La pantalla principal debe responder **"¿qué hago hoy?"**, no "aquí está tu lista".
- Debe haber un lugar para el **output** (problemas resueltos, frases escritas), no solo input.
- El sistema debe **recordarte repasar** lo que estás por olvidar.
- El progreso debe ser **honesto**: medido, no auto-declarado.

---

## 4. Ciencia del aprendizaje aplicada (cómo estudiar de la mejor forma)

Esto es lo que la evidencia (psicología cognitiva y adquisición de lenguas) dice que funciona. La app debería **empujarte hacia estas técnicas**, no solo almacenar archivos.

| Técnica | Qué es | Cómo la usaría StudyVault |
|---|---|---|
| **Repetición espaciada** (curva del olvido, Ebbinghaus / SM-2 de Anki) | Repasar justo antes de olvidar, a intervalos crecientes | Flashcards con algoritmo SM-2; cola diaria de repaso |
| **Recuerdo activo (active recall)** | Probarte a ti mismo > releer | Tarjetas, quizzes, "cloze" (rellenar huecos) |
| **Práctica intercalada (interleaving)** | Mezclar temas > bloques del mismo tema | En CP: no 50 problemas de DP seguidos; mezclar tags |
| **Práctica deliberada (i+1)** | Trabajar al borde de tu capacidad | Sugerir problemas ~100–200 pts arriba de tu rating |
| **Técnica Feynman** | Explicarlo simple = entenderlo | Campo de "nota/explicación" obligatorio al completar |
| **Pomodoro / time-boxing** | Bloques de foco de 25 min | Temporizador integrado que registra tiempo real |
| **Input comprensible (Krashen)** | Exponerte a lenguaje un poco arriba de tu nivel | Recomendar contenido por nivel CEFR (A2/B1/B2...) |
| **Consistencia > intensidad** | 30 min diarios > 5h el domingo | Rachas (streaks), recordatorios, calendario |
| **Codificación dual** | Texto + imagen/audio | Guardar audio de pronunciación, diagramas |

**Idea transversal**: la app podría tener un modo "coach" que, según tus datos, te diga: *"Llevas 3 días sin repasar inglés y tienes 18 palabras 'maduras' por caer. Te recomiendo 10 min de repaso ahora."*

---

## 5. El problema central: medir PROGRESO REAL

Tú lo preguntaste directamente: *"¿cómo veo avance real?"*. El estado actual (`pending/in_progress/completed`) es **progreso falso**: es una opinión tuya, no un dato. El progreso real tiene varias dimensiones:

1. **Granularidad** — % de un libro leído, lecciones hechas (no "todo/nada").
2. **Tiempo invertido** — horas reales registradas.
3. **Retención** — % de aciertos en repaso a lo largo del tiempo.
4. **Output / producción** — problemas resueltos, palabras dominadas, ensayos escritos.
5. **Validación externa** — rating de Codeforces, puntaje de un mock test.
6. **Consistencia** — días seguidos estudiando.
7. **Velocidad / ritmo** — ¿vas a tiempo para tu meta?

Un dashboard que vale la pena diría algo como:

> *"Estás al **47%** de tu meta B2. A tu ritmo actual (3 palabras nuevas/día, 2 problemas/día) la alcanzas el **3-mar-2026**. Vas **2 semanas atrasado** respecto a tu plan."*

Eso es progreso **real y motivante**. Eso es lo que falta.

### 5.1. El problema del LIBRO ENORME (tu pregunta clave)

Si subes *"English Grammar in Use"* (380 págs) o el *"Competitive Programmer's Handbook"* (300 págs), marcarlo `completed` vs `pending` **no sirve de nada**. La solución es modelar el recurso como un **árbol de unidades**:

```
Recurso: "Grammar in Use" (libro)
 ├── Unidad 1: Present Continuous     [✓ hecho · 12 min · nota]
 ├── Unidad 2: Present Simple          [✓ hecho · 9 min]
 ├── Unidad 3: ...                     [⟳ en progreso · pág 14/20]
 └── Unidad N
   → progreso del libro = unidades hechas / total = 23/145 = 16%
```

Beneficios inmediatos:
- **% real** del libro (no binario).
- **"Retomar"**: la app te dice "vas en la Unidad 3, página 14".
- **Notas/ejercicios por capítulo** (no una nota gigante por libro).
- **Tiempo por unidad** → estimas cuánto te falta.

Formas de definir las unidades (de menos a más esfuerzo):
- **Manual**: tú agregas capítulos/lecciones.
- **Por rango de páginas**: defines "total de páginas = 380" y registras "voy en la 247" → 65%.
- **Por checkpoints**: divides en N partes iguales.
- **Importación**: pegar un índice (tabla de contenidos) y que se generen unidades.

### 5.2. Varios libros, videos y prácticas a la vez (tu otra pregunta)

Si tu plan de inglés tiene: 2 libros + 1 curso en video + práctica diaria de vocabulario + escuchar podcasts… eso es una **META con varios recursos heterogéneos**. La capa de "Meta / Plan de estudio" lo resuelve:

```
META: "Inglés B2 para diciembre"   ──  progreso global 47%
 ├── 📕 Grammar in Use            16%   (peso 30%)
 ├── 🎥 Curso de listening        60%   (peso 25%)
 ├── 🗂️ Deck de vocabulario       80%   (peso 25%)  → 412/520 palabras
 └── 🎧 Podcasts (hábito diario)  racha 12 días (peso 20%)
```

- Cada recurso aporta al progreso de la meta con un **peso** que tú defines.
- La meta tiene **fecha objetivo** → la app calcula si vas a tiempo.
- Distingue **recursos finitos** (un libro, se termina) de **hábitos** (vocabulario diario, no se "terminan", se miden por racha/volumen).

---

## 6. Reestructuración del modelo de datos (propuesta)

Para soportar todo lo anterior, el esquema evoluciona así (no es obligatorio hacerlo todo; es el destino):

```sql
-- NUEVO: metas / planes de estudio
goals (
  id, user_id, title, description,
  target_date DATE, status,           -- active / paused / done
  created_at
)

-- NUEVO: une recursos a metas con un peso
goal_resources ( goal_id, resource_id, weight INT )  -- weight = importancia %

-- subjects: igual (sigue siendo la categoría/tema)

-- resources: añadir campos de progreso granular
resources (
  ... campos actuales ...,
  total_units INT DEFAULT 0,          -- ej. nº de capítulos o páginas
  unit_type ENUM('chapter','page','lesson','problem','custom'),
  is_habit BOOLEAN DEFAULT 0,         -- true = hábito recurrente, no finito
  estimated_minutes INT NULL
)

-- NUEVO: unidades dentro de un recurso (capítulos, lecciones, problemas)
units (
  id, resource_id, title, order_index,
  status ENUM('pending','in_progress','completed'),
  page_from INT, page_to INT,
  minutes_spent INT DEFAULT 0,
  note TEXT, completed_at
)

-- NUEVO: registro de sesiones de estudio (tiempo real)
study_sessions (
  id, user_id, resource_id NULL, unit_id NULL,
  started_at, ended_at, minutes INT,
  technique ENUM('read','practice','review','pomodoro')
)

-- NUEVO: vocabulario (inglés) — ver sección 7
vocab_cards ( id, user_id, word, phonetic, meaning,
  example_sentence, source_context, collocations,
  -- campos SRS:
  ease_factor FLOAT DEFAULT 2.5, interval_days INT DEFAULT 0,
  repetitions INT DEFAULT 0, due_date DATE, created_at )

-- NUEVO: problemas de CP — ver sección 8
cp_problems ( id, user_id, platform, problem_url, name,
  rating INT, tags VARCHAR(255),
  status ENUM('todo','attempted','solved','upsolved'),
  solved_at, time_spent INT, editorial_note TEXT, due_review DATE )

-- NUEVO: bitácora de actividad (era opcional en la rúbrica) 
activity_log ( id, user_id, action, entity, entity_id, created_at )

-- ÚTIL: índices para búsqueda y FKs
-- INDEX en resources(subject_id), resources(user_id),
--          units(resource_id), vocab_cards(user_id, due_date)
```

> Nota: para la **entrega académica** el esquema actual de 3 tablas está bien. Esto es la **evolución post-entrega** que pediste, pensada para que de verdad lo uses.

---

## 7. INGLÉS a fondo: ¿significados o usos? ¿es el mejor diccionario?

### 7.1. Tu pregunta directa: ¿significados o usos?

**Respuesta basada en evidencia: los USOS ganan, por mucho.** Saber la definición aislada de una palabra **no** significa que puedas usarla. La investigación en adquisición de segundas lenguas (Nation, Schmitt, Krashen) es clara:

- **El contexto manda.** Recuerdas y usas mejor una palabra si la aprendiste **dentro de una frase real**, no como entrada de diccionario.
- **Colocaciones** (qué palabras van juntas) son donde más fallan los estudiantes: se dice *"make a decision"*, no *"do a decision"*. Un diccionario de definiciones no te enseña esto.
- **Necesitas múltiples encuentros** (la regla práctica habla de ~7–12 exposiciones significativas) para "tener" una palabra. Una búsqueda única no basta.
- **Producción > reconocimiento.** Reconocer el significado es fácil; producir la palabra al hablar/escribir es lo difícil y lo valioso.
- **i+1 (Krashen)**: aprendes con input un poco arriba de tu nivel, no con listas de palabras sueltas.

**Conclusión**: el widget actual (buscar definición) es la forma **más débil** de aprender vocabulario. No está mal como apoyo puntual, pero el sistema debería girar hacia **construir tu propio banco de vocabulario en contexto + repaso espaciado**.

### 7.2. ¿Es Free Dictionary API el mejor?

Para lo que hace (definición + fonética + audio, **gratis y sin API key**) es de lo mejor que hay gratis. Pero es **pobre en USOS**. Comparativa:

| API | Gratis / sin key | Fuerte en | Débil en | Veredicto |
|---|---|---|---|---|
| **Free Dictionary** (dictionaryapi.dev) | ✅ Sí | Definiciones, fonética, audio | Colocaciones, ejemplos ricos | Buena base, ya la usas |
| **Datamuse** (api.datamuse.com) | ✅ Sí, sin key | **Colocaciones**, palabras relacionadas, sinónimos, "suena como" | Definiciones | **Ideal para "usos"** |
| **Tatoeba** (frases de ejemplo) | ✅ Sí | **Frases reales** de ejemplo (i+1) | API algo tosca | Excelente complemento |
| **Wordnik** | ⚠️ Requiere key gratuita | Ejemplos de uso reales, etimología | Setup | Más rico si registras key |
| **Reverso / Linguee** | ❌ Sin API pública limpia | Ejemplos bilingües buenísimos | No hay API oficial | El mejor contenido, difícil de integrar |

**Recomendación**: combinar **Free Dictionary** (definición/fonética/audio) + **Datamuse** (colocaciones y palabras que la acompañan) + **Tatoeba** (frases de ejemplo). Eso convierte "diccionario" en "centro de USO de la palabra".

### 7.3. Sistema de vocabulario propuesto (el gran salto en inglés)

En vez de buscar y olvidar:

1. **Guardar palabra → mazo personal** con: la palabra, fonética, **la frase donde la encontraste** (contexto), colocaciones (Datamuse) y un ejemplo (Tatoeba).
2. **Repetición espaciada (SM-2)**: cada día la app te da las palabras "que estás por olvidar".
3. **Modos de repaso**:
   - **Cloze** (rellenar hueco en una frase) — mucho mejor que reconocer.
   - **Producción**: escribe una frase usando la palabra.
   - **Escucha**: reproduce el audio, tú escribes la palabra.
4. **Métricas reales**: palabras "nuevas → aprendiendo → maduras → dominadas", curva de retención.
5. **Niveles CEFR**: etiquetar/recomendar contenido por A2/B1/B2 para input i+1.

Otras features de inglés:
- **Reproductor de audio** de pronunciación (ya tienes el `audio` de Free Dictionary; falta usarlo más).
- **Práctica de listening** con tracking (minutos escuchados).
- **Diario de escritura** con conteo de palabras y corrección (incluso autocorrección básica).
- **Importar/exportar mazos** (compatibilidad con Anki = CSV/`.apkg`).

### 7.4. Flashcards por nivel + banco de palabras online (análisis y diseño)

> Tu petición: estudiar con flashcards **por nivel** (no solo traducción/significado, sino **usos, casos de uso y ejemplos** por palabra), apoyándote en un **banco de datos online gratuito ya existente**. Mínimo: tener flashcards.

#### Veredicto: ¿conviene usar un banco de palabras online gratuito? → **Sí, pero híbrido.**

La clave está en separar dos cosas que ninguna API gratuita resuelve sola:

1. **El nivel (CEFR A1–C2)** → esto **NO** lo da ninguna API gratis sin key (Free Dictionary no te dice que *"although"* es B1). El nivel viene de **listas de vocabulario graduadas** ya existentes (datasets), que se **importan una sola vez** a una tabla local.
2. **Definición + usos + ejemplos + audio** → esto sí lo dan **APIs gratuitas**, pero conviene **enriquecer bajo demanda y cachear** (no llamar mil veces).

Por eso el enfoque ganador es **híbrido**:

> **Semilla local (lista CEFR gratuita) + enriquecimiento por API (cacheado).**

- ❌ **Solo API en vivo**: imposible filtrar "por nivel" y te expones a límites de uso / caídas.
- ❌ **Solo dataset estático**: tendrías niveles pero ejemplos pobres y sin audio.
- ✅ **Híbrido**: niveles fiables (dataset) + usos/ejemplos ricos (APIs) + rápido y semi-offline (caché). Es exactamente lo que pides.

#### Bancos de palabras CEFR gratuitos ya existentes (la "semilla")

| Dataset | Licencia | Qué aporta | Nota |
|---|---|---|---|
| **CEFR-J Wordlist** | Académica, descarga libre | ~7000 palabras mapeadas A1–B2 | **La mejor base para "por nivel"** |
| **Kelly list** | Proyecto UE, libre | Vocabulario graduado por nivel | Buena alternativa/complemento |
| **NGSL** (New General Service List) | Abierta (libre) | ~2800 palabras por **frecuencia** | Frecuencia ≈ proxy de nivel |
| **Oxford 3000/5000** | ⚠️ Propietaria (Oxford) | Niveles A1–C1 muy buenos | Copias CSV en GitHub: **cuidado con licencia si lo compartes** |

> Para una app **personal** puedes usar cualquiera. Para una app **pública/compartida**, prioriza **CEFR-J / Kelly / NGSL** (abiertas) y evita las listas propietarias.

#### APIs gratuitas para el contenido de cada tarjeta (el "enriquecimiento")

| Necesidad | Fuente | Key | Da |
|---|---|---|---|
| Definición, fonética, **audio** | Free Dictionary (ya integrada) | No | Significado + pronunciación |
| **Colocaciones / usos** | Datamuse | No | Con qué palabras va junta |
| **Frases de ejemplo reales** | Tatoeba | No | Casos de uso en contexto |
| Definición + ejemplos + **traducción ES** | Wiktionary/Wikidata REST | No | Rico y multilingüe |
| Traducción EN↔ES | MyMemory / LibreTranslate | No (tier libre) | Para el "significado" en español |

#### Anatomía de una buena flashcard (más que traducción)

En vez de *palabra → traducción*, cada tarjeta debería tener:

```
┌─────────────────────────────────────────────┐
│ FRONTAL:  "although"            [🔊 audio]    │
│           /ɔːlˈðəʊ/              nivel: B1     │
├─────────────────────────────────────────────┤
│ REVERSO:                                       │
│  • Significado: aunque / a pesar de            │
│  • Uso / casos: conjunción para contraste;     │
│    va al inicio de cláusula subordinada        │
│  • Colocaciones (Datamuse): although + sujeto  │
│  • Ejemplo (Tatoeba):                          │
│    "Although it was raining, we went out."     │
│  • Tu frase (producción): ____________         │
└─────────────────────────────────────────────┘
```

Esto convierte la tarjeta de "memorizar traducción" (débil) a "**aprender a usar**" (fuerte), justo lo que pediste.

#### Modos de estudio por nivel

- Elegir nivel objetivo (A2 / B1 / B2…) → el mazo se llena con palabras de **ese** nivel del banco.
- **Cola diaria SRS** (algoritmo SM-2, ya descrito en §9) mezclando palabras nuevas de tu nivel + repasos pendientes.
- Tipos de tarjeta: **reconocer** (ver palabra → recordar uso), **cloze** (rellenar hueco en la frase), **producción** (escribir frase), **escucha** (audio → escribir palabra).
- Métrica de avance: palabras *nuevas → aprendiendo → maduras → dominadas* por nivel, con % de cobertura del nivel ("dominas 312/700 palabras B1 = 45%").

#### Modelo de datos (se apoya en `vocab_cards` de §6)

```sql
-- Banco semilla (import único de un dataset CEFR gratuito)
word_bank (
  id, word, cefr_level ENUM('A1','A2','B1','B2','C1','C2'),
  part_of_speech, frequency_rank INT
)

-- Caché de enriquecimiento (evita re-llamar APIs)
word_enrichment (
  word_id, definition, phonetic, audio_url,
  collocations TEXT, example_en TEXT, translation_es TEXT,
  fetched_at
)

-- Tarjetas del usuario (con campos SRS de SM-2)
vocab_cards (
  id, user_id, word_id,
  user_sentence TEXT,            -- la frase que TÚ escribes (producción)
  source_context TEXT,           -- dónde la encontraste
  ease_factor FLOAT DEFAULT 2.5, interval_days INT, repetitions INT,
  due_date DATE, status ENUM('new','learning','mature','mastered'),
  created_at
)
```

#### Alcance: MÍNIMO viable vs completo

**MÍNIMO (lo que pediste como piso) — implementar primero:**
- Tabla `vocab_cards` simple + pantalla de estudio (frontal/reverso, "mostrar respuesta", calificar).
- Crear tarjetas: a mano **y/o** desde el widget de diccionario actual ("Guardar palabra" → tarjeta con definición + ejemplo de Free Dictionary).
- Repaso con SM-2 básico (cola diaria por `due_date`).
- Etiqueta de nivel manual por tarjeta (sin dataset todavía).

**COMPLETO (el objetivo real):**
- Importar **dataset CEFR** (CEFR-J/Kelly/NGSL) → estudiar **por nivel** automáticamente.
- Enriquecimiento automático (Datamuse + Tatoeba + audio) con caché.
- 4 modos de tarjeta (reconocer/cloze/producción/escucha).
- Cobertura por nivel + estadísticas de retención.
- Import/export Anki.

> **Recomendación de orden:** haz el **MÍNIMO** (flashcards + SM-2 + "guardar desde diccionario") primero — es alto impacto y bajo esfuerzo, y ya te sirve para estudiar. Luego añade el **banco CEFR** para el estudio "por nivel", y al final el enriquecimiento multi-API.

---

## 8. PROGRAMACIÓN COMPETITIVA a fondo

CP es muy distinto a inglés: aquí el progreso **sí se puede medir objetivamente y de forma automática**. Esta es la mayor oportunidad de "progreso real".

### 8.1. La joya: API pública de Codeforces (gratis, sin key)

Codeforces expone una API excelente y **sin API key**:

- `user.info` — datos y **rating actual**.
- `user.rating` — historial de rating (para graficar tu curva).
- `user.status` — **todas tus submissions** (qué problemas resolviste, cuándo, veredicto).
- `problemset.problems` — catálogo de problemas con **rating** y **tags**.

Base: `https://codeforces.com/api/`. Con esto, la app puede **automáticamente**:
- Mostrar tu **gráfica de rating** real.
- Contar **problemas resueltos por tag y por rating** (mapa de calor de fortalezas/debilidades).
- Detectar **qué temas y qué rangos de dificultad fallas más**.
- Sugerir el "siguiente problema" en tu zona i+1 (~tu rating + 100/200).

Eso es progreso real **sin que tú lo registres a mano**. Es el equivalente CP del "rating de inglés".

> Otras plataformas: **AtCoder Problems API** (kenkoooo.com, comunidad) y **LeetCode GraphQL** (no oficial, frágil). Empieza por Codeforces.

### 8.2. Tracking de problemas

- Estados ricos: `todo / attempted / solved / upsolved` (upsolve = lo resolviste después del concurso; clave en CP).
- **Editorial note**: ¿cuál fue la idea/insight clave? (técnica Feynman aplicada).
- **Repaso espaciado de problemas difíciles**: los problemas que te costaron deberían reaparecer para re-resolver. La gente olvida técnicas igual que palabras.
- **Etiquetas por tema** (DP, grafos, greedy, mates, estructuras) → análisis de debilidades.

### 8.3. Otras features CP

- **Biblioteca de plantillas/snippets** (tu template de C++, estructuras como DSU, segment tree, etc.) con resaltado de sintaxis.
- **Calendario de concursos** (Codeforces/AtCoder tienen endpoints o se puede usar clist.by).
- **Modo "virtual contest"** con cronómetro.
- **Roadmap por rating**: "para pasar de Pupil a Specialist domina: binary search, two pointers, DP básico…" como **plantilla de meta** (ver sección 11).

---

## 9. Repetición espaciada (el motor de retención) — transversal

Tanto inglés (palabras) como CP (problemas/algoritmos) se benefician del **mismo motor**: el algoritmo **SM-2** (el de Anki). Es simple de implementar:

- Cada tarjeta tiene: `ease_factor` (2.5 inicial), `interval_days`, `repetitions`, `due_date`.
- Al repasar, te calificas (Otra vez / Difícil / Bien / Fácil) y el intervalo crece o se reinicia.
- La app te da **una cola diaria**: "hoy toca repasar 14 palabras y 3 problemas".

Esto convierte el estudio de "leí esto una vez" a "lo recordaré para siempre". Es, probablemente, **la feature de mayor impacto** de todo el documento.

---

## 10. Tiempo, hábitos y consistencia

- **Temporizador Pomodoro** integrado que **registra tiempo real** por recurso/unidad (alimenta el progreso real).
- **Rachas (streaks)**: días seguidos estudiando — el motor de hábito estilo Duolingo.
- **Meta diaria**: "30 min" o "10 tarjetas + 2 problemas". Barra que se llena cada día.
- **Calendario / heatmap** tipo GitHub: ver tu consistencia de un vistazo.
- **Recordatorios**: notificaciones (web push / email) "tienes 14 repasos pendientes".
- **Reporte semanal**: "esta semana estudiaste 6h 20min, +40 palabras, +12 problemas".

---

## 11. Plantillas y planes de estudio (clave para compartir)

Una **meta** debería poder guardarse como **plantilla pública** y que otros la **clonen**:

- *"Roadmap Codeforces Pupil → Specialist"* (lista curada de temas, libros, problemas).
- *"Inglés A2 → B1 en 3 meses"* (libros, mazos, podcasts, orden sugerido).
- Al clonar, el usuario obtiene la estructura y va llenando su progreso.

Esto es lo que hace que la app sea **útil para más personas**: no solo guardas TUS cosas, sino que sigues rutas probadas y compartes las tuyas.

---

## 12. Para compartir y escalar a más usuarios

- **Onboarding con plantillas**: el estado vacío hoy intimida. Ofrecer "Empieza con un plan de inglés / de CP".
- **Perfiles públicos**: muestra tu racha, rating, palabras dominadas (motivación social).
- **Gamificación**: XP, niveles, logros/badges, rachas (modelo Duolingo, con cuidado de no volverlo adictivo-vacío).
- **Accountability social**: amigos, grupos de estudio, mini-leaderboards.
- **PWA / mobile-first**: el estudio (sobre todo flashcards) ocurre en el teléfono. Hacerla instalable y usable offline.
- **Importar/Exportar**: mazos Anki (CSV), problemas desde Codeforces, etc.
- **Internacionalización (i18n)**: hoy está en español; permitir inglés ampliaría el público.
- **Modo oscuro** (estaba en la rúbrica como opcional; buen quick win).

---

## 13. Mejoras técnicas y de arquitectura

- **Paginación / carga incremental** de recursos (hoy se cargan todos de golpe; con cientos será lento).
- **Caché del diccionario**: guardar respuestas de palabras ya buscadas (tabla `dictionary_cache`) → menos llamadas, más rápido, funciona offline parcialmente.
- **Búsqueda full-text** en notas (índice `FULLTEXT`).
- **Soft delete / papelera**: hoy borrar es definitivo (riesgo de perder trabajo).
- **API REST propia** bien estructurada (prepara el terreno para la app móvil y cumple "publicar servicios web" a nivel avanzado).
- **Migrar a un router más limpio** o framework ligero (Slim) si crece; por ahora el `match()` está bien.
- **Tests** (al menos de los modelos y validaciones).
- **Configuración por entorno** (`.env`) en vez de credenciales en `database.php` (hoy están hardcodeadas).
- **Docker** para despliegue reproducible (nivel avanzado de la rúbrica).
- **Manejo de errores centralizado** y logging.

---

## 14. Seguridad (hay deuda real aquí)

> Ojo: esto importa incluso para la entrega, porque la rúbrica pesa "Seguridad y sesiones" un 10%.

- ⚠️ **CSRF**: el plan lo mencionaba pero **no quedó implementado**. Todos los formularios y endpoints POST (crear/editar/borrar materias y recursos, cambiar estado) son vulnerables a CSRF. **Añadir token CSRF en sesión** y validarlo en cada POST.
- **Headers de seguridad**: `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`.
- **Subida de archivos**: hoy se validan MIME y tamaño (bien), pero los archivos quedan **dentro del webroot** (`assets/uploads/`). Mejor: carpeta fuera del root y servirlos vía script con control de acceso (evita que cualquiera adivine la URL de un PDF ajeno).
- **Autorización por dueño**: verificar siempre que el recurso pertenece al usuario (ya lo haces en queries con `user_id`; mantenerlo en TODO endpoint nuevo).
- **Rate limiting** en `dictionary.php` (proxy a API externa) para no ser abusado.
- **Regenerar ID de sesión** al hacer login (`session_regenerate_id(true)`) — previene fixation.
- **Cookies seguras**: `HttpOnly`, `SameSite=Lax`, `Secure` en producción.
- **Recuperación de contraseña** + verificación de email (estaba como opcional).
- **Validación de entrada más estricta** en todos los endpoints (longitudes, tipos, listas blancas — ya hay algo, reforzar).

---

## 15. APIs públicas recomendadas (resumen)

| Uso | API | Key | Notas |
|---|---|---|---|
| Definiciones EN | Free Dictionary (dictionaryapi.dev) | No | Ya integrada |
| Colocaciones / usos | Datamuse (api.datamuse.com) | No | **Recomendada para "usos"** |
| Frases de ejemplo | Tatoeba | No | i+1, frases reales |
| Rating + problemas CP | **Codeforces API** | No | **La joya: progreso automático** |
| Problemas AtCoder | AtCoder Problems (kenkoooo) | No | Comunidad |
| Problemas LeetCode | GraphQL no oficial | No | Frágil, úsalo con cuidado |
| Concursos | clist.by | Key | Calendario unificado |
| Pronunciación extra | Forvo | Key | Voces humanas reales |

---

## 16. Preguntas abiertas para que TÚ decidas

Antes de implementar, conviene que respondas estas (definen el rumbo):

1. **¿App personal o producto para muchos?** Si es personal, prioriza CP+inglés+SRS. Si es producto, prioriza onboarding, plantillas y PWA.
2. **¿Cuánto progreso quieres automático vs manual?** (Codeforces API = automático; libros = algo manual).
3. **Vocabulario: ¿reconocer o producir?** ¿Te basta entender al leer, o quieres usar las palabras al hablar/escribir? (define el tipo de flashcard).
4. **¿Qué cuenta como "dominar" una palabra/problema?** (define el umbral de "completado real").
5. **Libros: ¿divides por capítulos, por páginas o por checkpoints?** (define `unit_type`).
6. **¿Quieres rachas/gamificación o te distrae?** (a algunos les motiva, a otros les genera ansiedad).
7. **¿Móvil importa?** Si estudias en el teléfono, PWA sube mucho de prioridad.
8. **¿Mantienes 3 tablas (entrega) o migras al modelo de metas/unidades (uso real)?**

---

## 17. Roadmap priorizado (impacto × esfuerzo)

### Fase 0 — Tapar deuda de la entrega (rápido, alto valor académico)
- [ ] Implementar **CSRF** en todos los POST.
- [ ] `session_regenerate_id` en login + cookies seguras.
- [ ] Caché de diccionario + manejo de errores del proxy.
- [ ] Modo oscuro (quick win de rúbrica).

### Fase 1 — Progreso REAL (el corazón)
- [ ] **Unidades dentro de recurso** (capítulos/páginas) → resuelve el "libro enorme".
- [ ] **Metas / planes** con varios recursos y peso → resuelve "varios libros/videos".
- [ ] **Temporizador + registro de tiempo** por sesión.
- [ ] Dashboard de progreso real (ritmo, ¿voy a tiempo?, heatmap).

### Fase 2 — Retención (mayor impacto en aprendizaje)
- [ ] **Flashcards MÍNIMO** (§7.4): tarjetas + "guardar desde diccionario" + repaso → *empezar por aquí, es alto impacto / bajo esfuerzo*.
- [ ] **Motor SM-2** de repetición espaciada (compartido EN/CP).
- [ ] **Banco CEFR** (importar CEFR-J/Kelly/NGSL) → estudiar **por nivel**.
- [ ] **Vocabulario en contexto**: enriquecer con Datamuse + Tatoeba + audio (cacheado).
- [ ] Modos de repaso: reconocer / cloze / producción / escucha.

### Fase 3 — CP automático
- [ ] Integrar **Codeforces API**: rating, problemas resueltos, debilidades, sugerir siguiente.
- [ ] Tracking de problemas (todo/attempted/solved/upsolved) + editorial notes.
- [ ] Biblioteca de plantillas/snippets.

### Fase 4 — Compartir / escalar
- [ ] Plantillas de planes públicas + clonar.
- [ ] PWA / mobile.
- [ ] Perfiles, rachas, gamificación ligera.
- [ ] Import/export (Anki, CSV).

---

## 18. Mejoras FUERA del software (tus hábitos de estudio)

Aunque no toquen el código, esto multiplica el valor de la herramienta. La mejor app no compensa malos hábitos:

**Inglés**
- Cambia el "estudiar inglés" por **vivir en inglés**: pon el teléfono/PC en inglés, consume contenido que te gusta (CP en inglés, YouTube tech).
- Aprende **frases y colocaciones**, no palabras sueltas.
- **Output diario**: escribe 3–5 frases usando lo nuevo; habla aunque sea contigo.
- **Shadowing**: repetir en voz alta junto a un audio nativo (pronunciación + ritmo).
- Sube de nivel el input gradualmente (i+1), no saltes a contenido demasiado difícil.

**Programación competitiva**
- **Constancia > maratones**: 1–2 problemas diarios bien entendidos > 30 un domingo.
- **Upsolve siempre**: termina los problemas que no resolviste en el concurso; ahí está el 80% del aprendizaje.
- **Lee editoriales** y **re-implementa** sin ver — recuerdo activo.
- Practica en tu **zona i+1** (rating +100/200), no solo en lo que ya dominas.
- **Intercala temas**; lleva un registro de tus debilidades y atácalas.

**General**
- **Pomodoro** real (25/5) y elimina distracciones (teléfono fuera).
- **Repaso espaciado** de TODO lo importante (la herramienta debería forzarlo).
- **Revisión semanal**: ¿qué avancé?, ¿voy a tiempo?, ¿qué ajusto?
- **Duerme**: la consolidación de memoria ocurre durmiendo. Estudiar sin dormir es tirar el esfuerzo.

---

### Cierre

El proyecto entregable está sólido. El salto a "**herramienta que de verdad uso para estudiar mejor**" está en tres movimientos, en este orden de impacto:

1. **Progreso real** (unidades + metas + tiempo) → resuelve tus preguntas del libro enorme y los planes multi-recurso.
2. **Repetición espaciada** (SM-2) → convierte "leído" en "recordado".
3. **Codeforces API + vocabulario en contexto** → progreso medible y automático en tus dos enfoques.

Cuando quieras, priorizamos juntos y empezamos por la Fase 0/1.
