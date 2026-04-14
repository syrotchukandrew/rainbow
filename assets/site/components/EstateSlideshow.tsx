import React, { useEffect, useState } from 'react';

interface Props {
    images: string[];
}

export default function EstateSlideshow({ images }: Props) {
    const [current, setCurrent] = useState(0);

    useEffect(() => {
        if (images.length <= 1) return;
        const timer = setInterval(() => {
            setCurrent(i => (i + 1) % images.length);
        }, 4000);
        return () => clearInterval(timer);
    }, [images.length]);

    if (images.length === 0) return null;

    return (
        <div className="relative mb-5">
            <img
                src={images[current]}
                alt=""
                className="w-full max-h-96 object-cover"
            />
            {images.length > 1 && (
                <div className="text-center mt-2">
                    <button
                        className="px-3 py-1 text-sm font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200"
                        onClick={() => setCurrent(i => (i - 1 + images.length) % images.length)}
                    >
                        ‹
                    </button>
                    <span className="mx-2">{current + 1} / {images.length}</span>
                    <button
                        className="px-3 py-1 text-sm font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200"
                        onClick={() => setCurrent(i => (i + 1) % images.length)}
                    >
                        ›
                    </button>
                </div>
            )}
        </div>
    );
}
