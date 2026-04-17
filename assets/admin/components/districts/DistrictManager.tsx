import React, { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface District {
    id: number;
    slug: string;
    title: string;
}

interface Labels {
    title: string;
    slug: string;
    save: string;
    cancel: string;
    edit: string;
    delete: string;
    confirm_delete: string;
    placeholder: string;
    add: string;
}

interface Props {
    csrf: string;
    labels: Labels;
}

export default function DistrictManager({ csrf, labels }: Props) {
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
        setCreating(true);
        const res = await fetch('/api/admin/districts', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ title: newTitle }),
        });
        const data = await res.json();
        setCreating(false);
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
        if (!window.confirm(labels.confirm_delete)) return;
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
            {error && (
                <div className="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            )}

            <table className="w-full text-sm text-left border-collapse">
                <thead>
                    <tr className="border-b border-gray-200 bg-gray-50">
                        <th className="px-4 py-3 font-medium text-gray-700 w-12">ID</th>
                        <th className="px-4 py-3 font-medium text-gray-700">{labels.title}</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-40">{labels.slug}</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-48"></th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {districts.map(d => (
                        <tr key={d.slug} className="hover:bg-gray-50">
                            <td className="px-4 py-3 text-gray-500">{d.id}</td>
                            <td className="px-4 py-3 text-gray-900">
                                {editingSlug === d.slug ? (
                                    <form onSubmit={e => handleUpdate(e, d.slug)} className="flex items-center gap-2">
                                        <Input
                                            className="flex-1 h-9"
                                            value={editTitle}
                                            onChange={e => setEditTitle(e.target.value)}
                                            autoFocus
                                        />
                                        <Button type="submit" variant="success" size="sm">{labels.save}</Button>
                                        <Button type="button" variant="secondary" size="sm" onClick={() => setEditingSlug(null)}>{labels.cancel}</Button>
                                    </form>
                                ) : d.title}
                            </td>
                            <td className="px-4 py-3 text-gray-500">{d.slug}</td>
                            <td className="px-4 py-3">
                                {editingSlug !== d.slug && (
                                    <div className="flex items-center gap-2">
                                        <Button type="button" variant="secondary" size="sm" onClick={() => startEdit(d)}>
                                            {labels.edit}
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm" onClick={() => handleDelete(d.slug)}>
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
                    placeholder={labels.placeholder}
                    value={newTitle}
                    onChange={e => setNewTitle(e.target.value)}
                />
                <Button type="submit" variant="success" disabled={creating}>
                    {labels.add}
                </Button>
            </form>
        </div>
    );
}
