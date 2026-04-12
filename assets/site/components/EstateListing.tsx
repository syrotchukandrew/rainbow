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

interface Props {
    apiUrl: string;
    locale: string;
}

export default function EstateListing({ apiUrl, locale }: Props) {
    const [result, setResult] = useState<PagedResponse | null>(null);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const controller = new AbortController();
        setLoading(true);
        setError(null);
        const sep = apiUrl.includes('?') ? '&' : '?';
        fetch(`${apiUrl}${sep}page=${page}`, { signal: controller.signal })
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
                setError('Помилка завантаження. Спробуйте пізніше.');
                setLoading(false);
            });
        return () => controller.abort();
    }, [apiUrl, page]);

    if (loading) return <p>Завантаження...</p>;
    if (error) return <p>{error}</p>;
    if (!result || result.data.length === 0) return <p>Оголошення не знайдено.</p>;

    const totalPages = Math.ceil(result.total / result.perPage);

    return (
        <div className="space-y-6">
            {result.data.map(estate => (
                <div key={estate.id} className="rounded border border-gray-200 shadow-sm overflow-hidden">
                    <div className="p-4">
                        <h2 className="text-lg font-semibold mb-3">
                            <a className="text-gray-800 hover:text-primary" href={`/${locale}/show_estate/${estate.slug}`}>
                                {estate.title}
                            </a>
                        </h2>
                        <div className="flex gap-4">
                            <div className="flex-1 min-w-0">
                                {estate.primaryImageUrl && (
                                    <a href={`/${locale}/show_estate/${estate.slug}`}>
                                        <img alt="фото нерухомості" src={estate.primaryImageUrl} className="w-full h-auto rounded" />
                                    </a>
                                )}
                            </div>
                            {estate.secondaryImageUrls.length > 0 && (
                                <div className="w-40 flex-shrink-0 space-y-2 hidden sm:block">
                                    {estate.secondaryImageUrls.map((url, i) => (
                                        <a key={i} href={`/${locale}/show_estate/${estate.slug}`}>
                                            <img alt="фото нерухомості" src={url} className="w-full h-auto rounded" />
                                        </a>
                                    ))}
                                </div>
                            )}
                        </div>
                        <h3 className="text-sm font-semibold text-gray-600 mt-3 mb-1">Опис:</h3>
                        <div className="text-sm text-gray-700">{estate.description}</div>
                        <div className="mt-3">
                            <a href={`/${locale}/show_estate/${estate.slug}`}
                               className="inline-block px-3 py-1.5 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Детальніше
                            </a>
                        </div>
                    </div>
                </div>
            ))}
            {totalPages > 1 && (
                <div className="flex gap-1 mt-4">
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
                        <button
                            key={p}
                            onClick={() => setPage(p)}
                            className={`px-3 py-1 text-sm rounded border ${p === page ? 'bg-gray-800 text-white border-gray-800' : 'border-gray-300 text-gray-700 hover:bg-gray-50'}`}
                        >
                            {p}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
