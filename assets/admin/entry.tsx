import React from 'react';
import ReactDOM from 'react-dom/client';
import AdminSidebar from './components/AdminSidebar';
import DataTable from './components/DataTable';
import DistrictManager from './components/districts/DistrictManager';

function mountIsland<P extends object>(
    id: string,
    Component: React.ComponentType<P>,
    props: P,
): void {
    const el = document.getElementById(id);
    if (el) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <Component {...props} />
            </React.StrictMode>,
        );
    }
}

// AdminSidebar
const sidebarEl = document.getElementById('react-admin-sidebar');
if (sidebarEl) {
    const items = JSON.parse(sidebarEl.dataset.items ?? '[]');
    mountIsland('react-admin-sidebar', AdminSidebar, { items });
}

// DataTable
const dataEl = document.getElementById('react-estates-data');
if (dataEl) {
    const rows = JSON.parse(dataEl.textContent ?? '[]');
    mountIsland('react-admin-data-table', DataTable, { rows });
}

// DistrictManager
const districtsEl = document.getElementById('react-admin-districts');
if (districtsEl) {
    const csrf = districtsEl.dataset.csrf ?? '';
    mountIsland('react-admin-districts', DistrictManager, { csrf });
}
