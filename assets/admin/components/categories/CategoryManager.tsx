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

export default function CategoryManager() {
    const [rows, setRows] = useState<CategoryRow[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/admin/categories')
            .then(r => r.json())
            .then((data: CategoryRow[]) => { setRows(data); setLoading(false); });
    }, []);

    if (loading) {
        return <p>Завантаження...</p>;
    }

    return (
        <table className="table table">
            <thead>
                <tr>
                    <th>Назва</th>
                    <th>Вверх</th>
                    <th>Вниз</th>
                    <th><i className="fa fa-cogs" /> Дії</th>
                </tr>
            </thead>
            <tbody>
                {rows.map(row => (
                    <tr key={row.id}>
                        <td>
                            {'\u00a0'.repeat(row.lvl * 6)}{row.title}
                        </td>
                        <td>
                            {row.upUrl && (
                                <a href={row.upUrl}>
                                    <img src="/images/admin/arrow-up.png" alt="Вверх" />
                                </a>
                            )}
                        </td>
                        <td>
                            {row.downUrl && (
                                <a href={row.downUrl}>
                                    <img src="/images/admin/arrow-down.png" alt="Вниз" />
                                </a>
                            )}
                        </td>
                        <td>
                            <div className="item-actions">
                                <a href={row.deleteUrl} className="btn btn-sm btn-default">Видалити</a>
                                {' '}
                                <a href={row.editUrl} className="btn btn-sm btn-default">
                                    <i className="fa fa-edit" /> Змінити
                                </a>
                            </div>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
