import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

createInertiaApp({
  title: (title) => title ? `${title} · SPMB Lenterahati IBS` : 'SPMB Lenterahati IBS',
  resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx') as any) as any,
  setup({ el, App, props }: any) { createRoot(el!).render(<App {...props} />); },
  progress: { color: '#0077C8' },
});
