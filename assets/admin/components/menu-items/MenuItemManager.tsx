import React, { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface MenuItemData {
    id: number;
    title: string;
    description: string | null;
}

interface Labels {
    title: string;
    description: string;
    save: string;
    cancel: string;
    edit: string;
    delete: string;
    confirm_delete: string;
    add: string;
    placeholder_title: string;
    placeholder_desc: string;
}

interface Props {
    csrf: string;
    labels: Labels;
}

export default function MenuItemManager({ csrf, labels }: Props) {
    const [items, setItems] = useState<MenuItemData[]>([]);
    const [error, setError] = useState<string | null>(null);
    const [newTitle, setNewTitle] = useState('');
    const [newDesc, setNewDesc] = useState('');
    const [editingId, setEditingId] = useState<number | null>(null);
    const [editTitle, setEditTitle] = useState('');
    const [editDesc, setEditDesc] = useState('');
    const [creating, setCreating] = useState(false);
    const [updating, setUpdating] = useState(false);

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
        if (creating) return;
        setCreating(true);
        setError(null);
        try {
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
        } finally {
            setCreating(false);
        }
    }

    function startEdit(item: MenuItemData) {
        setEditingId(item.id);
        setEditTitle(item.title);
        setEditDesc(item.description ?? '');
        setError(null);
    }

    async function handleUpdate(id: number) {
        if (updating) return;
        setUpdating(true);
        setError(null);
        try {
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
        } finally {
            setUpdating(false);
        }
    }

    async function handleDelete(id: number) {
        if (!window.confirm(labels.confirm_delete)) return;
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
                        <th className="px-4 py-3 font-medium text-gray-700 w-48">{labels.title}</th>
                        <th className="px-4 py-3 font-medium text-gray-700">{labels.description}</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-48"></th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {items.map(m => (
                        <tr key={m.id} className="hover:bg-gray-50">
                            <td className="px-4 py-3 text-gray-500">{m.id}</td>
                            <td className="px-4 py-3 text-gray-900">
                                {editingId === m.id ? (
                                    <Input
                                        className="h-9"
                                        value={editTitle}
                                        onChange={e => setEditTitle(e.target.value)}
                                        onKeyDown={e => { if (e.key === 'Enter') handleUpdate(m.id); }}
                                        autoFocus
                                    />
                                ) : m.title}
                            </td>
                            <td className="px-4 py-3 text-gray-700">
                                {editingId === m.id ? (
                                    <Input
                                        className="h-9"
                                        value={editDesc}
                                        onChange={e => setEditDesc(e.target.value)}
                                        onKeyDown={e => { if (e.key === 'Enter') handleUpdate(m.id); }}
                                    />
                                ) : (m.description ?? '')}
                            </td>
                            <td className="px-4 py-3">
                                {editingId === m.id ? (
                                    <div className="flex items-center gap-2">
                                        <Button type="button" variant="success" size="sm" onClick={() => handleUpdate(m.id)} disabled={updating}>
                                            {labels.save}
                                        </Button>
                                        <Button type="button" variant="secondary" size="sm" onClick={() => setEditingId(null)}>
                                            {labels.cancel}
                                        </Button>
                                    </div>
                                ) : (
                                    <div className="flex items-center gap-2">
                                        <Button type="button" variant="secondary" size="sm" onClick={() => startEdit(m)}>
                                            {labels.edit}
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm" onClick={() => handleDelete(m.id)}>
                                            {labels.delete}
                                        </Button>
                                    </div>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <form onSubmit={handleCreate} className="mt-4 flex flex-wrap gap-2">
                <Input
                    className="flex-1 min-w-48"
                    placeholder={labels.placeholder_title}
                    value={newTitle}
                    onChange={e => setNewTitle(e.target.value)}
                />
                <Input
                    className="flex-[2] min-w-72"
                    placeholder={labels.placeholder_desc}
                    value={newDesc}
                    onChange={e => setNewDesc(e.target.value)}
                />
                <Button type="submit" variant="success" disabled={creating}>
                    {labels.add}
                </Button>
            </form>
        </div>
    );
}
