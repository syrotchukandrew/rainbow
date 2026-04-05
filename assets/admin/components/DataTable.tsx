import React, { useState } from 'react';

interface EstateRow {
    title: string;
    slug: string;
    category: string;
    price: number;
    createdAt: string;
    district: string;
    exclusive: boolean;
    showUrl: string;
    editUrl: string;
}

type SortKey = keyof Pick<EstateRow, 'title' | 'category' | 'price' | 'createdAt' | 'district'>;

interface Props {
    rows: EstateRow[];
}

export default function DataTable({ rows }: Props) {
    const [sortKey, setSortKey] = useState<SortKey>('createdAt');
    const [sortAsc, setSortAsc] = useState(false);

    function handleSort(key: SortKey) {
        if (key === sortKey) {
            setSortAsc(a => !a);
        } else {
            setSortKey(key);
            setSortAsc(true);
        }
    }

    const sorted = [...rows].sort((a, b) => {
        const av = a[sortKey];
        const bv = b[sortKey];
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
                    <tr key={row.slug}>
                        <td>{row.title}</td>
                        <td>{row.category}</td>
                        <td>{row.price}</td>
                        <td>{row.createdAt}</td>
                        <td>{row.district}</td>
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
