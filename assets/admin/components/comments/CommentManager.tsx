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

interface Labels {
    tab_pending: string;
    tab_published: string;
    tab_all: string;
    content: string;
    author: string;
    date: string;
    no_comments: string;
    publish: string;
    delete: string;
    confirm_delete: string;
}

interface Props {
    csrf: string;
    labels: Labels;
}

export default function CommentManager({ csrf, labels }: Props) {
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
        if (!window.confirm(labels.confirm_delete)) return;
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
        { key: 'pending', label: labels.tab_pending },
        { key: 'published', label: labels.tab_published },
        { key: 'all', label: labels.tab_all },
    ];

    return (
        <div>
            <div className="flex border-b border-gray-200 mb-4">
                {tabs.map(t => (
                    <button
                        key={t.key}
                        onClick={() => setTab(t.key)}
                        className={`px-4 py-2 text-sm font-medium -mb-px border-b-2 ${
                            tab === t.key
                                ? 'border-gray-900 text-gray-900'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        }`}
                    >
                        {t.label}
                    </button>
                ))}
            </div>

            {error && (
                <div className="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            )}

            <table className="w-full text-sm text-left border-collapse">
                <thead>
                    <tr className="border-b border-gray-200 bg-gray-50">
                        <th className="px-4 py-3 font-medium text-gray-700 w-12">ID</th>
                        <th className="px-4 py-3 font-medium text-gray-700">{labels.content}</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-36">{labels.author}</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-36">{labels.date}</th>
                        <th className="px-4 py-3 font-medium text-gray-700 w-40"></th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {comments.length === 0 ? (
                        <tr>
                            <td colSpan={5} className="px-4 py-6 text-center text-gray-400">
                                {labels.no_comments}
                            </td>
                        </tr>
                    ) : comments.map(c => (
                        <tr key={c.id} className="hover:bg-gray-50">
                            <td className="px-4 py-3 text-gray-500">{c.id}</td>
                            <td className="px-4 py-3 text-gray-900">{c.content}</td>
                            <td className="px-4 py-3 text-gray-700">{c.createdBy}</td>
                            <td className="px-4 py-3 text-gray-500 whitespace-nowrap">{c.createdAt}</td>
                            <td className="px-4 py-3">
                                <div className="flex items-center gap-2">
                                    {!c.enabled && (
                                        <button
                                            className="px-3 py-1 text-xs font-medium rounded bg-green-100 text-green-800 hover:bg-green-200"
                                            onClick={() => handleApprove(c.id)}
                                        >
                                            {labels.publish}
                                        </button>
                                    )}
                                    <button
                                        className="px-3 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200"
                                        onClick={() => handleDelete(c.id)}
                                    >
                                        {labels.delete}
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
