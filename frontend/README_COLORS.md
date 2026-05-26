# Color Palette & UI Components

This file documents the new color palette and example UI components added to make actions and sidebar easy to see and accessible.

## What was added

- Tailwind color tokens in `tailwind.config.js`: `action.*` and `sidebar.*`.
- CSS variables in `src/styles/colors.css` for non-Tailwind usage.
- Updated `Button` component in `src/components/UI/Button.tsx` with new variants: `add`, `edit`, `delete`, `view`, `info`, `neutral`.
- New `Sidebar` component in `src/components/UI/Sidebar.tsx` showing how to use sidebar colors.

## Usage examples

Import and use the Button component:

```tsx
import { Button } from './components/UI/Button';

<Button variant="add">Add</Button>
<Button variant="edit">Edit</Button>
<Button variant="delete">Delete</Button>
<Button variant="view">View</Button>
<Button variant="info">Info</Button>
```

Use the Sidebar in a layout:

```tsx
import Sidebar from './components/UI/Sidebar';

function AppLayout() {
  return (
    <div className="flex">
      <Sidebar />
      <main className="flex-1 p-6">{/* ... */}</main>
    </div>
  );
}
```

## Rebuild

After editing Tailwind config, rebuild the frontend dev server:

```bash
cd frontend
npm install
npm run dev
# or build for production
npm run build
```

That's it — the colors were chosen for clarity and contrast. If you'd like adjusted hues (darker/lighter), I can tweak them.