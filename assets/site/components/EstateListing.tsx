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
        <div className="panel">
            {result.data.map(estate => (
                <div key={estate.id}>
                    <h2>
                        <a className="grey" href={`/${locale}/show_estate/${estate.slug}`}>{estate.title}</a>
                    </h2>
                    <div className="row">
                        <div className="col col-sm-8">
                            {estate.primaryImageUrl && (
                                <a href={`/${locale}/show_estate/${estate.slug}`}>
                                    <img
                                        alt="фото нерухомості"
                                        src={estate.primaryImageUrl}
                                        className="img-responsive"
                                    />
                                </a>
                            )}
                        </div>
                        <div className="col col-sm-4">
                            {estate.secondaryImageUrls.map((url, i) => (
                                <React.Fragment key={i}>
                                    <a href={`/${locale}/show_estate/${estate.slug}`}>
                                        <img alt="фото нерухомості" src={url} className="img-responsive" />
                                    </a>
                                    <hr />
                                </React.Fragment>
                            ))}
                        </div>
                    </div>
                    <h3>Опис:</h3>
                    <p>{estate.description}</p>
                    <a href={`/${locale}/show_estate/${estate.slug}`} className="btn btn-default">
                        Детальніше
                    </a>
                    <hr />
                </div>
            ))}
            {totalPages > 1 && (
                <div className="navigation">
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
                        <button
                            key={p}
                            onClick={() => setPage(p)}
                            className={`btn btn-sm ${p === page ? 'btn-primary' : 'btn-default'}`}
                            style={{ margin: '0 2px' }}
                        >
                            {p}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
