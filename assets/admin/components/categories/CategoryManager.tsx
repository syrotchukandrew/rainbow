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
        return <p className="px-4 py-6 text-sm text-gray-400">Завантаження...</p>;
    }

    return (
        <table className="w-full text-sm text-left border-collapse">
            <thead>
                <tr className="border-b border-gray-200 bg-gray-50">
                    <th className="px-4 py-3 font-medium text-gray-700">Назва</th>
                    <th className="px-4 py-3 font-medium text-gray-700 w-12">Вверх</th>
                    <th className="px-4 py-3 font-medium text-gray-700 w-12">Вниз</th>
                    <th className="px-4 py-3 font-medium text-gray-700 w-40">Дії</th>
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
                                    <img src="/images/admin/arrow-up.png" alt="Вверх" />
                                </a>
                            )}
                        </td>
                        <td className="px-4 py-3">
                            {row.downUrl && (
                                <a href={row.downUrl}>
                                    <img src="/images/admin/arrow-down.png" alt="Вниз" />
                                </a>
                            )}
                        </td>
                        <td className="px-4 py-3">
                            <div className="flex items-center gap-2">
                                <a href={row.deleteUrl} className="px-3 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200">Видалити</a>
                                <a href={row.editUrl} className="px-3 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Змінити</a>
                            </div>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
