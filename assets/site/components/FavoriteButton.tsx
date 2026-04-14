import React, { useState } from 'react';

interface Props {
    slug: string;
    favorited: boolean;
    csrf: string;
    labelAdd: string;
    labelRemove: string;
}

export default function FavoriteButton({ slug, favorited: initialFavorited, csrf, labelAdd, labelRemove }: Props) {
    const [favorited, setFavorited] = useState(initialFavorited);
    const [loading, setLoading] = useState(false);

    async function toggle() {
        setLoading(true);
        try {
            const method = favorited ? 'DELETE' : 'POST';
            const response = await fetch(`/api/estate/${slug}/favorite`, {
                method,
                headers: { 'X-CSRF-Token': csrf },
            });
            if (response.ok) {
                const data = await response.json();
                setFavorited(data.favorited);
            }
        } finally {
            setLoading(false);
        }
    }

    return (
        <button
            type="button"
            className="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded bg-sky-100 text-sky-700 hover:bg-sky-200 disabled:opacity-50"
            onClick={toggle}
            disabled={loading}
        >
            ☆ {favorited ? labelRemove : labelAdd}
        </button>
    );
}
