import React, { useEffect, useState } from 'react';

interface District {
    id: number;
    title: string;
    slug: string;
}

interface EstateDetail {
    id: number;
    slug: string;
    title: string;
    price: number | null;
    floor: { floor: number; count_floor: number } | null;
    firstLastFloor: boolean | null;
    district: District | null;
}

interface Props {
    slug: string;
}

export default function EstateInfoPanel({ slug }: Props) {
    const [estate, setEstate] = useState<EstateDetail | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const controller = new AbortController();
        setLoading(true);
        setError(null);
        fetch(`/api/public/estates/${slug}`, { signal: controller.signal })
            .then(r => {
                if (!r.ok) throw new Error(String(r.status));
                return r.json();
            })
            .then((data: EstateDetail) => {
                setEstate(data);
                setLoading(false);
            })
            .catch((err: unknown) => {
                if (err instanceof Error && err.name === 'AbortError') return;
                setError('Помилка завантаження.');
                setLoading(false);
            });
        return () => controller.abort();
    }, [slug]);

    if (loading) return <p>Завантаження...</p>;
    if (error) return <p>{error}</p>;
    if (!estate) return null;

    return (
        <>
            <div className="mb-4 rounded border border-gray-200">
                <div className="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700">
                    Ціна
                </div>
                <div className="px-4 py-3 text-sm text-gray-800">
                    {estate.price !== null ? `${estate.price}\u00a0дол.` : '—'}
                </div>
            </div>
            {estate.district && (
                <div className="mb-4 rounded border border-gray-200">
                    <div className="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700">
                        Район
                    </div>
                    <div className="px-4 py-3 text-sm text-gray-800">
                        {estate.district.title}
                    </div>
                </div>
            )}
            {estate.floor && (
                <div className="mb-4 rounded border border-gray-200">
                    <div className="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700">
                        Поверх / Поверховість
                    </div>
                    <div className="px-4 py-3 text-sm text-gray-800">
                        {estate.floor.floor} / {estate.floor.count_floor}
                    </div>
                </div>
            )}
        </>
    );
}
