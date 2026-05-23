# 📐 Visual Reference Guide

## Form Structure & Layout

```
┌─────────────────────────────────────────────────────────────┐
│                                                               │
│  ▌ Dark slate-950 background with gradient accent overlay   │
│                                                               │
│              ┌───────────────────────────────────┐           │
│              │   backdrop-blur-xl               │           │
│              │   bg-white/10 border-white/20    │           │
│              │   rounded-[2.5rem]               │           │
│              │   box-shadow                     │           │
│              │                                   │           │
│              │   Welcome Back                    │ mb-8      │
│              │   Sign in to your account         │           │
│              │                                   │           │
│              ├───────────────────────────────────┤           │
│              │                                   │           │
│   space-y-6  │ Email Address              mb-2  │           │
│    between   │ [email@example.com input]  py-3  │ rounded-2xl
│    fields    │                            px-4  │ bg-white/5
│              │                                   │ border-white/10
│              │                                   │           │
│              │ Password                   mb-2  │           │
│              │ [••••••••••]  [👁 toggle]  py-3  │ rounded-2xl
│              │                            px-4  │ bg-white/5
│              │                                   │ border-white/10
│              │                                   │           │
│              │ ┌─────────────────────────────┐   │ space-y-6│
│              │ │ reCAPTCHA Checkbox         │   │ before   │
│              │ │ ☑ I'm not a robot         │   │ py-2     │
│              │ │ powered by Google Privacy   │   │ after    │
│              │ │                           │   │ py-2     │
│              │ └─────────────────────────────┘   │           │
│              │ (centered, 304px fixed width)    │           │
│              │                                   │           │
│              │ ☑ Remember me    [Forgot pwd?]   │           │
│              │                                   │           │
│              │ [Sign In Button]                  │ mt-8     │
│              │ bg-white text-slate-950          │ py-3     │
│              │ font-black shadow-lg              │ font-black
│              │                                   │           │
│              │ Don't have an account?            │           │
│              │ Sign up here                      │           │
│              │                                   │           │
│              └───────────────────────────────────┘           │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

## Color Palette Visual

```
┌─────────────────────────────────────────────────────┐
│ BACKGROUNDS                                         │
├─────────────────────────────────────────────────────┤
│                                                     │
│ bg-slate-950  ███████████████████████████████████  │
│ (#0f172a)     Page background, very dark          │
│                                                     │
│ backdrop-blur  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ │
│ + bg-white/10  Glass morphism card (blurred)      │
│                                                     │
│ bg-white/5    ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓  │
│               Input background (very subtle)     │
│                                                     │
├─────────────────────────────────────────────────────┤
│ TEXT COLORS                                         │
├─────────────────────────────────────────────────────┤
│                                                     │
│ text-white             Primary text (100%)        │
│ text-white/60          Secondary text (60%)       │
│ text-white/40          Tertiary text (40%)        │
│ text-slate-950         Button text (high contrast)│
│                                                     │
├─────────────────────────────────────────────────────┤
│ BORDER & FOCUS COLORS                              │
├─────────────────────────────────────────────────────┤
│                                                     │
│ border-white/20        Card border (subtle)       │
│ border-white/10        Input border (very subtle) │
│ ring-white/30          Focus ring (visible)       │
│ focus:bg-white/10      Focus background lift     │
│                                                     │
└─────────────────────────────────────────────────────┘
```

## Spacing System (Tailwind space-y utilities)

```
Form Structure:
───────────────────────────────────────
Heading Section
  ├─ Title: text-3xl font-black
  ├─ Subtitle: text-white/60 text-sm
  └─ Bottom margin: mb-8
───────────────────────────────────────

Form Group 1 (Email)
  ├─ Label: font-semibold text-white/90
  ├─ Top margin: space-y-2
  └─ Input: rounded-2xl, py-3, px-4
───────────────────────────────────────
                    ↕ space-y-6 gap
───────────────────────────────────────

Form Group 2 (Password)
  ├─ Label: font-semibold text-white/90
  ├─ Top margin: space-y-2
  └─ Input: rounded-2xl, py-3, px-4
───────────────────────────────────────
                    ↕ space-y-6 gap
───────────────────────────────────────

reCAPTCHA Section
  ├─ Top padding: py-2
  ├─ Fixed width: 304px
  ├─ Centered: flex justify-center
  └─ Responsive scaling at breakpoints
───────────────────────────────────────

Footer Controls
  ├─ Checkbox + Link: flex items-center
  └─ Bottom margin: text-sm
───────────────────────────────────────
                    ↕ mt-8 gap
───────────────────────────────────────

Submit Button
  ├─ Full width: w-full
  ├─ Padding: py-3, px-6
  ├─ Font: font-black, text-lg
  └─ Disabled state: opacity-50

───────────────────────────────────────
```

## Responsive Breakpoints Visual

```
Desktop (> 768px)          Tablet (768px - 380px)    Mobile (< 380px)
─────────────────────      ──────────────────────    ──────────────────

┌──────────────────────┐   ┌─────────────────────┐   ┌──────────────┐
│   ╔═══════════════╗  │   │  ╔─────────────────╗│   │  ╔────────╗  │
│   ║ Full Desktop  ║  │   │  ║ Tablet/Mobile   ║│   │  ║ Mobile ║  │
│   ║ Card 100%     ║  │   │  ║ Card ~100%      ║│   │  ║ Scaled ║  │
│   ║               ║  │   │  ║                 ║│   │  ║ 98%    ║  │
│   ║ Padding: 2.5  ║  │   │  ║ Padding: 2rem   ║│   │  ║ ~97%   ║  │
│   ║ rem (md)      ║  │   │  ║                 ║│   │  ║        ║  │
│   ║               ║  │   │  ║ reCAPTCHA       ║│   │  ║reCAPT: ║  │
│   ║ reCAPTCHA     ║  │   │  ║ 100% (no scale) ║│   │  ║98% 🔁  ║  │
│   ║ 100% 304px    ║  │   │  ║                 ║│   │  ║        ║  │
│   ║ (no scale)    ║  │   │  ╚─────────────────╝│   │  ╚────────╝  │
│   ║               ║  │   └─────────────────────┘   └──────────────┘
│   ╚═══════════════╝  │
│                      │
│   Max-width:         │
│   28rem (448px)      │
│                      │
└──────────────────────┘


Very Small (350px - 380px)  Ultra Small (< 320px)
────────────────────────     ──────────────────────

┌────────────────────┐       ┌──────────────┐
│   ╔──────────────╗ │       │ ╔──────────╗ │
│   ║ Card: 98%    ║ │       │ ║Card: 90% ║ │
│   ║              ║ │       │ ║          ║ │
│   ║ reCAPTCHA    ║ │       │ ║reCAPT:   ║ │
│   ║ 90% scale    ║ │       │ ║75% scale ║ │
│   ║ 273px (~273) ║ │       │ ║228px     ║ │
│   ║              ║ │       │ ║          ║ │
│   ╚──────────────╝ │       │ ╚──────────╝ │
│                    │       └──────────────┘
└────────────────────┘
```

## Input Field Details

```
Normal State:
┌────────────────────────────────────┐
│ you@example.com                    │
│ bg-white/5 border-white/10         │
│ rounded-2xl px-4 py-3              │
│ text-white placeholder-white/40    │
└────────────────────────────────────┘

Focus State:
┌─═─═─═─═─═─═─═─═─═─═─═─═─═─═─═─═─┐
│ you@example.com                    │
│ bg-white/10 (lifted)              │
│ border-white/30 (brighter)        │
│ ring: 2px white/30 + 4px white/5  │
│ ✨ Enhanced backdrop blur effect   │
└─═─═─═─═─═─═─═─═─═─═─═─═─═─═─═─═─┘

Error State:
┌────────────────────────────────────┐
│ you@example.com                    │
│ bg-red-500/20 border-red-500/30    │
│ rounded-2xl px-4 py-3              │
├─ Error message (text-red-400)      │
└────────────────────────────────────┘

Disabled State:
┌────────────────────────────────────┐
│ disabled                           │
│ opacity-50 cursor-not-allowed      │
│ bg-white/5 (faded)                │
│ Pointer events: none               │
└────────────────────────────────────┘
```

## Button States

```
Default:
┌─────────────────────────────────────┐
│         SIGN IN                     │
│  bg-white text-slate-950            │
│  font-black text-lg                 │
│  py-3 px-6 rounded-2xl              │
└─────────────────────────────────────┘

Hover:
┌─────────────────────────────────────┐ ↑ translateY(-2px)
│         SIGN IN                     │ ✨ shadow-lg white/20
│  bg-white (unchanged)               │
└─────────────────────────────────────┘

Active:
┌─────────────────────────────────────┐ ↓ scale(0.98)
│         SIGN IN                     │ Tactile feedback
│  bg-white (unchanged)               │
└─────────────────────────────────────┘

Disabled (reCAPTCHA pending):
┌─────────────────────────────────────┐
│         SIGN IN                     │
│  opacity-50 cursor-not-allowed      │
│  pointer-events-none                │
└─────────────────────────────────────┘

Loading:
┌─────────────────────────────────────┐
│  [spinner]  Signing in...           │
│  bg-white (button color stable)     │
│  animate-spin on icon               │
└─────────────────────────────────────┘
```

## reCAPTCHA Widget Positioning

```
Default (> 350px):
┌─────────────────────────────┐
│   [Email input]             │
│                             │
│   [Password input]          │
│                             │
│   ┌─────────────────────┐   │ 304px width
│   │ ☑ I'm not a robot  │   │ (Google standard)
│   │ powered by Google   │   │
│   │ Privacy - Terms     │   │
│   └─────────────────────┘   │
│   (centered, no scaling)    │
│                             │
│   [Sign In]                 │
└─────────────────────────────┘

Small Screens (350px - 380px):
┌──────────────────────────┐
│   [Email input]          │
│                          │
│   [Password input]       │
│                          │
│   ┌──────────────────┐   │ 273px
│   │ ☑ I'm not a... │   │ (90% of 304px)
│   │ powered by... │   │
│   └──────────────────┘   │
│   (transform: scale(0.9))│
│                          │
│   [Sign In]              │
└──────────────────────────┘

Ultra-Small (<320px):
┌─────────────────────┐
│  [Email input]      │
│                     │
│  [Password input]   │
│                     │
│  ┌────────────────┐ │ 228px
│  │ ☑ I'm not..   │ │ (75% of 304px)
│  │ powered...    │ │
│  └────────────────┘ │
│ (transform: scale75%)
│                     │
│  [Sign In]          │
└─────────────────────┘
```

## Animation Timings

```
Transition Durations:
├─ Fast (focus, hover):     150ms ease-in-out
├─ Normal (default):        200ms ease-in-out
├─ Slow (modals, alerts):   300ms ease-in-out
└─ Spinner:                 1s linear infinite

Keyframe Animations:
├─ slideUp:     0.3s ease-out (form entrance)
├─ fadeIn:      0.3s ease-in-out (general)
├─ spin:        1s linear infinite (loading)
└─ all use cubic-bezier(0.4, 0, 0.2, 1) when applicable
```

## Shadow Effects

```
Card Shadow:
box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1)
(Glass effect depth)

Button Hover Shadow:
box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2)
(White shadow for light button on dark bg)

Focus Ring Shadow:
box-shadow: 0 0 0 2px rgba(255,255,255,0.1),
            0 0 0 4px rgba(255,255,255,0.05)
(Double ring effect)
```

## Typography

```
Heading:
font-size: 1.875rem (30px)
font-weight: 900 (font-black)
line-height: 1
letter-spacing: -0.02em
color: text-white

Subheading:
font-size: 0.875rem (14px)
color: text-white/60

Label:
font-size: 0.875rem (14px)
font-weight: 600 (font-semibold)
color: text-white/90

Input:
font-size: 1rem (16px)
font-family: inherit
color: text-white

Button:
font-size: 1.125rem (18px)
font-weight: 900 (font-black)
color: text-slate-950 (on white bg)

Footer:
font-size: 0.875rem (14px)
color: text-white/60
```

## Accessibility Features

```
Semantic HTML:
<form>                      Form container
<label for="email">         Associated labels
<input id="email">          Unique IDs
<button type="submit">      Proper button types

Focus States:
:focus-visible {
  outline: 2px solid white/30
  outline-offset: 2px
}

Color Contrast:
White text on slate-950:    > 15:1 ratio ✓ WCAG AAA
White text on white/10:     > 4.5:1 ratio ✓ WCAG AA

Touch Targets:
min-height: 48px            (iOS guideline)
min-width: 48px             (WCAG guideline)

Reduced Motion Support:
@media (prefers-reduced-motion: reduce)
  - All animations disabled
  - Transitions set to 1ms
  - Smooth interactions preserved
```

---

**This visual guide helps understand the spatial relationships, scaling behavior, and styling of every element in the login form. Use it for design reviews, debugging, and customization.**
