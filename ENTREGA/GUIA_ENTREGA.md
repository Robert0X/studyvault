# Guía de entrega y pruebas — StudyVault

Todo lo que necesitas para instalar, probar y entregar el proyecto. Sigue los pasos en orden.

> **Resumen rápido:** el **código está completo y supera los requisitos** de la rúbrica. Para **entregar** solo te falta: (1) probarlo tú en el navegador con el checklist, y (2) la **documentación formal con capturas**. Ver §5.

---

## 1. Dónde está cada cosa
```
Proyecto Final/
├── studyvault/                   ← EL PROYECTO (código que va a htdocs)
│   ├── *.sql                     ← scripts de BD originales (por separado)
│   ├── README.md                 ← instalación + cobertura de rúbrica
│   ├── MANUAL_USUARIO.md         ← guía de uso de cada módulo
│   ├── DESCRIPCION_PROYECTO.md   ← descripción integral (capacidades, stack, arquitectura)
│   ├── PLAN_IMPLEMENTACION.md    ← seguimiento y bitácora de TODO lo hecho
│   ├── MEJORAS.md / PROPUESTAS_DISENO.md
│   └── graphify-out/             ← grafo del código (graph.html abrible en navegador)
└── ENTREGA/                      ← ESTA CARPETA (ayuda para la entrega)
    ├── studyvault_completo.sql   ← ★ BASE DE DATOS COMPLETA EN UN SOLO ARCHIVO
    ├── GUIA_ENTREGA.md           ← este documento
    └── DOCUMENTACION.md          ← esqueleto del documento de la rúbrica (rellénalo)
```

---

## 2. Instalar la base de datos — OPCIÓN FÁCIL (1 archivo)
1. Abre **phpMyAdmin** (http://localhost/phpmyadmin).
2. Pestaña **Importar** → elige `ENTREGA/studyvault_completo.sql` → **Continuar**.
3. Listo: crea la base `studyvault`, **todas** las tablas (15) y los datos demo.

> ✅ Este archivo ya está **completo y actualizado** (incluye migraciones v2 a v5: flashcards, metas, Codeforces, repaso, plantillas, audio, meta diaria, etc.). No necesitas ejecutar nada más.
>
> ⚠️ Empieza con `DROP DATABASE studyvault`: **borra y recrea** la base desde cero. Úsalo en instalación limpia (reemplaza cualquier dato de prueba previo).

### Opción manual (por separado)
Ejecuta en este orden los archivos dentro de `studyvault/`:
`studyvault.sql` → `migrations_v2.sql` → `migrations_v3.sql` → `migrations_v4.sql` → `migrations_v5.sql`

---

## 3. Poner el proyecto a correr
1. Copia la carpeta **`studyvault/`** a `C:\xampp\htdocs\studyvault`.
2. Dentro, copia `.env.example` a **`.env`** (ajusta `DB_PASS` si tu MySQL tiene contraseña).
3. Inicia **Apache** y **MySQL** en XAMPP.
4. Abre **http://localhost/studyvault/**

**Credenciales demo:** `admin@studyvault.com` / `password`

---

## 4. Checklist de prueba (verifica que todo funciona en el navegador)
- [ ] **Login / Logout** y registro de un usuario nuevo.
- [ ] **Modo oscuro** (botón 🌙 en la barra superior).
- [ ] **Materias**: crear, editar, eliminar (sin recargar).
- [ ] **Recursos**: crear un enlace y un PDF (subir archivo); buscar/filtrar en vivo; cambiar estado; **paginación** (10 por página) si hay muchos.
- [ ] **Unidades**: en un recurso → generar 5 capítulos → marcar 2 completados → ver %.
- [ ] **Flashcards**: crear tarjeta; **Estudiar** (modos Clásico / Cloze / Producción / **Escucha**); **Exportar** CSV; **Importar** CSV; **Por nivel** (CEFR).
- [ ] **Diccionario** (barra lateral): buscar "although" → definición, "se usa con" (colocaciones), **ejemplos reales**, 🔊 audio → **Guardar como tarjeta**.
- [ ] **Competitiva**: handle `tourist` → Sincronizar → rating y problemas; Sincronizar catálogo → sugerencias; Plantillas; **programar y hacer un repaso**.
- [ ] **Metas**: crear meta con fecha → agregar recursos con peso → ver % y "En camino/Atrasado"; **publicar como plantilla** (candado/globo).
- [ ] **Plantillas públicas**: Metas → "Plantillas" → **clonar** una (si hay de otro usuario).
- [ ] **Temporizador**: Pomodoro / registrar minutos → racha, heatmap y **meta diaria**.
- [ ] **Reporte semanal** (desde el Dashboard).
- [ ] **Seguridad**: cerrar sesión e intentar abrir `?page=dashboard` (debe redirigir a login).
- [ ] **Diseño**: revisa la paleta nueva (índigo) y el modo oscuro; si no te gusta, avísame (es solo CSS, reversible).

---

## 5. ¿Ya puedo entregar? — Qué falta para la ENTREGA

**Veredicto:** en **funcionalidad y código, SÍ estás listo** (cumple y supera la rúbrica). Para una **entrega formal completa** te faltan 2 cosas, ambas tuyas:

| Entregable | Estado | Acción |
|---|---|---|
| Código fuente organizado (MVC) | ✅ Listo | — |
| Base de datos `.sql` | ✅ Listo | `ENTREGA/studyvault_completo.sql` |
| Manual de usuario | ✅ Listo | `studyvault/MANUAL_USUARIO.md` |
| Descripción técnica del proyecto | ✅ Listo | `studyvault/DESCRIPCION_PROYECTO.md` |
| **Probarlo tú en el navegador** | ⏳ **Pendiente** | Sigue el checklist §4 |
| **Documentación formal (rúbrica)** | ⏳ **Pendiente** | Rellena `DOCUMENTACION.md` + capturas + diagrama BD |
| Video demostrativo (opcional) | ⏳ Opcional | login, CRUD, AJAX, servicios web, seguridad |

### Pasos finales (en orden)
1. **Importar el SQL** (§2) y **probar todo** con el checklist (§4). *(No pude probar la UI en navegador; hazlo tú, sobre todo la paleta nueva.)*
2. **Rellenar `ENTREGA/DOCUMENTACION.md`**: portada, intro, objetivos, problema, requerimientos, casos de uso (ya redactados, ajústalos) y **capturas** de cada módulo.
3. **Diagrama de BD**: phpMyAdmin → base `studyvault` → pestaña **Diseñador** → exportar imagen y pegarla en `DOCUMENTACION.md`. (Alternativa para lo técnico: `studyvault/graphify-out/graph.html`.)
4. **(Calidad, recomendado)** En Claude Code: `/security-review`; luego mergear `develop` → `main` en GitHub como versión final de entrega.
5. **(Opcional)** Grabar el video.

> Cuando completes (1) y (2), **ya puedes entregar**.

---

## 6. Estado del desarrollo (resumen actualizado)
**Completo:**
- Cimientos + seguridad (CSRF, hashing, sesiones, cabeceras, rate limiting, `.htaccess` en uploads).
- Modelo de datos (15 tablas, FKs, índices).
- Materias, Recursos (con **paginación**), **Unidades** (progreso de libros).
- **Flashcards + SM‑2**: 4 modos (Clásico/Cloze/Producción/**Escucha**), estudio por nivel CEFR, **export e import** CSV/Anki.
- **Diccionario enriquecido**: Free Dictionary + Datamuse (colocaciones) + Tatoeba (ejemplos) + audio.
- **Competitiva (Codeforces)**: sync de rating/problemas, repaso SM‑2, sugerir‑siguiente (i+1), concursos, plantillas.
- **Metas** con progreso ponderado y ritmo; **plantillas públicas + clonar** (compartir).
- **Temporizador** Pomodoro: tiempo, racha, heatmap, **meta diaria**.
- **Dashboard** y **Reporte semanal**.
- **Rediseño de paleta** (índigo + Inter) aplicado — *pendiente de tu revisión visual*.

**Pendiente (post‑entrega, requiere infraestructura o son extras):**
- Recordatorios (web push/email), recuperación de contraseña (necesitan correo/servidor push).
- Perfiles públicos, gamificación, onboarding (resto de B7).
- Banco CEFR completo, diario de escritura, búsqueda full‑text, API REST, PWA, Docker, i18n.

Detalle completo y bitácora de cada cambio: `studyvault/PLAN_IMPLEMENTACION.md`.
