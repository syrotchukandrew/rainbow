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
        fetch(`/${locale}/livesearch`)
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
        <div ref={wrapperRef} className="relative">
            <div className="flex">
                <input
                    type="text"
                    className="flex-1 border border-gray-300 rounded-l px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    placeholder={placeholder}
                    value={query}
                    onChange={e => setQuery(e.target.value)}
                />
                <button type="button" className="px-3 py-2 border border-l-0 border-gray-300 rounded-r bg-gray-50 text-gray-600 hover:bg-gray-100">
                    ⌕
                </button>
            </div>
            {open && (
                <ul className="absolute z-50 w-full top-full left-0 mt-0.5 bg-white border border-gray-200 rounded shadow-md text-sm">
                    {results.map(estate => (
                        <li key={estate.slug}>
                            <a href={`/${locale}/show_estate/${estate.slug}`} className="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                {estate.title}
                            </a>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
