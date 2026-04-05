import React from 'react';
import ReactDOM from 'react-dom/client';
import AdminSidebar from './components/AdminSidebar';
import EstateManager from './components/estates/EstateManager';
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
if (document.getElementById('react-admin-estates')) {
    mountIsland('react-admin-estates', EstateManager, {});
}

// DistrictManager
const districtsEl = document.getElementById('react-admin-districts');
if (districtsEl) {
    const csrf = districtsEl.dataset.csrf ?? '';
    mountIsland('react-admin-districts', DistrictManager, { csrf });
}

// CommentManager
const commentsEl = document.getElementById('react-admin-comments');
if (commentsEl) {
    const csrf = commentsEl.dataset.csrf ?? '';
    mountIsland('react-admin-comments', CommentManager, { csrf });
}

// UserManager
const usersEl = document.getElementById('react-admin-users');
if (usersEl) {
    const csrf = usersEl.dataset.csrf ?? '';
    mountIsland('react-admin-users', UserManager, { csrf });
}

// MenuItemManager
const menuItemsEl = document.getElementById('react-admin-menu-items');
if (menuItemsEl) {
    const csrf = menuItemsEl.dataset.csrf ?? '';
    mountIsland('react-admin-menu-items', MenuItemManager, { csrf });
}
