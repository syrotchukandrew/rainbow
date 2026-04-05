import React, { useEffect, useState } from 'react';

interface CommentItem {
    id: number;
    content: string;
    createdBy: string | null;
    createdAt: string | null;
    enabled: boolean;
    estateId: number | null;
}

type TabStatus = 'pending' | 'published' | 'all';

interface Props {
    csrf: string;
}

export default function CommentManager({ csrf }: Props) {
    const [tab, setTab] = useState<TabStatus>('pending');
    const [comments, setComments] = useState<CommentItem[]>([]);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        setError(null);
        fetch(`/api/admin/comments?status=${tab}`)
            .then(r => r.json())
            .then(setComments)
            .catch(() => setError('Failed to load comments'));
    }, [tab]);

    function headers(): Record<string, string> {
        return { 'X-CSRF-Token': csrf };
    }

    async function handleApprove(id: number) {
        setError(null);
        const res = await fetch(`/api/admin/comments/${id}/approve`, {
            method: 'POST',
            headers: headers(),
        });
        if (!res.ok) {
            const data = await res.json();
            setError(data.error ?? 'Error');
            return;
        }
        setComments(prev => prev.filter(c => c.id !== id));
    }

    async function handleDelete(id: number) {
        if (!window.confirm('Видалити коментар?')) return;
        setError(null);
        const res = await fetch(`/api/admin/comments/${id}`, {
            method: 'DELETE',
            headers: headers(),
        });
        if (!res.ok) {
            const data = await res.json();
            setError(data.error ?? 'Error');
            return;
        }
        setComments(prev => prev.filter(c => c.id !== id));
    }

    const tabs: { key: TabStatus; label: string }[] = [
        { key: 'pending', label: 'Неопубліковані' },
        { key: 'published', label: 'Опубліковані' },
        { key: 'all', label: 'Всі' },
    ];

    return (
        <div>
            <ul className="nav nav-tabs" style={{ marginBottom: 16 }}>
                {tabs.map(t => (
                    <li key={t.key} className={tab === t.key ? 'active' : ''}>
                        <a href="#" onClick={e => { e.preventDefault(); setTab(t.key); }}>{t.label}</a>
                    </li>
                ))}
            </ul>

            {error && <div className="alert alert-danger">{error}</div>}

            <table className="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Контент</th>
                        <th>Автор</th>
                        <th>Дата</th>
                        <th><i className="fa fa-cogs" /></th>
                    </tr>
                </thead>
                <tbody>
                    {comments.length === 0 ? (
                        <tr><td colSpan={5} className="text-center text-muted">Немає коментарів</td></tr>
                    ) : comments.map(c => (
                        <tr key={c.id}>
                            <td>{c.id}</td>
                            <td>{c.content}</td>
                            <td>{c.createdBy}</td>
                            <td>{c.createdAt}</td>
                            <td>
                                <div className="item-actions">
                                    {!c.enabled && (
                                        <>
                                            <button
                                                className="btn btn-sm btn-default"
                                                onClick={() => handleApprove(c.id)}
                                            >
                                                Опублікувати
                                            </button>
                                            {' '}
                                        </>
                                    )}
                                    <button
                                        className="btn btn-sm btn-danger"
                                        onClick={() => handleDelete(c.id)}
                                    >
                                        <i className="fa fa-trash" /> Видалити
                                    </button>
                                </div>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
