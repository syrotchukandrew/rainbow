import React from 'react';
import CommentBadge from './CommentBadge';

interface NavItem {
    route: string;
    label: string;
    badgeUrl?: string;
}

interface Props {
    items: NavItem[];
}

export default function AdminSidebar({ items }: Props) {
    const current = window.location.pathname;

    return (
        <ul className="nav" id="side-menu">
            {items.map(item => (
                <li key={item.route} className={current === item.route ? 'active' : ''}>
                    <a href={item.route}>
                        {item.label}
                        {item.badgeUrl && <CommentBadge url={item.badgeUrl} />}
                    </a>
                </li>
            ))}
        </ul>
    );
}
