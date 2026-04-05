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
            {error && <div className="alert alert-danger">{error}</div>}
            <table className="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Назва</th>
                        <th>Опис</th>
                        <th><i className="fa fa-cogs" /></th>
                    </tr>
                </thead>
                <tbody>
                    {items.map(m => (
                        <tr key={m.id}>
                            <td>{m.id}</td>
                            <td>
                                {editingId === m.id ? (
                                    <input
                                        className="form-control input-sm"
                                        value={editTitle}
                                        onChange={e => setEditTitle(e.target.value)}
                                        autoFocus
                                    />
                                ) : m.title}
                            </td>
                            <td>
                                {editingId === m.id ? (
                                    <input
                                        className="form-control input-sm"
                                        value={editDesc}
                                        onChange={e => setEditDesc(e.target.value)}
                                    />
                                ) : (m.description ?? '')}
                            </td>
                            <td>
                                {editingId === m.id ? (
                                    <form onSubmit={e => handleUpdate(e, m.id)} style={{ display: 'flex', gap: 4 }}>
                                        <button type="submit" className="btn btn-sm btn-success">Зберегти</button>
                                        <button type="button" className="btn btn-sm btn-default" onClick={() => setEditingId(null)}>Скасувати</button>
                                    </form>
                                ) : (
                                    <div className="item-actions">
                                        <button className="btn btn-sm btn-default" onClick={() => startEdit(m)}>
                                            <i className="fa fa-edit" /> Редагувати
                                        </button>
                                        {' '}
                                        <button className="btn btn-sm btn-danger" onClick={() => handleDelete(m.id)}>
                                            <i className="fa fa-trash" /> Видалити
                                        </button>
                                    </div>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            <form onSubmit={handleCreate} style={{ marginTop: 16, display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                <input
                    className="form-control"
                    placeholder="Назва"
                    value={newTitle}
                    onChange={e => setNewTitle(e.target.value)}
                    style={{ flex: '1 1 200px' }}
                />
                <input
                    className="form-control"
                    placeholder="Опис"
                    value={newDesc}
                    onChange={e => setNewDesc(e.target.value)}
                    style={{ flex: '2 1 300px' }}
                />
                <button type="submit" className="btn btn-success">
                    <i className="fa fa-plus" /> Додати
                </button>
            </form>
        </div>
    );
}
