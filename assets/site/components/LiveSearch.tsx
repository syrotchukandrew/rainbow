import React, { useEffect, useRef, useState } from 'react';

interface EstateResult {
    slug: string;
    title: string;
}

interface Props {
    placeholder: string;
    locale: string;
}

export default function LiveSearch({ placeholder, locale }: Props) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<EstateResult[]>([]);
    const [allEstates, setAllEstates] = useState<EstateResult[]>([]);
    const [open, setOpen] = useState(false);
    const wrapperRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        fetch('/livesearch')
            .then(r => r.json())
            .then((data: Record<string, string>) => {
                const estates = Object.entries(data).map(([slug, title]) => ({ slug, title }));
                setAllEstates(estates);
            })
            .catch(() => {});
    }, []);

    useEffect(() => {
        if (query.trim().length === 0) {
            setResults([]);
            setOpen(false);
            return;
        }
        const q = query.toLowerCase();
        const filtered = allEstates.filter(e => e.title.toLowerCase().includes(q)).slice(0, 10);
        setResults(filtered);
        setOpen(filtered.length > 0);
    }, [query, allEstates]);

    useEffect(() => {
        function handleClick(e: MouseEvent) {
            if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, []);

    return (
        <div ref={wrapperRef} style={{ position: 'relative' }} className="form-inline">
            <div className="input-group">
                <input
                    type="text"
                    className="form-control"
                    placeholder={placeholder}
                    value={query}
                    onChange={e => setQuery(e.target.value)}
                />
                <div className="input-group-btn">
                    <button type="button" className="btn btn-default">
                        <i className="glyphicon glyphicon-search" />
                    </button>
                </div>
            </div>
            {open && (
                <ul
                    className="dropdown-menu"
                    style={{ display: 'block', width: '100%', top: '100%', left: 0 }}
                >
                    {results.map(estate => (
                        <li key={estate.slug}>
                            <a href={`/${locale}/show_estate/${estate.slug}`}>{estate.title}</a>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
