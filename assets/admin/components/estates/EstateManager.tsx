import React, { useEffect, useState } from 'react';

interface EstateRow {
    id: number;
    title: string;
    slug: string;
    category: string | null;
    price: number;
    createdAt: string;
    district: string | null;
    exclusive: boolean;
    showUrl: string;
    editUrl: string;
}

type SortKey = keyof Pick<EstateRow, 'title' | 'category' | 'price' | 'createdAt' | 'district'>;

interface Labels {
    loading: string;
    title: string;
    category: string;
    price: string;
    created_at: string;
    district: string;
    exclusive: string;
    action: string;
    yes: string;
    show: string;
    change: string;
}

interface Props {
    labels: Labels;
}

export default function EstateManager({ labels }: Props) {
    const [rows, setRows] = useState<EstateRow[]>([]);
    const [loading, setLoading] = useState(true);
    const [sortKey, setSortKey] = useState<SortKey>('createdAt');
    const [sortAsc, setSortAsc] = useState(false);

    useEffect(() => {
        fetch('/api/admin/estates')
            .then(r => r.json())
            .then((data: EstateRow[]) => { setRows(data); setLoading(false); });
    }, []);

    function handleSort(key: SortKey) {
        if (key === sortKey) {
            setSortAsc(a => !a);
        } else {
            setSortKey(key);
            setSortAsc(true);
        }
    }

    const sorted = [...rows].sort((a, b) => {
        const av = a[sortKey] ?? '';
        const bv = b[sortKey] ?? '';
        const cmp = typeof av === 'number' && typeof bv === 'number'
            ? av - bv
            : String(av).localeCompare(String(bv));
        return sortAsc ? cmp : -cmp;
    });

    function th(label: string, key: SortKey) {
        const arrow = sortKey === key ? (sortAsc ? ' ▲' : ' ▼') : '';
        return (
            <th
                className="px-4 py-3 font-medium text-gray-700 cursor-pointer select-none hover:text-gray-900"
                onClick={() => handleSort(key)}
            >
                {label}{arrow}
            </th>
        );
    }

    if (loading) {
        return <p className="px-4 py-6 text-sm text-gray-400">{labels.loading}</p>;
    }

    return (
        <table className="w-full text-sm text-left border-collapse">
            <thead>
                <tr className="border-b border-gray-200 bg-gray-50">
                    {th(labels.title, 'title')}
                    {th(labels.category, 'category')}
                    {th(labels.price, 'price')}
                    {th(labels.created_at, 'createdAt')}
                    {th(labels.district, 'district')}
                    <th className="px-4 py-3 font-medium text-gray-700">{labels.exclusive}</th>
                    <th className="px-4 py-3 font-medium text-gray-700 w-40">{labels.action}</th>
                </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
                {sorted.map(row => (
                    <tr key={row.id} className="hover:bg-gray-50">
                        <td className="px-4 py-3 text-gray-900">{row.title}</td>
                        <td className="px-4 py-3 text-gray-700">{row.category ?? '—'}</td>
                        <td className="px-4 py-3 text-gray-700">{row.price}</td>
                        <td className="px-4 py-3 text-gray-500 whitespace-nowrap">{row.createdAt}</td>
                        <td className="px-4 py-3 text-gray-700">{row.district ?? '—'}</td>
                        <td className="px-4 py-3 text-gray-700">{row.exclusive ? labels.yes : '-'}</td>
                        <td className="px-4 py-3">
                            <div className="flex items-center gap-2">
                                <a href={row.showUrl} className="px-3 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">{labels.show}</a>
                                <a href={row.editUrl} className="px-3 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">{labels.change}</a>
                            </div>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
