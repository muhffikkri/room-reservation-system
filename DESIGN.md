# DESIGN SYSTEM GUIDELINES — REKSA CLAYMORPHISM UI

This document serves as the official, comprehensive **Design System Reference** for the **REKSA (Reservasi dan Kerusakan Sarana Akademik)** application. All team members building new pages, features, layout components, or UI elements must follow these standards to ensure 100% visual and interactive consistency across the application.

---

## 1. Vision & Visual Identity

REKSA utilizes a modern **Soft Neumorphism / Claymorphism UI** aesthetic. The design language is friendly, tactile, and highly dimensional, combining smooth multi-tone gradients, deep outer drop shadows, and dual-direction inner highlights to simulate soft 3D clay elements.

### Core Principles:

1. **Dimensionality & Softness**: Components feature soft 3D volume created via dual inner shadows (top-left light source, bottom-right ambient depth shadow) combined with wide outer blur shadows.
2. **Rounded Geometry**: Aggressive rounding (`rounded-2xl`, `rounded-3xl`, `rounded-full`, `rounded-[2rem]`). Sharp 90-degree corners should be avoided.
3. **Consistent Tactile Feedback**:
    - **Hover State**: Elevates element upward (`translateY(-2px)` to `-3px`) with expanded blur drop shadow.
    - **Pressed / Active State**: Compresses element downward (`translateY(1px)` to `2px`) with collapsed outer shadow or inner inset shadow.
4. **Cohesive Color Contrast**: High contrast text (`#0F172A`) over soft ice-blue (`#EAF1FA`) background canvas.

---

## 2. Typography & Font Hierarchy

- **Primary Font Family**: `Plus Jakarta Sans`, fallback to `Instrument Sans`, `ui-sans-serif`, `system-ui`, `sans-serif`.
- **Text Selection Highlight**: Background `bg-blue-600` (`#2563EB`), text `text-white`.

### Typography Scale & Weights:

| Element Role            | Font Size                             | Weight                      | Tailwind Class / Style                                                            | Color Hex |
| ----------------------- | ------------------------------------- | --------------------------- | --------------------------------------------------------------------------------- | --------- |
| **Hero Heading**        | `36px` - `44px` (`2.25rem - 2.75rem`) | Extrabold (800)             | `text-3xl sm:text-4xl xl:text-[44px] font-extrabold tracking-tight leading-tight` | `#10264A` |
| **Section Title**       | `24px` - `30px` (`1.5rem - 1.875rem`) | Extrabold (800)             | `text-2xl sm:text-3xl font-extrabold tracking-tight`                              | `#0F172A` |
| **Card Title**          | `16px` - `18px` (`1rem - 1.125rem`)   | Bold (700)                  | `text-base sm:text-lg font-bold text-slate-900`                                   | `#0F172A` |
| **Section Label / Tag** | `11px` (`0.6875rem`)                  | Bold (700)                  | `text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600`                 | `#2563EB` |
| **Body Text**           | `14px` - `16px` (`0.875rem - 1rem`)   | Normal (400) / Medium (500) | `text-sm sm:text-base leading-relaxed text-slate-600`                             | `#475569` |
| **Muted Caption**       | `12px` (`0.75rem`)                    | Normal (400) / Medium (500) | `text-xs leading-relaxed text-slate-500`                                          | `#64748B` |

---

## 3. Color Palette

```
Canvas Background:  #EAF1FA (Soft Ice Blue)
Primary Blue:       #2563EB (Blue 600) -> #3B82F6 (Blue 500) Gradient
Primary Hover:      #1D4ED8 (Blue 700) -> #2563EB (Blue 600) Gradient
Secondary Surface:  #FFFFFF (Pure White) -> #F0F6FF Gradient
Clay Shadow Light:  rgba(255, 255, 255, 0.96) (Top-Left Highlight)
Clay Shadow Dark:   rgba(112, 151, 205, 0.22) (Bottom-Right Depth)
Inset Ambient Dark: rgba(153, 185, 227, 0.20)
Text Dark:          #0F172A / #10264A
Text Muted:         #475569 / #64748B
```

### Complete Color Reference Table:

| Role                    | Color Name         | Hex / RGBA Value            | Applied UI Elements                                      |
| ----------------------- | ------------------ | --------------------------- | -------------------------------------------------------- |
| **Canvas Background**   | Soft Ice Blue      | `#EAF1FA`                   | Body page background                                     |
| **Primary Base**        | Blue 600           | `#2563EB`                   | Primary buttons, active badges, navigation highlights    |
| **Primary Accent**      | Blue 500           | `#3B82F6`                   | Primary button gradient stop, active indicators          |
| **Primary Hover**       | Blue 700           | `#1D4ED8`                   | Primary button hover state                               |
| **Card Surface Top**    | White Top          | `rgba(255, 255, 255, 0.98)` | Linear gradient start for Clay Cards                     |
| **Card Surface Bottom** | Ice Blue Tint      | `rgba(239, 246, 255, 0.94)` | Linear gradient end for Clay Cards                       |
| **Border Highlight**    | Crisp White        | `rgba(255, 255, 255, 0.96)` | 1px border on cards, inputs, and buttons                 |
| **Drop Shadow (Outer)** | Blue Slate Ambient | `rgba(112, 151, 205, 0.22)` | Outer 3D drop shadow (24px offset, 48px blur)            |
| **Highlight Inner**     | Pure White Inner   | `rgba(255, 255, 255, 0.96)` | Top-left inner bevel shadow (12px, 8px, 16px blur)       |
| **Ambient Inner**       | Soft Blue Shadow   | `rgba(153, 185, 227, 0.20)` | Bottom-right inner bevel shadow (-12px, -8px, 16px blur) |
| **Input Border**        | Light Blue Border  | `#D9E6F7`                   | Default state textfield border                           |
| **Input Focus Ring**    | Translucent Blue   | `rgba(59, 130, 246, 0.15)`  | Focus ring glow around textfields                        |

---

## 4. UI Components Specifications & Code Standards

### 4.1 Clay Card Component (`.clay-card`, `.landing-card`, `.dashboard-clay-card`, `.auth-clay-card`)

Main container used for sections, feature blocks, form wrappers, and item lists.

- **Border Radius**: `rounded-3xl` (`1.5rem / 24px`) or `rounded-[2rem]` (`32px`)
- **Border**: `1px solid rgba(255, 255, 255, 0.96)`
- **Background**: `linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(239, 246, 255, 0.94))`
- **Box Shadow Specification**:
    ```css
    box-shadow:
        24px 24px 48px rgba(112, 151, 205, 0.22),
        -18px -18px 40px rgba(255, 255, 255, 0.88),
        inset 12px 8px 16px rgba(255, 255, 255, 0.96),
        inset -12px -8px 16px rgba(153, 185, 227, 0.2);
    ```
- **Hover State (`:hover`, `:focus-within`)**:
    ```css
    transform: translateY(-3px);
    box-shadow:
        28px 28px 54px rgba(100, 142, 200, 0.26),
        -18px -18px 40px rgba(255, 255, 255, 0.95),
        inset 12px 8px 16px #ffffff,
        inset -12px -8px 16px rgba(153, 185, 227, 0.25);
    transition:
        transform 180ms ease,
        box-shadow 180ms ease;
    ```

---

### 4.2 Inset Container Component (`.clay-inset`)

Used for sunken inner containers, such as form boxes, metric statistic cards, and nested list items.

- **Border Radius**: `rounded-2xl` (`1rem / 16px`)
- **Border**: `1px solid rgba(255, 255, 255, 0.92)`
- **Background**: `linear-gradient(145deg, rgba(231, 241, 255, 0.75), rgba(255, 255, 255, 0.92))`
- **Box Shadow Specification**:
    ```css
    box-shadow:
        inset 3px 3px 8px rgba(123, 154, 196, 0.12),
        inset -3px -3px 8px rgba(255, 255, 255, 0.95);
    ```

---

### 4.3 Button Components & Interactive States

#### A. Primary Action Button (`.landing-button` with Primary Gradient)

Used for main call-to-actions, submit buttons, and primary actions.

- **Shape**: Pill-shaped (`rounded-full`)
- **Height / Padding**: `min-h-11` (`44px`), `px-5 py-3`
- **Background**: `linear-gradient(to right, #2563eb, #3b82f6)`
- **Typography**: `text-sm font-bold text-white`
- **Border**: `1px solid rgba(255, 255, 255, 0.48)`
- **Default Box Shadow**:
    ```css
    box-shadow:
        0 9px 18px rgba(24, 91, 225, 0.24),
        0 3px 0 rgba(22, 73, 184, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.62),
        inset 0 -2px 4px rgba(20, 77, 189, 0.18);
    ```
- **Hover State (`:hover`, `:focus-visible`)**:
    ```css
    transform: translateY(-3px) scale(1.015);
    filter: saturate(1.08) brightness(1.025);
    box-shadow:
        0 14px 25px rgba(24, 91, 225, 0.3),
        0 4px 0 rgba(22, 73, 184, 0.42),
        inset 0 1px 0 rgba(255, 255, 255, 0.72),
        inset 0 -2px 4px rgba(20, 77, 189, 0.14);
    ```
- **Pressed State (`:active`)**:
    ```css
    transform: translateY(2px) scale(0.975);
    filter: brightness(0.96) saturate(0.96);
    box-shadow:
        0 2px 5px rgba(24, 91, 225, 0.2),
        0 0 0 rgba(22, 73, 184, 0.35),
        inset 0 3px 7px rgba(20, 77, 189, 0.26),
        inset 0 1px 0 rgba(255, 255, 255, 0.24);
    ```

#### B. Secondary Action Button (`.landing-button-secondary` / Soft White)

Used for secondary actions, reset filters, and navigation controls.

- **Shape**: Pill-shaped (`rounded-full` or `rounded-xl`)
- **Background**: `linear-gradient(145deg, #ffffff, #f0f6ff)`
- **Typography**: `text-sm font-semibold text-blue-600` (`#2563eb`)
- **Border**: `1px solid rgba(255, 255, 255, 0.96)`
- **Default Box Shadow**:
    ```css
    box-shadow:
        8px 8px 20px rgba(166, 195, 235, 0.35),
        -8px -8px 20px rgba(255, 255, 255, 0.95),
        inset 0 1px 0 rgba(255, 255, 255, 0.98);
    ```
- **Hover State (`:hover`)**:
    ```css
    color: #1d4ed8;
    background: linear-gradient(145deg, #ffffff, #eaf2ff);
    box-shadow:
        10px 11px 22px rgba(151, 183, 227, 0.38),
        -8px -8px 20px rgba(255, 255, 255, 0.98),
        inset 0 1px 0 #ffffff;
    ```
- **Pressed State (`:active`)**:
    ```css
    color: #1d4ed8;
    background: linear-gradient(145deg, #edf4ff, #ffffff);
    box-shadow:
        inset 4px 4px 9px rgba(125, 158, 202, 0.18),
        inset -4px -4px 9px rgba(255, 255, 255, 0.95);
    ```

---

### 4.4 Form Controls (Textfields, Selects, Checkboxes)

#### A. Text Input & Select Box (`.landing-input`)

- **Shape**: `rounded-2xl` (`16px`) for auth forms, `rounded-xl` (`12px`) for search filters.
- **Height**: `h-11` (`44px`) or `h-10` (`40px`).
- **Border**: `1px solid #d9e6f7`.
- **Background**: `linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(231, 241, 255, 0.88))`.
- **Box Shadow Specification**:
    ```css
    box-shadow:
        inset 2px 2px 5px rgba(99, 137, 193, 0.12),
        inset -2px -2px 5px rgba(255, 255, 255, 0.95),
        0 3px 8px rgba(116, 155, 211, 0.1);
    ```
- **Focus State (`:focus`)**:
    - `border-color: #3b82f6`
    - `background: #ffffff`
    - `box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15)`

#### B. Form Label & Error Message

- **Label**: `block text-sm font-semibold text-slate-700 mb-1.5`
- **Error Text**: `mt-1.5 text-xs text-[#B42318] leading-relaxed`

---

### 4.5 Header Navigation Bar (`.landing-panel.rounded-full`)

- **Outer Wrapper**: Sticky header with `rounded-full`, glassmorphic blur `backdrop-filter: blur(12px)`, and clay elevation shadow.
- **Nav Group Container**: Inner rounded pill `border border-blue-100/60 bg-slate-100/70 p-1.5 shadow-inner`.
- **Active Navigation Pill**:
    ```html
    <a
        class="landing-button rounded-full bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-md"
        >Beranda</a
    >
    ```
- **Inactive Navigation Pill**:
    ```html
    <a
        class="landing-button landing-nav-muted rounded-full px-5 py-2 text-sm font-medium text-slate-600 opacity-60 transition hover:opacity-100 hover:text-blue-600"
        >Panduan</a
    >
    ```

---

## 5. Icons, Assets & Background Decorative Guidelines

1. **Icons**:
    - Use clean stroke-based SVG icons with `stroke-width="1.8"` or `2.0` and `stroke="currentColor"`.
    - Wrap interactive icon buttons inside `.landing-button` or `.clay-pressable` circular/pill containers.
2. **Branding Assets**:
    - **Logo**: `reksa-logo.webp` (rounded square with soft shadow).
    - **Mascot**: `reksa-mascot.webp` or `maskot-greetings.webp` placed above radial background glows.
3. **Background Overlays & Atmosphere**:
    - Canvas features fixed background images: `landing-cloud-overlay.webp` and `landing-background.webp`.
    - Background canvas class: `.landing-page`.
4. **Custom Cursor & Interactive Bubble Trail**:
    - Custom mouse pointers set in CSS: `main_cursor.png` (default), `link_cursor.png` (pointers), `text_cursor.png` (inputs).
    - JavaScript Bubble Trail: `.cursor-bubble-trail` elements rendered in body with multi-layer blue outer glow, spring physics, and sequential emerge/retract logic on mouse motion.

---

## 6. Summary Checklist for Building New Pages

When creating a new page or view component, verify that:

- [ ] The root canvas uses `class="landing-page min-h-screen font-sans text-slate-800 antialiased"`.
- [ ] Section containers use `.clay-card` or `.landing-panel` with `rounded-3xl` or `rounded-[2rem]`.
- [ ] Primary buttons use `.landing-button` with `bg-gradient-to-r from-blue-600 to-blue-500 rounded-full`.
- [ ] Secondary buttons use `.landing-button-secondary` or `.clay-pressable`.
- [ ] Text fields use `.landing-input` with proper focus ring classes.
- [ ] Inner card/stat sections use `.clay-inset` for sunken 3D depth.
- [ ] Font size and weight follow `Plus Jakarta Sans` typography standards.
