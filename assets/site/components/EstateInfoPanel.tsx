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

interface Labels {
    loading: string;
    error_loading_short: string;
    price: string;
    district: string;
    floor_label: string;
}

interface Props {
    slug: string;
    labels: Labels;
}

export default function EstateInfoPanel({ slug, labels }: Props) {
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
                setError(labels.error_loading_short);
                setLoading(false);
            });
        return () => controller.abort();
    }, [slug]);

    if (loading) return <p>{labels.loading}</p>;
    if (error) return <p>{error}</p>;
    if (!estate) return null;

    return (
        <>
            <div className="mb-4 rounded border border-gray-200">
                <div className="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700">
                    {labels.price}
                </div>
                <div className="px-4 py-3 text-sm text-gray-800">
                    {estate.price !== null ? `${estate.price}\u00a0дол.` : '—'}
                </div>
            </div>
            {estate.district && (
                <div className="mb-4 rounded border border-gray-200">
                    <div className="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700">
                        {labels.district}
                    </div>
                    <div className="px-4 py-3 text-sm text-gray-800">
                        {estate.district.title}
                    </div>
                </div>
            )}
            {estate.floor && (
                <div className="mb-4 rounded border border-gray-200">
                    <div className="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700">
                        {labels.floor_label}
                    </div>
                    <div className="px-4 py-3 text-sm text-gray-800">
                        {estate.floor.floor} / {estate.floor.count_floor}
                    </div>
                </div>
            )}
        </>
    );
}
