import React from 'react';
import ReactDOM from 'react-dom/client';
import HelloIsland from './components/HelloIsland';

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

mountIsland('react-hello-island', HelloIsland);
