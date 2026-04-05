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

export default function EstateManager() {
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
            <th style={{ cursor: 'pointer' }} onClick={() => handleSort(key)}>
                {label}{arrow}
            </th>
        );
    }

    if (loading) {
        return <p>Завантаження...</p>;
    }

    return (
        <table className="table table-striped">
            <thead>
                <tr>
                    {th('Назва', 'title')}
                    {th('Категорія', 'category')}
                    {th('Ціна', 'price')}
                    {th('Створено', 'createdAt')}
                    {th('Район', 'district')}
                    <th>Ексклюзив</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                {sorted.map(row => (
                    <tr key={row.id}>
                        <td>{row.title}</td>
                        <td>{row.category ?? '—'}</td>
                        <td>{row.price}</td>
                        <td>{row.createdAt}</td>
                        <td>{row.district ?? '—'}</td>
                        <td>{row.exclusive ? 'Так' : '-'}</td>
                        <td>
                            <a href={row.showUrl} className="btn btn-sm btn-default">Показати</a>
                            {' '}
                            <a href={row.editUrl} className="btn btn-sm btn-default">
                                <i className="fa fa-edit" /> Змінити
                            </a>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
