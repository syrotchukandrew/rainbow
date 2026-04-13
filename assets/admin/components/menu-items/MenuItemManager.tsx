import React, { useEffect, useState } from 'react';

interface MenuItemData {
    id: number;
    title: string;
    description: string | null;
}

interface Props {
    csrf: string;
}

export default function MenuItemManager({ csrf }: Props) {
    const [items, setItems] = useState<MenuItemData[]>([]);
    const [error, setError] = useState<string | null>(null);
    const [newTitle, setNewTitle] = useState('');
    const [newDesc, setNewDesc] = useState('');
    const [editingId, setEditingId] = useState<number | null>(null);
    const [editTitle, setEditTitle] = useState('');
    const [editDesc, setEditDesc] = useState('');

    useEffect(() => {
        fetch('/api/admin/menu-items')
            .then(r => r.json())
            .then(setItems)
            .catch(() => setError('Failed to load menu items'));
    }, []);

    function headers(): Record<string, string> {
        return { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf };
    }

    async function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        setError(null);
        const res = await fetch('/api/admin/menu-items', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ title: newTitle, description: newDesc }),
        });
        const data = await res.json();
        if (!res.ok) {
            setError(data.error ?? 'Error');
            return;
        }
        setItems(prev => [...prev, data]);
        setNewTitle('');
        setNewDesc('');
    }

    function startEdit(item: MenuItemData) {
        setEditingId(item.id);
        setEditTitle(item.title);
        setEditDesc(item.description ?? '');
        setError(null);
    }

    async function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        setError(null);
        const res = await fetch(`/api/admin/menu-items/${id}`, {
            method: 'PUT',
            headers: headers(),
            body: JSON.stringify({ title: editTitle, description: editDesc }),
        });
        const data = await res.json();
        if (!res.ok) {
            setError(data.error ?? 'Error');
            return;
        }
        setItems(prev => prev.map(m => m.id === id ? data : m));
        setEditingId(null);
    }

    async function handleDelete(id: number) {
        if (!window.confirm('Видалити пункт меню?')) return;
        setError(null);
        const res = await fetch(`/api/admin/menu-items/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': csrf },
        });
        if (!res.ok) {
            const data = await res.json();
            setError(data.error ?? 'Error');
            return;
        }
        setItems(prev => prev.filter(m => m.id !== id));
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
                        <th className="px-4 py-3 font-medium text-gray-700 w-12">ID</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-48">Назва</th>
                        <th className="px-4 py-3 font-medium text-gray-700">Опис</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-48"></th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {items.map(m => (
                        <tr key={m.id} className="hover:bg-gray-50">
                            <td className="px-4 py-3 text-gray-500">{m.id}</td>
                            <td className="px-4 py-3 text-gray-900">
                                {editingId === m.id ? (
                                    <input
                                        className="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                        value={editTitle}
                                        onChange={e => setEditTitle(e.target.value)}
                                        autoFocus
                                    />
                                ) : m.title}
                            </td>
                            <td className="px-4 py-3 text-gray-700">
                                {editingId === m.id ? (
                                    <input
                                        className="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                        value={editDesc}
                                        onChange={e => setEditDesc(e.target.value)}
                                    />
                                ) : (m.description ?? '')}
                            </td>
                            <td className="px-4 py-3">
                                {editingId === m.id ? (
                                    <form onSubmit={e => handleUpdate(e, m.id)} className="flex items-center gap-2">
                                        <button type="submit" className="px-3 py-1 text-xs font-medium rounded bg-green-100 text-green-800 hover:bg-green-200">
                                            Зберегти
                                        </button>
                                        <button type="button" className="px-3 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200" onClick={() => setEditingId(null)}>
                                            Скасувати
                                        </button>
                                    </form>
                                ) : (
                                    <div className="flex items-center gap-2">
                                        <button className="px-3 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200" onClick={() => startEdit(m)}>
                                            Редагувати
                                        </button>
                                        <button className="px-3 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200" onClick={() => handleDelete(m.id)}>
                                            Видалити
                                        </button>
                                    </div>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <form onSubmit={handleCreate} className="mt-4 flex flex-wrap gap-2">
                <input
                    className="flex-1 min-w-48 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    placeholder="Назва"
                    value={newTitle}
                    onChange={e => setNewTitle(e.target.value)}
                />
                <input
                    className="flex-[2] min-w-72 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    placeholder="Опис"
                    value={newDesc}
                    onChange={e => setNewDesc(e.target.value)}
                />
                <button type="submit" className="px-4 py-1.5 text-sm font-medium rounded bg-green-100 text-green-800 hover:bg-green-200">
                    Додати
                </button>
            </form>
        </div>
    );
}
