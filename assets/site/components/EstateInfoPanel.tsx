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
        setLoading(true);
        setError(null);
        fetch(`/api/public/estates/${slug}`)
            .then(r => {
                if (!r.ok) throw new Error(String(r.status));
                return r.json();
            })
            .then((data: EstateDetail) => {
                setEstate(data);
                setLoading(false);
            })
            .catch(() => {
                setError('Помилка завантаження.');
                setLoading(false);
            });
    }, [slug]);

    if (loading) return <p>Завантаження...</p>;
    if (error) return <p>{error}</p>;
    if (!estate) return null;

    return (
        <>
            <div className="panel panel-default">
                <div className="panel-heading">
                    <i className="glyphicon glyphicon-stats" /> Ціна
                </div>
                <div className="panel-body">
                    {estate.price !== null ? `${estate.price}\u00a0дол.` : '—'}
                </div>
            </div>
            {estate.district && (
                <div className="panel panel-default">
                    <div className="panel-heading">
                        <i className="glyphicon glyphicon-stats" /> Район
                    </div>
                    <div className="panel-body">
                        {estate.district.title}
                    </div>
                </div>
            )}
            {estate.floor && (
                <div className="panel panel-default">
                    <div className="panel-heading">
                        <i className="glyphicon glyphicon-stats" /> Поверх / Поверховість
                    </div>
                    <div className="panel-body">
                        {estate.floor.floor} / {estate.floor.count_floor}
                    </div>
                </div>
            )}
        </>
    );
}
