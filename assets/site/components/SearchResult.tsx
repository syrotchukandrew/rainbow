import React, { useEffect, useState } from 'react';

interface EstateSummary {
    id: number;
    slug: string;
    title: string;
    description: string;
    price: number | null;
    primaryImageUrl: string | null;
    secondaryImageUrls: string[];
}

interface PagedResponse {
    data: EstateSummary[];
    total: number;
    page: number;
    perPage: number;
}

interface Labels {
    loading: string;
    error_loading: string;
    no_results: string;
    photo_alt: string;
    description_label: string;
    more: string;
}

interface Props {
    categorySlug: string;
    districtSlug: string;
    price: string;
    exceptFloor: string;
    locale: string;
    labels: Labels;
}

export default function SearchResult({ categorySlug, districtSlug, price, exceptFloor, locale, labels }: Props) {
    const [result, setResult] = useState<PagedResponse | null>(null);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        setPage(1);
    }, [categorySlug, districtSlug, price, exceptFloor]);

    useEffect(() => {
        const controller = new AbortController();
        setLoading(true);
        setError(null);
        const params = new URLSearchParams({ category: categorySlug, page: String(page) });
        if (districtSlug) params.set('district', districtSlug);
        if (price) params.set('price', price);
        if (exceptFloor === '1') params.set('except_floor', '1');

        fetch(`/api/public/search?${params.toString()}`, { signal: controller.signal })
            .then(r => {
                if (!r.ok) throw new Error(String(r.status));
                return r.json();
            })
            .then((data: PagedResponse) => {
                setResult(data);
                setLoading(false);
            })
            .catch((err: unknown) => {
                if (err instanceof Error && err.name === 'AbortError') return;
                setError(labels.error_loading);
                setLoading(false);
            });
        return () => controller.abort();
    }, [categorySlug, districtSlug, price, exceptFloor, page]);

    if (loading) return <p>{labels.loading}</p>;
    if (error) return <p>{error}</p>;
    if (!result || result.data.length === 0) return <p>{labels.no_results}</p>;

    const totalPages = Math.ceil(result.total / result.perPage);

    return (
        <div>
            {result.data.map(estate => (
                <div key={estate.id}>
                    <h2>
                        <a className="grey" href={`/${locale}/show_estate/${estate.slug}`}>{estate.title}</a>
                    </h2>
                    <div className="flex gap-4">
                        <div className="flex-[2] min-w-0">
                            {estate.primaryImageUrl && (
                                <a href={`/${locale}/show_estate/${estate.slug}`}>
                                    <img
                                        alt={labels.photo_alt}
                                        src={estate.primaryImageUrl}
                                        className="w-full h-auto"
                                    />
                                </a>
                            )}
                        </div>
                        <div className="flex-1 min-w-0">
                            {estate.secondaryImageUrls.map((url, i) => (
                                <React.Fragment key={i}>
                                    <a href={`/${locale}/show_estate/${estate.slug}`}>
                                        <img alt={labels.photo_alt} src={url} className="w-full h-auto" />
                                    </a>
                                    <hr />
                                </React.Fragment>
                            ))}
                        </div>
                    </div>
                    <h3>{labels.description_label}</h3>
                    <p>{estate.description}</p>
                    <a href={`/${locale}/show_estate/${estate.slug}`} className="inline-block px-4 py-2 text-sm font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">
                        {labels.more}
                    </a>
                    <hr />
                </div>
            ))}
            {totalPages > 1 && (
                <div className="flex flex-wrap gap-1 mt-4">
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
                        <button
                            key={p}
                            onClick={() => setPage(p)}
                            className={`px-3 py-1 text-sm font-medium rounded ${p === page ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`}
                        >
                            {p}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
