import React from 'react';
import ReactDOM from 'react-dom/client';

function mountIsland(
    id: string,
    Component: React.ComponentType,
): void {
    const el = document.getElementById(id);
    if (el) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <Component />
            </React.StrictMode>,
        );
    }
}

// Admin islands registered below — Phase 2 will populate this section
export { mountIsland };
