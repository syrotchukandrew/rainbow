import React, { useEffect, useState } from 'react';

interface CategoryRow {
    id: number;
    slug: string;
    title: string;
    lvl: number;
    upUrl: string | null;
    downUrl: string | null;
    editUrl: string;
    deleteUrl: string;
}

interface Labels {
    loading: string;
    error_loading: string;
    title: string;
    up: string;
    down: string;
    action: string;
    delete: string;
    change: string;
}

interface Props {
    labels: Labels;
}

export default function CategoryManager({ labels }: Props) {
    const [rows, setRows] = useState<CategoryRow[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        setError(null);
        fetch('/api/admin/categories')
            .then(r => {
                if (!r.ok) throw new Error(String(r.status));
                return r.json();
            })
            .then((data: CategoryRow[]) => { setRows(data); setLoading(false); })
            .catch(() => { setError(labels.error_loading); setLoading(false); });
    }, []);

    if (loading) {
        return <p className="px-4 py-6 text-sm text-gray-400">{labels.loading}</p>;
    }

    if (error) {
        return <div className="p-3 rounded bg-red-50 border border-red-200 text-sm text-red-700">{error}</div>;
    }

    return (
        <table className="w-full text-sm text-left border-collapse">
            <thead>
                <tr className="border-b border-gray-200 bg-gray-50">
                    <th className="px-4 py-3 font-medium text-gray-700">{labels.title}</th>
                    <th className="px-4 py-3 font-medium text-gray-700 w-12">{labels.up}</th>
                    <th className="px-4 py-3 font-medium text-gray-700 w-12">{labels.down}</th>
                    <th className="px-4 py-3 font-medium text-gray-700 w-40">{labels.action}</th>
                </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
                {rows.map(row => (
                    <tr key={row.id} className="hover:bg-gray-50">
                        <td className="px-4 py-3 text-gray-900">
                            {'\u00a0'.repeat(row.lvl * 6)}{row.title}
                        </td>
                        <td className="px-4 py-3">
                            {row.upUrl && (
                                <a href={row.upUrl}>
                                    <img src="/images/admin/arrow-up.png" alt={labels.up} />
                                </a>
                            )}
                        </td>
                        <td className="px-4 py-3">
                            {row.downUrl && (
                                <a href={row.downUrl}>
                                    <img src="/images/admin/arrow-down.png" alt={labels.down} />
                                </a>
                            )}
                        </td>
                        <td className="px-4 py-3">
                            <div className="flex items-center gap-2">
                                <a href={row.deleteUrl} className="px-3 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200">{labels.delete}</a>
                                <a href={row.editUrl} className="px-3 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">{labels.change}</a>
                            </div>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
