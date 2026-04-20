# Apple-Minimalist UX Redesign — Option B

**Date:** 2026-04-20  
**Scope:** `resources/assets/less/overrides.less` + CSS build artifacts  
**Approach:** Dark sidebar retained, everything else cleaned up to Apple enterprise standard

---

## Goal

Make the VDOT/CannaAfrica UI feel like a polished Apple pro app (Xcode, Instruments, Linear, Vercel) — flat, spacious, typographically clean, with a single accent color used sparingly. No gradients, no zebra striping, no visual noise.

---

## Design Tokens

### Colors

| Token | Value | Usage |
|---|---|---|
| `--vdot-primary` | `#0ea5e9` | Sky 500 — accent only, buttons and links |
| `--vdot-primary-hover` | `#0284c7` | Sky 600 |
| `--vdot-bg-body` | `#ffffff` | Page background — pure white |
| `--vdot-bg-surface` | `#ffffff` | Card/box surface |
| `--vdot-bg-subtle` | `#f9fafb` | Gray 50 — table header, input bg |
| `--vdot-text-main` | `#111827` | Gray 900 — primary text |
| `--vdot-text-muted` | `#6b7280` | Gray 500 — secondary text |
| `--vdot-border` | `#e5e7eb` | Gray 200 — subtle borders |
| `--vdot-border-focus` | `#0ea5e9` | Sky 500 — input focus |
| `--vdot-radius` | `8px` | Slightly more Apple-like |
| `--vdot-shadow` | `none` | Flat — no shadows |
| `--vdot-shadow-lg` | `none` | Flat — no shadows |

---

## Component Changes

### Navbar / Header
- **Remove** the sky blue gradient background
- **Replace** with flat white (`#ffffff`) + `border-bottom: 1px solid var(--vdot-border)`
- Logo area: white background, dark text (`--vdot-text-main`), `font-weight: 700`
- Nav links: dark text, hover state uses `--vdot-bg-subtle`
- This mirrors macOS toolbar pattern — chrome disappears, content leads

### Sidebar (kept dark)
- Background: `#1e293b` (Slate 800) — no change
- Active item: left border accent + `#0f172a` bg — no change
- Tighten item padding slightly: `10px 15px` → `8px 14px`
- Remove any remaining inset shadows on sidebar items

### Tables
- **Remove zebra striping** (`table-striped` override → transparent odd rows)
- Row hover: `#f9fafb` (Gray 50) — very subtle
- Header: remove grey background, keep only a `border-bottom: 1px solid var(--vdot-border)`; remove `text-transform: uppercase` and `letter-spacing`; keep `font-weight: 600`, `font-size: 0.8125rem`, color `#4b5563` (Gray 600)
- Cell padding: `0.875rem 1rem` — slightly more generous

### Form Inputs
- Border: lighten from `#94a3b8` to `#d1d5db` (Gray 300) — less heavy
- Input padding: `0.625rem 0.75rem` → `0.6875rem 0.875rem` — slightly taller hit area
- Placeholder: `#9ca3af` (Gray 400)
- Focus: border becomes `--vdot-primary`, no shadow — clean Apple-style

### Labels
- Weight: `600` → `500`
- Color: `#4b5563` (Gray 600, ~7:1 contrast on white) — receded but accessible
- `font-size: 0.8125rem` (13px) — compact but readable

### Buttons
- `.btn-primary`: flat sky blue, no gradient, `font-weight: 500`
- `.btn-default`: white bg, `border: 1px solid var(--vdot-border)`, hover → `--vdot-bg-subtle`
- Remove `.btn` box-shadow (already done)
- Padding: `0.4375rem 0.875rem` — slightly tighter (Apple buttons are compact)

### Cards / Boxes
- `.box`: border `1px solid var(--vdot-border)` — already done
- `.box-header`: remove bottom border on non-`with-border` boxes; `padding: 1rem 1.25rem`
- `.box-body`: `padding: 1.25rem`
- `.box-footer`: transparent background (remove grey), just `border-top: 1px solid var(--vdot-border)`
- `border-radius: var(--vdot-radius)` (8px)

### Typography
- Body: 14px, `line-height: 1.6` (up from 1.5 — more breathing room)
- `h1–h6`: `font-weight: 600` — keep
- Page titles: `font-size: 1.25rem`, `font-weight: 600`
- Muted secondary text: `--vdot-text-muted`

### Page Background
- `--vdot-bg-body`: `#f8fafc` → `#ffffff` — pure white; content lives on white, no double-white

### Input Groups
- `.input-group-addon`: border lightened to match new input border (`#d1d5db`), bg `#f9fafb`

### Badges / Pills
- Border-radius: `999px` for status pills (`.label-*`), `4px` for square badges
- Font-size: `0.6875rem` (11px), `font-weight: 600`, `letter-spacing: 0.02em`
- Padding: `0.2rem 0.5rem`
- Colors: keep existing semantic colors (success/danger/warning/info) — no change to hues

### Modals
- `.modal-content`: `border-radius: var(--vdot-radius)`, `border: 1px solid var(--vdot-border)`, `box-shadow: none`
- `.modal-header`: `border-bottom: 1px solid var(--vdot-border)`, white background, no gradient
- `.modal-footer`: `border-top: 1px solid var(--vdot-border)`, transparent background

### Dropdowns
- `.dropdown-menu`: `border: 1px solid var(--vdot-border)`, `border-radius: var(--vdot-radius)`, `box-shadow: none`, white background
- `.dropdown-menu > li > a`: `padding: 0.5rem 1rem`, `color: var(--vdot-text-main)`
- `.dropdown-menu > li > a:hover`: `background-color: var(--vdot-bg-subtle)`, `color: var(--vdot-text-main)`

---

## What Is NOT Changed

- Sidebar dark scheme — kept as-is, it works
- Sidebar active/hover states — already good
- Primary color (`#0ea5e9`) — already clean
- Font stack (Inter / -apple-system) — already correct
- Icon set — not in scope

---

## File Changed

Single file: `resources/assets/less/overrides.less`  
Build artifacts updated via `npm run dev`

---

## Success Criteria

- No gradients anywhere in the light content area
- No zebra striping on any table
- No drop shadows on any element
- Navbar is white with a single bottom border line
- Form inputs feel lighter and more spacious
- Cards feel like floating white panels, not boxed sections
- The overall impression is "this could ship on apple.com"
