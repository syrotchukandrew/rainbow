import React, { useEffect, useState } from 'react';

interface District {
    id: number;
    slug: string;
    title: string;
}

interface Props {
    csrf: string;
}

export default function DistrictManager({ csrf }: Props) {
    const [districts, setDistricts] = useState<District[]>([]);
    const [error, setError] = useState<string | null>(null);
    const [newTitle, setNewTitle] = useState('');
    const [creating, setCreating] = useState(false);
    const [editingSlug, setEditingSlug] = useState<string | null>(null);
    const [editTitle, setEditTitle] = useState('');

    useEffect(() => {
        fetch('/api/admin/districts')
            .then(r => r.json())
            .then(setDistricts)
            .catch(() => setError('Failed to load districts'));
    }, []);

    function headers(): Record<string, string> {
        return { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf };
    }

    async function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        setError(null);
        const res = await fetch('/api/admin/districts', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ title: newTitle }),
        });
        const data = await res.json();
        if (!res.ok) {
            setError(data.error ?? 'Error');
            return;
        }
        setDistricts(prev => [...prev, data]);
        setNewTitle('');
    }

    function startEdit(district: District) {
        setEditingSlug(district.slug);
        setEditTitle(district.title);
        setError(null);
    }

    async function handleUpdate(e: React.FormEvent, slug: string) {
        e.preventDefault();
        setError(null);
        const res = await fetch(`/api/admin/districts/${slug}`, {
            method: 'PUT',
            headers: headers(),
            body: JSON.stringify({ title: editTitle }),
        });
        const data = await res.json();
        if (!res.ok) {
            setError(data.error ?? 'Error');
            return;
        }
        setDistricts(prev => prev.map(d => d.slug === slug ? data : d));
        setEditingSlug(null);
    }

    async function handleDelete(slug: string) {
        if (!window.confirm('Видалити район?')) return;
        setError(null);
        const res = await fetch(`/api/admin/districts/${slug}`, {
            method: 'DELETE',
            headers: headers(),
        });
        if (!res.ok) {
            const data = await res.json();
            setError(data.error ?? 'Error');
            return;
        }
        setDistricts(prev => prev.filter(d => d.slug !== slug));
    }

    return (
        <div>
            {error && <div className="alert alert-danger">{error}</div>}
            <table className="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Назва</th>
                        <th>Псевдонім</th>
                        <th><i className="fa fa-cogs" /></th>
                    </tr>
                </thead>
                <tbody>
                    {districts.map(d => (
                        <tr key={d.slug}>
                            <td>{d.id}</td>
                            <td>
                                {editingSlug === d.slug ? (
                                    <form onSubmit={e => handleUpdate(e, d.slug)} style={{ display: 'flex', gap: 4 }}>
                                        <input
                                            className="form-control input-sm"
                                            value={editTitle}
                                            onChange={e => setEditTitle(e.target.value)}
                                            autoFocus
                                        />
                                        <button type="submit" className="btn btn-sm btn-success">Зберегти</button>
                                        <button type="button" className="btn btn-sm btn-default" onClick={() => setEditingSlug(null)}>Скасувати</button>
                                    </form>
                                ) : d.title}
                            </td>
                            <td>{d.slug}</td>
                            <td>
                                {editingSlug !== d.slug && (
                                    <div className="item-actions">
                                        <button className="btn btn-sm btn-default" onClick={() => startEdit(d)}>
                                            <i className="fa fa-edit" /> Редагувати
                                        </button>
                                        {' '}
                                        <button className="btn btn-sm btn-danger" onClick={() => handleDelete(d.slug)}>
                                            <i className="fa fa-trash" /> Видалити
                                        </button>
                                    </div>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            <form onSubmit={handleCreate} className="form-inline" style={{ marginTop: 16 }}>
                <input
                    className="form-control"
                    placeholder="Назва нового району"
                    value={newTitle}
                    onChange={e => setNewTitle(e.target.value)}
                    style={{ marginRight: 8 }}
                />
                <button type="submit" className="btn btn-success" disabled={creating}>
                    <i className="fa fa-plus" /> Додати
                </button>
            </form>
        </div>
    );
}
