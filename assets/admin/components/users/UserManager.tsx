import React, { useEffect, useState } from 'react';

interface UserItem {
    id: number;
    username: string;
    email: string;
    roles: string[];
    enabled: boolean;
    locked: boolean;
    lastLogin: string | null;
}

interface Props {
    csrf: string;
}

export default function UserManager({ csrf }: Props) {
    const [users, setUsers] = useState<UserItem[]>([]);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetch('/api/admin/users')
            .then(r => r.json())
            .then(setUsers)
            .catch(() => setError('Failed to load users'));
    }, []);

    function headers(): Record<string, string> {
        return { 'X-CSRF-Token': csrf };
    }

    async function post(url: string): Promise<UserItem | null> {
        setError(null);
        const res = await fetch(url, { method: 'POST', headers: headers() });
        const data = await res.json();
        if (!res.ok) {
            setError(data.error ?? 'Error');
            return null;
        }
        return data as UserItem;
    }

    function updateUser(updated: UserItem) {
        setUsers(prev => prev.map(u => u.username === updated.username ? { ...u, ...updated } : u));
    }

    async function handleLock(username: string) {
        const updated = await post(`/api/admin/users/${username}/lock`);
        if (updated) updateUser(updated);
    }

    async function handleUnlock(username: string) {
        const updated = await post(`/api/admin/users/${username}/unlock`);
        if (updated) updateUser(updated);
    }

    async function handleMakeManager(username: string) {
        const updated = await post(`/api/admin/users/${username}/make-manager`);
        if (updated) updateUser(updated);
    }

    async function handleMakeUser(username: string) {
        const updated = await post(`/api/admin/users/${username}/make-user`);
        if (updated) updateUser(updated);
    }

    return (
        <div>
            {error && (
                <div className="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            )}

            <table className="w-full text-sm text-left border-collapse">
                <thead>
                    <tr className="border-b border-gray-200 bg-gray-50">
                        <th className="px-4 py-3 font-medium text-gray-700">Ім'я користувача</th>
                        <th className="px-4 py-3 font-medium text-gray-700">Роль</th>
                        <th className="px-4 py-3 font-medium text-gray-700">Email</th>
                        <th className="px-4 py-3 font-medium text-gray-700">Останній вхід</th>
                        <th className="px-4 py-3 font-medium text-gray-700">Стан</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-40">Блокування</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-48">Роль менеджера</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-28">Об'єкти</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {users.map(u => (
                        <tr key={u.username} className="hover:bg-gray-50">
                            <td className="px-4 py-3 text-gray-900">{u.username}</td>
                            <td className="px-4 py-3 text-gray-700">{u.roles.includes('ROLE_MANAGER') ? 'Менеджер' : 'Користувач'}</td>
                            <td className="px-4 py-3 text-gray-700">{u.email}</td>
                            <td className="px-4 py-3 text-gray-500 whitespace-nowrap">{u.lastLogin ?? 'Немає входів'}</td>
                            <td className="px-4 py-3 text-gray-700">{u.locked ? 'Заблокований' : 'Активний'}</td>
                            <td className="px-4 py-3">
                                {u.locked ? (
                                    <button className="px-3 py-1 text-xs font-medium rounded bg-green-100 text-green-800 hover:bg-green-200" onClick={() => handleUnlock(u.username)}>
                                        Розблокувати
                                    </button>
                                ) : (
                                    <button className="px-3 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200" onClick={() => handleLock(u.username)}>
                                        Заблокувати
                                    </button>
                                )}
                            </td>
                            <td className="px-4 py-3">
                                {u.roles.includes('ROLE_MANAGER') ? (
                                    <button className="px-3 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200" onClick={() => handleMakeUser(u.username)}>
                                        Зробити користувачем
                                    </button>
                                ) : (
                                    <button className="px-3 py-1 text-xs font-medium rounded bg-green-100 text-green-800 hover:bg-green-200" onClick={() => handleMakeManager(u.username)}>
                                        Призначити менеджером
                                    </button>
                                )}
                            </td>
                            <td className="px-4 py-3">
                                {u.roles.includes('ROLE_MANAGER') ? (
                                    <a href={`/admin/estates/${u.username}`} className="px-3 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700 hover:bg-blue-200">
                                        Показати
                                    </a>
                                ) : '-'}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
