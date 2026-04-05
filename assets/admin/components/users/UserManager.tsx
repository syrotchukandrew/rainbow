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
            {error && <div className="alert alert-danger">{error}</div>}
            <table className="table table-striped">
                <thead>
                    <tr>
                        <th>Ім'я користувача</th>
                        <th>Роль</th>
                        <th>Email</th>
                        <th>Останній вхід</th>
                        <th>Стан</th>
                        <th>Блокування</th>
                        <th>Роль менеджера</th>
                        <th>Об'єкти</th>
                    </tr>
                </thead>
                <tbody>
                    {users.map(u => (
                        <tr key={u.username}>
                            <td>{u.username}</td>
                            <td>{u.roles.includes('ROLE_MANAGER') ? 'Менеджер' : 'Користувач'}</td>
                            <td>{u.email}</td>
                            <td>{u.lastLogin ?? 'Немає входів'}</td>
                            <td>{u.locked ? 'Заблокований' : 'Активний'}</td>
                            <td>
                                {u.locked ? (
                                    <button className="btn btn-sm btn-success" onClick={() => handleUnlock(u.username)}>
                                        <i className="fa fa-edit" /> Розблокувати
                                    </button>
                                ) : (
                                    <button className="btn btn-sm btn-danger" onClick={() => handleLock(u.username)}>
                                        <i className="fa fa-edit" /> Заблокувати
                                    </button>
                                )}
                            </td>
                            <td>
                                {u.roles.includes('ROLE_MANAGER') ? (
                                    <button className="btn btn-sm btn-danger" onClick={() => handleMakeUser(u.username)}>
                                        <i className="fa fa-edit" /> Зробити користувачем
                                    </button>
                                ) : (
                                    <button className="btn btn-sm btn-success" onClick={() => handleMakeManager(u.username)}>
                                        <i className="fa fa-edit" /> Призначити менеджером
                                    </button>
                                )}
                            </td>
                            <td>
                                {u.roles.includes('ROLE_MANAGER') ? (
                                    <a href={`/admin/estates/${u.username}`} className="btn btn-sm btn-primary">
                                        <i className="fa fa-edit" /> Показати
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
