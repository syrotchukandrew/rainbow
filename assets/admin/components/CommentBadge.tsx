import React, { useEffect, useState } from 'react';

interface Props {
    url: string;
}

export default function CommentBadge({ url }: Props) {
    const [count, setCount] = useState(0);

    useEffect(() => {
        function fetchCount() {
            fetch(url)
                .then(r => r.json())
                .then(data => setCount(data.count ?? 0))
                .catch(() => {});
        }

        fetchCount();
        const id = setInterval(fetchCount, 30_000);
        return () => clearInterval(id);
    }, [url]);

    if (count === 0) return null;

    return <span className="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-xs font-bold rounded-full bg-red-500 text-white">{count}</span>;
}
