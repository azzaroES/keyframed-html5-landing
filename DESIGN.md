# Design Contract: Cariñena Kebab FSE

> **Theme:** Cariñena Kebab FSE (v2.0.0)  
> **Standard:** WordPress Full Site Editing (FSE) Block Theme & Novamira MCP  
> **Schema Compatibility:** Novamira Design Contract v1.x, WCAG 2.2 AA (2026 Mandates)

---

## 1. Global Dials

These dials govern the generative aesthetic behavior for both AI agents (Novamira MCP) and human visual editors.

| Dial | Value | Range | Intent |
| :--- | :--- | :--- | :--- |
| **`variance`** | `0.18` | `0.0 - 1.0` | High brand discipline, minimal stylistic drift between templates |
| **`density`** | `0.58` | `0.0 - 1.0` | Generous breathing room, luxurious 8px-grid vertical rhythm |
| **`motion`** | `0.75` | `0.0 - 1.0` | Fluid 60fps canvas scrubbing, smooth pull-up sheets, micro-interactions |

---

## 2. Color Tokens

All colors are codified in `theme.json` and mirrored in CSS custom properties. Agents modifying colors MUST maintain a minimum 4.5:1 contrast ratio against background surfaces for body text, and 3:1 for large display headings.

```yaml
palette:
  crust:
    hex: "#2C1810"
    role: "Warm deep umber; secondary container backgrounds and pill accents"
  gold:
    hex: "#C9A962"
    role: "Primary luxury metallic accent; primary buttons, kicker badges, active states"
  gold-bright:
    hex: "#DFC482"
    role: "Hover highlights, radiant glow overlays, and focus rings"
  cream:
    hex: "#F5F0E8"
    role: "Primary body text, card copy, and secondary headlines on dark"
  ivory:
    hex: "#FEFDFB"
    role: "High-contrast headings, hero titles, and modal text"
  night:
    hex: "#1A1A1A"
    role: "Default page viewport background"
  night-pitch:
    hex: "#0F0F0F"
    role: "Cinema backdrop for 3D scrubber canvas and video backgrounds"
  night-surface:
    hex: "#242424"
    role: "Elevated card backgrounds, dropdowns, and form inputs"
  accent:
    hex: "#EE5862"
    role: "Coral status badges, notification pings, and alert states"
  muted:
    hex: "#A09A93"
    role: "Micro copy, timestamps, copyright lines, and borders"
```

---

## 3. Typography Hierarchy

- **Display Headings (`fontFamilies.archivo`):** `Archivo`, heavy geometric sans-serif (weight `800`), tight tracking (`-0.03em`).
- **Body & Controls (`fontFamilies.lexend`):** `Lexend`, hyper-legible geometric grotesque (weights `350` body, `500` UI, `600` CTA).

```yaml
scale:
  hero: "clamp(2.75rem, 7vw + 1rem, 6.25rem)" # Canvas headlines
  xx-large: "clamp(2rem, 3.5vw + 1rem, 3.25rem)" # H2 Section titles
  x-large: "clamp(1.5rem, 2vw + 1rem, 2rem)"     # H3 Card titles
  large: "1.25rem"                               # Cast cards / callouts
  medium: "1.0rem"                               # Body standard text
  small: "0.875rem"                              # Secondary metadata & labels
  micro: "0.75rem"                               # Kickers, badges, tags
```

---

## 4. Spacing System (8px Grid Scale)

Strict adherence to 8-point increments. No arbitrary margins or paddings:

```yaml
spacing:
  step-10: "8px"
  step-20: "16px"
  step-30: "24px"
  step-40: "32px"
  step-50: "40px"
  step-60: "48px"
  step-70: "64px"
  step-80: "80px"
```

---

## 5. Shape, Blur & Glassmorphism

- **Border Radius:**
  - Micro tags / badges: `4px` (`--radius-sm`)
  - Bento cards / containers: `16px` (`--radius-lg`)
  - CTAs / Pills / FAB buttons: `9999px` (`--radius-pill`)
- **Backdrop Filters:**
  - Dark glass surface: `background: rgba(26, 26, 26, 0.75); backdrop-filter: blur(16px);`
  - Border border glow: `1px solid rgba(201, 169, 98, 0.22);`

---

## 6. Page Layout Archetypes

### A. Front Page (`templates/front-page.html`)
- **Hero:** Sticky 100vh canvas viewport driven by user scroll (240 WebP keyframes). Static poster fallback in Gutenberg Site Editor.
- **Radial FAB Navigation:** Bottom-right floating trigger expanding into circular action nodes. Includes instant **Blog** navigation node.
- **Footer:** 4-row structured grid (Brand & bio, quick links, NAP & copyright, interactive Gemini Argon terminal popup).

### B. Cinematic Blog Post (`templates/single.html`)
- **Pinned 100vh Video/Canvas Hero:** Dark cinema hero with post title, kicker, category, reading duration, and scroll-down indicator.
- **Contrast Content Sheet:** Light cream/ivory sheet that smoothly translates up over the pinned media as the user finishes scrolling past the hero.
- **Bottom App Bar:** Sticky 4-tab mobile navigation (`Acasă`, `Carta`, `Blog`, `Contact`).

### C. Contact Hub (`templates/page-contact.html` - Foto V-MAG Reference)
- **Business ID Data Binding:** Automatically populates NAP info from active profile.
- **vCard CTA:** Instant "Salvează contactul nostru" `.vcf` streaming link.
- **4 Action Cards:** *Sună acum* (tel:), *Scrie pe WhatsApp* (wa.me), *Trimite Email* (mailto:), *Direcții Waze/Maps* (geo:).
- **Live Hours Badge:** Evaluated client-side to remain 100% accurate under LiteSpeed Cache (`● Deschis acum - închidem la 19:00`).
- **Interactive Map & Fiscal Verification:** Google Map embed, CIF/Reg. Com. trust badges.

---

## 7. Accessibility & Performance Guardrails
1. **WCAG 2.2 AA Compliance:** All interactive touch targets >= 24x24px (mobile bottom nav >= 48px). Focus visible indicator: 2px solid `#C9A962` with 3px offset.
2. **Motion Preference:** Respect `prefers-reduced-motion` at all times.
3. **LiteSpeed Cache (LSCache) Synergy:** Dynamic business status must use client-side hydration or ESI tags; static assets cached for 1 year immutable.
4. **Super PWA Compatibility:** Standalone display mode, dark status bar `#1A1A1A`, offline fallback shell.
