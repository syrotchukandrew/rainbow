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
        <nav aria-label="Admin navigation">
            <ul className="space-y-0.5 px-2">
                {items.map(item => (
                    <li key={item.route}>
                        <a
                            href={item.route}
                            className={`flex items-center justify-between px-3 py-2 rounded text-sm font-medium transition-colors ${
                                current === item.route
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            }`}
                        >
                            <span>{item.label}</span>
                            {item.badgeUrl && <CommentBadge url={item.badgeUrl} />}
                        </a>
                    </li>
                ))}
            </ul>
        </nav>
    );
}
