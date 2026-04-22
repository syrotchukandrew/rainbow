import React from 'react';
import ReactDOM from 'react-dom/client';
import AdminSidebar from './components/AdminSidebar';
import EstateManager from './components/estates/EstateManager';
import CategoryManager from './components/categories/CategoryManager';
import DistrictManager from './components/districts/DistrictManager';
import CommentManager from './components/comments/CommentManager';
import UserManager from './components/users/UserManager';
import MenuItemManager from './components/menu-items/MenuItemManager';

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

// EstateManager
const estatesEl = document.getElementById('react-admin-estates');
if (estatesEl) {
    const labels = JSON.parse(estatesEl.dataset.labels ?? '{}');
    mountIsland('react-admin-estates', EstateManager, { labels });
}

// CategoryManager
const categoriesEl = document.getElementById('react-admin-categories');
if (categoriesEl) {
    const labels = JSON.parse(categoriesEl.dataset.labels ?? '{}');
    mountIsland('react-admin-categories', CategoryManager, { labels });
}

// DistrictManager
const districtsEl = document.getElementById('react-admin-districts');
if (districtsEl) {
    const csrf = districtsEl.dataset.csrf ?? '';
    const labels = JSON.parse(districtsEl.dataset.labels ?? '{}');
    mountIsland('react-admin-districts', DistrictManager, { csrf, labels });
}

// CommentManager
const commentsEl = document.getElementById('react-admin-comments');
if (commentsEl) {
    const csrf = commentsEl.dataset.csrf ?? '';
    const labels = JSON.parse(commentsEl.dataset.labels ?? '{}');
    mountIsland('react-admin-comments', CommentManager, { csrf, labels });
}

// UserManager
const usersEl = document.getElementById('react-admin-users');
if (usersEl) {
    const csrf = usersEl.dataset.csrf ?? '';
    const labels = JSON.parse(usersEl.dataset.labels ?? '{}');
    mountIsland('react-admin-users', UserManager, { csrf, labels });
}

// MenuItemManager
const menuItemsEl = document.getElementById('react-admin-menu-items');
if (menuItemsEl) {
    const csrf = menuItemsEl.dataset.csrf ?? '';
    const labels = JSON.parse(menuItemsEl.dataset.labels ?? '{}');
    mountIsland('react-admin-menu-items', MenuItemManager, { csrf, labels });
}
