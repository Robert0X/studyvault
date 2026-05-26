# Guía de entrega y pruebas — StudyVault

Todo lo que necesitas para instalar, probar y entregar el proyecto. Sigue los pasos en orden.

---

## 1. Dónde está cada cosa
```
Proyecto Final/
├── studyvault/                  ← EL PROYECTO (código que va a htdocs)
│   ├── *.sql                    ← scripts de base de datos (originales, por separado)
│   ├── README.md                ← instalación + cobertura de rúbrica
│   ├── MANUAL_USUARIO.md        ← guía de uso de cada módulo
│   ├── PLAN_IMPLEMENTACION.md   ← seguimiento y bitácora de todo lo hecho
│   ├── MEJORAS.md / PROPUESTAS_DISENO.md
│   └── graphify-out/            ← grafo del código (graph.html abrible en navegador)
└── ENTREGA/                     ← ESTA CARPETA (ayuda para la entrega)
    ├── studyvault_completo.sql  ← ★ BASE DE DATOS EN UN SOLO ARCHIVO
    ├── GUIA_ENTREGA.md          ← este documento
    └── DOCUMENTACION.md         ← esqueleto del documento de la rúbrica (rellénalo)
```

---

## 2. Instalar la base de datos — OPCIÓN FÁCIL (1 archivo)
1. Abre **phpMyAdmin** (http://localhost/phpmyadmin).
2. Pestaña **Importar** → elige `ENTREGA/studyvault_completo.sql` → **Continuar**.
3. Listo: crea lapassword base `studyvault`, todas las tablas y datos demo.

> ⚠️ Este archivo **borra y recrea** la base `studyvault` (empieza con `DROP DATABASE`). Úsalo en una instalación limpia. Si ya tienes datos que quieras conservar, usa la Opción manual.

### Opción manual (por separado)
Si prefieres, ejecuta en este orden los archivos dentro de `studyvault/`:
1. `studyvault.sql` → 2. `migrations_v2.sql` → 3. `migrations_v3.sql` → 4. `migrations_v4.sql`

---

## 3. Poner el proyecto a correr
1. Copia la carpeta **`studyvault/`** a `C:\xampp\htdocs\studyvault`.
2. Dentro, copia `.env.example` a **`.env`** (ajusta `DB_PASS` si tu MySQL tiene contraseña).
3. Inicia **Apache** y **MySQL** en XAMPP.
4. Abre **http://localhost/studyvault/**

**Credenciales demo:** `admin@studyvault.com` / `password`

---

## 4. Checklist de prueba (verifica que todo funciona)
- [ ] **Login/Logout** y registro de un usuario nuevo.
- [ ] **Modo oscuro** (botón 🌙 en la barra superior).
- [ ] **Materias**: crear, editar, eliminar (sin recargar).
- [ ] **Recursos**: crear un enlace y un PDF (subir archivo); buscar/filtrar en vivo; cambiar estado.
- [ ] **Unidades**: en un recurso → generar 5 capítulos → marcar 2 completados → ver %.
- [ ] **Flashcards**: crear tarjeta; Estudiar (modos Clásico/Cloze/Producción); Exportar CSV.
- [ ] **Diccionario** (barra lateral): buscar "although" → ver definición, "se usa con" y ejemplos → Guardar como tarjeta.
- [ ] **Competitiva**: poner handle `tourist` → Sincronizar → ver rating y problemas; Sincronizar catálogo → sugerencias; Plantillas; programar un repaso.
- [ ] **Metas**: crear meta con fecha → agregar recursos con peso → ver % y "En camino/Atrasado".
- [ ] **Temporizador**: iniciar Pomodoro / registrar minutos → ver racha y heatmap.
- [ ] **Reporte semanal** (desde el Dashboard).
- [ ] **Seguridad**: cerrar sesión e intentar abrir `?page=dashboard` (debe redirigir a login).

---

## 5. Qué falta para la ENTREGA (rúbrica)
| Entregable | Estado |
|---|---|
| Código fuente organizado | ✅ Listo (`studyvault/`, MVC) |
| Base de datos .sql | ✅ Listo (`ENTREGA/studyvault_completo.sql`) |
| Manual de usuario | ✅ Listo (`studyvault/MANUAL_USUARIO.md`) |
| **Documentación formal** | ⏳ **Rellenar `DOCUMENTACION.md`** (portada, diagrama BD, casos de uso, **capturas**) |
| Video demostrativo (opcional) | ⏳ Grabar: login, CRUD, AJAX, servicios web, seguridad |

### Pasos finales sugeridos
1. Probar todo con el checklist de arriba.
2. Rellenar `DOCUMENTACION.md` y tomar **capturas** de cada módulo.
3. **Diagrama de BD**: en phpMyAdmin → base `studyvault` → pestaña **Diseñador** → exportar como imagen. (O abre `studyvault/graphify-out/graph.html` para el grafo del código.)
4. (Opcional/calidad) Ejecutar `/security-review` en Claude Code y luego mergear `develop` → `main` en GitHub como versión final.

---

## 6. Estado del desarrollo (resumen)
- **Completo:** cimientos+seguridad, modelo de datos, flashcards+SM-2, progreso real (unidades/metas/temporizador/dashboard), Codeforces (sync, repaso, sugerencias, concursos, plantillas), diccionario enriquecido (Datamuse + Tatoeba), estudio por nivel CEFR, export CSV/Anki, reporte semanal, rate limiting.
- **Pendiente (post-entrega):** import Anki, modo escucha, compartir/plantillas públicas (B7), aplicar el rediseño (`PROPUESTAS_DISENO.md`), paginación.
- Detalle completo y bitácora: `studyvault/PLAN_IMPLEMENTACION.md`.
