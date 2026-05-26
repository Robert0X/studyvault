# StudyVault — Propuestas de rediseño (UI/UX y paleta)

> Documento de **propuestas**. No hay cambios aplicados. Objetivo: que la app se vea más **clara, elegante y profesional**, y que el diseño **sirva mejor al propósito** (estudiar concentrado, ver avance real, reducir fricción).
>
> Fecha: 2026-05-25 · No implementar hasta confirmación.

---

## 0. Diagnóstico del diseño actual

Lo que ya está bien:
- Estructura coherente (navbar + sidebar + main), tarjetas con sombra, barras de progreso, modo oscuro funcional.
- Iconografía consistente (Font Awesome), badges por estado.

Lo que resta claridad/elegancia:
- **Paleta muy saturada y "playful"** (indigo + rosa fuerte + cian + morado): compite por atención y cansa en sesiones largas de estudio.
- **Navbar con degradado fuerte**: llamativo pero poco sobrio.
- **Densidad inconsistente**: mezcla de paddings, tamaños de fuente y radios; faltan "tokens" unificados.
- **Jerarquía visual plana**: títulos, subtítulos y metadatos compiten; poco uso de espacio en blanco.
- **Navbar saturado**: 8 ítems en la barra superior + sidebar duplicado.
- **Tablas y formularios**: estilo Bootstrap "de fábrica", poco refinado.
- **Falta foco**: la pantalla de estudio (flashcards/repaso) debería ser inmersiva y silenciosa.

---

## 1. Paleta de colores (propuesta principal: "Índigo sereno")

Una paleta calmada, con **un solo color de marca** (índigo), neutros fríos (slate) y **un acento cálido** (ámbar) reservado para logros/racha. Transmite enfoque y profesionalismo.

### Modo claro
| Rol | Hex | Uso |
|---|---|---|
| Primario | `#4F46E5` | botones, links, barras de progreso |
| Primario oscuro | `#4338CA` | hover, navbar |
| Acento (ámbar) | `#F59E0B` | racha 🔥, destacados, logros |
| Fondo app | `#F8FAFC` | fondo general |
| Superficie | `#FFFFFF` | tarjetas, modales |
| Borde | `#E2E8F0` | divisores, inputs |
| Texto | `#0F172A` | texto principal |
| Texto tenue | `#64748B` | metadatos, labels |
| Éxito | `#10B981` · Info `#0EA5E9` · Aviso `#F59E0B` · Peligro `#EF4444` | estados |

### Modo oscuro
| Rol | Hex |
|---|---|
| Fondo | `#0F172A` · Superficie `#1E293B` · Borde `#334155` |
| Texto | `#E2E8F0` · Tenue `#94A3B8` |
| Primario (más claro para contraste) | `#818CF8` |

### Importante: el color por **materia** se conserva
Las materias (Inglés, CP, etc.) ya tienen color propio elegido por el usuario → eso se mantiene como **acento contextual** (banner de la tarjeta, badge), pero el **chrome de la app** (navbar, botones, links) usa la paleta única. Así cada materia "pinta" su zona sin romper la coherencia global.

### Alternativa: "Verde foco"
Si prefieres un tono más "calma/concentración": primario `#0D9488` (teal-600), acento `#F59E0B`, mismos neutros slate. Mismo principio.

---

## 2. Tipografía
- Fuente: **Inter** (o system-ui como fallback) — más limpia que Segoe UI por defecto. Cargar desde Google Fonts o `font-family: 'Inter', system-ui, sans-serif`.
- Escala consistente (type scale): `h2` 1.5rem/700, `h5` 1.05rem/600, body 0.95rem, small 0.8rem.
- Números de stats con **tabular-nums** y peso 700 para que "se sientan" como métricas.
- Menos negritas sueltas; usar peso 600 solo para jerarquía real.

---

## 3. Tokens de diseño (unificar)
Definir variables CSS y usarlas en TODO (hoy hay valores sueltos):
```css
:root{
  --radius: 14px;            /* tarjetas */
  --radius-sm: 10px;         /* inputs, badges */
  --shadow-sm: 0 1px 2px rgba(15,23,42,.06);
  --shadow-md: 0 4px 16px rgba(15,23,42,.08);
  --space: 16px;             /* unidad base de espaciado */
  --transition: .15s ease;
}
```
- **Sombras suaves y consistentes** (hoy varían). Menos borde, más sombra sutil.
- **Radio uniforme** (14px tarjetas, 10px controles).
- **Más aire**: subir padding de tarjetas a 20–24px y separar secciones con 24px.

---

## 4. Navegación (reducir ruido)
- **Navbar superior sobria**: fondo sólido (`#FFFFFF` claro / `#1E293B` oscuro) con borde inferior fino, en vez de degradado. El logo en índigo.
- **Sidebar como navegación principal**; en la navbar superior dejar solo: logo, buscador global, toggle oscuro y usuario. Quitar la duplicación de los 8 links arriba.
- **Agrupar el sidebar** en secciones: *Estudiar* (Dashboard, Flashcards, Temporizador), *Contenido* (Materias, Recursos, Metas), *Competitiva* (CP), con encabezados tenues.
- Indicador de activo más claro (barra lateral de color + fondo suave).

---

## 5. Mejoras por pantalla

**Dashboard**
- Una fila "hero" con saludo + racha 🔥 + tiempo hoy, grande y claro.
- Tarjetas de stats con icono en círculo de color suave (ya existe) pero con números más grandes y label en mayúscula tenue.
- Mover "metas con pace" arriba (es lo más accionable).

**Flashcards / Estudio (modo foco)**
- Pantalla de estudio **inmersiva**: ocultar sidebar/navbar (o atenuar), centrar la tarjeta, fondo neutro. Menos elementos = más concentración.
- Tarjeta más grande, con animación de **flip 3D** suave.
- Botones de calificación con color semántico y atajo visible (1–4).
- Barra de progreso de la sesión más prominente arriba.

**Temporizador**
- El número del Pomodoro como protagonista (display grande, fuente tabular).
- Anillo de progreso circular (SVG) en vez de solo número.
- Heatmap con tooltip más legible y leyenda alineada.

**Tablas (Recursos, CP)**
- Filas más altas, separadores sutiles, hover suave.
- Estados como "pills" de color suave (ya casi están).
- Acciones en iconos con tooltip; agruparlas a la derecha con menos peso visual.

**Formularios y modales**
- Inputs con borde fino, foco con anillo índigo (`box-shadow` de color).
- Labels en peso 600 tenue, ayuda en small.
- Modales con más padding y título claro.

**Estados vacíos**
- Ya hay buenos empty states; unificar ilustración/icono + 1 acción primaria.

---

## 6. Micro-interacciones y detalles
- Transiciones suaves (`--transition`) en hover de tarjetas, botones y links.
- Feedback de acciones AJAX con **toasts** (Bootstrap toast) en vez de `alert()`.
- Skeletons o spinners discretos al cargar (búsqueda, diccionario).
- Confirmaciones con un modal elegante en lugar de `confirm()` nativo.

---

## 7. Accesibilidad y contraste
- Verificar contraste AA: texto tenue actual sobre blanco roza el mínimo; `#64748B` cumple mejor.
- Foco visible por teclado en todos los controles (anillo).
- `aria-label` en botones que son solo icono (ya hay `title`, añadir aria).
- Tamaño mínimo de toque 40px en móvil.

---

## 8. Modo oscuro (refinar)
- Hoy es funcional pero algo plano. Con la paleta slate quedaría más elegante: superficies `#1E293B`, bordes `#334155`, primario aclarado `#818CF8` para contraste.
- Evitar negros puros (cansan); usar slate-900.
- Persistir preferencia (ya se hace) y respetar `prefers-color-scheme` en la primera visita.

---

## 9. Cómo se implementaría (cuando autorices)
La mayoría es **CSS** centrado en `assets/css/app.css` + variables, con cambios mínimos de markup:
1. Definir tokens y nueva paleta en `:root` y `[data-theme="dark"]`.
2. Cargar Inter.
3. Ajustar navbar/sidebar (sobrio + agrupado).
4. Modo foco en la pantalla de estudio.
5. Reemplazar `alert()/confirm()` por toasts/modales.
6. Pulir tablas, formularios, dashboard.

Bajo riesgo: casi todo es estético y reversible; no toca lógica ni datos.

---

## 10. Prioridad sugerida (impacto visual / esfuerzo)
1. **Paleta + tokens + tipografía** (cambio global, alto impacto, bajo esfuerzo). 🟢
2. **Navbar/sidebar sobrios y agrupados**. 🟢
3. **Modo foco en estudio** (mayor impacto en el propósito). 🟡
4. **Toasts/modales** en vez de alert/confirm. 🟡
5. **Anillo del Pomodoro + dashboard hero**. 🟡
6. **Refinar tablas/formularios/dark mode**. 🟢

> Recomendación: empezar por (1) y (2) — transforman la sensación de toda la app con poco código — y luego (3) que es lo que más ayuda a estudiar.
